<?php
// app/Views/admin/pedidos.php
$pageTitle  = 'Orders';
$activePage = 'pedidos';

// ── Paginación ────────────────────────────────────────────────────────────
$perPage     = 20;
$currentPage = max(1, (int)($_GET['page'] ?? 1));
$tabActual   = $_GET['tab'] ?? 'all';

// Filtrar según tab
$pedidosFiltrados = match ($tabActual) {
    'paid'      => array_values(array_filter($pedidos, fn($p) => $p['estado'] === 'pagado')),
    'pending'   => array_values(array_filter($pedidos, fn($p) => $p['estado'] === 'pendiente')),
    'refunded'  => array_values(array_filter($pedidos, fn($p) => $p['estado'] === 'reembolsado')),
    default     => $pedidos,
};

$totalFiltered = count($pedidosFiltrados);
$totalPages    = max(1, ceil($totalFiltered / $perPage));
$currentPage   = min($currentPage, $totalPages);
$offset        = ($currentPage - 1) * $perPage;
$pedidosPaged  = array_slice($pedidosFiltrados, $offset, $perPage);

// Conteos por tab
$counts = [
    'all'      => count($pedidos),
    'paid'     => count(array_filter($pedidos, fn($p) => $p['estado'] === 'pagado')),
    'pending'  => count(array_filter($pedidos, fn($p) => $p['estado'] === 'pendiente')),
    'refunded' => count(array_filter($pedidos, fn($p) => $p['estado'] === 'reembolsado')),
    'error'    => 0,
];

ob_start();
?>

<!-- ── KPI row ─────────────────────────────────────────────────────────── -->
<div class="stat-grid mb-6">
    <div class="stat-card">
        <div class="st-icon" style="background:#eef2ff"><i class="fa-solid fa-receipt" style="color:#635bff"></i></div>
        <div class="st-val"><?= number_format($stats['total_pedidos']) ?></div>
        <div class="st-lbl">Total orders</div>
    </div>
    <div class="stat-card">
        <div class="st-icon" style="background:#d4f5e9"><i class="fa-solid fa-circle-check" style="color:#1a9e6e"></i></div>
        <div class="st-val"><?= number_format($stats['pagados']) ?></div>
        <div class="st-lbl">Successful</div>
    </div>
    <div class="stat-card">
        <div class="st-icon" style="background:#fef3c7"><i class="fa-solid fa-clock" style="color:#b7791f"></i></div>
        <div class="st-val"><?= number_format($stats['pendientes']) ?></div>
        <div class="st-lbl">Pending</div>
    </div>
    <div class="stat-card">
        <div class="st-icon" style="background:#ebf5ff"><i class="fa-solid fa-euro-sign" style="color:#1a56db"></i></div>
        <div class="st-val"><?= number_format($stats['ingresos_pagados'], 2) ?>€</div>
        <div class="st-lbl">Revenue</div>
    </div>
</div>

<!-- ── Panel principal ───────────────────────────────────────────────────── -->
<div class="panel" style="padding:0;overflow:hidden;">

    <!-- Título -->
    <div style="padding:20px 24px 0;display:flex;align-items:center;justify-content:space-between;">
        <div>
            <div style="font-size:16px;font-weight:700;color:var(--text);">Transactions</div>
            <div style="font-size:12px;color:var(--muted);margin-top:2px)"><?= number_format($stats['total_pedidos']) ?> total payments</div>
        </div>
        <div style="display:flex;gap:8px;">
            <button class="btn-secondary" style="font-size:12px;padding:6px 12px;" onclick="exportCSV()">
                <i class="fa-solid fa-arrow-up-from-bracket"></i> Export
            </button>
            <button class="btn-secondary" style="font-size:12px;padding:6px 12px;">
                <i class="fa-solid fa-sliders"></i> Edit columns
            </button>
        </div>
    </div>

    <!-- ── TABS ──────────────────────────────────────────────────────────── -->
    <div class="stripe-tabs" style="margin:16px 0 0;padding:0 24px;">
        <?php
        $tabs = [
            'all'      => 'All',
            'paid'     => 'Successful',
            'refunded' => 'Refunded',
            'pending'  => 'Pending',
            'error'    => 'Error',
        ];
        foreach ($tabs as $key => $label):
            $active = $tabActual === $key ? 'active' : '';
            $url    = '?tab=' . $key;
        ?>
            <a href="<?= $url ?>" class="stripe-tab <?= $active ?>">
                <?= $label ?>
                <span class="tab-count"><?= $counts[$key] ?? 0 ?></span>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- ── FILTER BAR ────────────────────────────────────────────────────── -->
    <div class="filter-bar">
        <button class="filter-pill">
            <i class="fa-solid fa-calendar"></i> Date <i class="fa-solid fa-chevron-down"></i>
        </button>
        <button class="filter-pill">
            <i class="fa-solid fa-euro-sign"></i> Amount <i class="fa-solid fa-chevron-down"></i>
        </button>
        <button class="filter-pill">
            <i class="fa-solid fa-circle-half-stroke"></i> Status <i class="fa-solid fa-chevron-down"></i>
        </button>
        <button class="filter-pill">
            <i class="fa-solid fa-credit-card"></i> Payment method <i class="fa-solid fa-chevron-down"></i>
        </button>
        <button class="filter-pill">
            <i class="fa-solid fa-filter"></i> More filters
        </button>
        <a class="btn-export" id="exportBtn" href="#">
            <i class="fa-solid fa-arrow-up-from-bracket"></i> Export
        </a>
    </div>

    <!-- ── TABLA DESKTOP ─────────────────────────────────────────────────── -->
    <div style="overflow-x:auto;" id="tableWrapper">
        <table class="stripe-table" id="ordersTable">
            <thead>
                <tr>
                    <th style="width:40px;">
                        <input type="checkbox" id="checkAll" onchange="toggleAll(this)">
                    </th>
                    <th>Amount</th>
                    <th>Payment method</th>
                    <th>Description</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>Status</th>
                    <th style="text-align:right;">Invoice</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pedidosPaged)): ?>
                    <tr>
                        <td colspan="8" style="text-align:center;padding:48px;color:var(--muted);">
                            <i class="fa-solid fa-receipt" style="font-size:32px;display:block;margin-bottom:12px;opacity:.3;"></i>
                            No orders found
                        </td>
                    </tr>
                <?php endif; ?>
                <?php foreach ($pedidosPaged as $p):
                    $estadoClass = match ($p['estado']) {
                        'pagado'      => 'paid',
                        'pendiente'   => 'pending',
                        'reembolsado' => 'refunded',
                        default       => 'unknown',
                    };
                    $estadoLabel = match ($p['estado']) {
                        'pagado'      => 'Successful',
                        'pendiente'   => 'Pending',
                        'reembolsado' => 'Refunded',
                        default       => 'Unknown',
                    };
                ?>
                    <tr>
                        <!-- Checkbox -->
                        <td><input type="checkbox" class="row-check" value="<?= $p['id'] ?>"></td>

                        <!-- Amount -->
                        <td>
                            <span style="font-weight:600;color:var(--text);">
                                <?= number_format($p['total'], 2) ?> €
                            </span>
                            <span style="font-size:11px;color:var(--muted);margin-left:4px;">EUR</span>
                        </td>

                        <!-- Payment method -->
                        <td>
                            <div class="payment-method">
                                <div class="card-icon">
                                    <i class="fa-brands fa-cc-visa"></i>
                                </div>
                                <span class="card-dots">•••• 4242</span>
                            </div>
                        </td>

                        <!-- Description (Stripe ID) -->
                        <td>
                            <?php if (!empty($p['stripe_sid'])): ?>
                                <span style="font-family:monospace;font-size:12px;color:var(--accent);"
                                    title="<?= htmlspecialchars($p['stripe_sid']) ?>">
                                    <?= htmlspecialchars(substr($p['stripe_sid'], 0, 26)) ?>
                                </span>
                            <?php else: ?>
                                <span style="font-family:monospace;font-size:12px;color:var(--muted);">
                                    #<?= str_pad($p['id'], 8, '0', STR_PAD_LEFT) ?>
                                </span>
                            <?php endif; ?>
                        </td>

                        <!-- Customer -->
                        <td>
                            <div style="display:flex;align-items:center;gap:8px;">
                                <img src="https://ui-avatars.com/api/?name=<?= urlencode($p['cliente_nombre'] ?? 'U') ?>&background=635bff&color=fff&size=26"
                                    style="width:26px;height:26px;border-radius:50%;flex-shrink:0;" alt="">
                                <div>
                                    <div style="font-size:13px;font-weight:500;color:var(--text);">
                                        <?= htmlspecialchars($p['cliente_nombre'] ?? '—') ?>
                                    </div>
                                    <div style="font-size:11px;color:var(--muted);">
                                        <?= htmlspecialchars($p['cliente_email'] ?? '') ?>
                                    </div>
                                </div>
                            </div>
                        </td>

                        <!-- Date -->
                        <td style="white-space:nowrap;">
                            <div style="font-size:13px;color:var(--text);">
                                <?= date('d M Y', strtotime($p['fecha_pedido'])) ?>
                            </div>
                            <div style="font-size:11px;color:var(--muted);">
                                <?= date('H:i', strtotime($p['fecha_pedido'])) ?>
                            </div>
                        </td>

                        <!-- Status -->
                        <td>
                            <span class="status-badge <?= $estadoClass ?>">
                                <?= $estadoLabel ?>
                            </span>
                        </td>

                        <!-- Invoice -->
                        <td style="text-align:right;">
                            <a href="/gamestore/public/factura?id=<?= $p['id'] ?>" target="_blank"
                                style="font-size:12px;color:var(--accent);text-decoration:none;font-weight:500;display:inline-flex;align-items:center;gap:4px;">
                                <i class="fa-solid fa-file-pdf"></i> PDF
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- ── PAGINACIÓN ────────────────────────────────────────────────────── -->
    <div class="stripe-pagination">
        <span>
            Showing <?= $totalFiltered === 0 ? 0 : $offset + 1 ?>–<?= min($offset + $perPage, $totalFiltered) ?>
            of <?= number_format($totalFiltered) ?> results
        </span>
        <div class="pagination-btns">
            <?php if ($currentPage > 1): ?>
                <a href="?tab=<?= $tabActual ?>&page=<?= $currentPage - 1 ?>" class="page-btn">
                    ← Previous
                </a>
            <?php else: ?>
                <span class="page-btn disabled">← Previous</span>
            <?php endif; ?>

            <?php if ($currentPage < $totalPages): ?>
                <a href="?tab=<?= $tabActual ?>&page=<?= $currentPage + 1 ?>" class="page-btn">
                    Next →
                </a>
            <?php else: ?>
                <span class="page-btn disabled">Next →</span>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ── CARDS MÓVIL ───────────────────────────────────────────────────────── -->
<div style="display:none;" class="mobile-cards" id="mobileCards">
    <?php foreach ($pedidosPaged as $p):
        $estadoClass = match ($p['estado']) {
            'pagado'      => 'paid',
            'pendiente'   => 'pending',
            'reembolsado' => 'refunded',
            default       => 'unknown',
        };
        $estadoLabel = match ($p['estado']) {
            'pagado'      => 'Successful',
            'pendiente'   => 'Pending',
            'reembolsado' => 'Refunded',
            default       => 'Unknown',
        };
    ?>
        <div class="panel" style="margin-bottom:10px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
                <div style="display:flex;align-items:center;gap:8px;">
                    <img src="https://ui-avatars.com/api/?name=<?= urlencode($p['cliente_nombre'] ?? 'U') ?>&background=635bff&color=fff&size=32"
                        style="width:32px;height:32px;border-radius:50%;" alt="">
                    <div>
                        <div style="font-size:13px;font-weight:600;"><?= htmlspecialchars($p['cliente_nombre'] ?? '—') ?></div>
                        <div style="font-size:11px;color:var(--muted);"><?= htmlspecialchars($p['cliente_email'] ?? '') ?></div>
                    </div>
                </div>
                <span class="status-badge <?= $estadoClass ?>"><?= $estadoLabel ?></span>
            </div>
            <div style="display:flex;align-items:center;justify-content:space-between;">
                <div>
                    <div style="font-size:18px;font-weight:700;"><?= number_format($p['total'], 2) ?>€</div>
                    <div style="font-size:11px;color:var(--muted);"><?= date('d M Y H:i', strtotime($p['fecha_pedido'])) ?></div>
                </div>
                <a href="/gamestore/public/factura?id=<?= $p['id'] ?>" target="_blank"
                    style="font-size:12px;color:var(--accent);font-weight:500;display:flex;align-items:center;gap:4px;text-decoration:none;">
                    <i class="fa-solid fa-file-pdf"></i> PDF
                </a>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<script>
    // Mostrar cards en móvil
    if (window.innerWidth <= 768) {
        document.getElementById('tableWrapper').style.display = 'none';
        document.getElementById('mobileCards').style.display = 'block';
    }

    // Select all checkboxes
    function toggleAll(master) {
        document.querySelectorAll('.row-check').forEach(cb => cb.checked = master.checked);
    }

    // Export CSV
    function exportCSV() {
        const rows = [
            ['ID', 'Customer', 'Email', 'Amount', 'Status', 'Date']
        ];
        document.querySelectorAll('#ordersTable tbody tr').forEach(tr => {
            const cells = tr.querySelectorAll('td');
            if (cells.length < 7) return;
            rows.push([
                cells[3].textContent.trim(),
                cells[4].querySelector('div div:first-child')?.textContent.trim() ?? '',
                cells[4].querySelector('div div:last-child')?.textContent.trim() ?? '',
                cells[1].textContent.trim(),
                cells[6].textContent.trim(),
                cells[5].textContent.trim(),
            ]);
        });
        const csv = rows.map(r => r.map(c => `"${c.replace(/"/g,'""')}"`).join(',')).join('\n');
        const blob = new Blob([csv], {
            type: 'text/csv'
        });
        const a = document.createElement('a');
        a.href = URL.createObjectURL(blob);
        a.download = 'orders_<?= date('Y-m-d') ?>.csv';
        a.click();
    }
    document.getElementById('exportBtn').addEventListener('click', function(e) {
        e.preventDefault();
        exportCSV();
    });
</script>

<?php
$content = ob_get_clean();
include BASE_PATH . '/app/Views/layouts/admin.php';
