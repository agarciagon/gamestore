<?php
// app/Views/layouts/admin.php

$pageTitle  = $pageTitle  ?? 'Admin Panel';
$activePage = $activePage ?? '';
$adminName  = $adminName  ?? ($_SESSION['user_name'] ?? 'Admin');

$navItems = [
    'dashboard'    => ['icon' => 'fa-house',      'label' => 'Dashboard',  'href' => '/gamestore/public/admin'],
    'clientes'     => ['icon' => 'fa-users',      'label' => 'Customers',  'href' => '/gamestore/public/admin/clientes'],
    'productos'    => ['icon' => 'fa-box',        'label' => 'Products',   'href' => '/gamestore/public/admin/productos'],
    'pedidos'      => ['icon' => 'fa-receipt',    'label' => 'Orders',     'href' => '/gamestore/public/admin/pedidos'],
    'estadisticas' => ['icon' => 'fa-chart-line', 'label' => 'Statistics', 'href' => '/gamestore/public/admin/estadisticas'],
];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="/gamestore/public/dist/output.css">
    <link rel="stylesheet" href="/gamestore/public/assets/css/admin.css">
    <title><?= htmlspecialchars($pageTitle) ?> — Admin</title>
</head>

<body>

    <!-- ═══ SIDEBAR ══════════════════════════════════════════════════════════ -->
    <aside id="sidebar">
        <div class="s-logo">
            <div class="t">GameStore</div>
            <div class="st">Admin Panel</div>
        </div>

        <nav class="s-nav">
            <div class="s-sec">Main</div>
            <?php foreach ($navItems as $key => $item): ?>
                <a href="<?= $item['href'] ?>"
                    class="nav-link <?= $activePage === $key ? 'active' : '' ?>">
                    <i class="fa-solid <?= $item['icon'] ?>"></i>
                    <span class="s-label"><?= $item['label'] ?></span>
                </a>
            <?php endforeach; ?>

            <div class="s-sec">Store</div>
            <a href="/gamestore/public/catalogo" target="_blank" class="nav-link">
                <i class="fa-solid fa-store"></i>
                <span class="s-label">View Store</span>
            </a>
        </nav>

        <div class="s-foot">
            <div class="u-info">
                <img src="https://ui-avatars.com/api/?name=<?= urlencode($adminName) ?>&background=635bff&color=fff" alt="">
                <div>
                    <div class="u-name"><?= htmlspecialchars($adminName) ?></div>
                    <div class="u-role">Administrator</div>
                </div>
            </div>
            <a href="/gamestore/public/auth/logout?from=admin" class="logout-btn">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Logout</span>
            </a>
        </div>
    </aside>

    <!-- ═══ TOPBAR ════════════════════════════════════════════════════════════ -->
    <header id="topbar">
        <button id="tbtn" onclick="toggleSidebar()">
            <i class="fa-solid fa-bars"></i>
        </button>
        <span class="tb-title"><?= htmlspecialchars($pageTitle) ?></span>
        <span class="tb-date"><?= date('D, M j Y') ?></span>
    </header>

    <!-- ═══ MAIN CONTENT ══════════════════════════════════════════════════════ -->
    <main id="content">
        <?= $content ?? '' ?>
    </main>

    <!-- ═══ CONFIRM MODAL ════════════════════════════════════════════════════ -->
    <div id="confirmModal">
        <div class="modal-box">
            <div class="modal-icon"><i class="fa-solid fa-triangle-exclamation"></i></div>
            <div class="modal-title">Confirm action</div>
            <div class="modal-body" id="confirmMsg">Are you sure?</div>
            <div class="modal-actions">
                <button class="btn-secondary" onclick="closeConfirmModal()">Cancel</button>
                <button class="btn-danger" id="confirmBtn">Delete</button>
            </div>
        </div>
    </div>

    <!-- ═══ TOAST ═════════════════════════════════════════════════════════════ -->
    <div id="toastContainer"></div>

    <script src="/gamestore/public/js/app.js"></script>
    <script>
        // ── Sidebar ───────────────────────────────────────────────────────────────
        let _collapsed = false;

        function toggleSidebar() {
            const sb = document.getElementById('sidebar');
            const tb = document.getElementById('topbar');
            const ct = document.getElementById('content');
            if (window.innerWidth <= 768) {
                sb.classList.toggle('mobile-open');
                return;
            }
            _collapsed = !_collapsed;
            sb.classList.toggle('collapsed', _collapsed);
            tb.classList.toggle('exp', _collapsed);
            ct.classList.toggle('exp', _collapsed);
        }
        document.addEventListener('click', e => {
            const sb = document.getElementById('sidebar');
            const btn = document.getElementById('tbtn');
            if (window.innerWidth <= 768 &&
                sb.classList.contains('mobile-open') &&
                !sb.contains(e.target) &&
                !btn.contains(e.target)) {
                sb.classList.remove('mobile-open');
            }
        });

        // ── Confirm modal ─────────────────────────────────────────────────────────
        let _cb = null;

        function showConfirmModal(msg, callback, label = 'Delete') {
            document.getElementById('confirmMsg').textContent = msg;
            document.getElementById('confirmBtn').textContent = label;
            document.getElementById('confirmModal').classList.add('open');
            _cb = callback;
        }

        function closeConfirmModal() {
            document.getElementById('confirmModal').classList.remove('open');
            _cb = null;
        }

        document.getElementById('confirmBtn').addEventListener('click', () => {
            const cb = _cb;
            closeConfirmModal();
            if (cb) cb();
        });

        document.getElementById('confirmModal').addEventListener('click', function(e) {
            if (e.target === this) closeConfirmModal();
        });

        // ── Toast ─────────────────────────────────────────────────────────────────
        function showToast(type, msg) {
            const c = document.getElementById('toastContainer');
            const el = document.createElement('div');
            el.className = 'toast ' + type;
            el.innerHTML = `${type === 'success'
        ? '<i class="fa-solid fa-circle-check"></i>'
        : '<i class="fa-solid fa-circle-xmark"></i>'}
        <span>${msg}</span>
        <button class="toast-close" onclick="this.parentElement.remove()">
            <i class="fa-solid fa-xmark"></i>
        </button>`;
            c.appendChild(el);
            setTimeout(() => el.remove(), 4500);
        }

        function showErrorModal(msg) {
            showToast('error', msg);
        }

        // ── Toggle order (perfil) ─────────────────────────────────────────────────
        function toggleOrder(id) {
            const el = document.getElementById('order-' + id);
            const icon = document.getElementById('icon-' + id);
            if (!el) return;
            el.classList.toggle('hidden');
            if (icon) {
                icon.classList.toggle('fa-chevron-down', el.classList.contains('hidden'));
                icon.classList.toggle('fa-chevron-up', !el.classList.contains('hidden'));
            }
        }
    </script>

    <?php if (isset($extraScripts)): ?>
        <?php foreach ($extraScripts as $src): ?>
            <script src="<?= htmlspecialchars($src) ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>

</body>

</html>