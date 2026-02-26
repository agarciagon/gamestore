<?php
// app/Views/layouts/main.php
// Uso: include al principio y al final de cada vista cliente.
// Las vistas llaman: $layout = 'main'; y definen $pageTitle, $bodyClass, $scripts.
//
// Patron de uso en una vista:
//   ob_start();
//   ... contenido HTML ...
//   $content = ob_get_clean();
//   include BASE_PATH . '/app/Views/layouts/main.php';

// Variables con defaults
$pageTitle  = $pageTitle  ?? 'GameStore';
$bodyClass = 'flex flex-col min-h-screen ' . ($bodyClass ?? 'bg-gray-900 text-white');
$scripts    = $scripts    ?? [];
$cartCount  = $cartCount  ?? 0;
$rolActual  = $rolActual  ?? ($_SESSION['rol'] ?? 'invitado');
$nombre     = $_SESSION['user_name'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="/gamestore/public/dist/output.css">
    <link rel="stylesheet" href="/gamestore/public/assets/css/input.css">
    <title><?= htmlspecialchars($pageTitle) ?> — GameStore</title>
</head>

<body class="<?= $bodyClass ?>">

    <!-- ═══ NAVBAR ═══════════════════════════════════════════════════════════════ -->
    <nav class="bg-gray-800 sticky top-0 z-50">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">

                <!-- Logo + links -->
                <div class="flex items-center">
                    <a href="/gamestore/public/"><img class="h-22 w-24" src="/gamestore/public/assets/img/logo.png" alt="GameStore"></a>
                    <div class="hidden md:flex ml-8 space-x-2 items-center">
                        <a href="/gamestore/public/" class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Home</a>
                        <span class="text-gray-600">|</span>
                        <a href="/gamestore/public/catalogo" class="text-gray-300 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Catalog</a>
                        <form role="search" action="/gamestore/public/buscar" method="get" class="ml-4">
                            <div class="relative">
                                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
                                <input type="search" name="q" placeholder="Search products..."
                                    class="w-full pl-10 pr-4 py-2 text-gray-400 rounded-full bg-gray-100 border border-gray-200 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:outline-none transition">
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Right side -->
                <div class="hidden md:flex items-center gap-4">
                    <!-- Carrito -->
                    <a href="/gamestore/public/carrito" class="relative text-white">
                        <i class="fa-solid fa-cart-shopping text-xl"></i>
                        <?php if ($cartCount > 0): ?>
                            <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center font-bold"><?= $cartCount ?></span>
                        <?php endif; ?>
                    </a>

                    <?php if ($rolActual === 'invitado'): ?>
                        <a href="/gamestore/public/auth/login" class="text-gray-300 hover:text-white text-sm font-medium">Sign in</a>
                        <a href="/gamestore/public/auth/register" class="px-4 py-2 bg-sky-500 hover:bg-sky-600 text-white text-sm font-semibold rounded-md transition">Sign up</a>
                    <?php else: ?>
                        <!-- User dropdown -->
                        <div class="relative">
                            <button id="user-menu-button" type="button" class="flex rounded-full focus:outline-none focus:ring-2 focus:ring-white">
                                <img class="h-8 w-8 rounded-full"
                                    src="https://ui-avatars.com/api/?name=<?= urlencode($nombre) ?>&background=0D8ABC&color=fff"
                                    alt="<?= htmlspecialchars($nombre) ?>">
                            </button>
                            <div id="user-menu"
                                class="hidden absolute right-0 z-10 mt-2 w-48 rounded-md bg-white shadow-lg py-1 ring-1 ring-black/5">
                                <a href="/gamestore/public/perfil" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">My profile</a>
                                <a href="/gamestore/public/carrito" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">My cart</a>
                                <a href="/gamestore/public/perfil#orders" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">My orders</a>
                                <?php if ($rolActual === 'admin'): ?>
                                    <a href="/gamestore/public/admin" class="block px-4 py-2 text-sm text-indigo-700 hover:bg-indigo-50">Admin panel</a>
                                <?php endif; ?>
                                <hr class="my-1">
                                <a href="/gamestore/public/auth/logout" class="block px-4 py-2 text-sm text-red-700 hover:bg-red-50">Log out</a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Mobile burger -->
                <button id="mobile-menu-btn" class="md:hidden p-2 rounded-md text-gray-400 hover:text-white hover:bg-gray-700">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile menu completo -->
        <div id="mobile-menu" class="hidden md:hidden">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                <a href="/gamestore/public" class="text-gray-300 hover:bg-gray-700 hover:text-white block px-3 py-2 rounded-md text-base font-medium">Home</a>
                <a href="/gamestore/public/catalogo" class="text-gray-300 hover:bg-gray-700 hover:text-white block px-3 py-2 rounded-md text-base font-medium">Catalog</a>
                <form role="search" action="/gamestore/public/busquedas" method="get" class="ml-4 py-4">
                    <div class="relative">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
                        <input type="search" name="q" placeholder="Search products..."
                            class="w-full pl-10 pr-4 py-2 text-gray-400 rounded-full bg-gray-100 border border-gray-200 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:outline-none transition">
                    </div>
                </form>
            </div>

            <div class="border-t border-gray-700 pt-4 pb-3">
                <div class="flex items-center justify-end px-5 gap-4">

                    <!-- Carrito invitados mobile -->
                    <a href="/gamestore/public/carrito" class="relative flex items-center justify-end">
                        <i class="fa-solid fa-cart-shopping text-white text-xl"></i>
                        <?php if ($cartCount > 0): ?>
                            <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center font-bold"><?= $cartCount ?></span>
                        <?php endif; ?>
                    </a>
                    <?php if ($rolActual === 'invitado'): ?>
                        <a href="/gamestore/public/auth/login" class="text-gray-300 text-sm">Sign in</a>
                        <a href="/gamestore/public/auth/register" class="px-3 py-1.5 bg-sky-500 text-white text-sm font-semibold rounded-md">Sign up</a>
                    <?php else: ?>
                        <!-- User dropdown mobile -->
                        <div class="relative">
                            <button id="user-menu-button-mobile" type="button" class="flex rounded-full focus:outline-none focus:ring-2 focus:ring-white">
                                <img class="h-8 w-8 rounded-full"
                                    src="https://ui-avatars.com/api/?name=<?= urlencode($nombre) ?>&background=0D8ABC&color=fff"
                                    alt="<?= htmlspecialchars($nombre) ?>">
                            </button>
                            <div id="user-menu-mobile"
                                class="hidden absolute right-0 z-10 mt-2 w-48 rounded-md bg-white shadow-lg py-1 ring-1 ring-black/5">
                                <a href="/gamestore/public/perfil" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">My profile</a>
                                <a href="/gamestore/public/carrito" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">My cart</a>
                                <a href="/gamestore/public/perfil#orders" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">My orders</a>
                                <?php if ($rolActual === 'admin'): ?>
                                    <a href="/gamestore/public/admin" class="block px-4 py-2 text-sm text-indigo-700 hover:bg-indigo-50">Admin panel</a>
                                <?php endif; ?>
                                <hr class="my-1">
                                <a href="/gamestore/public/auth/logout" class="block px-4 py-2 text-sm text-red-700 hover:bg-red-50">Log out</a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- ═══ CONTENIDO ════════════════════════════════════════════════════════════ -->
    <main class="flex-1">
        <?= $content ?? '' ?>
    </main>

    <!-- ═══ FOOTER ═══════════════════════════════════════════════════════════════ -->
    <footer class="bg-gray-800 py-4 w-full">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-auto">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div>
                    <h5 class="text-violet-400 font-semibold mb-2">GameStore</h5>
                    <p class="text-gray-400 text-sm">Your video game purchasing platform.</p>
                </div>
                <div>
                    <h5 class="text-violet-400 font-semibold mb-2">Useful links</h5>
                    <ul class="space-y-1 text-sm">
                        <li><a href="/gamestore/public/" class="text-gray-400 hover:text-violet-400">Home</a></li>
                        <li><a href="/gamestore/public/catalogo" class="text-gray-400 hover:text-violet-400">Catalog</a></li>
                        <li><a href="/gamestore/public/auth/login" class="text-gray-400 hover:text-violet-400">Sign in</a></li>
                        <li><a href="/gamestore/public/auth/register" class="text-gray-400 hover:text-violet-400">Sign up</a></li>
                    </ul>
                </div>
                <div>
                    <h5 class="text-violet-400 font-semibold mb-2">Contact</h5>
                    <p class="text-gray-400 text-sm flex items-center gap-1 mb-1"><i class="fa-regular fa-envelope w-4"></i> soport@videogame.com</p>
                    <p class="text-gray-400 text-sm flex items-center gap-1 mb-1"><i class="fa-solid fa-location-dot w-4"></i> C/Francia, 34. Sevilla</p>
                    <p class="text-gray-400 text-sm flex items-center gap-1"><i class="fa-solid fa-phone w-4"></i> +34 600 123 456</p>
                </div>
            </div>
            <div class="border-t border-gray-700 pt-4 flex flex-col sm:flex-row items-center justify-between gap-3 py-2">
                <p class="text-gray-500 text-sm">&copy; <?= date('Y') ?> GameStore — All rights reserved</p>
                <div class="flex gap-4 text-lg">
                    <a href="#" class="text-gray-500 hover:text-violet-400"><i class="fa-brands fa-facebook"></i></a>
                    <a href="#" class="text-gray-500 hover:text-violet-400"><i class="fa-brands fa-instagram"></i></a>
                    <a href="#" class="text-gray-500 hover:text-violet-400"><i class="fa-brands fa-x-twitter"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <!-- ═══ SCRIPTS ═══════════════════════════════════════════════════════════════ -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    <script src="/gamestore/public/js/mobile_menu.js"></script>
    <script src="/gamestore/public/js/user_drop.js"></script>
    <script src="/gamestore/public/js/app.js"></script>
    
    <?php foreach ($scripts ?? [] as $src): ?>
        <script src="<?= htmlspecialchars($src) ?>"></script>
    <?php endforeach; ?>

</body>

</html>