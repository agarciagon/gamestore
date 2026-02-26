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
    $stmt = $pdo->prepare("SELECT id_usuario, nombre, email, fecha_creacion FROM usuarios WHERE rol='cliente' AND (nombre LIKE ? OR email LIKE ?) ORDER BY fecha_creacion DESC");
    $stmt->execute(["%$search%", "%$search%"]);
} else {
    $stmt = $pdo->query("SELECT id_usuario, nombre, email, fecha_creacion FROM usuarios WHERE rol='cliente' ORDER BY fecha_creacion DESC");
}
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
$total = count($clientes);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="../dist/output.css">
    <link rel="stylesheet" href="../assets/css/input.css">
    <title>Customers — Admin</title>

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
            <a href="dashboard.php" class="nav-link"><i class="fa-solid fa-house"></i> Dashboard</a>
            <a href="clientes.php" class="nav-link active"><i class="fa-solid fa-users"></i> Customers</a>
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
        <span class="tb-title">Customers</span>
    </header>

    <main id="content">
        <div class="page-header">
            <div>
                <h1 class="page-title">Customers</h1>
                <p class="page-sub"><?= $total ?> registered customer<?= $total !== 1 ? 's' : '' ?></p>
            </div>
        </div>

        <form method="GET" class="search-row">
            <div class="sw"><i class="fa-solid fa-magnifying-glass"></i><input type="search" name="q" class="si" value="<?= htmlspecialchars($search) ?>" placeholder="Search by name or email..."></div>
            <button type="submit" class="btn btn-primary">Search</button>
            <?php if ($search): ?><a href="clientes.php" class="btn btn-ghost">Clear</a><?php endif; ?>
        </form>

        <div class="panel">
            <table>
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Email</th>
                        <th>Joined</th>
                        <th style="text-align:right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($clientes)): ?>
                        <tr>
                            <td colspan="4">
                                <div class="empty-state"><i class="fa-solid fa-users"></i>No customers found</div>
                            </td>
                        </tr>
                        <?php else: foreach ($clientes as $c): ?>
                            <tr>
                                <td>
                                    <div style="display:flex;align-items:center;gap:10px">
                                        <img class="avatar" src="https://ui-avatars.com/api/?name=<?= urlencode($c['nombre']) ?>&background=3b5bdb&color=fff&size=40" alt="">
                                        <div>
                                            <div style="font-weight:500;color:#e2e8f0"><?= htmlspecialchars($c['nombre']) ?></div>
                                            <div style="font-size:11px;color:#64748b">#<?= $c['id_usuario'] ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td style="color:#94a3b8"><?= htmlspecialchars($c['email']) ?></td>
                                <td style="color:#64748b"><?= date('M d, Y', strtotime($c['fecha_creacion'])) ?></td>
                                <td style="text-align:right">
                                    <button onclick="confirmDelete(<?= $c['id_usuario'] ?>, '<?= htmlspecialchars($c['nombre'], ENT_QUOTES) ?>')" class="btn btn-danger">
                                        <i class="fa-solid fa-trash" style="font-size:11px"></i> Delete
                                    </button>
                                    <form id="df-<?= $c['id_usuario'] ?>" method="POST" action="cliente_delete.php" style="display:none">
                                        <input type="hidden" name="id" value="<?= $c['id_usuario'] ?>">
                                    </form>
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
            <div class="modal-title">Delete Customer</div>
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