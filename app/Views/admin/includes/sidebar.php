<?php
// admin/includes/sidebar.php
// Incluir en cada página del admin con: require_once __DIR__ . '/includes/sidebar.php';
// $adminName y $activePage deben estar definidos antes del include.
// $activePage: 'dashboard' | 'clientes' | 'productos' | 'estadisticas'
$adminName  = $adminName  ?? ($_SESSION['user_name'] ?? 'Admin');
$activePage = $activePage ?? '';
?>
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
        <a href="dashboard.php" class="nav-link <?= $activePage === 'dashboard'   ? 'active' : '' ?>"><i class="fa-solid fa-house"></i> Dashboard</a>
        <a href="clientes.php" class="nav-link <?= $activePage === 'clientes'    ? 'active' : '' ?>"><i class="fa-solid fa-users"></i> Customers</a>
        <a href="productos.php" class="nav-link <?= $activePage === 'productos'   ? 'active' : '' ?>"><i class="fa-solid fa-box"></i> Products</a>
        <a href="estadisticas.php" class="nav-link <?= $activePage === 'estadisticas' ? 'active' : '' ?>"><i class="fa-solid fa-chart-line"></i> Statistics</a>
        <div class="s-sec">Store</div>
        <a href="../cliente/catalogo.php" target="_blank" class="nav-link">
            <i class="fa-solid fa-store"></i> View Catalog
            <i class="fa-solid fa-arrow-up-right-from-square" style="font-size:9px;margin-left:auto;opacity:.4"></i>
        </a>
    </nav>
    <div class="s-foot">
        <div class="u-info">
            <img src="https://ui-avatars.com/api/?name=<?= urlencode($adminName) ?>&background=3b5bdb&color=fff" alt="">
            <div>
                <div class="u-name"><?= htmlspecialchars($adminName) ?></div>
                <div class="u-role">Administrator</div>
            </div>
        </div>
        <a href="../auth/logout.php?from=admin" class="logout-btn">
            <i class="fa-solid fa-right-from-bracket"></i> Logout
        </a>
    </div>
</aside>