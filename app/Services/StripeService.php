<?php
// app/Services/StripeService.php
// Integración simple con Stripe Checkout para total del carrito
// Requiere: composer require stripe/stripe-php

use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Webhook;
use Stripe\Event;

class StripeService
{
    public function __construct()
    {
        if (empty($_ENV['STRIPE_SECRET_KEY'])) {
            throw new \Exception("Stripe API key no configurada.");
        }
        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);
    }

    /**
     * Crea una sesión de checkout y devuelve la URL de redirección.
     * Calcula el total del carrito + IVA como un solo artículo para evitar redondeos.
     *
     * @param array $items Array de artículos: ['titulo' => string, 'precio' => float, 'cantidad' => int]
     * @param int $idUsuario ID del usuario
     * @return string URL de Stripe Checkout
     */
    public function crearSesion(array $items, int $idUsuario): string
    {
        $appUrl = rtrim($_ENV['APP_URL'] ?? 'http://localhost', '/');

        // 1️⃣ Calculamos subtotal
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += $item['precio'] * $item['cantidad'];
        }

        // 2️⃣ Calculamos IVA (21%)
        $iva = round($subtotal * 0.21, 2);

        // 3️⃣ Total final
        $total = round($subtotal + $iva, 2);

        // 4️⃣ Creamos line item único para Stripe
        $lineItems = [
            [
                'price_data' => [
                    'currency'     => 'eur',
                    'product_data' => ['name' => 'Total carrito'],
                    'unit_amount'  => (int) round($total * 100), // en céntimos
                ],
                'quantity' => 1,
            ]
        ];

        // 5️⃣ Creamos la sesión de checkout
        $session = StripeSession::create([
            'payment_method_types' => ['card'],
            'line_items'           => $lineItems,
            'mode'                 => 'payment',
            'metadata'             => ['id_usuario' => $idUsuario],
            'success_url'          => $appUrl . '/pago/success?sid={CHECKOUT_SESSION_ID}',
            'cancel_url'           => $appUrl . '/carrito',
            'payment_intent_data'  => [
                'statement_descriptor' => 'MiTienda Test', // descripción en extracto bancario
            ],
        ]);

        return $session->url;
    }

    /**
     * Verifica la firma del webhook y devuelve el evento Stripe.
     *
     * @param string $payload Raw body recibido
     * @param string $sigHeader Stripe-Signature
     * @return Event
     */
    public function verificarWebhook(string $payload, string $sigHeader): Event
    {
        return Webhook::constructEvent(
            $payload,
            $sigHeader,
            $_ENV['STRIPE_WEBHOOK_SECRET'] ?? ''
        );
    }
}
