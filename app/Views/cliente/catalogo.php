<?php
// app/Views/cliente/catalogo.php
$pageTitle = 'Catalog';
$filtros   = $filtros ?? [];
ob_start();
?>

<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">

    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-8">
        <h1 class="text-3xl font-bold text-white flex items-center gap-2">
            <i class="fa-solid fa-gamepad text-sky-400"></i> Game Catalog
            <span class="text-lg text-gray-400 font-normal">(<?= count($juegos) ?> games)</span>
        </h1>
    </div>

    <!-- Barra de filtros horizontal -->
    <div class="bg-gray-800 rounded-xl p-4 mb-6 border border-gray-700">
        <form method="GET" action="/gamestore/public/catalogo"
              class="flex flex-wrap gap-3 items-end">

            <!-- Género -->
            <div class="min-w-[130px]">
                <label class="block text-xs text-gray-400 mb-1">Genre</label>
                <select name="genero"
                        class="w-full px-3 py-2 rounded-lg bg-gray-700 border border-gray-600 text-white text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
                    <option value="">All genres</option>
                    <?php foreach ($generos as $g): ?>
                        <option value="<?= htmlspecialchars($g) ?>"
                            <?= ($filtros['genero'] ?? '') === $g ? 'selected' : '' ?>>
                            <?= htmlspecialchars($g) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Plataforma -->
            <div class="min-w-[140px]">
                <label class="block text-xs text-gray-400 mb-1">Platform</label>
                <select name="plataforma"
                        class="w-full px-3 py-2 rounded-lg bg-gray-700 border border-gray-600 text-white text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
                    <option value="">All platforms</option>
                    <?php foreach ($plataformas as $p): ?>
                        <option value="<?= htmlspecialchars($p) ?>"
                            <?= ($filtros['plataforma'] ?? '') === $p ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Orden -->
            <div class="min-w-[160px]">
                <label class="block text-xs text-gray-400 mb-1">Sort by</label>
                <select name="orden"
                        class="w-full px-3 py-2 rounded-lg bg-gray-700 border border-gray-600 text-white text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
                    <option value="">Latest</option>
                    <option value="precio_asc"  <?= ($filtros['orden'] ?? '') === 'precio_asc'  ? 'selected' : '' ?>>Price: Low → High</option>
                    <option value="precio_desc" <?= ($filtros['orden'] ?? '') === 'precio_desc' ? 'selected' : '' ?>>Price: High → Low</option>
                    <option value="nombre"      <?= ($filtros['orden'] ?? '') === 'nombre'      ? 'selected' : '' ?>>Name A–Z</option>
                </select>
            </div>

            <!-- Precio máx -->
            <div class="min-w-[110px]">
                <label class="block text-xs text-gray-400 mb-1">Max Price (€)</label>
                <input type="number" name="precio_max" min="0" step="1"
                       value="<?= htmlspecialchars($filtros['precio_max'] ?? '') ?>"
                       placeholder="No limit"
                       class="w-full px-3 py-2 rounded-lg bg-gray-700 border border-gray-600 text-white text-sm focus:outline-none focus:ring-2 focus:ring-sky-500">
            </div>

            <!-- Botón aplicar -->
            <div class="flex gap-2">
                <button type="submit"
                        class="px-5 py-2 bg-sky-500 hover:bg-sky-400 text-white text-sm font-semibold rounded-lg transition">
                    <i class="fa-solid fa-filter mr-1"></i> Apply
                </button>
                <a href="/gamestore/public/catalogo"
                   class="px-4 py-2 bg-gray-700 hover:bg-red-600 text-white text-sm font-semibold rounded-lg transition flex items-center">
                    <i class="fa-solid fa-xmark"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Grid de juegos -->
    <?php if (empty($juegos)): ?>
        <div class="text-center py-20 bg-gray-800 rounded-2xl">
            <i class="fa-solid fa-magnifying-glass text-5xl text-gray-600 mb-4 block"></i>
            <p class="text-xl text-gray-400 mb-4">No games found</p>
            <a href="/gamestore/public/catalogo"
               class="inline-block px-6 py-2 bg-sky-600 hover:bg-sky-500 text-white rounded-lg font-semibold transition">
                Clear filters
            </a>
        </div>
    <?php else: ?>
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 sm:gap-5">
            <?php foreach ($juegos as $j): ?>
                <a href="/gamestore/public/detalle?id=<?= $j['id'] ?>"
                   class="group bg-gray-800 rounded-xl overflow-hidden hover:ring-2 hover:ring-sky-500 hover:scale-[1.02] transition-all flex flex-col">

                    <!-- Imagen -->
                    <div class="relative bg-gradient-to-br from-cyan-900 to-blue-900">
                        <?php if (!empty($j['imagen_portada'])): ?>
                            <img src="/gamestore/public/assets/img/caratulas/<?= htmlspecialchars($j['imagen_portada']) ?>"
                                 alt="<?= htmlspecialchars($j['titulo']) ?>"
                                 class="w-full h-40 object-cover group-hover:brightness-110 transition">
                        <?php else: ?>
                            <div class="h-40 flex items-center justify-center">
                                <i class="fa-solid fa-gamepad text-white/20 text-4xl sm:text-6xl"></i>
                            </div>
                        <?php endif; ?>

                        <?php if ($j['precio'] == 0): ?>
                            <span class="absolute top-2 left-2 bg-green-500 text-white text-xs font-bold px-2 py-0.5 rounded">FREE</span>
                        <?php endif; ?>

                        <?php if ($j['stock'] <= 0): ?>
                            <div class="absolute inset-0 bg-black/50 flex items-center justify-center">
                                <span class="bg-red-600 text-white text-xs font-bold px-3 py-1 rounded-full">Out of stock</span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Info -->
                    <div class="p-3 flex flex-col flex-1">
                        <p class="text-white font-semibold text-sm truncate"><?= htmlspecialchars($j['titulo']) ?></p>
                        <p class="text-gray-400 text-xs mt-0.5 truncate">
                            <?= htmlspecialchars($j['genero']) ?>
                            <?php if (!empty($j['plataforma'])): ?>
                                · <?= htmlspecialchars($j['plataforma']) ?>
                            <?php endif; ?>
                        </p>
                        <p class="mt-auto pt-3 font-bold <?= $j['precio'] > 0 ? 'text-sky-400' : 'text-green-400' ?> text-base">
                            <?= $j['precio'] > 0 ? number_format($j['precio'], 2) . '€' : 'Free' ?>
                        </p>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

<?php
$content = ob_get_clean();
include BASE_PATH . '/app/Views/layouts/main.php';