<?php
// app/Views/cliente/checkout_success.php
$pageTitle = 'Order Confirmed';
ob_start();
?>

<div class="max-w-2xl mx-auto px-4 sm:px-6 py-24 text-center">
    <div class="bg-gray-800 rounded-3xl p-8 sm:p-12 shadow-2xl">
        <!-- Icono de confirmación -->
        <div class="w-24 h-24 rounded-full bg-green-500/20 flex items-center justify-center mx-auto mb-8">
            <i class="fa-solid fa-check text-green-400 text-5xl"></i>
        </div>

        <!-- Título principal -->
        <h1 class="text-4xl font-extrabold text-white mb-4">Order Confirmed!</h1>

        <!-- Mensajes de pedido -->
        <?php if ($pedido): ?>
            <p class="text-gray-400 text-lg mb-2">Thank you for your purchase.</p>
            <p class="text-white font-semibold text-xl mb-8">
                Order #<?= $pedido['id'] ?> — <?= number_format($pedido['total'], 2) ?> €
            </p>
        <?php else: ?>
            <p class="text-gray-400 text-lg mb-8">
                Thank you for your purchase. A confirmation email has been sent.
            </p>
        <?php endif; ?>

        <!-- Botones -->
        <div class="flex flex-col sm:flex-row gap-4 sm:gap-6 justify-center mb-8 px-4 sm:px-0">
            <a href="/gamestore/public/perfil#orders" class="flex-1 sm:flex-none px-4 sm:px-8 py-4 bg-sky-600 hover:bg-sky-500 text-white font-semibold rounded-2xl transition-all shadow-md flex items-center justify-center">
                <i class="fa-solid fa-receipt inline-block mr-2"></i> View Orders
            </a>
            <a href="/gamestore/public/catalogo" class="flex-1 sm:flex-none px-4 sm:px-8 py-4 bg-gray-700 hover:bg-gray-600 text-white font-semibold rounded-2xl transition-all shadow-md flex items-center justify-center">
                <i class="fa-solid fa-gamepad inline-block mr-2"></i> Continue Shopping
            </a>
        </div>

        <!-- Espacio extra debajo de botones -->
        <div class="h-6"></div>
    </div>
</div>

<?php
$content = ob_get_clean();
include BASE_PATH . '/app/Views/layouts/main.php';
