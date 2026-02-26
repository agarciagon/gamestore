<?php
// app/Views/cliente/catalogo.php
$pageTitle = 'Catalog';
$filtros   = $filtros ?? [];
ob_start();
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">

    <!-- Header + buscador -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-8">
        <h1 class="text-3xl font-bold text-white flex items-center gap-2">
            <i class="fa-solid fa-gamepad text-sky-400"></i> Game Catalog
            <span class="text-lg text-gray-400 font-normal">(<?= count($juegos) ?> games)</span>
        </h1>
    </div>

    <div class="flex flex-col lg:flex-row gap-8">

        <!-- Sidebar filtros -->
        <aside class="w-full flex-shrink-0">
            <div class="flex items-center gap-3 w-full sm:w-auto">

                <!-- Botón Filtros -->
                <div class="relative">
                    <button id="filterToggle"
                        class="bg-gray-800 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 transition text-sm">
                        <i class="fa-solid fa-sliders text-blue-400"></i>
                        <span>Filters</span>
                        <i class="fa-solid fa-chevron-down text-xs"></i>
                    </button>

                    <!-- Panel Filtros -->
                    <div id="filterDropdown"
                        class="hidden absolute left-0 mt-3 w-[90vw] sm:w-80 bg-gray-800 border border-gray-700 rounded-xl shadow-2xl p-4 sm:p-6 z-50 max-h-[70vh] overflow-y-auto">
                        <form method="GET" action="/gamestore/public/catalogo" class="space-y-4">

                            <!-- Search -->
                            <div>
                                <label class="block text-xs text-gray-400 mb-1">Search</label>
                                <input type="search" name="q" value="<?= htmlspecialchars($filtros['q'] ?? '') ?>"
                                    placeholder="Title, developer…"
                                    class="w-full px-3 py-2 rounded-lg bg-gray-700 border border-gray-600 text-white text-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-sky-500">
                            </div>

                            <!-- Género -->
                            <div>
                                <h3 class="text-sm font-semibold text-gray-400 mb-3">
                                    <i class="fa-solid fa-gamepad text-blue-400"></i> Genre
                                </h3>
                                <select name="genero" class="w-full px-3 py-2 rounded-lg bg-gray-700 border border-gray-600 text-white text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
                                    <option value="">All genres</option>
                                    <?php foreach ($generos as $g): ?>
                                        <option value="<?= htmlspecialchars($g) ?>" <?= ($filtros['genero'] ?? '') === $g ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($g) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Plataforma -->
                            <div>
                                <h3 class="text-sm font-semibold text-gray-400 mb-3">
                                    <i class="fa-solid fa-desktop text-green-400"></i> Platform
                                </h3>
                                <select name="plataforma" class="w-full px-3 py-2 rounded-lg bg-gray-700 border border-gray-600 text-white text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
                                    <option value="">All platforms</option>
                                    <?php foreach ($plataformas as $p): ?>
                                        <option value="<?= htmlspecialchars($p) ?>" <?= ($filtros['plataforma'] ?? '') === $p ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($p) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Sort -->
                            <div>
                                <label class="block text-xs text-gray-400 mb-1">Sort by</label>
                                <select name="orden" class="w-full px-3 py-2 rounded-lg bg-gray-700 border border-gray-600 text-white text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
                                    <option value="">Latest</option>
                                    <option value="precio_asc" <?= ($filtros['orden'] ?? '') === 'precio_asc'  ? 'selected' : '' ?>>Price: Low to High</option>
                                    <option value="precio_desc" <?= ($filtros['orden'] ?? '') === 'precio_desc' ? 'selected' : '' ?>>Price: High to Low</option>
                                    <option value="nombre" <?= ($filtros['orden'] ?? '') === 'nombre'      ? 'selected' : '' ?>>Name A–Z</option>
                                </select>
                            </div>

                            <!-- Precio máx -->
                            <div>
                                <label class="block text-xs text-gray-400 mb-1">Max Price (€)</label>
                                <input type="number" name="precio_max" min="0" step="1" value="<?= htmlspecialchars($filtros['precio_max'] ?? '') ?>"
                                    placeholder="No limit"
                                    class="w-full px-3 py-2 rounded-lg bg-gray-700 border border-gray-600 text-white text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
                            </div>

                            <!-- Botones -->
                            <div class="flex gap-3 pt-4 border-t border-gray-700">
                                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg transition text-sm font-semibold">
                                    Apply
                                </button>
                                <a href="/gamestore/public/catalogo" class="flex-1 text-center bg-gray-700 hover:bg-gray-600 text-white py-2 rounded-lg transition text-sm font-semibold">
                                    Clear
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Grid de juegos -->
        <div class="flex-1">
            <?php if (empty($juegos)): ?>
                <div class="text-center py-20 bg-gray-800 rounded-2xl">
                    <i class="fa-solid fa-search text-5xl text-gray-600 mt-4 mb-4"></i>
                    <p class="text-xl text-gray-400">No games found</p>
                    <a href="/gamestore/public/catalogo" class="mt-4 inline-block text-sky-400 hover:text-sky-300 mb-4">Clear filters</a>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                    <?php foreach ($juegos as $j): ?>
                        <a href="/gamestore/public/detalle?id=<?= $j['id'] ?>"
                            class="bg-gray-800 rounded-xl overflow-hidden hover:scale-[1.03] transition-transform group flex flex-col">
                            <div class="relative">
                                <img src="/gamestore/public/assets/img/caratulas/<?= htmlspecialchars($j['imagen_portada']) ?>"
                                    alt="<?= htmlspecialchars($j['titulo']) ?>"
                                    class="w-full h-40 object-cover group-hover:brightness-110 transition">
                                <?php if ($j['precio'] == 0): ?>
                                    <span class="absolute top-2 left-2 bg-green-500 text-white text-xs font-bold px-2 py-0.5 rounded">FREE</span>
                                <?php endif; ?>
                                <?php if ($j['stock'] <= 0): ?>
                                    <span class="absolute top-2 right-2 bg-red-600 text-white text-xs font-bold px-2 py-0.5 rounded">OUT OF STOCK</span>
                                <?php endif; ?>
                            </div>
                            <div class="p-3 flex flex-col flex-1">
                                <p class="text-white font-semibold text-sm truncate"><?= htmlspecialchars($j['titulo']) ?></p>
                                <p class="text-gray-400 text-xs mt-0.5 truncate"><?= htmlspecialchars($j['genero']) ?> · <?= htmlspecialchars($j['plataforma']) ?></p>
                                <p class="mt-auto pt-3 font-bold <?= $j['precio'] > 0 ? 'text-sky-400' : 'text-green-400' ?>">
                                    <?= $j['precio'] > 0 ? number_format($j['precio'], 2) . '€' : 'Free' ?>
                                </p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<script src="/gamestore/public/js/filters.js"></script>
<?php
$content = ob_get_clean();
include BASE_PATH . '/app/Views/layouts/main.php';
