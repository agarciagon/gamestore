<?php
session_start();

// Incluir la conexión a la base de datos 
require_once("../config/conection.php");

$nombre = $_SESSION['user_name'] ?? 'usuario';
$rolActual = $_SESSION['rol'] ?? 'invitado';

$userId = $_SESSION['user_id'] ?? null;
$cartCount = 0;

if ($userId) {
    $stmtCount = $pdo->prepare("SELECT SUM(cantidad) as total FROM carrito WHERE id_usuario = :id_usuario");
    $stmtCount->execute([':id_usuario' => $userId]);
    $cartCount = (int)($stmtCount->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
} elseif (!empty($_SESSION['carrito_guest'])) {
    $cartCount = array_sum($_SESSION['carrito_guest']);
}

// ========== LÓGICA DE BÚSQUEDA Y FILTROS ==========
$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$genero = isset($_GET['genero']) ? trim($_GET['genero']) : '';
$plataforma = isset($_GET['plataforma']) ? trim($_GET['plataforma']) : '';
$orden = isset($_GET['orden']) ? trim($_GET['orden']) : '';

// Construir la consulta SQL
$sql = "SELECT * FROM videojuego WHERE 1=1";
$params = [];

// Filtro de búsqueda
if (!empty($search)) {
    $sql .= " AND (titulo LIKE :search1 
                OR descripcion LIKE :search2 
                OR desarrollador LIKE :search3)";
    $params[':search1'] = "%$search%";
    $params[':search2'] = "%$search%";
    $params[':search3'] = "%$search%";
}

// Filtro de género
if (!empty($genero)) {
    $sql .= " AND genero = :genero";
    $params[':genero'] = $genero;
}

// Filtro de plataforma
if (!empty($plataforma)) {
    $sql .= " AND plataforma = :plataforma";
    $params[':plataforma'] = $plataforma;
}

// Orden
switch ($orden) {
    case 'precio_asc':
        $sql .= " ORDER BY precio ASC";
        break;
    case 'precio_desc':
        $sql .= " ORDER BY precio DESC";
        break;
    case 'titulo':
        $sql .= " ORDER BY titulo ASC";
        break;
    case 'mas_nuevo':
        $sql .= " ORDER BY fecha_lanzamiento DESC";
        break;
    default:
        $sql .= " ORDER BY fecha_creacion DESC";
}

// Ejecutar consulta
try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $videojuegos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $videojuegos = [];
}

// Obtener géneros únicos
try {
    $generos = $pdo->query("SELECT DISTINCT genero FROM videojuego WHERE genero IS NOT NULL ORDER BY genero")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $generos = [];
}

// Obtener plataformas únicas
try {
    $plataformas = $pdo->query("SELECT DISTINCT plataforma FROM videojuego WHERE plataforma IS NOT NULL ORDER BY plataforma")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $plataformas = [];
}

// Contar total
try {
    $totalVideojuegos = $pdo->query("SELECT COUNT(*) FROM videojuego")->fetchColumn();
} catch (PDOException $e) {
    $totalVideojuegos = 0;
}

$juegosFiltrados = count($videojuegos);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="../dist/output.css">
    <title>Game Store - Catalog</title>
</head>

<body class="bg-gray-900">

    <!-- Navbar -->
    <nav class="bg-gray-800">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">

                <!-- Logo + Links -->
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <a href="../index.php">
                            <img class="h-36 w-36" src="../assets/img/logo.png" alt="Logo">
                        </a>
                    </div>

                    <div class="hidden md:flex ml-10 space-x-4 items-center">
                        <a href="../index.php" class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Home</a>
                        <span class="text-gray-300 content-center">|</span>
                        <a href="./catalogo.php" class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Catalog</a>

                        <!-- Buscador -->
                        <form role="search" action="./catalogo.php" method="get" class="ml-4">
                            <div class="relative">
                                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm"></i>
                                <input type="search" name="q"
                                    value="<?= htmlspecialchars($search ?? '') ?>"
                                    placeholder="Search games..."
                                    class="w-full pl-10 pr-4 py-2 rounded-full bg-gray-100 border border-gray-200 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:outline-none transition">
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Desktop Right Side -->
                <div class="hidden md:flex items-center gap-4">

                    <!-- Carrito SIEMPRE visible -->
                    <a href="./shopping.php" class="relative flex items-center justify-center">
                        <i class="fa-solid fa-cart-shopping text-white text-xl"></i>

                        <?php if ($cartCount > 0): ?>
                            <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center font-bold">
                                <?= $cartCount ?>
                            </span>
                        <?php endif; ?>
                    </a>

                    <?php if ($userId): ?>
                        <!-- Usuario logueado -->
                        <div class="relative">
                            <button id="user-menu-button" type="button"
                                class="flex rounded-full bg-gray-800 text-sm focus:outline-none">
                                <img class="h-8 w-8 rounded-full"
                                    src="https://ui-avatars.com/api/?name=<?= urlencode($nombre) ?>&background=0D8ABC&color=fff"
                                    alt="<?= htmlspecialchars($nombre) ?>">
                            </button>

                            <div id="user-menu"
                                class="hidden absolute right-0 mt-2 w-48 rounded-md bg-white py-1 shadow-lg">
                                <a href="./perfil.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Your profile</a>
                                <a href="./shopping.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">My cart</a>
                                <hr class="my-1">
                                <a href="../auth/logout.php?from=cliente" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50">Log out</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Invitado -->
                        <a href="../auth/login.php"
                            class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium">
                            Sign in
                        </a>

                        <a href="../auth/register.php"
                            class="px-4 py-2 bg-sky-500 text-white font-semibold rounded-md hover:bg-sky-600 transition">
                            Sign up
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Mobile Button -->
                <div class="md:hidden">
                    <button id="mobile-menu-btn"
                        class="p-2 rounded-md text-gray-400 hover:text-white hover:bg-gray-700">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobile-menu" class="hidden md:hidden border-t border-gray-700">

            <div class="px-3 py-3 space-y-2">
                <a href="../index.php" class="block text-gray-300 hover:bg-gray-700 px-3 py-2 rounded-md">Home</a>
                <a href="./catalogo.php" class="block text-gray-300 hover:bg-gray-700 px-3 py-2 rounded-md">Catalog</a>
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

    <main class="bg-gray-900 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">

            <!-- Header con controles -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                
                <!-- Título -->
                <div class="flex-shrink-0">
                    <h2 class="text-xl sm:text-2xl font-bold text-white">
                        <?php if (!empty($search)): ?>
                            Results for "<?= htmlspecialchars($search) ?>"
                        <?php elseif (!empty($genero)): ?>
                            <?= htmlspecialchars($genero) ?>
                        <?php else: ?>
                            All Games
                        <?php endif; ?>
                    </h2>
                    <p class="text-gray-400 mt-1 text-sm"><?= $juegosFiltrados ?> games available</p>
                </div>

                <!-- Controles -->
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
                            class="hidden absolute right-0 mt-3 w-[90vw] sm:w-80 bg-gray-800 border border-gray-700 rounded-xl shadow-2xl p-4 sm:p-6 z-50 max-h-[70vh] overflow-y-auto">
                            <form method="GET" action="" class="space-y-4">

                                <?php if (!empty($search)): ?>
                                    <input type="hidden" name="q" value="<?= htmlspecialchars($search) ?>">
                                <?php endif; ?>

                                <!-- Género -->
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-400 mb-3">
                                        <i class="fa-solid fa-gamepad text-blue-400"></i> Genre
                                    </h3>
                                    <div class="space-y-2 max-h-40 overflow-y-auto">
                                        <label class="flex items-center gap-2 text-gray-300 hover:text-white cursor-pointer">
                                            <input type="radio" name="genero" value="" <?= empty($genero) ? 'checked' : '' ?> class="accent-blue-500">
                                            All
                                        </label>
                                        <?php foreach ($generos as $gen): ?>
                                            <label class="flex items-center gap-2 text-gray-300 hover:text-white cursor-pointer">
                                                <input type="radio" name="genero" value="<?= htmlspecialchars($gen['genero']) ?>"
                                                    <?= $genero === $gen['genero'] ? 'checked' : '' ?> class="accent-blue-500">
                                                <?= htmlspecialchars($gen['genero']) ?>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <!-- Plataforma -->
                                <div>
                                    <h3 class="text-sm font-semibold text-gray-400 mb-3">
                                        <i class="fa-solid fa-desktop text-green-400"></i> Platform
                                    </h3>
                                    <div class="space-y-2 max-h-40 overflow-y-auto">
                                        <label class="flex items-center gap-2 text-gray-300 hover:text-white cursor-pointer">
                                            <input type="radio" name="plataforma" value="" <?= empty($plataforma) ? 'checked' : '' ?> class="accent-green-500">
                                            All
                                        </label>
                                        <?php foreach ($plataformas as $plat): ?>
                                            <label class="flex items-center gap-2 text-gray-300 hover:text-white cursor-pointer">
                                                <input type="radio" name="plataforma" value="<?= htmlspecialchars($plat['plataforma']) ?>"
                                                    <?= $plataforma === $plat['plataforma'] ? 'checked' : '' ?> class="accent-green-500">
                                                <?= htmlspecialchars($plat['plataforma']) ?>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <!-- Botones -->
                                <div class="flex gap-3 pt-4 border-t border-gray-700">
                                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-lg transition text-sm font-semibold">
                                        Apply
                                    </button>
                                    <a href="./catalogo.php" class="flex-1 text-center bg-gray-700 hover:bg-gray-600 text-white py-2 rounded-lg transition text-sm font-semibold">
                                        Clear
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Ordenar -->
                    <form method="GET" action="" class="flex items-center gap-2 flex-1 sm:flex-initial">
                        <?php if (!empty($search)): ?>
                            <input type="hidden" name="q" value="<?= htmlspecialchars($search) ?>">
                        <?php endif; ?>
                        <?php if (!empty($genero)): ?>
                            <input type="hidden" name="genero" value="<?= htmlspecialchars($genero) ?>">
                        <?php endif; ?>
                        <?php if (!empty($plataforma)): ?>
                            <input type="hidden" name="plataforma" value="<?= htmlspecialchars($plataforma) ?>">
                        <?php endif; ?>

                        <label class="text-gray-400 text-xs sm:text-sm hidden sm:inline">Order:</label>
                        <select name="orden" onchange="this.form.submit()"
                            class="bg-gray-800 text-white border border-gray-700 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500 text-xs sm:text-sm w-full sm:w-auto">
                            <option value="">Featured</option>
                            <option value="mas_nuevo" <?= $orden === 'mas_nuevo' ? 'selected' : '' ?>>Newest</option>
                            <option value="titulo" <?= $orden === 'titulo' ? 'selected' : '' ?>>Name (A-Z)</option>
                            <option value="precio_asc" <?= $orden === 'precio_asc' ? 'selected' : '' ?>>Price: Low-High</option>
                            <option value="precio_desc" <?= $orden === 'precio_desc' ? 'selected' : '' ?>>Price: High-Low</option>
                        </select>
                    </form>
                </div>
            </div>

            <!-- Grid de Juegos -->
            <?php if (count($videojuegos) > 0): ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 sm:gap-6">
                    <?php foreach ($videojuegos as $juego): ?>
                        <a href="./detalle_juego.php?id=<?= $juego['id'] ?>"
                            class="group bg-gray-800 rounded-xl overflow-hidden hover:ring-2 hover:ring-blue-500 transition-all duration-300 hover:scale-105">

                            <!-- Imagen -->
                            <div class="relative aspect-[16/9] bg-gradient-to-br from-purple-900 to-blue-900">
                                <?php
                                $rutaImagen = '../assets/img/caratulas/' . $juego['imagen_portada'];
                                $tieneImagen = !empty($juego['imagen_portada']) && file_exists($rutaImagen);
                                ?>

                                <?php if ($tieneImagen): ?>
                                    <img src="<?= htmlspecialchars($rutaImagen) ?>"
                                        alt="<?= htmlspecialchars($juego['titulo']) ?>"
                                        class="w-full h-full object-cover">
                                <?php else: ?>
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <i class="fa-solid fa-gamepad text-white/20 text-6xl"></i>
                                    </div>
                                <?php endif; ?>

                                <?php if ($juego['precio'] == 0): ?>
                                    <div class="absolute top-2 right-2 bg-green-500 text-white px-2 py-1 rounded-full text-xs font-bold">
                                        FREE
                                    </div>
                                <?php endif; ?>

                                <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                    <span class="text-white font-semibold">See Details</span>
                                </div>
                            </div>

                            <!-- Info -->
                            <div class="p-4">
                                <div class="flex items-center gap-2 mb-2 flex-wrap">
                                    <span class="text-xs bg-gray-700 text-gray-300 px-2 py-1 rounded">
                                        <?= htmlspecialchars($juego['plataforma']) ?>
                                    </span>
                                    <span class="text-xs bg-blue-900/50 text-blue-300 px-2 py-1 rounded">
                                        <?= htmlspecialchars($juego['genero']) ?>
                                    </span>
                                </div>

                                <h3 class="text-white font-semibold text-lg mb-1 line-clamp-1 group-hover:text-blue-400 transition">
                                    <?= htmlspecialchars($juego['titulo']) ?>
                                </h3>

                                <p class="text-gray-400 text-sm mb-3 line-clamp-1">
                                    <?= htmlspecialchars($juego['desarrollador']) ?>
                                </p>

                                <div class="flex items-center justify-between pt-3 border-t border-gray-700">
                                    <div>
                                        <?php if ($juego['precio'] > 0): ?>
                                            <span class="text-2xl font-bold text-white">
                                                <?= number_format($juego['precio'], 2) ?>€
                                            </span>
                                        <?php else: ?>
                                            <span class="text-2xl font-bold text-green-400">FREE</span>
                                        <?php endif; ?>
                                    </div>

                                    <?php if ($juego['stock'] > 0): ?>
                                        <span class="text-xs text-green-400">
                                            <i class="fa-solid fa-circle-check"></i> Available
                                        </span>
                                    <?php else: ?>
                                        <span class="text-xs text-red-400">
                                            <i class="fa-solid fa-circle-xmark"></i> Out
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-24 bg-gray-800 rounded-xl">
                    <i class="fa-solid fa-ghost text-gray-600 text-6xl mb-4"></i>
                    <h3 class="text-2xl font-bold text-white mb-2">No games found</h3>
                    <p class="text-gray-400 mb-6">Try adjusting the filters or search again</p>
                    <a href="catalogo.php" class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg font-semibold transition">
                        <i class="fa-solid fa-arrow-left mr-2"></i> All Games
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Footer -->
    <?php include "../includes/footer.php" ?>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="../js/mobile_menu.js"></script>
    <script src="../js/user_drop.js"></script>
    <script src="../js/filters.js"></script>

</body>

</html>