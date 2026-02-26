<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: login_admin.php');
    exit;
}
require_once '../config/conection.php';
$adminName = $_SESSION['user_name'] ?? 'Admin';

$totalClientes   = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol='cliente'")->fetchColumn();
$totalProductos  = $pdo->query("SELECT COUNT(*) FROM videojuego")->fetchColumn();
$totalStock      = $pdo->query("SELECT SUM(stock) FROM videojuego")->fetchColumn() ?? 0;
$valorInventario = $pdo->query("SELECT SUM(precio * stock) FROM videojuego")->fetchColumn() ?? 0;
$itemsEnCarritos = $pdo->query("SELECT SUM(cantidad) FROM carrito")->fetchColumn() ?? 0;
$usuariosConCarrito = $pdo->query("SELECT COUNT(DISTINCT id_usuario) FROM carrito")->fetchColumn();

$masEnCarrito = $pdo->query("
    SELECT v.titulo, SUM(c.cantidad) as total
    FROM carrito c JOIN videojuego v ON c.id_videojuego = v.id
    GROUP BY v.id ORDER BY total DESC LIMIT 6
")->fetchAll(PDO::FETCH_ASSOC);

$porGenero = $pdo->query("
    SELECT genero, COUNT(*) as qty, SUM(stock) as stock_total
    FROM videojuego GROUP BY genero ORDER BY qty DESC
")->fetchAll(PDO::FETCH_ASSOC);

$porPlataforma = $pdo->query("
    SELECT plataforma, COUNT(*) as qty FROM videojuego GROUP BY plataforma ORDER BY qty DESC
")->fetchAll(PDO::FETCH_ASSOC);

$precioStats = $pdo->query("SELECT MIN(precio) as mn, MAX(precio) as mx, AVG(precio) as avg FROM videojuego WHERE precio > 0")->fetch(PDO::FETCH_ASSOC);

$registrosMes = $pdo->query("
    SELECT DATE_FORMAT(fecha_creacion,'%b %Y') as mes, DATE_FORMAT(fecha_creacion,'%Y-%m') as mk, COUNT(*) as total
    FROM usuarios WHERE rol='cliente' AND fecha_creacion >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY mk ORDER BY mk ASC
")->fetchAll(PDO::FETCH_ASSOC);

$maxCarrito = !empty($masEnCarrito) ? max(array_column($masEnCarrito, 'total')) : 1;
$maxGenero  = !empty($porGenero)    ? max(array_column($porGenero, 'qty'))    : 1;
$maxMes     = !empty($registrosMes) ? max(array_column($registrosMes, 'total')) : 1;
$colors = ['#748ffc', '#7c3aed', '#0ea5e9', '#10b981', '#f59e0b', '#ef4444', '#ec4899'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="../dist/output.css">
    <link rel="stylesheet" href="../assets/css/input.css">
    <title>Statistics — Admin</title>
  
</head>

<body>

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
            <a href="productos.php" class="nav-link"><i class="fa-solid fa-box"></i> Products</a>
            <a href="estadisticas.php" class="nav-link active"><i class="fa-solid fa-chart-line"></i> Statistics</a>
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

    <header id="topbar">
        <button id="tbtn" onclick="toggle()"><i class="fa-solid fa-bars"></i></button>
        <span class="tb-title">Statistics</span>
    </header>

    <main id="content">
        <h1 class="page-title">Statistics</h1>
        <p class="page-sub">Store overview and analytics</p>

        <div class="stat-grid">
            <div class="stat-card">
                <div class="st-icon" style="background:#1e3a8a20"><i class="fa-solid fa-users" style="color:#60a5fa"></i></div>
                <div class="st-val"><?= $totalClientes ?></div>
                <div class="st-lbl">Customers</div>
            </div>
            <div class="stat-card">
                <div class="st-icon" style="background:#3b5bdb20"><i class="fa-solid fa-gamepad" style="color:#748ffc"></i></div>
                <div class="st-val"><?= $totalProductos ?></div>
                <div class="st-lbl">Products</div>
            </div>
            <div class="stat-card">
                <div class="st-icon" style="background:#92400e20"><i class="fa-solid fa-cart-shopping" style="color:#fbbf24"></i></div>
                <div class="st-val"><?= $itemsEnCarritos ?></div>
                <div class="st-lbl">Items in Carts</div>
            </div>
            <div class="stat-card">
                <div class="st-icon" style="background:#064e3b20"><i class="fa-solid fa-euro-sign" style="color:#34d399"></i></div>
                <div class="st-val"><?= number_format($valorInventario, 0) ?>€</div>
                <div class="st-lbl">Inventory Value</div>
            </div>
        </div>

        <!-- Row: price stats + most in cart -->
        <div class="grid3">
            <div class="panel">
                <div class="p-title">Price & Store Stats</div>
                <div class="kv-row"><span class="kv-key">Min price</span><span class="kv-val" style="color:#34d399"><?= number_format($precioStats['mn'], 2) ?>€</span></div>
                <div class="kv-row"><span class="kv-key">Avg price</span><span class="kv-val" style="color:#fbbf24"><?= number_format($precioStats['avg'], 2) ?>€</span></div>
                <div class="kv-row"><span class="kv-key">Max price</span><span class="kv-val" style="color:#f87171"><?= number_format($precioStats['mx'], 2) ?>€</span></div>
                <div class="kv-row"><span class="kv-key">Units in stock</span><span class="kv-val"><?= number_format($totalStock) ?></span></div>
                <div class="kv-row"><span class="kv-key">Users with cart</span><span class="kv-val" style="color:#748ffc"><?= $usuariosConCarrito ?></span></div>
            </div>
            <div class="panel">
                <div class="p-title">Most Added to Cart</div>
                <?php if (empty($masEnCarrito)): ?>
                    <p style="font-size:13px;color:#64748b">No cart data yet.</p>
                    <?php else: foreach ($masEnCarrito as $item): ?>
                        <div class="bar-row">
                            <div class="bar-label"><span class="name"><?= htmlspecialchars($item['titulo']) ?></span><span class="val"><?= $item['total'] ?> units</span></div>
                            <div class="bar-track">
                                <div class="bar-fill" style="width:<?= round(($item['total'] / $maxCarrito) * 100) ?>%;background:#3b5bdb"></div>
                            </div>
                        </div>
                <?php endforeach;
                endif; ?>
            </div>
        </div>

        <!-- Row: genre + platform + monthly -->
        <div class="grid2">
            <div class="panel">
                <div class="p-title">Products by Genre</div>
                <?php foreach ($porGenero as $i => $g): $c = $colors[$i % count($colors)]; ?>
                    <div class="bar-row">
                        <div class="bar-label"><span class="name"><?= htmlspecialchars($g['genero']) ?></span><span class="val"><?= $g['qty'] ?> · <?= $g['stock_total'] ?> stock</span></div>
                        <div class="bar-track">
                            <div class="bar-fill" style="width:<?= round(($g['qty'] / $maxGenero) * 100) ?>%;background:<?= $c ?>"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="panel">
                <div class="p-title">Products by Platform</div>
                <div class="plat-grid" style="margin-bottom:20px">
                    <?php foreach ($porPlataforma as $pl): ?>
                        <div class="plat-card">
                            <div class="plat-num"><?= $pl['qty'] ?></div>
                            <div class="plat-name"><?= htmlspecialchars($pl['plataforma']) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if (!empty($registrosMes)): ?>
                    <div class="p-title" style="margin-top:8px">New Customers / Month</div>
                    <?php foreach ($registrosMes as $r): ?>
                        <div class="bar-row">
                            <div class="bar-label"><span class="name"><?= $r['mes'] ?></span><span class="val"><?= $r['total'] ?></span></div>
                            <div class="bar-track">
                                <div class="bar-fill" style="width:<?= round(($r['total'] / $maxMes) * 100) ?>%;background:#10b981"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script>
        let col = false;

        function toggle() {
            col = !col;
            document.getElementById('sidebar').classList.toggle('collapsed', col);
            document.getElementById('topbar').classList.toggle('exp', col);
            document.getElementById('content').classList.toggle('exp', col);
        }
    </script>
</body>

</html>