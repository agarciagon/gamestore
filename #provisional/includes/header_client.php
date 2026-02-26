<?php
require_once("./config/conection.php");

$userId = $_SESSION['user_id'] ?? null;
$nombre = $_SESSION['user_name'] ?? 'usuario';

$cartCount = 0;

if ($userId) {
    $stmtCount = $pdo->prepare("SELECT SUM(cantidad) as total FROM carrito WHERE id_usuario = :id_usuario");
    $stmtCount->execute([':id_usuario' => $userId]);
    $cartCount = $stmtCount->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
}

?>

<!-- Navbar -->
<nav class="bg-gray-800">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-16 items-center justify-between">

            <!-- Logo + Links -->
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <a href="./index.php">
                        <img class="h-36 w-36" src="./assets/img/logo.png" alt="Logo">
                    </a>
                </div>
                <div class="hidden md:flex ml-10 space-x-4">
                    <a href="./index.php" class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium content-center">Home</a> <span class="text-gray-300 content-center">|</span>
                    <a href="./cliente/catalogo.php" class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium content-center">Catalog</a>

                    <!-- Buscador -->
                    <form role="search" action="./cliente/search.php" method="get" class="ml-4">
                        <div class="relative">
                            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 
                                text-gray-400 text-sm pointer-events-none">
                            </i>
                            <input type="search" name="q" placeholder="Search products..."
                                class="w-full pl-10 pr-4 py-2 rounded-full 
                                    bg-gray-100 border border-gray-200 
                                    focus:bg-white focus:ring-2 focus:ring-indigo-500 
                                    focus:outline-none transition">
                        </div>
                    </form>
                </div>
            </div>

            <!-- Button sig in and sign up -->
            <div class="hidden md:flex md:flex-row items-center space-x-4">
                <!-- Shopping -->
                <a href="./cliente/shopping.php" class="relative flex items-center justify-center">
                    <i class="fa-solid fa-cart-shopping text-white text-xl"></i>
                    <?php if ($cartCount > 0): ?>
                        <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center font-bold">
                            <?= $cartCount ?>
                        </span>
                    <?php endif; ?>

                </a>

                <!-- User -->
                <div class="relative">
                    <button id="user-menu-button" type="button"
                        class="relative flex rounded-full bg-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800"
                        aria-expanded="false" aria-haspopup="true">
                        <img class="h-8 w-8 rounded-full"
                            src="https://ui-avatars.com/api/?name=<?= urlencode($nombre) ?>&background=0D8ABC&color=fff"
                            alt="<?= htmlspecialchars($nombre) ?>">
                    </button>

                    <!-- Dropdown menu -->
                    <div id="user-menu"
                        class="hidden absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
                        role="menu" aria-orientation="vertical" aria-labelledby="user-menu-button">

                        <a href="./cliente/perfil.php"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                            role="menuitem">
                            My profile
                        </a>

                        <a href="./cliente/shopping.php"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                            role="menuitem">
                            My cart
                        </a>

                        <hr class="my-1 border-gray-200">

                        <a href="./auth/logout.php?from=cliente"
                            class="block px-4 py-2 text-sm text-red-700 hover:bg-red-50"
                            role="menuitem">
                            Log out
                        </a>
                    </div>
                </div>
            </div>

            <!-- Mobile menu button -->
            <div class="-mr-2 flex md:hidden">
                <button id="mobile-menu-btn" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div id="mobile-menu" class="hidden md:hidden">
        <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
            <a href="./index.php" class="text-gray-300 hover:bg-gray-700 hover:text-white block px-3 py-2 rounded-md text-base font-medium">Home</a>
            <a href="./cliente/catalogo.php" class="text-gray-300 hover:bg-gray-700 hover:text-white block px-3 py-2 rounded-md text-base font-medium">Catalog</a>

            <!-- Buscador -->
            <form role="search" action="./cliente/search.php" method="get" class="ml-4 py-4">
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 
                                text-gray-400 text-sm pointer-events-none">
                    </i>
                    <input type="search" name="q" placeholder="Buscar productos..."
                        class="w-full pl-10 pr-4 py-2 rounded-full 
                                    bg-gray-100 border border-gray-200 
                                    focus:bg-white focus:ring-2 focus:ring-indigo-500 
                                    focus:outline-none transition">
                </div>
            </form>
        </div>


        <div class="border-t border-gray-700 pt-4 pb-3 flex items-center space-x-4 px-4 justify-end">
            <!-- Shopping -->
            <a href="./cliente/shopping.php" class="relative flex items-center justify-center">
                <i class="fa-solid fa-cart-shopping text-white text-xl"></i>
                <?php if ($cartCount > 0): ?>
                    <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center font-bold">
                        <?= $cartCount ?>
                    </span>
                <?php endif; ?>

            </a>
            <!-- User -->
            <div class="relative">
                <button id="user-menu-button-mobile" type="button"
                    class="relative flex rounded-full bg-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800"
                    aria-expanded="false" aria-haspopup="true">
                    <img class="h-8 w-8 rounded-full"
                        src="https://ui-avatars.com/api/?name=<?= urlencode($nombre) ?>&background=0D8ABC&color=fff"
                        alt="<?= htmlspecialchars($nombre) ?>">
                </button>

                <!-- Dropdown menu -->
                <div id="user-menu-mobile"
                    class="hidden absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
                    role="menu" aria-orientation="vertical" aria-labelledby="user-menu-button">

                    <a href="./cliente/perfil.php"
                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                        role="menuitem">
                        My profile
                    </a>

                    <a href="./cliente/shopping.php"
                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                        role="menuitem">
                        My cart
                    </a>

                    <hr class="my-1 border-gray-200">

                    <a href="./auth/logout.php?from=cliente"
                        class="block px-4 py-2 text-sm text-red-700 hover:bg-red-50"
                        role="menuitem">
                        Log out
                    </a>
                </div>
            </div>
        </div>
    </div>
</nav>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="./js/user_drop.js"></script>