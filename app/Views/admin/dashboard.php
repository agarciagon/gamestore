<?php
// app/Views/admin/dashboard.php
$pageTitle  = 'Dashboard';
$activePage = 'dashboard';
ob_start();
?>

<!-- Bienvenida -->
<div style="margin-bottom:28px;">
    <h1 style="font-size:22px;font-weight:700;color:var(--text);">Good <?= (date('H') < 12 ? 'morning' : (date('H') < 18 ? 'afternoon' : 'evening')) ?>, <?= htmlspecialchars(explode(' ', $adminName)[0]) ?> 👋</h1>
    <p style="font-size:13px;color:var(--muted);margin-top:4px;">Here's what's happening in your store today.</p>
</div>

<!-- KPI cards -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:28px;">

    <div style="background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:20px;display:flex;flex-direction:column;gap:12px;">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <span style="font-size:12px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;">Customers</span>
            <div style="width:36px;height:36px;background:#ebf5ff;border-radius:9px;display:flex;align-items:center;justify-content:center;">
                <i class="fa-solid fa-users" style="color:#1a56db;font-size:14px;"></i>
            </div>
        </div>
        <div style="font-size:32px;font-weight:700;color:var(--text);line-height:1;"><?= number_format($totalClientes) ?></div>
        <div style="font-size:12px;color:var(--muted);">Total registered users</div>
    </div>

    <div style="background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:20px;display:flex;flex-direction:column;gap:12px;">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <span style="font-size:12px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;">Products</span>
            <div style="width:36px;height:36px;background:#eef2ff;border-radius:9px;display:flex;align-items:center;justify-content:center;">
                <i class="fa-solid fa-gamepad" style="color:#635bff;font-size:14px;"></i>
            </div>
        </div>
        <div style="font-size:32px;font-weight:700;color:var(--text);line-height:1;"><?= number_format($statsJuegos['total_productos']) ?></div>
        <div style="font-size:12px;color:var(--muted);">In catalog</div>
    </div>

    <div style="background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:20px;display:flex;flex-direction:column;gap:12px;">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <span style="font-size:12px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;">Stock</span>
            <div style="width:36px;height:36px;background:#d4f5e9;border-radius:9px;display:flex;align-items:center;justify-content:center;">
                <i class="fa-solid fa-boxes-stacked" style="color:#1a9e6e;font-size:14px;"></i>
            </div>
        </div>
        <div style="font-size:32px;font-weight:700;color:var(--text);line-height:1;"><?= number_format($statsJuegos['total_stock']) ?></div>
        <div style="font-size:12px;color:var(--muted);">Total units available</div>
    </div>

    <div style="background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:20px;display:flex;flex-direction:column;gap:12px;">
        <div style="display:flex;align-items:center;justify-content:space-between;">
            <span style="font-size:12px;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.05em;">Inventory Value</span>
            <div style="width:36px;height:36px;background:#fef3c7;border-radius:9px;display:flex;align-items:center;justify-content:center;">
                <i class="fa-solid fa-euro-sign" style="color:#b7791f;font-size:14px;"></i>
            </div>
        </div>
        <div style="font-size:32px;font-weight:700;color:var(--text);line-height:1;"><?= number_format($statsJuegos['valor_inventario'], 0) ?>€</div>
        <div style="font-size:12px;color:var(--muted);">At current prices</div>
    </div>

</div>

<!-- Two columns -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

    <!-- Recent customers -->
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:12px;overflow:hidden;">
        <div style="padding:18px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;">
            <div>
                <div style="font-size:14px;font-weight:700;color:var(--text);">Recent Customers</div>
                <div style="font-size:12px;color:var(--muted);margin-top:2px;">Latest registrations</div>
            </div>
            <a href="/gamestore/public/admin/clientes"
               style="font-size:12px;color:var(--accent);text-decoration:none;font-weight:600;padding:5px 12px;background:#eef2ff;border-radius:20px;">
                View all →
            </a>
        </div>
        <div style="padding:8px 0;">
            <?php foreach ($recentClientes as $c): ?>
                <div style="display:flex;align-items:center;gap:12px;padding:10px 20px;border-bottom:1px solid var(--border);">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($c['nombre']) ?>&background=635bff&color=fff&size=36"
                         style="width:36px;height:36px;border-radius:50%;flex-shrink:0;" alt="">
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:13px;font-weight:600;color:var(--text);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($c['nombre']) ?></div>
                        <div style="font-size:12px;color:var(--muted);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($c['email']) ?></div>
                    </div>
                    <span style="font-size:11px;color:var(--muted);white-space:nowrap;flex-shrink:0;"><?= date('M d', strtotime($c['fecha_creacion'])) ?></span>
                </div>
            <?php endforeach; ?>
            <?php if (empty($recentClientes)): ?>
                <div style="text-align:center;padding:32px;color:var(--muted);font-size:13px;">No customers yet</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Low stock -->
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:12px;overflow:hidden;">
        <div style="padding:18px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;">
            <div>
                <div style="font-size:14px;font-weight:700;color:var(--text);">Low Stock Alert</div>
                <div style="font-size:12px;color:var(--muted);margin-top:2px;">Products running low</div>
            </div>
            <a href="/gamestore/public/admin/productos"
               style="font-size:12px;color:#c0392b;text-decoration:none;font-weight:600;padding:5px 12px;background:#fef2f2;border-radius:20px;">
                Manage →
            </a>
        </div>
        <div style="padding:8px 0;">
            <?php foreach ($stockBajo as $j): ?>
                <div style="display:flex;align-items:center;gap:12px;padding:10px 20px;border-bottom:1px solid var(--border);">
                    <div style="width:36px;height:36px;background:#fef2f2;border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="fa-solid fa-gamepad" style="color:#ef4444;font-size:13px;"></i>
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:13px;font-weight:600;color:var(--text);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($j['titulo']) ?></div>
                    </div>
                    <span style="font-size:12px;font-weight:700;padding:3px 10px;border-radius:20px;flex-shrink:0;
                        <?= $j['stock'] <= 5 ? 'background:#fef2f2;color:#ef4444;' : 'background:#fef9c3;color:#854d0e;' ?>">
                        <?= $j['stock'] ?> left
                    </span>
                </div>
            <?php endforeach; ?>
            <?php if (empty($stockBajo)): ?>
                <div style="text-align:center;padding:32px;color:var(--muted);font-size:13px;">
                    <i class="fa-solid fa-circle-check" style="color:#1a9e6e;font-size:20px;display:block;margin-bottom:8px;"></i>
                    All stock levels are OK
                </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<!-- Quick actions -->
<div style="background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:20px;margin-top:20px;">
    <div style="font-size:14px;font-weight:700;color:var(--text);margin-bottom:16px;">Quick Actions</div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
        <a href="/gamestore/public/admin/productos/form" class="btn-primary">
            <i class="fa-solid fa-plus"></i> Add Product
        </a>
        <a href="/gamestore/public/admin/clientes" class="btn-secondary">
            <i class="fa-solid fa-users"></i> View Customers
        </a>
        <a href="/gamestore/public/admin/pedidos" class="btn-secondary">
            <i class="fa-solid fa-receipt"></i> View Orders
        </a>
        <a href="/gamestore/public/admin/estadisticas" class="btn-secondary">
            <i class="fa-solid fa-chart-line"></i> Statistics
        </a>
        <a href="/gamestore/public/catalogo" target="_blank" class="btn-secondary">
            <i class="fa-solid fa-store"></i> View Store
        </a>
    </div>
</div>

<?php
$content = ob_get_clean();
include BASE_PATH . '/app/Views/layouts/admin.php';