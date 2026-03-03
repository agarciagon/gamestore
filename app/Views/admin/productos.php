<?php
// app/Views/admin/productos.php
$pageTitle  = 'Products';
$activePage = 'productos';
$success    = $success ?? null;
$error      = $error   ?? null;
ob_start();
?>
<?php if ($success): ?><script>
        document.addEventListener('DOMContentLoaded', () => showToast('success', <?= json_encode($success) ?>));
    </script><?php endif; ?>
<?php if ($error): ?><script>
        document.addEventListener('DOMContentLoaded', () => showToast('error', <?= json_encode($error) ?>));
    </script><?php endif; ?>

<!-- Header -->
<div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:28px;flex-wrap:wrap;gap:16px;">
    <div>
        <h1 style="font-size:22px;font-weight:700;color:var(--text);">Products</h1>
        <p style="font-size:13px;color:var(--muted);margin-top:4px;"><?= count($juegos) ?> products in catalog</p>
    </div>
    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
        <form method="GET" action="/gamestore/public/admin/productos" style="display:flex;gap:8px;">
            <div style="position:relative;">
                <i class="fa-solid fa-magnifying-glass" style="position:absolute;left:11px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:12px;pointer-events:none;"></i>
                <input type="search" name="q" value="<?= htmlspecialchars($search) ?>"
                    placeholder="Search products…"
                    style="padding:8px 14px 8px 34px;border:1px solid var(--border);border-radius:8px;font-size:13px;color:var(--text);background:var(--surface);outline:none;width:240px;"
                    onfocus="this.style.borderColor='var(--accent)';this.style.boxShadow='0 0 0 3px rgba(99,91,255,.1)'"
                    onblur="this.style.borderColor='var(--border)';this.style.boxShadow='none'">
            </div>
            <button class="btn-secondary" type="submit" style="padding:8px 14px;font-size:13px;">Search</button>
            <?php if ($search): ?>
                <a href="/gamestore/public/admin/productos" class="btn-secondary" style="padding:8px 12px;">
                    <i class="fa-solid fa-xmark"></i>
                </a>
            <?php endif; ?>
        </form>
        <a href="/gamestore/public/admin/productos/form" class="btn-primary">
            <i class="fa-solid fa-plus"></i> Add Product
        </a>
    </div>
</div>

<!-- Stock summary pills -->
<?php
$lowStock  = count(array_filter($juegos, fn($j) => $j['stock'] <= 5));
$midStock  = count(array_filter($juegos, fn($j) => $j['stock'] > 5 && $j['stock'] <= 12));
$goodStock = count(array_filter($juegos, fn($j) => $j['stock'] > 12));
?>
<div style="display:flex;gap:10px;margin-bottom:20px;flex-wrap:wrap;">
    <div class="stock-badge" data-type="good" onclick="filterByStock('good')"
        style="padding:8px 16px;background:var(--surface);border:1px solid var(--border);border-radius:20px;font-size:12px;font-weight:600;color:var(--text);display:flex;align-items:center;gap:6px;cursor:pointer;transition:all .2s;">
        <span style="width:8px;height:8px;border-radius:50%;background:#1a9e6e;display:inline-block;"></span>
        <?= $goodStock ?> good stock
    </div>
    <div class="stock-badge" data-type="low" onclick="filterByStock('low')"
        style="padding:8px 16px;background:var(--surface);border:1px solid var(--border);border-radius:20px;font-size:12px;font-weight:600;color:var(--text);display:flex;align-items:center;gap:6px;cursor:pointer;transition:all .2s;">
        <span style="width:8px;height:8px;border-radius:50%;background:#b7791f;display:inline-block;"></span>
        <?= $midStock ?> low stock
    </div>
    <div class="stock-badge" data-type="critical" onclick="filterByStock('critical')"
        style="padding:8px 16px;background:#fef2f2;border:1px solid #fca5a5;border-radius:20px;font-size:12px;font-weight:600;color:#c0392b;display:flex;align-items:center;gap:6px;cursor:pointer;transition:all .2s;">
        <span style="width:8px;height:8px;border-radius:50%;background:#ef4444;display:inline-block;"></span>
        <?= $lowStock ?> critical
    </div>
</div>

<!-- Table -->
<div style="width:100%; overflow-x:auto; border:1px solid var(--border); border-radius:12px; background:var(--surface);">
    <table class="stripe-table" style="width:100%; min-width:600px; border-collapse:collapse;">
        <thead>
            <tr>
                <th style="width:60px;">Cover</th>
                <th>Product</th>
                <th>Genre</th>
                <th>Platform</th>
                <th>Price</th>
                <th>Stock</th>
                <th style="text-align:right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($juegos as $j):
                $stockType = $j['stock'] <= 5 ? 'critical' : ($j['stock'] <= 12 ? 'low' : 'good');
            ?>
                <tr data-stock-type="<?= $stockType ?>">
                    <td>
                        <?php if (!empty($j['imagen_portada'])): ?>
                            <img src="/gamestore/public/assets/img/caratulas/<?= htmlspecialchars($j['imagen_portada']) ?>"
                                style="width:44px;height:44px;object-fit:cover;border-radius:8px;border:1px solid var(--border);" alt="">
                        <?php else: ?>
                            <div style="width:44px;height:44px;background:var(--bg);border-radius:8px;display:flex;align-items:center;justify-content:center;border:1px solid var(--border);">
                                <i class="fa-solid fa-gamepad" style="color:var(--muted);font-size:15px;"></i>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div style="font-size:13px;font-weight:600;color:var(--text);max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                            <?= htmlspecialchars($j['titulo']) ?>
                        </div>
                        <div style="font-size:11px;color:var(--muted);margin-top:2px;"><?= htmlspecialchars($j['desarrollador'] ?? '') ?></div>
                    </td>
                    <td>
                        <span style="font-size:12px;background:#eef2ff;color:#635bff;padding:3px 9px;border-radius:20px;font-weight:600;">
                            <?= htmlspecialchars($j['genero']) ?>
                        </span>
                    </td>
                    <td>
                        <span style="font-size:12px;color:var(--muted);font-weight:500;"><?= htmlspecialchars($j['plataforma']) ?></span>
                    </td>
                    <td>
                        <span style="font-size:14px;font-weight:700;color:var(--text);"><?= number_format($j['precio'], 2) ?>€</span>
                    </td>
                    <td>
                        <?php
                        if ($j['stock'] <= 5) {
                            $sb = '#fef2f2';
                            $sc = '#ef4444';
                            $sl = 'Critical';
                        } elseif ($j['stock'] <= 12) {
                            $sb = '#fef9c3';
                            $sc = '#854d0e';
                            $sl = 'Low';
                        } else {
                            $sb = '#d4f5e9';
                            $sc = '#1a9e6e';
                            $sl = 'OK';
                        }
                        ?>
                        <div style="display:flex;align-items:center;gap:7px;">
                            <span style="font-size:13px;font-weight:700;color:var(--text);"><?= $j['stock'] ?></span>
                            <span style="font-size:11px;font-weight:600;padding:2px 8px;border-radius:20px;background:<?= $sb ?>;color:<?= $sc ?>;"><?= $sl ?></span>
                        </div>
                    </td>
                    <td style="text-align:right;">
                        <div style="display:flex;gap:6px;justify-content:flex-end;align-items:center;">
                            <a href="/gamestore/public/admin/productos/form?id=<?= $j['id'] ?>"
                                style="padding:5px 12px;background:#eef2ff;color:#635bff;border-radius:7px;font-size:12px;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:5px;transition:background .15s;"
                                onmouseover="this.style.background='#e0e7ff'" onmouseout="this.style.background='#eef2ff'">
                                <i class="fa-solid fa-pen" style="font-size:11px;"></i>
                            </a>
                            <form id="del-p-<?= $j['id'] ?>" method="POST" action="/gamestore/public/admin/productos/delete" style="display:inline;">
                                <?= \Csrf::field() ?>
                                <input type="hidden" name="id" value="<?= $j['id'] ?>">
                                <button type="button" onclick="confirmDelProd(<?= $j['id'] ?>, '<?= addslashes($j['titulo']) ?>')"
                                    style="padding:5px 10px;background:#fef2f2;border:1px solid #fca5a5;color:#c0392b;border-radius:7px;font-size:12px;font-weight:600;cursor:pointer;display:inline-flex;align-items:center;gap:5px;transition:background .15s;"
                                    onmouseover="this.style.background='#fee2e2'" onmouseout="this.style.background='#fef2f2'">
                                    <i class="fa-solid fa-trash" style="font-size:11px;"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($juegos)): ?>
                <tr>
                    <td colspan="7" style="text-align:center;padding:56px;">
                        <div style="display:inline-flex;flex-direction:column;align-items:center;gap:10px;">
                            <div style="width:52px;height:52px;background:var(--bg);border-radius:50%;display:flex;align-items:center;justify-content:center;">
                                <i class="fa-solid fa-box" style="font-size:20px;color:var(--muted);opacity:.5;"></i>
                            </div>
                            <div style="font-size:14px;font-weight:600;color:var(--text);">No products found</div>
                            <a href="/gamestore/public/admin/productos/form" class="btn-primary" style="margin-top:4px;">Add first product</a>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script>
    function confirmDelProd(id, name) {
        showConfirmModal(`Delete "${name}"? This cannot be undone.`, () => {
            const f = document.getElementById('del-p-' + id);
            if (f) f.submit();
        });
    }

    let activeFilter = null;

    function filterByStock(type) {
        activeFilter = activeFilter === type ? null : type;

        document.querySelectorAll('.stock-badge').forEach(b => {
            b.style.opacity = activeFilter && b.dataset.type !== activeFilter ? '0.4' : '1';
            b.style.transform = b.dataset.type === activeFilter ? 'scale(1.05)' : 'scale(1)';
        });

        const tbody = document.querySelector('tbody');
        const rows = tbody.querySelectorAll('tr[data-stock-type]');
        let visibleCount = 0;

        rows.forEach(row => {
            if (!activeFilter || row.dataset.stockType === activeFilter) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        // Eliminar fila de "No products found" existente
        const existingMsg = tbody.querySelector('.no-products-msg');
        if (existingMsg) existingMsg.remove();

        // Si no hay filas visibles, insertar mensaje dentro de la tabla
        if (activeFilter && visibleCount === 0) {
            const tr = document.createElement('tr');
            tr.classList.add('no-products-msg');
            tr.innerHTML = `
            <td colspan="7" style="text-align:center;padding:56px;">
                <div style="display:inline-flex;flex-direction:column;align-items:center;gap:10px;">
                    <div style="width:52px;height:52px;background:var(--bg);border-radius:50%;display:flex;align-items:center;justify-content:center;">
                        <i class="fa-solid fa-box" style="font-size:20px;color:var(--muted);opacity:.5;"></i>
                    </div>
                    <div style="font-size:14px;font-weight:600;color:var(--text);">
                        No products found for this stock type
                    </div>
                </div>
            </td>
        `;
            tbody.appendChild(tr);
        }
    }
</script>

<?php
$content = ob_get_clean();
include BASE_PATH . '/app/Views/layouts/admin.php';
