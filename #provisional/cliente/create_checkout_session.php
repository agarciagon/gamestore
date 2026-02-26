<?php
require_once '../vendor/autoload.php'; // Stripe PHP SDK
session_start();
require_once '../config/conection.php';

\Stripe\Stripe::setApiKey('sk_test_TU_SECRET_KEY'); // tu secret key

$cart = $_SESSION['carrito_guest'] ?? [];
if (empty($cart)) {
    http_response_code(400);
    echo json_encode(['error' => 'Cart is empty']);
    exit;
}

// Construir items para Stripe
$line_items = [];
foreach ($cart as $id_juego => $cantidad) {
    $stmt = $pdo->prepare("SELECT nombre, precio FROM videojuego WHERE id = ?");
    $stmt->execute([$id_juego]);
    $juego = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$juego) continue;

    $line_items[] = [
        'price_data' => [
            'currency' => 'usd',
            'product_data' => [
                'name' => $juego['nombre'],
            ],
            'unit_amount' => $juego['precio'] * 100, // en centavos
        ],
        'quantity' => $cantidad,
    ];
}

// Crear sesión de checkout
$checkout_session = \Stripe\Checkout\Session::create([
    'payment_method_types' => ['card'],
    'line_items' => $line_items,
    'mode' => 'payment',
    'success_url' => 'http://localhost/proyecto/success.php?session_id={CHECKOUT_SESSION_ID}',
    'cancel_url' => 'http://localhost/proyecto/cancel.php',
]);

echo json_encode(['id' => $checkout_session->id]);
