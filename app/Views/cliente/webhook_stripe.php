<?php
// public/webhook_stripe.php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../app/Models/Pedido.php';
require_once __DIR__ . '/../app/Models/Carrito.php';
require_once __DIR__ . '/../app/Models/Videojuego.php';
require_once __DIR__ . '/../app/Services/MailService.php';
require_once __DIR__ . '/../app/Services/StripeService.php';

$pdo = new PDO($_ENV['DB_DSN'], $_ENV['DB_USER'], $_ENV['DB_PASS']);
$mailer = new MailService();
$stripeService = new StripeService();
$pedidoModel = new Pedido($pdo);
$carritoModel = new Carrito($pdo);
$juegoModel = new Videojuego($pdo);

// Obtiene el payload y la cabecera
$payload = @file_get_contents('php://input');
$sigHeader = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

try {
    $event = $stripeService->verificarWebhook($payload, $sigHeader);

    if ($event->type === 'checkout.session.completed') {
        $session = $event->data->object;

        $stripeSid = $session->id;
        $idUsuario = (int) $session->metadata->id_usuario;

        // Evita duplicar pedidos
        if (!$pedidoModel->existeConStripeSid($stripeSid)) {

            // Recupera items del carrito de este usuario
            $itemsCart = $carritoModel->getByUsuario($idUsuario);
            $itemsPedido = [];
            $total = 0;

            foreach ($itemsCart as $item) {
                $itemsPedido[] = [
                    'id_videojuego' => $item['id_videojuego'],
                    'cantidad'      => $item['cantidad'],
                    'precio_unidad' => $item['precio'],
                ];
                $total += $item['precio'] * $item['cantidad'];
            }

            // Crear pedido
            $pedidoId = $pedidoModel->crear($idUsuario, $total, $itemsPedido, $stripeSid);

            // Limpiar carrito
            $carritoModel->vaciar($idUsuario);

            // Enviar correo
            $usuarioStmt = $pdo->prepare('SELECT nombre, email FROM usuarios WHERE id_usuario=?');
            $usuarioStmt->execute([$idUsuario]);
            $usuario = $usuarioStmt->fetch();

            if ($usuario) {
                $mailer->enviar(
                    $usuario['email'],
                    'Order Confirmation – GameStore',
                    'order_confirmation', // plantilla
                    [
                        'nombre' => $usuario['nombre'],
                        'pedido' => [
                            'id'    => $pedidoId,
                            'total' => $total,
                            'items' => $itemsPedido
                        ]
                    ]
                );
            }
        }
    }

    http_response_code(200);
    echo json_encode(['received' => true]);
} catch (\Throwable $e) {
    error_log('[Webhook Stripe] ' . $e->getMessage());
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
