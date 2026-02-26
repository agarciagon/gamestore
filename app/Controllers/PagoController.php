<?php
// app/Controllers/PagoController.php
// Gestiona la integracion completa con Stripe:
//   - checkout()  → crea la sesion de Stripe y redirige
//   - webhook()   → recibe el evento de Stripe, crea el pedido, vacia carrito, envia email
//   - success()   → pagina de confirmacion (solo informativa, la logica va en webhook)

require_once BASE_PATH . '/app/Models/Carrito.php';
require_once BASE_PATH . '/app/Models/Videojuego.php';
require_once BASE_PATH . '/app/Models/Pedido.php';
require_once BASE_PATH . '/app/Models/Usuario.php';
require_once BASE_PATH . '/app/Services/StripeService.php';
require_once BASE_PATH . '/app/Services/MailService.php';
require_once BASE_PATH . '/app/Helpers/Auth.php';

class PagoController
{
    private Carrito       $carrito;
    private Videojuego    $juegos;
    private Pedido        $pedidos;
    private Usuario       $usuarios;
    private StripeService $stripe;
    private MailService   $mailer;

    public function __construct(private PDO $pdo)
    {
        $this->carrito  = new Carrito($pdo);
        $this->juegos   = new Videojuego($pdo);
        $this->pedidos  = new Pedido($pdo);
        $this->usuarios = new Usuario($pdo);
        $this->stripe   = new StripeService();
        $this->mailer   = new MailService();
    }

    // ── POST /cliente/checkout.php ───────────────────────────────────────────
    // Solo usuarios logueados pueden pagar
    public function checkout(): void
    {
        Auth::requireLogin();

        $uid   = (int) $_SESSION['user_id'];
        $items = $this->carrito->getByUsuario($uid);

        if (empty($items)) {
            $_SESSION['error'] = 'Your cart is empty.';
            header('Location: /gamestore/public/carrito');
            exit;
        }

        try {
            $url = $this->stripe->crearSesion($items, $uid);
            header('Location: ' . $url);
            exit;
        } catch (\Throwable $e) {
            error_log('[PagoController::checkout] ' . $e->getMessage());
            $_SESSION['error'] = 'Could not initiate payment. Please try again.';
            header('Location: /gamestore/public/carrito');
            exit;
        }
    }

    // ── POST /cliente/webhook.php ─────────────────────────────────────────────
    // IMPORTANTE: este endpoint NO debe tener session_start ni output previo.
    // Stripe envia el raw body — hay que leerlo antes de cualquier otro procesamiento.
    // Registrar en Stripe Dashboard: https://dashboard.stripe.com/webhooks
    // O en local: stripe listen --forward-to http://localhost/CRUD_AGG/cliente/webhook.php
    public function webhook(): void
    {
        $payload   = file_get_contents('php://input');
        $sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

        try {
            $event = $this->stripe->verificarWebhook($payload, $sigHeader);
        } catch (\Throwable $e) {
            error_log('[Webhook] Firma invalida: ' . $e->getMessage());
            http_response_code(400);
            exit;
        }

        // Solo nos interesa el evento de pago completado
        if ($event->type !== 'checkout.session.completed') {
            http_response_code(200);
            exit;
        }

        $session = $event->data->object;
        $sid     = $session->id;

        // Idempotencia: si ya procesamos este sid, ignorar
        if ($this->pedidos->existeConStripeSid($sid)) {
            http_response_code(200);
            exit;
        }

        $uid     = (int) ($session->metadata->id_usuario ?? 0);
        $usuario = $this->usuarios->findById($uid);
        if (!$usuario || $uid <= 0) {
            error_log('[Webhook] Usuario no encontrado para sid=' . $sid);
            http_response_code(200);
            exit;
        }

        // Construir items del pedido desde el carrito BD
        $carritoItems = $this->carrito->getByUsuario($uid);
        if (empty($carritoItems)) {
            error_log('[Webhook] Carrito vacio para uid=' . $uid . ', sid=' . $sid);
            http_response_code(200);
            exit;
        }

        $pedidoItems = array_map(fn($i) => [
            'id_videojuego' => $i['id_videojuego'],
            'cantidad'      => $i['cantidad'],
            'precio_unidad' => $i['precio'],
        ], $carritoItems);

        $total = array_reduce($carritoItems, fn($acc, $i) => $acc + ($i['precio'] * $i['cantidad']), 0.0);

        try {
            $idPedido = $this->pedidos->crear($uid, $total, $pedidoItems, $sid);

            // Vaciar carrito (el stock ya estaba decrementado al anadir al carrito)
            $this->carrito->vaciar($uid);

            // Enviar email de confirmacion
            $pedidoConItems = $this->pedidos->findWithItems($idPedido);
            $this->mailer->enviar(
                $usuario['email'],
                "Order #{$idPedido} confirmed – GameStore",
                'confirmacion_compra',
                [
                    'nombre'    => $usuario['nombre'],
                    'pedido_id' => $idPedido,
                    'items'     => $pedidoConItems['items'],
                    'total'     => $total,
                ]
            );

            error_log('[Webhook] Pedido #' . $idPedido . ' creado para uid=' . $uid);
        } catch (\Throwable $e) {
            error_log('[Webhook] Error creando pedido: ' . $e->getMessage());
            http_response_code(500);
            exit;
        }

        http_response_code(200);
        exit;
    }

    // ── GET /cliente/checkout_success.php ────────────────────────────────────
    // Pagina de "gracias" — SOLO informativa.
    // La logica real (crear pedido, vaciar carrito, email) ya ocurrio en webhook().
    public function success(): array
    {
        Auth::requireLogin();
        $sid    = trim($_GET['sid'] ?? '');
        $pedido = $sid ? $this->pedidos->findByStripeSid($sid) : null;

        return [
            'pedido'    => $pedido,
            'cartCount' => 0,
            'rolActual' => $_SESSION['rol'] ?? 'invitado',
        ];
    }
}
