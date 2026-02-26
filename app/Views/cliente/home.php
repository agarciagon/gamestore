<?php
// app/Views/cliente/home.php
$pageTitle = 'Home';
ob_start();
?>

<!-- Hero -->
<div class="relative bg-gradient-to-r from-violet-900 via-sky-900 to-cyan-900 overflow-hidden">
    <div class="max-w-7xl mx-auto py-12 sm:py-16 lg:py-24 px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
            <div class="z-10 text-center lg:text-left">
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-white mb-4">Welcome to Game Store</h1>
                <p class="text-lg sm:text-xl lg:text-2xl text-gray-300 mb-6 sm:mb-8">Discover amazing games at incredible prices</p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
                    <a href="/gamestore/public/catalogo" class="bg-cyan-950 hover:bg-cyan-900 text-white px-6 sm:px-8 py-3 sm:py-4 rounded-lg font-bold text-base sm:text-lg transition text-center">
                        <i class="fa-solid fa-gamepad mr-2"></i> Browse Catalog
                    </a>
                    <a href="#ofertas" class="bg-white/10 backdrop-blur-sm hover:bg-white/20 text-white px-6 sm:px-8 py-3 sm:py-4 rounded-lg font-bold text-base sm:text-lg transition border border-white/20 text-center">
                        <i class="fa-solid fa-fire mr-2"></i> View Deals
                    </a>
                </div>
            </div>
            <div class="order-first lg:order-last">
                <img src="/gamestore/public/assets/img/header.jpg" alt="Gaming"
                    class="rounded-2xl shadow-2xl w-full h-auto max-h-64 sm:max-h-80 lg:max-h-none object-cover">
            </div>
        </div>
    </div>
</div>

<!-- NEW RELEASES -->
<?php if (!empty($recientes)): ?>
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
        <div class="flex justify-between items-center mb-6 sm:mb-8">
            <h2 class="text-2xl sm:text-3xl font-bold text-white flex items-center gap-3">
                <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-emerald-500/20">
                    <i class="fa-solid fa-bolt text-emerald-400 text-base"></i>
                </span>
                New Releases
            </h2>
            <a href="/gamestore/public/catalogo" class="text-emerald-400 hover:text-emerald-300 text-sm sm:text-base transition">
                View All <i class="fa-solid fa-arrow-right ml-1"></i>
            </a>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
            <?php foreach ($recientes as $juego): ?>
                <a href="/gamestore/public/detalle?id=<?= $juego['id'] ?>"
                    class="group relative bg-gray-800 rounded-xl overflow-hidden hover:ring-2 hover:ring-emerald-500 transition-all">
                    <div class="absolute top-2 left-2 z-10 bg-emerald-500 text-white text-xs font-bold px-2 py-0.5 rounded-full shadow">NEW</div>
                    <div class="relative bg-gradient-to-br from-emerald-900 to-teal-900">
                        <?php if (!empty($juego['imagen_portada'])): ?>
                            <img src="/gamestore/public/assets/img/caratulas/<?= htmlspecialchars($juego['imagen_portada']) ?>"
                                alt="<?= htmlspecialchars($juego['titulo']) ?>"
                                class="w-full h-40 object-cover group-hover:brightness-110 transition">
                        <?php else: ?>
                            <div class="h-40 flex items-center justify-center">
                                <i class="fa-solid fa-gamepad text-white/20 text-4xl sm:text-6xl"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="p-3 sm:p-4">
                        <?php if ($juego['stock'] > 0): ?>
                            <span class="text-xs text-green-400"><i class="fa-solid fa-circle-check"></i> Available</span>
                        <?php else: ?>
                            <span class="text-xs text-red-400"><i class="fa-solid fa-circle-xmark"></i> Out of stock</span>
                        <?php endif; ?>
                        <h3 class="text-white font-semibold mt-1 mb-1 line-clamp-1 text-sm sm:text-base"><?= htmlspecialchars($juego['titulo']) ?></h3>
                        <p class="text-gray-400 text-xs sm:text-sm mb-2"><?= htmlspecialchars($juego['genero']) ?></p>
                        <p class="text-white text-lg sm:text-xl font-bold">
                            <?= $juego['precio'] > 0 ? number_format($juego['precio'], 2) . '€' : 'FREE' ?>
                        </p>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

<!-- TOP SELLERS -->
<?php if (!empty($topVentas)): ?>
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
        <div class="flex justify-between items-center mb-6 sm:mb-8">
            <h2 class="text-2xl sm:text-3xl font-bold text-white flex items-center gap-3">
                <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-orange-500/20">
                    <i class="fa-solid fa-fire text-orange-400 text-base"></i>
                </span>
                Best Sellers
            </h2>
            <a href="/gamestore/public/catalogo" class="text-orange-400 hover:text-orange-300 text-sm sm:text-base transition">
                View All <i class="fa-solid fa-arrow-right ml-1"></i>
            </a>
        </div>

        <div class="space-y-3">
            <?php
            $medalColors = [
                1 => ['bg' => 'bg-yellow-400',  'text' => 'text-yellow-900', 'ring' => 'hover:ring-yellow-400'],
                2 => ['bg' => 'bg-gray-400',    'text' => 'text-gray-900',   'ring' => 'hover:ring-gray-400'],
                3 => ['bg' => 'bg-orange-600',  'text' => 'text-orange-100', 'ring' => 'hover:ring-orange-600'],
            ];
            foreach ($topVentas as $rank => $juego):
                $pos    = $rank + 1;
                $medal  = $medalColors[$pos] ?? ['bg' => 'bg-gray-700', 'text' => 'text-gray-300', 'ring' => 'hover:ring-gray-600'];
            ?>
                <a href="/gamestore/public/detalle?id=<?= $juego['id'] ?>"
                    class="group flex items-center gap-4 bg-gray-800 rounded-xl p-3 sm:p-4 hover:ring-2 <?= $medal['ring'] ?> transition-all">

                    <!-- Posición -->
                    <div class="flex-shrink-0 w-10 h-10 rounded-full <?= $medal['bg'] ?> flex items-center justify-center">
                        <span class="font-black text-lg <?= $medal['text'] ?>"><?= $pos ?></span>
                    </div>

                    <!-- Imagen -->
                    <div class="flex-shrink-0 w-14 h-14 sm:w-16 sm:h-16 rounded-lg overflow-hidden bg-gray-700">
                        <?php if (!empty($juego['imagen_portada'])): ?>
                            <img src="/gamestore/public/assets/img/caratulas/<?= htmlspecialchars($juego['imagen_portada']) ?>"
                                alt="<?= htmlspecialchars($juego['titulo']) ?>"
                                class="w-full h-full object-cover group-hover:brightness-110 transition">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center">
                                <i class="fa-solid fa-gamepad text-gray-500 text-xl"></i>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Info -->
                    <div class="flex-1 min-w-0">
                        <h3 class="text-white font-semibold text-sm sm:text-base truncate"><?= htmlspecialchars($juego['titulo']) ?></h3>
                        <p class="text-gray-400 text-xs sm:text-sm"><?= htmlspecialchars($juego['genero']) ?> · <?= htmlspecialchars($juego['plataforma']) ?></p>
                        <p class="text-orange-400 text-xs mt-1">
                            <i class="fa-solid fa-bag-shopping mr-1"></i>
                            <?= $juego['total_vendidos'] ?> sold
                        </p>
                    </div>

                    <!-- Precio + stock -->
                    <div class="flex-shrink-0 text-right">
                        <p class="text-white font-bold text-base sm:text-lg">
                            <?= $juego['precio'] > 0 ? number_format($juego['precio'], 2) . '€' : 'FREE' ?>
                        </p>
                        <?php if ($juego['stock'] > 0): ?>
                            <span class="text-xs text-green-400"><i class="fa-solid fa-circle-check"></i> In stock</span>
                        <?php else: ?>
                            <span class="text-xs text-red-400"><i class="fa-solid fa-circle-xmark"></i> Out of stock</span>
                        <?php endif; ?>
                    </div>

                    <i class="fa-solid fa-chevron-right text-gray-600 group-hover:text-white transition flex-shrink-0 hidden sm:block"></i>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
<?php endif; ?>

<!-- GÉNEROS -->
<?php if (!empty($generos)): ?>
    <section class="py-14">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl sm:text-3xl font-bold text-white flex items-center gap-3 mb-6">
                <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-violet-500/20">
                    <i class="fa-solid fa-tags text-violet-400 text-base"></i>
                </span>
                Browse by Genre
            </h2>
            <div class="flex flex-wrap gap-3">
                <?php foreach ($generos as $g): ?>
                    <a href="/gamestore/public/catalogo?genero=<?= urlencode($g) ?>"
                        class="px-4 py-2 bg-gray-700 hover:bg-violet-700 text-gray-300 hover:text-white rounded-full text-sm font-medium transition">
                        <?= htmlspecialchars($g) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<!-- BEST DEALS -->
<section id="ofertas" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16">
    <h2 class="text-2xl sm:text-3xl font-bold text-white flex items-center gap-3 mb-6">
        <span class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-yellow-500/20">
            <i class="fa-solid fa-star text-yellow-400 text-base"></i>
        </span>
        Best Deals
    </h2>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
        <?php foreach ($ofertas ?? [] as $j): ?>
            <a href="/gamestore/public/detalle?id=<?= $j['id'] ?>" class="bg-gray-800 border-2 border-gray-800 rounded-xl overflow-hidden hover:border-yellow-400 transition group">
                <div class="relative">
                    <img src="/gamestore/public/assets/img/caratulas/<?= htmlspecialchars($j['imagen_portada']) ?>"
                        alt="<?= htmlspecialchars($j['titulo']) ?>"
                        class="w-full h-40 object-cover group-hover:brightness-110 transition">
                    <span class="absolute top-2 left-2 bg-yellow-500 text-gray-900 text-xs font-bold px-2 py-0.5 rounded">DEAL</span>
                </div>
                <div class="p-3">
                    <p class="text-white font-semibold text-sm truncate"><?= htmlspecialchars($j['titulo']) ?></p>
                    <p class="text-yellow-400 font-bold mt-2"><?= number_format($j['precio'], 2) ?>€</p>
                </div>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<?php
$content = ob_get_clean();
include BASE_PATH . '/app/Views/layouts/main.php';
