<?php
// app/Views/cliente/shopping.php
$pageTitle = 'My Cart';
$impuesto  = ($total ?? 0) * 0.21;
$totalFinal = ($total ?? 0) + $impuesto;
ob_start();
?>

<?php if ($toastSuccess): ?>
    <div id="toast-success" style="position:fixed;bottom:1.5rem;left:50%;transform:translateX(-50%);z-index:9999;display:flex;align-items:center;gap:.75rem;background:#16a34a;color:#fff;padding:1rem 1.25rem;border-radius:.75rem;box-shadow:0 10px 25px rgba(0,0,0,.4);white-space:nowrap;">
        <i class="fa-solid fa-circle-check text-xl"></i>
        <span class="font-semibold"><?= htmlspecialchars($toastSuccess) ?></span>
        <button onclick="dismissToast('toast-success')" style="background:none;border:none;color:rgba(255,255,255,.7);cursor:pointer;margin-left:.5rem;"><i class="fa-solid fa-times"></i></button>
    </div>
<?php endif; ?>
<?php if ($toastError): ?>
    <div id="toast-error" style="position:fixed;bottom:1.5rem;left:50%;transform:translateX(-50%);z-index:9999;display:flex;align-items:center;gap:.75rem;background:#dc2626;color:#fff;padding:1rem 1.25rem;border-radius:.75rem;box-shadow:0 10px 25px rgba(0,0,0,.4);white-space:nowrap;">
        <i class="fa-solid fa-circle-xmark text-xl"></i>
        <span class="font-semibold"><?= htmlspecialchars($toastError) ?></span>
        <button onclick="dismissToast('toast-error')" style="background:none;border:none;color:rgba(255,255,255,.7);cursor:pointer;margin-left:.5rem;"><i class="fa-solid fa-times"></i></button>
    </div>
<?php endif; ?>

<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8" style="min-height: calc(100vh - 400px);">
    <h1 class="text-3xl font-bold text-white mb-8 flex items-center gap-3 mt-8">
        <i class="fa-solid fa-cart-shopping text-sky-400"></i> Shopping Cart
        <span class="text-lg text-gray-400 font-normal">(<?= count($items ?? []) ?> items)</span>
    </h1>

    <?php if (empty($items)): ?>
        <div class="text-center py-20 bg-gray-800 rounded-2xl pt-8 pb-8">
            <i class="fa-solid fa-cart-shopping text-6xl text-gray-600 mb-4"></i>
            <p class="text-xl text-gray-400 mb-6">Your cart is empty</p>
            <a href="/gamestore/public/catalogo" class="px-6 py-3 bg-sky-600 hover:bg-sky-500 text-white font-semibold rounded-lg transition">Browse Catalog</a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            <!-- Items -->
            <div class="lg:col-span-2 space-y-4">
                <?php foreach ($items as $item): ?>
                    <div class="bg-gray-800 rounded-xl p-4 flex gap-4 items-center">
                        <img src="/gamestore/public/assets/img/caratulas/<?= htmlspecialchars($item['imagen_portada']) ?>"
                            alt="<?= htmlspecialchars($item['titulo']) ?>"
                            class="w-20 h-20 object-cover rounded-lg flex-shrink-0">

                        <!-- Qty controls (solo usuarios logueados) -->
                        <div class="flex-1 min-w-0">
                            <a href="/gamestore/public/detalle?id=<?= $item['id_videojuego'] ?? $item['id'] ?>" class="text-white font-semibold hover:text-sky-400 transition truncate block">
                                <?= htmlspecialchars($item['titulo']) ?>
                            </a>
                            <p class="text-gray-400 text-sm mt-0.5"><?= htmlspecialchars($item['plataforma'] ?? '') ?></p>
                            <p id="precio-<?= $item['id_videojuego'] ?? $item['id'] ?>"
                                data-precio="<?= $item['precio'] ?>"
                                class="text-sky-400 font-bold mt-1"><?= number_format($item['precio'], 2) ?>€</p>
                        </div>

                        <!-- Qty controls -->
                        <div class="flex items-center gap-2">
                            <button onclick="updateQty(<?= $item['id_videojuego'] ?? $item['id'] ?>, 'decrease')"
                                class="w-7 h-7 rounded-full bg-gray-700 hover:bg-gray-600 text-white text-sm font-bold transition">−</button>

                            <span id="qty-<?= $item['id_videojuego'] ?? $item['id'] ?>" class="text-white font-semibold w-6 text-center">
                                <?= $item['cantidad'] ?>
                            </span>

                            <button onclick="updateQty(<?= $item['id_videojuego'] ?? $item['id'] ?>, 'increase')"
                                class="w-7 h-7 rounded-full bg-gray-700 hover:bg-gray-600 text-white text-sm font-bold transition">+</button>
                        </div>

                        <p id="subtotal-<?= $item['id_videojuego'] ?? $item['id'] ?>" class="text-white font-bold w-20 text-right">
                            <?= number_format($item['precio'] * $item['cantidad'], 2) ?>€
                        </p>

                        <!-- Remove -->
                        <form id="remove-form-<?= $item['id_videojuego'] ?? $item['id'] ?>" method="POST" action="/gamestore/public/carrito/remove">
                            <input type="hidden" name="id_videojuego" value="<?= $item['id_videojuego'] ?? $item['id'] ?>">
                            <button type="button"
                                onclick="confirmRemove(<?= $item['id_videojuego'] ?? $item['id'] ?>, '<?= addslashes($item['titulo']) ?>')"
                                class="text-gray-500 hover:text-red-400 transition mr-2" style="cursor:pointer;">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Summary -->
            <div class="bg-gray-800 rounded-xl p-6 h-fit sticky top-20 mb-12">
                <h2 class="text-lg font-bold text-white mb-4">Order Summary</h2>
                <div class="space-y-2 text-sm text-gray-400">
                    <div class="flex justify-between">
                        <span>Subtotal</span>
                        <span id="summary-subtotal" class="text-white"><?= number_format($total, 2) ?>€</span>
                    </div>
                    <div class="flex justify-between">
                        <span>TAX (21%)</span>
                        <span id="summary-vat" class="text-white"><?= number_format($impuesto, 2) ?>€</span>
                    </div>
                    <hr class="border-gray-700 my-2">
                    <div class="flex justify-between text-base font-bold text-white">
                        <span>Total</span>
                        <span id="summary-total" class="text-sky-400"><?= number_format($totalFinal, 2) ?>€</span>
                    </div>
                </div>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <form method="POST" action="/gamestore/public/pago/checkout" class="mt-6">
                        <button type="submit"
                            class="w-full py-3 bg-sky-600 hover:bg-sky-500 text-white font-bold rounded-lg transition flex items-center justify-center gap-2">
                            <i class="fa-solid fa-lock"></i> Checkout
                        </button>
                    </form>
                <?php else: ?>
                    <a href="/gamestore/public/auth/login" class="mt-6 block text-center py-3 bg-sky-600 hover:bg-sky-500 text-white font-bold rounded-lg transition">
                        Sign in to checkout
                    </a>
                <?php endif; ?>
                <a href="/gamestore/public/catalogo" class="block text-center text-gray-400 hover:text-white text-sm mt-3 transition">
                    ← Continue shopping
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>
<script src="/gamestore/public/js/amount.js"></script>
<?php
$content = ob_get_clean();
include BASE_PATH . '/app/Views/layouts/main.php';
