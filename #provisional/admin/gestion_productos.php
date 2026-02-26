<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: login_admin.php');
    exit;
}

require_once '../config/conection.php'; 
$adminName = $_SESSION['user_name'] ?? 'Admin';

// Procesar POST para añadir o editar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = $_POST['titulo'];
    $descripcion = $_POST['descripcion'];
    $desarrollador = $_POST['desarrollador'];
    $genero = $_POST['genero'];
    $plataforma = $_POST['plataforma'];
    $fecha_lanzamiento = $_POST['fecha_lanzamiento'];
    $precio = $_POST['precio'];
    $stock = $_POST['stock'];
    $feature = $_POST['feature'];
    $imagen_portada = $_POST['imagen_portada'];

    if (!empty($_POST['id'])) {
        // Editar producto
        $stmt = $pdo->prepare("UPDATE videojuego SET titulo=?, descripcion=?, desarrollador=?, genero=?, plataforma=?, fecha_lanzamiento=?, precio=?, stock=?, feature=?, imagen_portada=? WHERE id=?");
        $stmt->execute([$titulo, $descripcion, $desarrollador, $genero, $plataforma, $fecha_lanzamiento, $precio, $stock, $feature, $imagen_portada, $_POST['id']]);
    } else {
        // Añadir producto
        $stmt = $pdo->prepare("INSERT INTO videojuego (titulo, descripcion, desarrollador, genero, plataforma, fecha_lanzamiento, precio, stock, feature, imagen_portada) VALUES (?,?,?,?,?,?,?,?,?,?)");
        $stmt->execute([$titulo, $descripcion, $desarrollador, $genero, $plataforma, $fecha_lanzamiento, $precio, $stock, $feature, $imagen_portada]);
    }

    header('Location: gestion_productos.php');
    exit;
}

// Obtener todos los productos
$stmt = $pdo->query("SELECT * FROM videojuego ORDER BY id DESC");
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener producto a editar si existe GET[id]
$producto_editar = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM videojuego WHERE id=?");
    $stmt->execute([$_GET['edit']]);
    $producto_editar = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="../dist/output.css">
    <link rel="stylesheet" href="../assets/css/input.css">
    <title>Gestión de Productos</title>

</head>

<body class="bg-gray-900 text-white flex h-screen">

    <!--Sidebar  -->
    <aside id="sidebar">
        <div class="s-logo">
            <div class="ic"><i class="fa-solid fa-gamepad" style="color:#fff;font-size:14px"></i></div>
            <div>
                <div class="t">GameStore</div>
                <div class="st">Admin Panel</div>
            </div>
        </div>
        <nav class="s-nav">
            <div class="s-sec">Main</div>
            <a href="dashboard.php" class="nav-link"><i class="fa-solid fa-house"></i> Dashboard</a>
            <a href="clientes.php" class="nav-link"><i class="fa-solid fa-users"></i> Customers</a>
            <a href="productos.php" class="nav-link active"><i class="fa-solid fa-box"></i> Products</a>
            <a href="estadisticas.php" class="nav-link"><i class="fa-solid fa-chart-line"></i> Statistics</a>
            <div class="s-sec">Store</div>
            <a href="../cliente/catalogo.php" target="_blank" class="nav-link"><i class="fa-solid fa-store"></i> View Catalog <i class="fa-solid fa-arrow-up-right-from-square" style="font-size:9px;margin-left:auto;opacity:.4"></i></a>
        </nav>
        <div class="s-foot">
            <div class="u-info">
                <img src="https://ui-avatars.com/api/?name=<?= urlencode($adminName) ?>&background=3b5bdb&color=fff" alt="">
                <div>
                    <div class="u-name"><?= htmlspecialchars($adminName) ?></div>
                    <div class="u-role">Administrator</div>
                </div>
            </div>
            <a href="../auth/logout.php?from=admin" class="logout-btn"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
        </div>
    </aside>

    <main class="flex-1 p-8 overflow-auto">
        <h1 class="text-3xl font-bold mb-6">Gestión de Productos</h1>

        <!-- Formulario añadir/editar -->
        <div class="mb-8 p-6 bg-gray-800 rounded-lg">
            <h2 class="text-xl font-semibold mb-4"><?= $producto_editar ? "Editar Producto" : "Añadir Producto" ?></h2>
            <form method="POST" class="space-y-4">
                <?php if ($producto_editar): ?>
                    <input type="hidden" name="id" value="<?= $producto_editar['id'] ?>">
                <?php endif; ?>
                <input type="text" name="titulo" placeholder="Título" class="w-full px-4 py-2 rounded bg-gray-900 border border-gray-600" value="<?= $producto_editar['titulo'] ?? '' ?>" required>
                <input type="text" name="descripcion" placeholder="Descripción" class="w-full px-4 py-2 rounded bg-gray-900 border border-gray-600" value="<?= $producto_editar['descripcion'] ?? '' ?>">
                <input type="text" name="desarrollador" placeholder="Desarrollador" class="w-full px-4 py-2 rounded bg-gray-900 border border-gray-600" value="<?= $producto_editar['desarrollador'] ?? '' ?>">
                <input type="text" name="genero" placeholder="Género" class="w-full px-4 py-2 rounded bg-gray-900 border border-gray-600" value="<?= $producto_editar['genero'] ?? '' ?>">
                <input type="text" name="plataforma" placeholder="Plataforma" class="w-full px-4 py-2 rounded bg-gray-900 border border-gray-600" value="<?= $producto_editar['plataforma'] ?? '' ?>">
                <input type="date" name="fecha_lanzamiento" placeholder="Fecha Lanzamiento" class="w-full px-4 py-2 rounded bg-gray-900 border border-gray-600" value="<?= $producto_editar['fecha_lanzamiento'] ?? '' ?>">
                <input type="number" step="0.01" name="precio" placeholder="Precio" class="w-full px-4 py-2 rounded bg-gray-900 border border-gray-600" value="<?= $producto_editar['precio'] ?? '' ?>">
                <input type="number" name="stock" placeholder="Stock" class="w-full px-4 py-2 rounded bg-gray-900 border border-gray-600" value="<?= $producto_editar['stock'] ?? '' ?>">
                <input type="text" name="feature" placeholder="Características" class="w-full px-4 py-2 rounded bg-gray-900 border border-gray-600" value="<?= $producto_editar['feature'] ?? '' ?>">
                <input type="text" name="imagen_portada" placeholder="Imagen (URL o nombre)" class="w-full px-4 py-2 rounded bg-gray-900 border border-gray-600" value="<?= $producto_editar['imagen_portada'] ?? '' ?>">
                <button type="submit" class="px-6 py-2 bg-sky-600 hover:bg-sky-500 rounded"><?= $producto_editar ? "Actualizar" : "Añadir" ?></button>
                <?php if ($producto_editar): ?>
                    <a href="gestion_productos.php" class="ml-4 px-6 py-2 bg-gray-600 hover:bg-gray-500 rounded">Cancelar</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Tabla de productos -->
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b border-gray-700">
                    <th class="py-2 px-3">ID</th>
                    <th class="py-2 px-3">Título</th>
                    <th class="py-2 px-3">Precio</th>
                    <th class="py-2 px-3">Stock</th>
                    <th class="py-2 px-3">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($productos as $p): ?>
                    <tr class="border-b border-gray-800 hover:bg-gray-800">
                        <td class="py-2 px-3"><?= $p['id'] ?></td>
                        <td class="py-2 px-3"><?= htmlspecialchars($p['titulo']) ?></td>
                        <td class="py-2 px-3"><?= $p['precio'] ?> €</td>
                        <td class="py-2 px-3"><?= $p['stock'] ?></td>
                        <td class="py-2 px-3 space-x-3">
                            <a href="?edit=<?= $p['id'] ?>" class="text-sky-400 hover:underline">Editar</a>
                            <a href="producto_delete.php?id=<?= $p['id'] ?>" class="text-red-500 hover:underline" onclick="return confirm('¿Seguro que quieres borrar este producto?')">Borrar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    </main>
</body>

</html>