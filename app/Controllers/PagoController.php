<?php
// app/Controllers/PagoController.php

require_once BASE_PATH . '/app/Models/Carrito.php';
require_once BASE_PATH . '/app/Models/Videojuego.php';
require_once BASE_PATH . '/app/Models/Pedido.php';
require_once BASE_PATH . '/app/Models/Usuario.php';
require_once BASE_PATH . '/app/Services/StripeService.php';
require_once BASE_PATH . '/app/Services/MailService.php';
require_once BASE_PATH . '/app/Helpers/Auth.php';

// IVA aplicado a todos los precios (21 %)
const IVA = 1.21;

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

    // ── POST /cliente/checkout ────────────────────────────────────────────────
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

        // Aplicar IVA antes de enviar a Stripe
        $itemsConIva = array_map(function ($i) {
            $i['precio'] = round($i['precio'], 2);
            return $i;
        }, $items);

        try {
            $url = $this->stripe->crearSesion($itemsConIva, $uid);
            header('Location: ' . $url);
            exit;
        } catch (\Throwable $e) {
            error_log('[PagoController::checkout] ' . $e->getMessage());
            $_SESSION['error'] = 'Could not initiate payment. Please try again.';
            header('Location: /gamestore/public/carrito');
            exit;
        }
    }

    // ── POST /pago/webhook ────────────────────────────────────────────────────
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

        if ($event->type !== 'checkout.session.completed') {
            http_response_code(200);
            exit;
        }

        $session = $event->data->object;
        $sid     = $session->id;

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

        $carritoItems = $this->carrito->getByUsuario($uid);
        if (empty($carritoItems)) {
            error_log('[Webhook] Carrito vacio para uid=' . $uid . ', sid=' . $sid);
            http_response_code(200);
            exit;
        }

        // Guardar precio_unidad CON IVA incluido
        $pedidoItems = array_map(fn($i) => [
            'id_videojuego' => $i['id_videojuego'],
            'cantidad'      => $i['cantidad'],
            'precio_unidad' => round($i['precio'] * IVA, 2),
        ], $carritoItems);

        // Total CON IVA
        $total = round(array_reduce($carritoItems, fn($acc, $i) =>
            $acc + round($i['precio'] * IVA, 2) * $i['cantidad']
        , 0.0), 2);

        try {
            $idPedido = $this->pedidos->crear($uid, $total, $pedidoItems, $sid);
            $this->carrito->vaciar($uid);

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
            error_log('[Webhook] Pedido #' . $idPedido . ' creado. Total (IVA inc.): ' . $total);
        } catch (\Throwable $e) {
            error_log('[Webhook] Error creando pedido: ' . $e->getMessage());
            http_response_code(500);
            exit;
        }

        http_response_code(200);
        exit;
    }

    // ── GET /pago/success ─────────────────────────────────────────────────────
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