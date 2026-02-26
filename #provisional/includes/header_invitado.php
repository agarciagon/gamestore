<?php
require_once("./config/conection.php");

// Solo carrito de invitados
$cartCount = 0;
if (!empty($_SESSION['carrito_guest'])) {
    $cartCount = array_sum($_SESSION['carrito_guest']);
}
?>
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
                <div class="hidden md:flex ml-10 space-x-4 items-center">
                    <a href="./index.php" class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Home</a> <span class="text-gray-300 self-center">|</span>
                    <a href="./cliente/catalogo.php" class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Catalog</a>
                    <form role="search" action="./cliente/search.php" method="get" class="ml-4">
                        <div class="relative">
                            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
                            <input type="search" name="q" placeholder="Search products..."
                                class="w-full pl-10 pr-4 py-2 rounded-full bg-gray-100 border border-gray-200 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:outline-none transition">
                        </div>
                    </form>
                </div>
            </div>

            <!-- Desktop right side -->
            <div class="hidden md:flex items-center space-x-4">

                <!-- Carrito invitados -->
                <a href="./cliente/shopping.php" class="relative flex items-center justify-center">
                    <i class="fa-solid fa-cart-shopping text-white text-xl"></i>
                    <?php if ($cartCount > 0): ?>
                        <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center font-bold">
                            <?= $cartCount ?>
                        </span>
                    <?php endif; ?>
                </a>

                <!-- Botones de invitado -->
                <a href="./auth/login.php" class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Sign in</a>
                <a href="./auth/register.php" class="flex items-center gap-2 px-4 py-2 bg-sky-500 text-white font-semibold rounded-md hover:bg-sky-600 transition">Sign up</a>

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
            <form role="search" action="./cliente/busquedas.php" method="get" class="ml-4 py-4">
                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
                    <input type="search" name="q" placeholder="Search products..."
                        class="w-full pl-10 pr-4 py-2 rounded-full bg-gray-100 border border-gray-200 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:outline-none transition">
                </div>
            </form>
        </div>

        <div class="border-t border-gray-700 pt-4 pb-3">
            <div class="flex items-center justify-end px-5 gap-4">

                <!-- Carrito invitados mobile -->
                <a href="./cliente/shopping.php" class="relative flex items-center justify-center">
                    <i class="fa-solid fa-cart-shopping text-white text-xl"></i>
                    <?php if ($cartCount > 0): ?>
                        <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center font-bold"><?= $cartCount ?></span>
                    <?php endif; ?>
                </a>

                <a href="./auth/login.php" class="text-gray-300 hover:bg-gray-700 hover:text-white block px-3 py-2 rounded-md text-base font-medium">Sign in</a>
                <a href="./auth/register.php" class="flex items-center gap-2 px-4 py-2 bg-sky-500 text-white font-semibold rounded-md hover:bg-sky-600 transition">Sign up</a>

            </div>
        </div>
    </div>
</nav>
