<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: login_admin.php');
    exit;
}
require_once '../config/conection.php';
$adminName = $_SESSION['user_name'] ?? 'Admin';
$search = trim($_GET['q'] ?? '');
if ($search) {
    $stmt = $pdo->prepare("SELECT * FROM videojuego WHERE titulo LIKE ? OR genero LIKE ? OR desarrollador LIKE ? ORDER BY fecha_creacion DESC");
    $stmt->execute(["%$search%", "%$search%", "%$search%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM videojuego ORDER BY fecha_creacion DESC");
}
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
$total = count($productos);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="../dist/output.css">
    <link rel="stylesheet" href="../assets/css/input.css">
    <title>Products — Admin</title>
</head>

<body>

    <!-- Sidebar -->
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

    <header id="topbar">
        <button id="tbtn" onclick="toggle()"><i class="fa-solid fa-bars"></i></button>
        <span class="tb-title">Products</span>
    </header>

    <main id="content">
        <div class="page-header">
            <div>
                <h1 class="page-title">Products</h1>
                <p class="page-sub"><?= $total ?> product<?= $total !== 1 ? 's' : '' ?> in catalog</p>
            </div>
            <a href="gestion_productos.php" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Add Product</a>
        </div>

        <form method="GET" class="search-row">
            <div class="sw"><i class="fa-solid fa-magnifying-glass"></i><input type="search" name="q" class="si" value="<?= htmlspecialchars($search) ?>" placeholder="Search by title, genre or developer..."></div>
            <button type="submit" class="btn btn-primary">Search</button>
            <?php if ($search): ?><a href="productos.php" class="btn btn-ghost">Clear</a><?php endif; ?>
        </form>

        <div class="panel">
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Genre</th>
                        <th>Platform</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th style="text-align:right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($productos)): ?>
                        <tr>
                            <td colspan="6">
                                <div class="empty-state"><i class="fa-solid fa-box"></i>No products found</div>
                            </td>
                        </tr>
                        <?php else: foreach ($productos as $p): ?>
                            <tr>
                                <td>
                                    <div style="display:flex;align-items:center;gap:10px">
                                        <?php $img = '../assets/img/caratulas/' . $p['imagen_portada']; ?>
                                        <?php if (!empty($p['imagen_portada']) && file_exists($img)): ?>
                                            <img src="<?= htmlspecialchars($img) ?>" style="width:36px;height:44px;object-fit:cover;border-radius:6px;flex-shrink:0">
                                        <?php else: ?>
                                            <div style="width:36px;height:44px;background:#1e2535;border-radius:6px;display:flex;align-items:center;justify-content:center;flex-shrink:0"><i class="fa-solid fa-gamepad" style="color:#475569;font-size:12px"></i></div>
                                        <?php endif; ?>
                                        <div>
                                            <div style="font-weight:500;color:#e2e8f0;max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= htmlspecialchars($p['titulo']) ?></div>
                                            <div style="font-size:11px;color:#64748b"><?= htmlspecialchars($p['desarrollador']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="genre-badge"><?= htmlspecialchars($p['genero']) ?></span></td>
                                <td style="color:#94a3b8;font-size:12px"><?= htmlspecialchars($p['plataforma']) ?></td>
                                <td style="font-weight:600;color:#e2e8f0"><?= $p['precio'] > 0 ? number_format($p['precio'], 2) . '€' : '<span style="color:#34d399">FREE</span>' ?></td>
                                <td><span class="badge <?= $p['stock'] <= 5 ? 'br' : ($p['stock'] <= 15 ? 'by' : 'bg') ?>"><?= $p['stock'] ?></span></td>
                                <td style="text-align:right">
                                    <div style="display:flex;justify-content:flex-end;gap:6px">
                                        <a href="gestion_productos.php?edit=<?= $p['id'] ?>" class="btn btn-edit"><i class="fa-solid fa-pen" style="font-size:10px"></i> Edit</a>
                                        <button onclick="confirmDelete(<?= $p['id'] ?>, '<?= htmlspecialchars($p['titulo'], ENT_QUOTES) ?>')" class="btn btn-danger"><i class="fa-solid fa-trash" style="font-size:10px"></i> Delete</button>
                                        <form id="df-<?= $p['id'] ?>" method="POST" action="producto_delete.php" style="display:none"><input type="hidden" name="id" value="<?= $p['id'] ?>"></form>
                                    </div>
                                </td>
                            </tr>
                    <?php endforeach;
                    endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <div id="modal" class="modal-bg hidden">
        <div class="modal-box">
            <div class="modal-icon"><i class="fa-solid fa-trash"></i></div>
            <div class="modal-title">Delete Product</div>
            <p id="modal-msg" class="modal-msg"></p>
            <div class="modal-actions">
                <button onclick="closeModal()" style="background:#1e2535;color:#94a3b8">Cancel</button>
                <button id="modal-ok" style="background:#dc2626;color:#fff">Delete</button>
            </div>
        </div>
    </div>

    <script>
        let col = false;

        function toggle() {
            col = !col;
            document.getElementById('sidebar').classList.toggle('collapsed', col);
            document.getElementById('topbar').classList.toggle('exp', col);
            document.getElementById('content').classList.toggle('exp', col);
        }

        function confirmDelete(id, name) {
            document.getElementById('modal-msg').textContent = `Are you sure you want to delete "${name}"? This cannot be undone.`;
            document.getElementById('modal').classList.remove('hidden');
            document.getElementById('modal-ok').onclick = () => document.getElementById('df-' + id).submit();
        }

        function closeModal() {
            document.getElementById('modal').classList.add('hidden');
        }
        document.getElementById('modal').addEventListener('click', e => {
            if (e.target === e.currentTarget) closeModal();
        });
    </script>
</body>

</html>