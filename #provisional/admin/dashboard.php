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
$ultimosClientes = $pdo->query("SELECT nombre, email, fecha_creacion FROM usuarios WHERE rol='cliente' ORDER BY fecha_creacion DESC LIMIT 6")->fetchAll(PDO::FETCH_ASSOC);
$stockBajo       = $pdo->query("SELECT titulo, stock, precio FROM videojuego ORDER BY stock ASC LIMIT 6")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
  <link rel="stylesheet" href="../dist/output.css">
  <link rel="stylesheet" href="../assets/css/input.css">
  <title>Dashboard — Admin</title>
</head>

<body>
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
      <a href="dashboard.php" class="nav-link active"><i class="fa-solid fa-house"></i> Dashboard</a>
      <a href="clientes.php" class="nav-link"><i class="fa-solid fa-users"></i> Customers</a>
      <a href="productos.php" class="nav-link"><i class="fa-solid fa-box"></i> Products</a>
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

  <header id="topbar">
    <button id="tbtn" onclick="toggle()"><i class="fa-solid fa-bars"></i></button>
    <span class="tb-title">Dashboard</span>
    <span class="tb-date"><?= date('D, M j Y') ?></span>
  </header>

  <main id="content">
    <div class="stat-grid">
      <div class="stat-card">
        <div class="st-icon" style="background:#1e3a8a20"><i class="fa-solid fa-users" style="color:#60a5fa"></i></div>
        <div class="st-val"><?= $totalClientes ?></div>
        <div class="st-lbl">Total Customers</div>
      </div>
      <div class="stat-card">
        <div class="st-icon" style="background:#3b5bdb20"><i class="fa-solid fa-gamepad" style="color:#748ffc"></i></div>
        <div class="st-val"><?= $totalProductos ?></div>
        <div class="st-lbl">Products</div>
      </div>
      <div class="stat-card">
        <div class="st-icon" style="background:#92400e20"><i class="fa-solid fa-warehouse" style="color:#fbbf24"></i></div>
        <div class="st-val"><?= number_format($totalStock) ?></div>
        <div class="st-lbl">Units in Stock</div>
      </div>
      <div class="stat-card">
        <div class="st-icon" style="background:#064e3b20"><i class="fa-solid fa-euro-sign" style="color:#34d399"></i></div>
        <div class="st-val"><?= number_format($valorInventario, 0) ?>€</div>
        <div class="st-lbl">Inventory Value</div>
      </div>
    </div>

    <div class="two-col">
      <div class="panel">
        <div class="p-head"><span class="p-title">Recent Customers</span><a href="clientes.php" class="p-link">View all →</a></div>
        <?php foreach ($ultimosClientes as $c): ?>
          <div class="li">
            <img src="https://ui-avatars.com/api/?name=<?= urlencode($c['nombre']) ?>&background=3b5bdb&color=fff&size=40" alt="">
            <div class="inf">
              <div class="n"><?= htmlspecialchars($c['nombre']) ?></div>
              <div class="s"><?= htmlspecialchars($c['email']) ?></div>
            </div>
            <div class="m"><?= date('M d', strtotime($c['fecha_creacion'])) ?></div>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="panel">
        <div class="p-head"><span class="p-title">Stock Overview</span><a href="productos.php" class="p-link">Manage →</a></div>
        <?php foreach ($stockBajo as $p): ?>
          <div class="li">
            <div style="width:34px;height:34px;background:#1e2535;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
              <i class="fa-solid fa-gamepad" style="color:#475569;font-size:12px"></i>
            </div>
            <div class="inf">
              <div class="n"><?= htmlspecialchars($p['titulo']) ?></div>
              <div class="s"><?= number_format($p['precio'], 2) ?>€</div>
            </div>
            <span class="badge <?= $p['stock'] <= 5 ? 'br' : ($p['stock'] <= 15 ? 'by' : 'bg') ?>"><?= $p['stock'] ?> left</span>
          </div>
        <?php endforeach; ?>
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