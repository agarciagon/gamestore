<?php
// app/Views/cliente/detalle.php
$pageTitle = htmlspecialchars($juego['titulo'] ?? 'Game Detail');
ob_start();
?>

<?php if ($toastSuccess): ?>
    <div id="toast-success" style="position:fixed;bottom:1.5rem;left:1rem;right:1rem;transform:none;z-index:9999;display:flex;align-items:flex-start;gap:.75rem;background:#16a34a;color:#fff;padding:1rem 1.25rem;border-radius:.75rem;box-shadow:0 10px 25px rgba(0,0,0,.4);"> 
        <i class="fa-solid fa-circle-check text-xl"></i>
        <span class="font-semibold"><?= htmlspecialchars($toastSuccess) ?></span>
        <button onclick="dismissToast('toast-success')" style="background:none;border:none;color:rgba(255,255,255,.7);
            cursor:pointer;margin-left:.5rem;"><i class="fa-solid fa-times"></i></button>
    </div>
<?php endif; ?>
<?php if ($toastError): ?>
    <div id="toast-error" style="position:fixed;bottom:1.5rem;left:50%;transform:translateX(-50%);z-index:9999;display:flex;align-items:center;gap:.75rem;background:#dc2626;color:#fff;padding:1rem 1.25rem;border-radius:.75rem;box-shadow:0 10px 25px rgba(0,0,0,.4);white-space:nowrap;">
        <i class="fa-solid fa-circle-xmark text-xl"></i>
        <span class="font-semibold"><?= htmlspecialchars($toastError) ?></span>
        <button onclick="dismissToast('toast-error')" style="background:none;border:none;color:rgba(255,255,255,.7);
        cursor:pointer;margin-left:.5rem;"><i class="fa-solid fa-times"></i></button>
    </div>
<?php endif; ?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">

    <!-- Breadcrumb -->
    <nav class="relative z-50 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3 text-gray-400 text-xs">
        <a href="/gamestore/public" class="hover:text-gray-200">Home</a>
        <span>/</span>
        <a href="/gamestore/public/catalogo" class="hover:text-gray-200">Catalog</a>
        <span>/</span>
        <span class="text-gray-200 truncate max-w-xs"><?= htmlspecialchars($juego['titulo']) ?></span>
    </nav>

    <!-- Main grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 mb-14">

        <!-- Portada + screenshots -->
        <div>
            <!-- Imagen principal -->
            <img id="mainImage"
                src="/gamestore/public/assets/img/caratulas/<?= htmlspecialchars($juego['imagen_portada']) ?>"
                alt="<?= htmlspecialchars($juego['titulo']) ?>"
                onclick="toggleScreen(this)"
                class="screenshot rounded-2xl shadow-2xl mb-4 object-cover w-full cursor-pointer transition-all duration-300"
                style="height: 380px;">

            <?php if (!empty($screenshots)): ?>
                <div class="grid grid-row gap-2">
                    <?php foreach ($screenshots as $ss): ?>
                        <img src="/gamestore/public/assets/img/screenshots/<?= htmlspecialchars($ss['nombre_archivo']) ?>"
                            alt="Screenshot"
                            onclick="toggleScreen(this)"
                            class="screenshot rounded-lg object-cover w-full cursor-pointer hover:opacity-80 transition-all duration-300"
                            style="height: 64px;">
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Info + compra  -->
        <div class="flex flex-col ml-4 mt-4">
            <h1 class="text-3xl sm:text-4xl font-bold text-white mb-4"><?= htmlspecialchars($juego['titulo']) ?></h1>

            <div class="flex flex-wrap gap-2 mb-4">
                <span class="px-3 py-1 bg-gray-700 text-gray-300 rounded-full text-xs font-medium"><?= htmlspecialchars($juego['genero']) ?></span>
                <span class="px-3 py-1 bg-gray-700 text-gray-300 rounded-full text-xs font-medium"><?= htmlspecialchars($juego['plataforma']) ?></span>
                <?php if ($juego['fecha_lanzamiento']): ?>
                    <span class="px-3 py-1 bg-gray-700 text-gray-300 rounded-full text-xs font-medium">
                        <i class="fa-regular fa-calendar mr-1"></i><?= date('Y', strtotime($juego['fecha_lanzamiento'])) ?>
                    </span>
                <?php endif; ?>
            </div>

            <p class="text-gray-300 leading-relaxed mb-6 flex-1"><?= nl2br(htmlspecialchars($juego['descripcion'])) ?></p>

            <!-- Desarrollador -->
            <?php if ($juego['desarrollador']): ?>
                <p class="text-sm text-gray-400 mb-6">
                    <i class="fa-solid fa-building mr-2"></i>
                    Developed by <span class="text-white font-medium"><?= htmlspecialchars($juego['desarrollador']) ?></span>
                </p>
            <?php endif; ?>

            <!-- Features -->
            <?php if (!empty($juego['feature'])): ?>
                <div class="flex flex-wrap gap-2 mb-6">
                    <?php foreach (explode(',', $juego['feature']) as $feat): ?>
                        <span class="px-2 py-1 border border-gray-600 text-gray-400 rounded text-xs">
                            <?= htmlspecialchars(trim($feat)) ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Precio + stock + botón -->
            <div class="bg-gray-800 rounded-xl p-5 mt-auto mb-4">
                <div class="flex items-center justify-between mt-4 mb-4 px-4">
                    <div>
                        <p class="text-3xl font-bold <?= $juego['precio'] > 0 ? 'text-sky-400' : 'text-green-400' ?>">
                            <?= $juego['precio'] > 0 ? number_format($juego['precio'], 2) . '€' : 'Free' ?>
                        </p>
                        <?php if (!$sinStock): ?>
                            <p class="text-xs text-gray-400 mt-1">
                                <i class="fa-solid fa-boxes-stacked mr-1"></i><?= $stockDisponible ?> in stock
                            </p>
                        <?php else: ?>
                            <p class="text-xs text-red-400 mt-1"><i class="fa-solid fa-circle-xmark"></i> Out of stock</p>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (!$sinStock): ?>
                    <form method="POST" action="/gamestore/public/carrito/add" class="flex gap-3 px-4">
                        <select name="cantidad" class="w-20 px-2 py-2 rounded-lg bg-gray-700 border border-gray-600 text-white text-sm focus:outline-none focus:ring-2 focus:ring-sky-500 mb-4">
                            <?php for ($i = 1; $i <= min($stockDisponible, 10); $i++): ?>
                                <option value="<?= $i ?>"><?= $i ?></option>
                            <?php endfor; ?>
                        </select>
                        <input type="hidden" name="id_juego" value="<?= $juego['id'] ?>">
                        <button type="submit"
                            class="flex-1 py-2 bg-sky-600 hover:bg-sky-500 text-white font-semibold rounded-lg transition flex items-center justify-center gap-2 mb-4">
                            <i class="fa-solid fa-cart-plus"></i> Add to Cart
                        </button>
                    </form>
                <?php else: ?>
                    <div class="px-4">
                        <button disabled class="w-full py-2 bg-gray-700 text-gray-500 font-semibold rounded-lg cursor-not-allowed mb-4">
                            Out of Stock
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Juegos relacionados -->
    <?php if (!empty($relacionados)): ?>
        <section class="mb-8">
            <h2 class="text-xl font-bold text-white mb-5 flex items-center gap-2 mt-8 mb-4">
                <i class="fa-solid fa-shuffle text-sky-400"></i> You might also like
            </h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                <?php foreach ($relacionados as $r): ?>
                    <a href="/gamestore/public/detalle?id=<?= $r['id'] ?>" class="bg-gray-800 rounded-xl overflow-hidden hover:scale-105 transition-transform group">
                        <img src="/gamestore/public/assets/img/caratulas/<?= htmlspecialchars($r['imagen_portada']) ?>"
                            alt="<?= htmlspecialchars($r['titulo']) ?>"
                            class="w-full h-32 object-cover group-hover:brightness-110 transition">
                        <div class="p-3">
                            <p class="text-white text-sm font-medium truncate"><?= htmlspecialchars($r['titulo']) ?></p>
                            <p class="text-sky-400 font-bold text-sm mt-1">
                                <?= $r['precio'] > 0 ? number_format($r['precio'], 2) . '€' : '<span class="text-green-400">Free</span>' ?>
                            </p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

</div>

<script src="/gamestore/public/js/change_img.js"></script>

<?php
$content = ob_get_clean();
include BASE_PATH . '/app/Views/layouts/main.php';
