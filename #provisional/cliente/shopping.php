<?php
session_start();
require_once("../config/conection.php");

// Usuario
$userId = $_SESSION['user_id'] ?? null;
$nombre = $_SESSION['user_name'] ?? null;

// Inicializar carrito
$items = [];
$totalItems = 0;
$subtotal = 0;

if ($userId) {
    // Usuario logueado → carrito desde BD
    $stmt = $pdo->prepare("
        SELECT c.*, v.titulo, v.precio, v.stock, v.imagen_portada, v.genero, v.plataforma, v.desarrollador
        FROM carrito c
        JOIN videojuego v ON c.id_videojuego = v.id
        WHERE c.id_usuario = :id_usuario
        ORDER BY c.fecha_agregado DESC
    ");
    $stmt->execute([':id_usuario' => $userId]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} elseif (!empty($_SESSION['carrito_guest'])) {
    // Invitado → carrito en sesión
    foreach ($_SESSION['carrito_guest'] as $idJuego => $cantidad) {
        $stmt = $pdo->prepare("SELECT * FROM videojuego WHERE id = :id");
        $stmt->execute([':id' => $idJuego]);
        $juego = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($juego) {
            $juego['cantidad'] = $cantidad;
            $items[] = $juego;
        }
    }
}

// Totales
foreach ($items as $item) {
    $subtotal += $item['precio'] * $item['cantidad'];
    $totalItems += $item['cantidad'];
}

$impuesto = $subtotal * 0.21;
$total = $subtotal + $impuesto;
?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="../dist/output.css">
    <title>Shopping Cart - Game Store</title>
</head>

<body class="bg-gray-900">
    <!-- Navbar -->
    <nav class="bg-gray-800">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <a href="../index.php">
                            <img class="h-36 w-36" src="../assets/img/logo.png" alt="Logo">
                        </a>
                    </div>
                    <div class="hidden md:flex ml-10 space-x-4">
                        <a href="../index.php" class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Home</a>
                        <span class="text-gray-300 content-center">|</span>
                        <a href="../cliente/catalogo.php" class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Catalog</a>
                    </div>
                </div>

                <div class="hidden md:flex items-center space-x-4">

                    <!-- Carrito -->
                    <a href="../cliente/shopping.php" class="relative flex items-center justify-center">
                        <i class="fa-solid fa-cart-shopping text-white text-xl"></i>
                        <?php if ($totalItems > 0): ?>
                            <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center font-bold">
                                <?= $totalItems ?>
                            </span>
                        <?php endif; ?>
                    </a>

                    <?php if ($userId): ?>
                        <!-- Usuario logueado -->
                        <div class="relative">
                            <button id="user-menu-button" type="button"
                                class="relative flex rounded-full bg-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-white">
                                <img class="h-8 w-8 rounded-full"
                                    src="https://ui-avatars.com/api/?name=<?= urlencode($nombre) ?>&background=0D8ABC&color=fff"
                                    alt="<?= htmlspecialchars($nombre) ?>">
                            </button>

                            <div id="user-menu" class="hidden absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg">
                                <a href="../cliente/perfil.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">My profile</a>
                                <a href="../cliente/shopping.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">My cart</a>
                                <hr class="my-1 border-gray-200">
                                <a href="../auth/logout.php?from=cliente" class="block px-4 py-2 text-sm text-red-700 hover:bg-red-50">Log out</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Invitado -->
                        <a href="../auth/login.php"
                            class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium">
                            Sign in
                        </a>
                        <a href="../auth/register.php"
                            class="flex items-center gap-2 px-4 py-2 bg-sky-500 text-white font-semibold rounded-md hover:bg-sky-600 transition">
                            Sign up
                        </a>
                    <?php endif; ?>

                </div>

                <!-- Mobile button -->
                <div class="-mr-2 flex md:hidden">
                    <button id="mobile-menu-btn" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-white hover:bg-gray-700">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobile-menu" class="hidden md:hidden">
            <div class="px-2 pt-2 pb-3 space-y-1">
                <a href="../index.php" class="text-gray-300 hover:bg-gray-700 hover:text-white block px-3 py-2 rounded-md text-base font-medium">Home</a>
                <a href="../cliente/catalogo.php" class="text-gray-300 hover:bg-gray-700 hover:text-white block px-3 py-2 rounded-md text-base font-medium">Catalog</a>
            </div>
            <div class="border-t border-gray-700 pt-4 pb-3 flex items-center space-x-4 px-4 justify-end">
                <!-- Shopping -->
                <a href="../cliente/shopping.php" class="relative">
                    <i class="fa-solid fa-cart-shopping text-white text-xl"></i>
                    <?php if ($totalItems > 0): ?>
                        <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center font-bold">
                            <?= $totalItems ?>
                        </span>
                    <?php endif; ?>
                </a>

                <!-- User -->
                <?php if ($userId): ?>
                    <!-- Usuario logueado -->
                    <div class="relative">
                        <button id="user-menu-button-mobile" type="button"
                            class="relative flex rounded-full bg-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-white">
                            <img class="h-8 w-8 rounded-full"
                                src="https://ui-avatars.com/api/?name=<?= urlencode($nombre) ?>&background=0D8ABC&color=fff"
                                alt="<?= htmlspecialchars($nombre) ?>">
                        </button>

                        <div id="user-menu-mobile" class="hidden absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg">
                            <a href="../cliente/perfil.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">My profile</a>
                            <a href="../cliente/shopping.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">My cart</a>
                            <hr class="my-1 border-gray-200">
                            <a href="../auth/logout.php?from=cliente" class="block px-4 py-2 text-sm text-red-700 hover:bg-red-50">Log out</a>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Invitado -->
                    <a href="../auth/login.php"
                        class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium">
                        Sign in
                    </a>
                    <a href="../auth/register.php"
                        class="flex items-center gap-2 px-4 py-2 bg-sky-500 text-white font-semibold rounded-md hover:bg-sky-600 transition">
                        Sign up
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Header -->
    <div class="bg-gradient-to-r from-violet-900 via-sky-900 to-cyan-900 py-8 sm:py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl sm:text-4xl font-bold text-white flex items-center gap-3">
                <i class="fa-solid fa-shopping-cart"></i>
                Shopping Cart
            </h1>
            <p class="text-gray-300 mt-2 text-sm sm:text-base"><?= count($items) ?> items in your cart</p>
        </div>
    </div>

    <!-- TOAST -->
    <?php if (isset($_SESSION['success'])): ?>
        <div id="toast-success" style="position:fixed;bottom:1.5rem;left:50%;transform:translateX(-50%);z-index:9999;display:flex;align-items:center;gap:.75rem;background:#16a34a;color:#fff;padding:1rem 1.25rem;border-radius:.75rem;box-shadow:0 10px 25px rgba(0,0,0,.4);transition:opacity .4s,transform .4s;white-space:nowrap;">
            <i class="fa-solid fa-circle-check" style="font-size:1.25rem;"></i>
            <span style="font-weight:600;"><?= htmlspecialchars($_SESSION['success']) ?></span>
            <button onclick="dismissToast('toast-success')" style="background:none;border:none;color:rgba(255,255,255,.7);cursor:pointer;margin-left:.5rem;">
                <i class="fa-solid fa-times"></i>
            </button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <!-- Contenido -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
        <?php if (count($items) > 0): ?>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 sm:gap-8">

                <!-- Lista de items -->
                <div class="lg:col-span-2 space-y-4">
                    <?php foreach ($items as $item): ?>
                        <div class="bg-gray-800 rounded-xl p-4 sm:p-6 hover:ring-2 hover:ring-blue-500 transition">
                            <div class="flex gap-4 sm:gap-6">
                                <!-- Imagen -->
                                <div class="flex-shrink-0">
                                    <div class="w-20 h-20 sm:w-32 sm:h-32 rounded-lg overflow-hidden bg-gradient-to-br from-purple-900 to-blue-900">
                                        <?php
                                        $rutaImg = '../assets/img/caratulas/' . $item['imagen_portada'];
                                        if (!empty($item['imagen_portada']) && file_exists($rutaImg)): ?>
                                            <img src="<?= htmlspecialchars($rutaImg) ?>" alt="<?= htmlspecialchars($item['titulo']) ?>" class="w-full h-full object-cover">
                                        <?php else: ?>
                                            <div class="w-full h-full flex items-center justify-center">
                                                <i class="fa-solid fa-gamepad text-white/20 text-2xl sm:text-4xl"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Info -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex justify-between items-start mb-2 gap-2">
                                        <div class="flex-1 min-w-0">
                                            <h3 class="text-base sm:text-xl font-bold text-white mb-1 truncate">
                                                <a href="detalle_juego.php?id=<?= $item['id_videojuego'] ?? $item['id'] ?>" class="hover:text-blue-400">
                                                    <?= htmlspecialchars($item['titulo']) ?>
                                                </a>
                                            </h3>
                                            <p class="text-gray-400 text-xs sm:text-sm truncate"><?= htmlspecialchars($item['desarrollador'] ?? '') ?></p>
                                        </div>

                                        <!-- Botón eliminar -->
                                        <form id="remove-form-<?= $item['id_videojuego'] ?? $item['id'] ?>" action="remove_from_cart.php" method="POST">
                                            <input type="hidden" name="id_videojuego" value="<?= $item['id_videojuego'] ?? $item['id'] ?>">
                                            <button type="button"
                                                onclick="confirmRemove(<?= $item['id_videojuego'] ?? $item['id'] ?>, '<?= htmlspecialchars($item['titulo'], ENT_QUOTES) ?>')"
                                                class="text-red-400 hover:text-red-300 p-2 hover:bg-red-500/10 rounded-lg transition">
                                                <i class="fa-solid fa-trash text-sm"></i>
                                            </button>
                                        </form>
                                    </div>

                                    <div class="flex items-center gap-2 mb-3 flex-wrap">
                                        <span class="text-[10px] sm:text-xs bg-blue-900/50 text-blue-300 px-2 py-1 rounded"><?= htmlspecialchars($item['genero'] ?? '') ?></span>
                                        <span class="text-[10px] sm:text-xs bg-gray-700 text-gray-300 px-2 py-1 rounded"><?= htmlspecialchars($item['plataforma'] ?? '') ?></span>
                                    </div>

                                    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
                                        <!-- Cantidad -->
                                        <form action="update_cart.php" method="POST" class="flex items-center gap-2">
                                            <input type="hidden" name="id_videojuego" value="<?= $item['id_videojuego'] ?? $item['id'] ?>">
                                            <label class="text-gray-400 text-xs sm:text-sm">Qty:</label>
                                            <div class="flex items-center bg-gray-700 rounded-lg">
                                                <button type="submit" name="action" value="decrease" class="px-2 sm:px-3 py-1.5 hover:bg-gray-600 rounded-l-lg transition">
                                                    <i class="fa-solid fa-minus text-white text-xs"></i>
                                                </button>
                                                <span class="px-3 sm:px-4 py-1.5 text-white font-bold text-sm"><?= $item['cantidad'] ?></span>
                                                <button type="submit" name="action" value="increase" class="px-2 sm:px-3 py-1.5 hover:bg-gray-600 rounded-r-lg transition" <?= ($item['cantidad'] >= ($item['stock'] ?? 99)) ? 'disabled class="opacity-50 cursor-not-allowed"' : '' ?>>
                                                    <i class="fa-solid fa-plus text-white text-xs"></i>
                                                </button>
                                            </div>
                                        </form>

                                        <!-- Precio -->
                                        <div class="text-left sm:text-right">
                                            <p class="text-xl sm:text-2xl font-bold text-white"><?= number_format($item['precio'] * $item['cantidad'], 2) ?>€</p>
                                            <?php if ($item['cantidad'] > 1): ?>
                                                <p class="text-gray-400 text-xs sm:text-sm"><?= number_format($item['precio'], 2) ?>€ each</p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Resumen -->
                <div class="lg:col-span-1">
                    <div class="bg-gray-800 rounded-xl p-4 sm:p-6 sticky top-4">
                        <h2 class="text-xl sm:text-2xl font-bold text-white mb-4 sm:mb-6">Order Summary</h2>

                        <div class="space-y-2 sm:space-y-3 mb-4 sm:mb-6">
                            <div class="flex justify-between text-gray-300 text-sm sm:text-base"><span>Subtotal:</span><span class="font-semibold"><?= number_format($subtotal, 2) ?>€</span></div>
                            <div class="flex justify-between text-gray-300 text-sm sm:text-base"><span>VAT (21%):</span><span class="font-semibold"><?= number_format($impuesto, 2) ?>€</span></div>
                            <div class="border-t border-gray-700 pt-2 sm:pt-3"></div>
                            <div class="flex justify-between text-white text-lg sm:text-xl font-bold"><span>Total:</span><span><?= number_format($total, 2) ?>€</span></div>
                        </div>
                        <button class="w-full bg-violet-700 hover:bg-violet-500 text-white py-3 sm:py-4 rounded-lg font-bold text-base sm:text-lg transition mb-3">
                            <i class="fa-solid fa-credit-card mr-2"></i> Proceed to Checkout
                        </button>
                        <a href="../cliente/catalogo.php" class="block w-full text-center bg-gray-700 hover:bg-gray-600 text-white py-2 sm:py-3 rounded-lg font-semibold transition text-sm sm:text-base">
                            <i class="fa-solid fa-arrow-left mr-2"></i> Continue Shopping
                        </a>
                        <div class="mt-4 sm:mt-6 p-3 sm:p-4 bg-blue-900/30 rounded-lg border border-blue-800">
                            <p class="text-blue-300 text-xs sm:text-sm flex items-start gap-2">
                                <i class="fa-solid fa-shield-halved text-base sm:text-lg mt-0.5"></i>
                                <span>Secure checkout. Your payment information is protected.</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <!-- Carrito vacío -->
            <div class="text-center py-16 sm:py-24">
                <div class="mb-6">
                    <i class="fa-solid fa-cart-shopping text-gray-600 text-6xl sm:text-8xl"></i>
                </div>
                <h2 class="text-2xl sm:text-3xl font-bold text-white mb-4">Your cart is empty</h2>
                <p class="text-gray-400 mb-6 sm:mb-8 text-sm sm:text-base">Add some games to get started!</p>
                <a href="../cliente/catalogo.php" class="inline-block bg-violet-700 text-white px-8 sm:px-10 py-3 sm:py-4 rounded-lg font-bold text-base sm:text-lg hover:bg-violet-500 transition">
                    <i class="fa-solid fa-gamepad mr-2"></i> Browse Catalog
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <?php include "../includes/footer.php"; ?>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="../js/user_drop.js"></script>
    <script src="../js/mobile_menu.js"></script>
    <script src="../js/modal.js"></script>
    <script src="../js/amount.js"></script>
    <script src="../js/toast.js"></script>
</body>

</html>