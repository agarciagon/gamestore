<?php
session_start();
require_once("../config/conection.php");

$nombre    = $_SESSION['user_name'] ?? 'user';
$rolActual = $_SESSION['rol'] ?? 'guest';
$userId    = $_SESSION['user_id'] ?? null;

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: ../index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM videojuego WHERE id = :id");
$stmt->execute([':id' => $id]);
$juego = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$juego) {
    header('Location: ../index.php');
    exit;
}

$stmtRelacionados = $pdo->prepare("SELECT * FROM videojuego WHERE genero = :genero AND id != :id LIMIT 4");
$stmtRelacionados->execute([':genero' => $juego['genero'], ':id' => $id]);
$relacionados = $stmtRelacionados->fetchAll(PDO::FETCH_ASSOC);

// ─── STOCK DISPONIBLE ─────────────────────────────────────────────────────────
// Logged users: stock in DB is already decremented when added to cart.
// Guests: stock in DB is not touched; available stock = DB stock - guest session qty.
if ($userId) {
    $stockDisponible = (int)$juego['stock'];
} else {
    $reservadaGuest  = (int)($_SESSION['carrito_guest'][$juego['id']] ?? 0);
    $stockDisponible = max(0, (int)$juego['stock'] - $reservadaGuest);
}
$sinStock = $stockDisponible <= 0;

// ─── CART COUNT ───────────────────────────────────────────────────────────────
$cartCount = 0;
if ($userId) {
    $stmtCart = $pdo->prepare("SELECT SUM(cantidad) as total FROM carrito WHERE id_usuario = :id_usuario");
    $stmtCart->execute([':id_usuario' => $userId]);
    $cartCount = $stmtCart->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
} elseif (!empty($_SESSION['carrito_guest'])) {
    $cartCount = array_sum($_SESSION['carrito_guest']);
}

// ─── TOASTS ───────────────────────────────────────────────────────────────────
$toastSuccess = $_SESSION['success'] ?? null;
$toastError   = $_SESSION['error'] ?? null;
unset($_SESSION['success'], $_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="../dist/output.css">
    <title><?= htmlspecialchars($juego['titulo']) ?> - Game Store</title>
</head>

<body class="bg-gray-900">

    <!-- TOASTS ─────────────────────────────────────────────────────────────────── -->
    <?php if ($toastSuccess): ?>
        <div id="toast-success" style="position:fixed;bottom:1.5rem;left:50%;transform:translateX(-50%);z-index:9999;display:flex;align-items:center;gap:.75rem;background:#16a34a;color:#fff;padding:1rem 1.25rem;border-radius:.75rem;box-shadow:0 10px 25px rgba(0,0,0,.4);transition:opacity .4s,transform .4s;white-space:nowrap;">
            <i class="fa-solid fa-circle-check" style="font-size:1.25rem;"></i>
            <span style="font-weight:600;"><?= htmlspecialchars($toastSuccess) ?></span>
            <button onclick="dismissToast('toast-success')" style="background:none;border:none;color:rgba(255,255,255,.7);cursor:pointer;margin-left:.5rem;">
                <i class="fa-solid fa-times"></i>
            </button>
        </div>
    <?php endif; ?>
    <?php if ($toastError): ?>
        <div id="toast-error" style="position:fixed;bottom:1.5rem;left:50%;transform:translateX(-50%);z-index:9999;display:flex;align-items:center;gap:.75rem;background:#dc2626;color:#fff;padding:1rem 1.25rem;border-radius:.75rem;box-shadow:0 10px 25px rgba(0,0,0,.4);transition:opacity .4s,transform .4s;white-space:nowrap;">
            <i class="fa-solid fa-circle-xmark" style="font-size:1.25rem;"></i>
            <span style="font-weight:600;"><?= htmlspecialchars($toastError) ?></span>
            <button onclick="dismissToast('toast-error')" style="background:none;border:none;color:rgba(255,255,255,.7);cursor:pointer;margin-left:.5rem;">
                <i class="fa-solid fa-times"></i>
            </button>
        </div>
    <?php endif; ?>

    <!-- Navbar ─────────────────────────────────────────────────────────────────── -->
    <nav class="bg-gray-800 sticky top-0 z-50">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <a href="../index.php"><img class="h-36 w-36" src="../assets/img/logo.png" alt="Logo"></a>
                    </div>
                    <div class="hidden md:flex ml-10 space-x-4">
                        <a href="../index.php" class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Home</a>
                        <span class="text-gray-300 content-center">|</span>
                        <a href="../cliente/catalogo.php" class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Catalog</a>
                    </div>
                </div>
                <div class="hidden md:flex items-center space-x-4">
                    <?php if ($userId): ?>
                    <a href="../cliente/shopping.php" class="relative flex items-center justify-center">
                        <i class="fa-solid fa-cart-shopping text-white text-xl"></i>
                        <?php if ($cartCount > 0): ?>
                            <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center font-bold"><?= $cartCount ?></span>
                        <?php endif; ?>
                    </a>
                    <div class="relative">
                        <button id="user-menu-button" type="button" class="relative flex rounded-full bg-gray-800 text-sm focus:outline-none">
                            <img class="h-8 w-8 rounded-full" src="https://ui-avatars.com/api/?name=<?= urlencode($nombre) ?>&background=0D8ABC&color=fff" alt="<?= htmlspecialchars($nombre) ?>">
                        </button>
                        <div id="user-menu" class="hidden absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5" role="menu">
                            <a href="./perfil.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">My profile</a>
                            <a href="./shopping.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">My cart</a>
                            <hr class="my-1 border-gray-200">
                            <a href="../auth/logout.php?from=cliente" class="block px-4 py-2 text-sm text-red-700 hover:bg-red-50">Log out</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="../cliente/shopping.php" class="relative flex items-center justify-center">
                        <i class="fa-solid fa-cart-shopping text-white text-xl"></i>

                        <?php if ($cartCount > 0): ?>
                            <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center font-bold">
                                <?= $cartCount ?>
                            </span>
                        <?php endif; ?>
                    </a>

                    <a href="../auth/login.php" class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-base font-medium">Sign in</a>
                    <a href="../auth/register.php" class="flex items-center gap-2 px-4 py-2 bg-sky-500 text-white font-semibold rounded-md hover:bg-sky-600 transition">Sign up</a>
                <?php endif; ?>

                </div>
                <div class="-mr-2 flex md:hidden">
                    <button id="mobile-menu-btn" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-white hover:bg-gray-700">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <div id="mobile-menu" class="hidden md:hidden">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                <a href="../index.php" class="text-gray-300 hover:bg-gray-700 hover:text-white block px-3 py-2 rounded-md text-base font-medium">Home</a>
                <a href="./catalogo.php" class="text-gray-300 hover:bg-gray-700 hover:text-white block px-3 py-2 rounded-md text-base font-medium">Catalog</a>
            </div>
            <div class="border-t border-gray-700 pt-4 pb-3 flex items-center space-x-4 px-4 justify-end">
                <?php if ($userId): ?>
                    <a href="../cliente/shopping.php" class="relative flex items-center justify-center">
                        <i class="fa-solid fa-cart-shopping text-white text-xl"></i>
                        <?php if ($cartCount > 0): ?>
                            <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center font-bold"><?= $cartCount ?></span>
                        <?php endif; ?>
                    </a>
                    <div class="relative">
                        <button id="user-menu-button-mobile" type="button" class="relative flex rounded-full bg-gray-800 text-sm focus:outline-none">
                            <img class="h-8 w-8 rounded-full" src="https://ui-avatars.com/api/?name=<?= urlencode($nombre) ?>&background=0D8ABC&color=fff" alt="<?= htmlspecialchars($nombre) ?>">
                        </button>
                        <div id="user-menu-mobile" class="hidden absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5" role="menu">
                            <a href="./perfil.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">My profile</a>
                            <a href="./shopping.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">My cart</a>
                            <hr class="my-1 border-gray-200">
                            <a href="../auth/logout.php?from=cliente" class="block px-4 py-2 text-sm text-red-700 hover:bg-red-50">Log out</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="../cliente/shopping.php" class="relative flex items-center justify-center">
                        <i class="fa-solid fa-cart-shopping text-white text-xl"></i>

                        <?php if ($cartCount > 0): ?>
                            <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center font-bold">
                                <?= $cartCount ?>
                            </span>
                        <?php endif; ?>
                    </a>

                    <a href="../auth/login.php" class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-base font-medium">Sign in</a>
                    <a href="../auth/register.php" class="flex items-center gap-2 px-4 py-2 bg-sky-500 text-white font-semibold rounded-md hover:bg-sky-600 transition">Sign up</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Back button ─────────────────────────────────────────────────────────────────── -->
    <div class="relative z-50 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3">
        <a href="./catalogo.php" 
            class="inline-flex items-center gap-2 text-gray-400 hover:text-gray-200 transition px-4 py-3">
                <i class="fa-solid fa-arrow-left"></i> Back to catalog
        </a>

    </div>

    <!-- Hero Section ─────────────────────────────────────────────────────────────────── -->
    <div class="relative min-h-[400px] sm:h-[500px] overflow-hidden">
        <?php
        $rutaImagen  = '../assets/img/caratulas/' . $juego['imagen_portada'];
        $tieneImagen = !empty($juego['imagen_portada']) && file_exists($rutaImagen);
        ?>
        <div class="absolute inset-0">
            <?php if ($tieneImagen): ?>
                <img src="<?= htmlspecialchars($rutaImagen) ?>" alt="" class="w-full h-full object-cover blur-2xl scale-110">
            <?php else: ?>
                <div class="w-full h-full bg-gradient-to-br from-purple-900 via-blue-900 to-indigo-900"></div>
            <?php endif; ?>
            <div class="absolute inset-0 bg-gradient-to-t from-gray-900 via-gray-900/80 to-transparent"></div>
        </div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-full flex items-end pb-8 pt-8">
            <div class="flex flex-col md:flex-row gap-6 sm:gap-8 items-center md:items-end w-full">

                <!-- Cover ───────────────────────────────────────────────────────────────────-->
                <div class="flex-shrink-0 w-48 sm:w-56 md:w-64">
                    <div class="w-full aspect-[3/4] rounded-lg overflow-hidden shadow-2xl ring-2 ring-white/20 hover:scale-105 transition-transform duration-300">
                        <?php if ($tieneImagen): ?>
                            <img src="<?= htmlspecialchars($rutaImagen) ?>" alt="<?= htmlspecialchars($juego['titulo']) ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <div class="w-full h-full bg-gradient-to-br from-purple-900 to-blue-900 flex items-center justify-center">
                                <i class="fa-solid fa-gamepad text-white/30 text-4xl sm:text-6xl"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Info ───────────────────────────────────────────────────────────────────-->
                <div class="flex-1 text-center md:text-left w-full">
                    <div class="flex items-center justify-center md:justify-start gap-2 mb-3 flex-wrap">
                        <span class="bg-blue-600 text-white px-3 py-1 rounded-full text-xs sm:text-sm font-bold"><?= htmlspecialchars($juego['genero']) ?></span>
                        <span class="bg-gray-700 text-gray-300 px-3 py-1 rounded-full text-xs sm:text-sm font-bold">
                            <i class="fa-solid fa-desktop mr-1"></i><?= htmlspecialchars($juego['plataforma']) ?>
                        </span>
                        <?php if ($juego['precio'] == 0): ?>
                            <span class="bg-green-600 text-white px-3 py-1 rounded-full text-xs sm:text-sm font-bold animate-pulse">
                                <i class="fa-solid fa-gift mr-1"></i> FREE TO PLAY
                            </span>
                        <?php endif; ?>
                    </div>

                    <h1 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-2 sm:mb-3">
                        <?= htmlspecialchars($juego['titulo']) ?>
                    </h1>
                    <p class="text-gray-300 text-sm sm:text-base md:text-lg mb-2">
                        <i class="fa-solid fa-building mr-2 text-blue-400"></i><?= htmlspecialchars($juego['desarrollador']) ?>
                    </p>
                    <?php if (!empty($juego['fecha_lanzamiento'])): ?>
                        <p class="text-gray-400 text-sm mb-4">
                            <i class="fa-solid fa-calendar mr-2"></i>Released: <?= date('F j, Y', strtotime($juego['fecha_lanzamiento'])) ?>
                        </p>
                    <?php endif; ?>

                    <div class="flex flex-col sm:flex-row items-center justify-center md:justify-start gap-3 sm:gap-4 mt-4 sm:mt-6">

                        <!-- Price ───────────────────────────────────────────────────────────────────-->
                        <div class="bg-gray-800/90 backdrop-blur-sm px-4 sm:px-6 py-3 sm:py-4 rounded-lg border border-gray-700">
                            <?php if ($juego['precio'] > 0): ?>
                                <p class="text-gray-400 text-xs mb-1">Price</p>
                                <p class="text-2xl sm:text-3xl md:text-4xl font-bold text-white"><?= number_format($juego['precio'], 2) ?>€</p>
                            <?php else: ?>
                                <p class="text-2xl sm:text-3xl md:text-4xl font-bold text-green-400">FREE</p>
                            <?php endif; ?>
                        </div>

                        <!-- Add to cart ───────────────────────────────────────────────────────────────────-->
                        <div class="flex-1 w-full sm:w-auto">
                            <?php if (!$sinStock): ?>
                                <form action="../cliente/add_to_cart.php" method="POST" class="w-full">
                                    <input type="hidden" name="id_juego" value="<?= $juego['id'] ?>">
                                    <div class="flex flex-col sm:flex-row gap-2 mb-2">
                                        <div class="flex items-center bg-gray-800 rounded-lg justify-center">
                                            <button type="button" onclick="decrementQty()" class="px-3 sm:px-4 py-2 sm:py-3 text-white hover:bg-gray-700 rounded-l-lg">
                                                <i class="fa-solid fa-minus text-sm"></i>
                                            </button>
                                            <input type="number" name="cantidad" id="cantidad" value="1" min="1" max="<?= $stockDisponible ?>"
                                                class="w-12 sm:w-16 text-center bg-gray-800 text-white border-x border-gray-700 py-2 sm:py-3 text-sm" readonly>
                                            <button type="button" onclick="incrementQty(<?= $stockDisponible ?>)" class="px-3 sm:px-4 py-2 sm:py-3 text-white hover:bg-gray-700 rounded-r-lg">
                                                <i class="fa-solid fa-plus text-sm"></i>
                                            </button>
                                        </div>
                                        <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 sm:px-8 py-2 sm:py-3 rounded-lg font-bold text-sm sm:text-base md:text-lg transition flex items-center justify-center gap-2">
                                            <i class="fa-solid fa-cart-plus"></i>
                                            <span class="hidden sm:inline">Add to Cart</span>
                                            <span class="sm:hidden">Add</span>
                                        </button>
                                    </div>
                                    <p class="text-green-400 text-xs sm:text-sm text-center">
                                        <i class="fa-solid fa-circle-check"></i> In Stock (<?= $stockDisponible ?> available)
                                    </p>
                                    <?php if (!$userId): ?>
                                        <p class="text-yellow-400 text-xs text-center mt-1">
                                            <i class="fa-solid fa-circle-info mr-1"></i>
                                            <a href="../auth/login.php" class="underline hover:text-yellow-300">Sign in</a> to save your cart permanently.
                                        </p>
                                    <?php endif; ?>
                                </form>
                            <?php else: ?>
                                <button disabled class="w-full bg-gray-700 text-gray-400 px-4 sm:px-8 py-3 sm:py-4 rounded-lg font-bold text-sm sm:text-base md:text-lg cursor-not-allowed flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-circle-xmark"></i> Out of Stock
                                </button>
                                <?php if (!$userId && (int)$juego['stock'] > 0): ?>
                                    <p class="text-yellow-400 text-xs text-center mt-2">
                                        <i class="fa-solid fa-cart-shopping mr-1"></i>
                                        You already have all available stock in your cart.
                                        <a href="../auth/login.php" class="underline hover:text-yellow-300">Sign in</a> to complete your purchase.
                                    </p>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content ───────────────────────────────────────────────────────────────────-->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 space-y-8">
                <section class="bg-gray-800 rounded-xl p-8">
                    <h2 class="text-2xl font-bold text-white mb-4">About This Game</h2>
                    <p class="text-gray-300 leading-relaxed text-lg"><?= nl2br(htmlspecialchars($juego['descripcion'])) ?></p>
                </section>

                <section class="bg-gray-800 rounded-xl p-8">
                    <h2 class="text-2xl font-bold text-white mb-6 flex items-center gap-2">
                        <i class="fa-solid fa-images text-purple-400"></i> Screenshots
                    </h2>
                    <?php
                    $stmtScreenshots = $pdo->prepare("SELECT * FROM screenshots WHERE id_videojuego = :id ORDER BY orden ASC");
                    $stmtScreenshots->execute([':id' => $id]);
                    $screenshots = $stmtScreenshots->fetchAll(PDO::FETCH_ASSOC);
                    ?>
                    <?php if (count($screenshots) > 0): ?>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <?php foreach ($screenshots as $screenshot): ?>
                                <?php $rutaScreenshot = '../assets/img/screenshots/' . $screenshot['nombre_archivo']; ?>
                                <?php if (file_exists($rutaScreenshot)): ?>
                                    <div class="aspect-video bg-gray-900 rounded-lg overflow-hidden hover:scale-105 transition-transform cursor-pointer group relative"
                                        onclick="openLightbox('<?= htmlspecialchars($rutaScreenshot) ?>', '<?= htmlspecialchars($juego['titulo']) ?>')">
                                        <img src="<?= htmlspecialchars($rutaScreenshot) ?>" alt="Screenshot" class="w-full h-full object-cover group-hover:opacity-90 transition-opacity">
                                        <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                            <div class="text-center">
                                                <i class="fa-solid fa-expand text-white text-3xl mb-2"></i>
                                                <p class="text-white font-semibold">Click to enlarge</p>
                                            </div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="aspect-video bg-gradient-to-br from-red-900 to-orange-900 rounded-lg flex items-center justify-center">
                                        <div class="text-center">
                                            <i class="fa-solid fa-triangle-exclamation text-white/40 text-4xl mb-2"></i>
                                            <p class="text-white/60 text-sm">Image not found</p>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <?php for ($i = 1; $i <= 4; $i++): ?>
                                <div class="aspect-video bg-gradient-to-br from-purple-900 to-blue-900 rounded-lg flex items-center justify-center">
                                    <div class="text-center">
                                        <i class="fa-solid fa-image text-white/20 text-5xl mb-3"></i>
                                        <p class="text-white/40 text-sm">No screenshots available</p>
                                    </div>
                                </div>
                            <?php endfor; ?>
                        </div>
                    <?php endif; ?>
                </section>
            </div>

            <!-- Sidebar ───────────────────────────────────────────────────────────────────-->
            <div class="space-y-6">
                <div class="bg-gray-800 rounded-xl p-6">
                    <h3 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-star text-yellow-400"></i> Features
                    </h3>
                    <?php if (!empty($juego['feature'])): ?>
                        <ul class="space-y-2">
                            <?php foreach (explode(',', $juego['feature']) as $feature): ?>
                                <li class="flex items-start gap-2 text-sm text-gray-300">
                                    <i class="fa-solid fa-check text-green-400 mt-0.5 shrink-0"></i>
                                    <span><?= htmlspecialchars(trim($feature)) ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-gray-400 text-sm">No features available.</p>
                    <?php endif; ?>
                </div>

                <div class="bg-gray-800 rounded-xl p-6">
                    <h3 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                        <i class="fa-solid fa-share-nodes text-pink-400"></i> Share
                    </h3>
                    <div class="grid grid-cols-3 gap-2">
                        <button class="bg-blue-600 hover:bg-blue-700 text-white py-3 rounded-lg transition flex items-center justify-center">
                            <i class="fa-brands fa-facebook text-xl"></i>
                        </button>
                        <button class="bg-sky-500 hover:bg-sky-600 text-white py-3 rounded-lg transition flex items-center justify-center">
                            <i class="fa-brands fa-twitter text-xl"></i>
                        </button>
                        <button class="bg-red-600 hover:bg-red-700 text-white py-3 rounded-lg transition flex items-center justify-center">
                            <i class="fa-solid fa-heart text-xl"></i>
                        </button>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-green-900/30 to-emerald-900/30 border border-green-700 rounded-xl p-6">
                    <div class="flex items-start gap-3">
                        <i class="fa-solid fa-shield-halved text-green-400 text-2xl mt-1"></i>
                        <div>
                            <h4 class="text-white font-bold mb-2">Secure Purchase</h4>
                            <p class="text-green-300 text-sm">Your payment information is protected with industry-standard encryption.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php if (count($relacionados) > 0): ?>
            <section class="mt-16">
                <h2 class="text-3xl font-bold text-white mb-8 flex items-center gap-3">
                    <i class="fa-solid fa-fire text-orange-500"></i> More Like This
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <?php foreach ($relacionados as $relacionado): ?>
                        <a href="./detalle_juego.php?id=<?= $relacionado['id'] ?>" class="group bg-gray-800 rounded-xl overflow-hidden hover:ring-2 hover:ring-blue-500 transition-all hover:scale-105">
                            <div class="relative aspect-[16/9] bg-gradient-to-br from-purple-900 to-blue-900">
                                <?php
                                $rutaRelacionado = '../assets/img/caratulas/' . $relacionado['imagen_portada'];
                                if (!empty($relacionado['imagen_portada']) && file_exists($rutaRelacionado)): ?>
                                    <img src="<?= htmlspecialchars($rutaRelacionado) ?>" alt="<?= htmlspecialchars($relacionado['titulo']) ?>" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <i class="fa-solid fa-gamepad text-white/20 text-5xl"></i>
                                    </div>
                                <?php endif; ?>
                                <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                    <span class="text-white font-semibold">View Details</span>
                                </div>
                            </div>
                            <div class="p-4">
                                <h3 class="text-white font-semibold mb-1 line-clamp-1"><?= htmlspecialchars($relacionado['titulo']) ?></h3>
                                <p class="text-gray-400 text-sm mb-2"><?= htmlspecialchars($relacionado['genero']) ?></p>
                                <p class="text-white text-lg font-bold">
                                    <?= $relacionado['precio'] > 0 ? number_format($relacionado['precio'], 2) . '€' : 'FREE' ?>
                                </p>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    </div>

    <?php include "../includes/footer.php" ?>

    <!-- Lightbox ───────────────────────────────────────────────────────────────────-->
    <div id="lightbox" class="hidden fixed inset-0 bg-black/95 z-[60] flex items-center justify-center p-4" onclick="closeLightbox()">
        <button onclick="closeLightbox()" class="absolute top-4 right-4 text-white text-4xl hover:text-gray-300 z-10 bg-black/50 w-14 h-14 rounded-full flex items-center justify-center transition">
            <i class="fa-solid fa-times"></i>
        </button>
        <button id="prev-btn" onclick="navigateLightbox(-1); event.stopPropagation();" class="absolute left-4 text-white text-4xl hover:text-gray-300 bg-black/50 w-14 h-14 rounded-full flex items-center justify-center transition">
            <i class="fa-solid fa-chevron-left"></i>
        </button>
        <button id="next-btn" onclick="navigateLightbox(1); event.stopPropagation();" class="absolute right-4 text-white text-4xl hover:text-gray-300 bg-black/50 w-14 h-14 rounded-full flex items-center justify-center transition">
            <i class="fa-solid fa-chevron-right"></i>
        </button>
        <div class="max-w-6xl max-h-full flex flex-col items-center" onclick="event.stopPropagation()">
            <img id="lightbox-img" src="" alt="Screenshot" class="max-w-full max-h-[80vh] object-contain rounded-lg shadow-2xl">
            <p id="lightbox-caption" class="text-white text-lg mt-4 text-center"></p>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="../js/user_drop.js"></script>
    <script src="../js/mobile_menu.js"></script>
    <script src="../js/amount.js"></script>
    <script src="../js/zoomImg.js"></script>
    <script>
        function dismissToast(id) {
            const toast = document.getElementById(id);
            if (!toast) return;
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(-50%) translateY(20px)';
            setTimeout(() => toast.remove(), 400);
        }
        document.addEventListener('DOMContentLoaded', () => {
            ['toast-success', 'toast-error'].forEach(id => {
                const toast = document.getElementById(id);
                if (toast) setTimeout(() => dismissToast(id), 3000);
            });
        });
    </script>
</body>

</html>