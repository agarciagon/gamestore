<?php
// app/Views/admin/pedidos.php
$pageTitle  = 'Orders';
$activePage = 'pedidos';

$perPage     = 20;
$currentPage = max(1, (int)($_GET['page'] ?? 1));
$tabActual   = $_GET['tab'] ?? 'all';

// ENUM en BD: 'Successful' | 'Pending' | 'Error'
function estadoClass(string $e): string
{
    return match ($e) {
        'Successful' => 'paid',
        'Pending'    => 'pending',
        'Error'      => 'error',
        default      => 'paid',
    };
}

$pedidosFiltrados = match ($tabActual) {
    'paid'    => array_values(array_filter($pedidos, fn($p) => ($p['estado'] ?? '') === 'Successful')),
    'pending' => array_values(array_filter($pedidos, fn($p) => ($p['estado'] ?? '') === 'Pending')),
    'error'   => array_values(array_filter($pedidos, fn($p) => ($p['estado'] ?? '') === 'Error')),
    default   => $pedidos,
};

$totalFiltered = count($pedidosFiltrados);
$totalPages    = max(1, ceil($totalFiltered / $perPage));
$currentPage   = min($currentPage, $totalPages);
$offset        = ($currentPage - 1) * $perPage;
$pedidosPaged  = array_slice($pedidosFiltrados, $offset, $perPage);

$counts = [
    'all'     => count($pedidos),
    'paid'    => count(array_filter($pedidos, fn($p) => ($p['estado'] ?? '') === 'Successful')),
    'pending' => count(array_filter($pedidos, fn($p) => ($p['estado'] ?? '') === 'Pending')),
    'error'   => count(array_filter($pedidos, fn($p) => ($p['estado'] ?? '') === 'Error')),
];

$sTotalPedidos = (int)   ($stats['total_pedidos']    ?? 0);
$sPagados      = (int)   ($stats['pagados']          ?? 0);
$sPendientes   = (int)   ($stats['pendientes']       ?? 0);
$sIngresos     = (float) ($stats['ingresos_pagados'] ?? 0);

ob_start();
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>

<!-- KPI -->
<div class="stat-grid" style="margin-bottom:28px;">
    <div class="stat-card">
        <div class="st-icon" style="background:#eef2ff"><i class="fa-solid fa-receipt" style="color:#635bff"></i></div>
        <div class="st-val"><?= number_format($sTotalPedidos) ?></div>
        <div class="st-lbl">Total orders</div>
    </div>
    <div class="stat-card">
        <div class="st-icon" style="background:#d4f5e9"><i class="fa-solid fa-circle-check" style="color:#1a9e6e"></i></div>
        <div class="st-val"><?= number_format($sPagados) ?></div>
        <div class="st-lbl">Successful</div>
    </div>
    <div class="stat-card">
        <div class="st-icon" style="background:#fef3c7"><i class="fa-solid fa-clock" style="color:#b7791f"></i></div>
        <div class="st-val"><?= number_format($sPendientes) ?></div>
        <div class="st-lbl">Pending</div>
    </div>
    <div class="stat-card">
        <div class="st-icon" style="background:#ebf5ff"><i class="fa-solid fa-euro-sign" style="color:#1a56db"></i></div>
        <div class="st-val"><?= number_format($sIngresos, 2) ?>€</div>
        <div class="st-lbl">Revenue (IVA inc.)</div>
    </div>
</div>

<!-- Panel -->
<div class="panel" style="padding:0; margin-top:32px;">

    <div style="padding:20px 24px 0; display:flex; align-items:center; justify-content:space-between;">
        <div>
            <div style="font-size:16px; font-weight:700; color:var(--text);">Transactions</div>
            <div style="font-size:12px; color:var(--muted); margin-top:2px;"><?= number_format($sTotalPedidos) ?> total payments</div>
        </div>
        <div style="display:flex; gap:8px; align-items:center;">
            <button id="exportSelectedBtn"
                style="display:none; align-items:center; gap:6px; background:#635bff; color:#fff;
                       border:none; padding:7px 14px; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer;"
                onclick="exportSelectedPDF()">
                <i class="fa-solid fa-file-pdf"></i> Export selected&nbsp;(<span id="selectedCount">0</span>)
            </button>
            <button id="exportBtn"
                style="display:flex; align-items:center; gap:6px; background:#f4f4f5; color:#374151;
                       border:none; padding:7px 14px; border-radius:8px; font-size:13px; font-weight:500; cursor:pointer;">
                <i class="fa-solid fa-file-pdf" style="color:#e53e3e;"></i> Export PDF
            </button>
        </div>
    </div>

    <!-- TABS -->
    <div class="stripe-tabs" style="margin:16px 0 0; padding:0 24px;">
        <?php
        $tabs = ['all' => 'All', 'paid' => 'Successful', 'pending' => 'Pending', 'error' => 'Error'];
        foreach ($tabs as $key => $label):
            $active = $tabActual === $key ? 'active' : '';
        ?>
            <a href="?tab=<?= $key ?>" class="stripe-tab <?= $active ?>">
                <?= $label ?><span class="tab-count"><?= $counts[$key] ?? 0 ?></span>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- FILTERS -->
    <div class="filter-bar">
        <div class="dropdown-wrapper">
            <button class="filter-pill" onclick="toggleDD('ddDate',event)"><i class="fa-solid fa-calendar"></i> Date <i class="fa-solid fa-chevron-down"></i></button>
            <div class="dropdown-menu" id="ddDate">
                <div class="dd-section">Quick range</div>
                <label><input type="radio" name="dateRange" value="today"> Today</label>
                <label><input type="radio" name="dateRange" value="7d"> Last 7 days</label>
                <label><input type="radio" name="dateRange" value="30d"> Last 30 days</label>
                <label><input type="radio" name="dateRange" value="90d"> Last 90 days</label>
                <hr class="dd-divider">
                <div class="dd-section">Custom</div>
                <div class="dd-date">
                    <span>From</span><input type="date" id="dateFrom">
                    <span style="margin-top:4px;">To</span><input type="date" id="dateTo">
                </div>
                <button class="dd-apply" onclick="applyDateFilter()">Apply</button>
            </div>
        </div>
        <div class="dropdown-wrapper">
            <button class="filter-pill" onclick="toggleDD('ddAmount',event)"><i class="fa-solid fa-euro-sign"></i> Amount <i class="fa-solid fa-chevron-down"></i></button>
            <div class="dropdown-menu" id="ddAmount">
                <div class="dd-section">Range</div>
                <label><input type="radio" name="amtRange" value="0-50"> 0 – 50 €</label>
                <label><input type="radio" name="amtRange" value="50-200"> 50 – 200 €</label>
                <label><input type="radio" name="amtRange" value="200-500"> 200 – 500 €</label>
                <label><input type="radio" name="amtRange" value="500+"> 500 € +</label>
                <hr class="dd-divider">
                <button class="dd-apply" onclick="applyAmountFilter()">Apply</button>
            </div>
        </div>
        <div class="dropdown-wrapper">
            <button class="filter-pill" onclick="toggleDD('ddStatus',event)"><i class="fa-solid fa-circle-half-stroke"></i> Status <i class="fa-solid fa-chevron-down"></i></button>
            <div class="dropdown-menu" id="ddStatus">
                <div class="dd-section">Show statuses</div>
                <label><input type="checkbox" class="status-filter" value="Successful" checked> Successful</label>
                <label><input type="checkbox" class="status-filter" value="Pending" checked> Pending</label>
                <label><input type="checkbox" class="status-filter" value="Error" checked> Error</label>
                <hr class="dd-divider">
                <button class="dd-apply" onclick="applyStatusFilter()">Apply</button>
            </div>
        </div>
        <div class="dropdown-wrapper">
            <button class="filter-pill" onclick="toggleDD('ddPayment',event)"><i class="fa-solid fa-credit-card"></i> Payment method <i class="fa-solid fa-chevron-down"></i></button>
            <div class="dropdown-menu" id="ddPayment">
                <div class="dd-section">Card type</div>
                <label><input type="checkbox" value="visa" checked> <i class="fa-brands fa-cc-visa" style="color:#1a1f71;font-size:15px;"></i> Visa</label>
                <label><input type="checkbox" value="mc" checked> <i class="fa-brands fa-cc-mastercard" style="color:#eb001b;font-size:15px;"></i> Mastercard</label>
                <label><input type="checkbox" value="amex" checked> <i class="fa-brands fa-cc-amex" style="color:#007bc1;font-size:15px;"></i> Amex</label>
                <label><input type="checkbox" value="paypal" checked> <i class="fa-brands fa-paypal" style="color:#003087;font-size:15px;"></i> PayPal</label>
                <hr class="dd-divider">
                <button class="dd-apply" onclick="closeAll()">Apply</button>
            </div>
        </div>
        <div class="dropdown-wrapper">
            <button class="filter-pill" onclick="toggleDD('ddMore',event)"><i class="fa-solid fa-filter"></i> More filters</button>
            <div class="dropdown-menu" id="ddMore">
                <div class="dd-section">Sort by</div>
                <label><input type="radio" name="sortBy" value="date_desc" checked> Newest first</label>
                <label><input type="radio" name="sortBy" value="date_asc"> Oldest first</label>
                <label><input type="radio" name="sortBy" value="amount_desc"> Highest amount</label>
                <label><input type="radio" name="sortBy" value="amount_asc"> Lowest amount</label>
                <hr class="dd-divider">
                <button class="dd-apply" onclick="applyMoreFilters()">Apply</button>
            </div>
        </div>
    </div>

    <!-- TABLE -->
    <div style="overflow-x:auto;" id="tableWrapper">
        <table class="stripe-table" id="ordersTable">
            <thead>
                <tr>
                    <th style="width:40px;"><input type="checkbox" id="checkAll" onchange="toggleAll(this)"></th>
                    <th>Amount (IVA inc.)</th>
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
                    $eRaw    = $p['estado'] ?? 'Successful';
                    $eCls    = estadoClass($eRaw);
                    $rowJson = htmlspecialchars(json_encode([
                        'id'     => $p['id'],
                        'nombre' => $p['cliente_nombre'] ?? '—',
                        'email'  => $p['cliente_email']  ?? '',
                        'total'  => number_format((float)$p['total'], 2),
                        'estado' => $eRaw,
                        'fecha'  => date('d M Y H:i', strtotime($p['fecha_pedido'])),
                        'stripe' => $p['stripe_sid'] ?? '#' . str_pad($p['id'], 8, '0', STR_PAD_LEFT),
                    ]), ENT_QUOTES);
                ?>
                    <tr data-row="<?= $rowJson ?>" data-status="<?= $eRaw ?>">
                        <td><input type="checkbox" class="row-check" value="<?= $p['id'] ?>" onchange="updateSelectionUI()"></td>
                        <td>
                            <span style="font-weight:600;color:var(--text);"><?= number_format((float)$p['total'], 2) ?> €</span>
                            <span style="font-size:10px;color:var(--muted);margin-left:3px;">IVA inc.</span>
                        </td>
                        <td>
                            <div class="payment-method">
                                <div class="card-icon"><i class="fa-brands fa-cc-visa"></i></div>
                                <span class="card-dots">•••• 4242</span>
                            </div>
                        </td>
                        <td>
                            <?php if (!empty($p['stripe_sid'])): ?>
                                <span style="font-family:monospace;font-size:12px;color:var(--accent);" title="<?= htmlspecialchars($p['stripe_sid']) ?>">
                                    <?= htmlspecialchars(substr($p['stripe_sid'], 0, 26)) ?>
                                </span>
                            <?php else: ?>
                                <span style="font-family:monospace;font-size:12px;color:var(--muted);">#<?= str_pad($p['id'], 8, '0', STR_PAD_LEFT) ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div style="display:flex;align-items:center;gap:8px;">
                                <img src="https://ui-avatars.com/api/?name=<?= urlencode($p['cliente_nombre'] ?? 'U') ?>&background=635bff&color=fff&size=26"
                                    style="width:26px;height:26px;border-radius:50%;flex-shrink:0;" alt="">
                                <div>
                                    <div style="font-size:13px;font-weight:500;color:var(--text);"><?= htmlspecialchars($p['cliente_nombre'] ?? '—') ?></div>
                                    <div style="font-size:11px;color:var(--muted);"><?= htmlspecialchars($p['cliente_email'] ?? '') ?></div>
                                </div>
                            </div>
                        </td>
                        <td style="white-space:nowrap;">
                            <div style="font-size:13px;color:var(--text);"><?= date('d M Y', strtotime($p['fecha_pedido'])) ?></div>
                            <div style="font-size:11px;color:var(--muted);"><?= date('H:i', strtotime($p['fecha_pedido'])) ?></div>
                        </td>
                        <td><span class="status-badge <?= $eCls ?>"><?= $eRaw ?></span></td>
                        <td style="text-align:right;">
                            <button onclick="exportSinglePDF(this)"
                                style="background:none;border:none;cursor:pointer;font-size:12px;color:var(--accent);font-weight:500;display:inline-flex;align-items:center;gap:4px;">
                                <i class="fa-solid fa-file-pdf" style="color:#e53e3e;"></i> PDF
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- PAGINATION -->
    <div class="stripe-pagination">
        <span>Showing <?= $totalFiltered === 0 ? 0 : $offset + 1 ?>–<?= min($offset + $perPage, $totalFiltered) ?> of <?= number_format($totalFiltered) ?> results</span>
        <div class="pagination-btns">
            <?php if ($currentPage > 1): ?>
                <a href="?tab=<?= $tabActual ?>&page=<?= $currentPage - 1 ?>" class="page-btn">← Previous</a>
            <?php else: ?>
                <span class="page-btn disabled">← Previous</span>
            <?php endif; ?>
            <?php if ($currentPage < $totalPages): ?>
                <a href="?tab=<?= $tabActual ?>&page=<?= $currentPage + 1 ?>" class="page-btn">Next →</a>
            <?php else: ?>
                <span class="page-btn disabled">Next →</span>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- MOBILE CARDS -->
<div style="display:none;" id="mobileCards">
    <?php foreach ($pedidosPaged as $p):
        $eRaw = $p['estado'] ?? 'Successful';
        $eCls = estadoClass($eRaw);
        $rd   = htmlspecialchars(json_encode([
            'id' => $p['id'],
            'nombre' => $p['cliente_nombre'] ?? '—',
            'email' => $p['cliente_email'] ?? '',
            'total' => number_format((float)$p['total'], 2),
            'estado' => $eRaw,
            'fecha' => date('d M Y H:i', strtotime($p['fecha_pedido'])),
            'stripe' => $p['stripe_sid'] ?? '#' . str_pad($p['id'], 8, '0', STR_PAD_LEFT),
        ]), ENT_QUOTES);
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
                <span class="status-badge <?= $eCls ?>"><?= $eRaw ?></span>
            </div>
            <div style="display:flex;align-items:center;justify-content:space-between;">
                <div>
                    <div style="font-size:18px;font-weight:700;"><?= number_format((float)$p['total'], 2) ?>€ <span style="font-size:10px;color:var(--muted);">IVA inc.</span></div>
                    <div style="font-size:11px;color:var(--muted);"><?= date('d M Y H:i', strtotime($p['fecha_pedido'])) ?></div>
                </div>
                <button onclick='exportRowPDF(JSON.parse(this.dataset.row))' data-row="<?= $rd ?>"
                    style="background:none;border:none;cursor:pointer;font-size:12px;color:var(--accent);font-weight:500;display:flex;align-items:center;gap:4px;">
                    <i class="fa-solid fa-file-pdf" style="color:#e53e3e;"></i> PDF
                </button>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<script>
    if (window.innerWidth <= 768) {
        document.getElementById('tableWrapper').style.display = 'none';
        document.getElementById('mobileCards').style.display = 'block';
    }

    /* ── Dropdowns ── */
    function closeAll() {
        document.querySelectorAll('.dropdown-menu').forEach(m => m.classList.remove('open'));
    }

    function toggleDD(id, e) {
        e.stopPropagation();
        const m = document.getElementById(id),
            was = m.classList.contains('open');
        closeAll();
        if (!was) m.classList.add('open');
    }
    document.addEventListener('click', closeAll);
    document.querySelectorAll('.dropdown-menu').forEach(m => m.addEventListener('click', e => e.stopPropagation()));

    /* ── Filters ── */
    function applyDateFilter() {
        const range = document.querySelector('input[name="dateRange"]:checked')?.value;
        const from = document.getElementById('dateFrom').value,
            to = document.getElementById('dateTo').value;
        let cutoff = null;
        if (range === 'today') {
            cutoff = new Date();
            cutoff.setHours(0, 0, 0, 0);
        } else if (range === '7d') cutoff = new Date(Date.now() - 7 * 86400000);
        else if (range === '30d') cutoff = new Date(Date.now() - 30 * 86400000);
        else if (range === '90d') cutoff = new Date(Date.now() - 90 * 86400000);
        document.querySelectorAll('#ordersTable tbody tr[data-row]').forEach(tr => {
            const d = new Date(JSON.parse(tr.dataset.row).fecha);
            let show = true;
            if (cutoff && d < cutoff) show = false;
            if (from && d < new Date(from)) show = false;
            if (to && d > new Date(to + 'T23:59:59')) show = false;
            tr.style.display = show ? '' : 'none';
        });
        closeAll();
    }

    function applyAmountFilter() {
        const val = document.querySelector('input[name="amtRange"]:checked')?.value;
        if (!val) {
            closeAll();
            return;
        }
        const [minA, maxA] = val === '500+' ? [500, Infinity] : val.split('-').map(Number);
        document.querySelectorAll('#ordersTable tbody tr[data-row]').forEach(tr => {
            const amt = parseFloat(JSON.parse(tr.dataset.row).total.replace(',', '.'));
            tr.style.display = (amt >= minA && amt <= maxA) ? '' : 'none';
        });
        closeAll();
    }

    function applyStatusFilter() {
        const checked = [...document.querySelectorAll('.status-filter:checked')].map(c => c.value);
        document.querySelectorAll('#ordersTable tbody tr[data-row]').forEach(tr => {
            tr.style.display = checked.includes(tr.dataset.status) ? '' : 'none';
        });
        closeAll();
    }

    function applyMoreFilters() {
        const sort = document.querySelector('input[name="sortBy"]:checked')?.value ?? 'date_desc';
        const tbody = document.querySelector('#ordersTable tbody');
        const rows = [...tbody.querySelectorAll('tr[data-row]')];
        rows.sort((a, b) => {
            const ra = JSON.parse(a.dataset.row),
                rb = JSON.parse(b.dataset.row);
            if (sort === 'date_desc') return new Date(rb.fecha) - new Date(ra.fecha);
            if (sort === 'date_asc') return new Date(ra.fecha) - new Date(rb.fecha);
            if (sort === 'amount_desc') return parseFloat(rb.total) - parseFloat(ra.total);
            if (sort === 'amount_asc') return parseFloat(ra.total) - parseFloat(rb.total);
            return 0;
        });
        rows.forEach(r => tbody.appendChild(r));
        closeAll();
    }

    function toggleAll(m) {
        document.querySelectorAll('.row-check').forEach(cb => cb.checked = m.checked);
        updateSelectionUI();
    }

    function updateSelectionUI() {
        const n = document.querySelectorAll('.row-check:checked').length;
        const btn = document.getElementById('exportSelectedBtn');
        document.getElementById('selectedCount').textContent = n;
        btn.style.display = n > 0 ? 'flex' : 'none';
    }

    /* ══════════════════════════════════════════════════════════════════════════
       PDF ENGINE
    ══════════════════════════════════════════════════════════════════════════ */
    function buildPDF(rows) {
        const {
            jsPDF
        } = window.jspdf;
        const single = rows.length === 1;
        const doc = new jsPDF({
            orientation: single ? 'portrait' : 'landscape',
            unit: 'mm',
            format: 'a4'
        });
        const W = doc.internal.pageSize.getWidth();
        const H = doc.internal.pageSize.getHeight();

        const C = {
            purple: [99, 91, 255],
            dark: [15, 23, 42],
            mid: [51, 65, 85],
            muted: [100, 116, 139],
            light: [248, 250, 252],
            border: [226, 232, 240],
            white: [255, 255, 255],
            green: [22, 163, 74],
            amber: [217, 119, 6],
            red: [220, 38, 38],
            blue: [37, 99, 235],
            lightPurple: [240, 240, 255],
        };
        const sBg = s => s === 'Successful' ? C.green : s === 'Pending' ? C.amber : s === 'Error' ? C.red : C.blue;
        const f = (style = 'normal', size = 9, color = C.dark) => {
            doc.setFont('helvetica', style);
            doc.setFontSize(size);
            doc.setTextColor(...color);
        };
        const hline = (x1, y, x2, col = C.border, w = 0.3) => {
            doc.setDrawColor(...col);
            doc.setLineWidth(w);
            doc.line(x1, y, x2, y);
        };

        /* ════════════════════════  SINGLE — Portrait Invoice  ══════════════ */
        if (single) {
            const r = rows[0];
            const tot = parseFloat(r.total.replace(',', '.'));
            const base = (tot / 1.21).toFixed(2);
            const iva = (tot - parseFloat(base)).toFixed(2);

            // Purple top bar
            doc.setFillColor(...C.purple);
            doc.rect(0, 0, W, 2, 'F');
            // Dark bottom bar
            doc.setFillColor(...C.dark);
            doc.rect(0, H - 2, W, 2, 'F');

            // Brand
            f('bold', 16, C.dark);
            doc.text('Game', 14, 18);
            f('bold', 16, C.purple);
            doc.text('Store', 30, 18);

            // INVOICE + number
            f('bold', 22, C.dark);
            doc.text('INVOICE', W - 14, 15, {
                align: 'right'
            });
            f('normal', 9, C.muted);
            doc.text('#' + String(r.id).padStart(8, '0'), W - 14, 21, {
                align: 'right'
            });

            // Status badge
            doc.setFillColor(...sBg(r.estado));
            doc.roundedRect(W - 46, 24, 32, 8, 2, 2, 'F');
            f('bold', 7, C.white);
            doc.text(r.estado.toUpperCase(), W - 30, 29, {
                align: 'center'
            });

            hline(14, 36, W - 14);

            // Billed to
            f('bold', 7.5, C.muted);
            doc.text('BILLED TO', 14, 45);
            f('bold', 11, C.dark);
            doc.text(r.nombre, 14, 52);
            f('normal', 9, C.muted);
            doc.text(r.email, 14, 58);

            // Right-side meta
            [
                ['Invoice date', r.fecha],
                ['Payment', 'Visa •••• 4242'],
                ['Via', 'Stripe']
            ].forEach(([l, v], i) => {
                const y = 45 + i * 8;
                f('normal', 7.5, C.muted);
                doc.text(l, W - 60, y);
                f('bold', 7.5, C.dark);
                doc.text(v, W - 14, y, {
                    align: 'right'
                });
            });

            // Items table (with IVA columns)
            doc.autoTable({
                startY: 70,
                margin: {
                    left: 14,
                    right: 14
                },
                head: [
                    ['#', 'Description', 'Qty', 'Base', 'IVA 21%', 'Total']
                ],
                body: [
                    ['01', 'Digital purchase — Order #' + String(r.id).padStart(8, '0'),
                        '1', base + ' €', iva + ' €', r.total + ' €'
                    ]
                ],
                headStyles: {
                    fillColor: C.dark,
                    textColor: C.white,
                    fontSize: 8,
                    fontStyle: 'bold',
                    cellPadding: {
                        top: 5,
                        bottom: 5,
                        left: 5,
                        right: 5
                    }
                },
                bodyStyles: {
                    fontSize: 9,
                    textColor: C.dark,
                    cellPadding: {
                        top: 7,
                        bottom: 7,
                        left: 5,
                        right: 5
                    }
                },
                columnStyles: {
                    0: {
                        cellWidth: 10,
                        halign: 'center',
                        textColor: C.muted
                    },
                    1: {
                        cellWidth: 'auto'
                    },
                    2: {
                        cellWidth: 12,
                        halign: 'center'
                    },
                    3: {
                        cellWidth: 26,
                        halign: 'right'
                    },
                    4: {
                        cellWidth: 24,
                        halign: 'right',
                        textColor: C.muted
                    },
                    5: {
                        cellWidth: 26,
                        halign: 'right',
                        fontStyle: 'bold'
                    },
                },
                tableLineColor: C.border,
                tableLineWidth: 0.2,
            });

            const ty = doc.lastAutoTable.finalY;

            // Totals
            [
                ['Base amount', base + ' €'],
                ['IVA 21%', iva + ' €']
            ].forEach(([l, v], i) => {
                const y = ty + 12 + i * 8;
                f('normal', 8.5, C.muted);
                doc.text(l, W - 60, y);
                f('bold', 8.5, C.dark);
                doc.text(v, W - 14, y, {
                    align: 'right'
                });
            });
            // Total box
            doc.setFillColor(...C.dark);
            doc.roundedRect(W - 74, ty + 32, 60, 13, 3, 3, 'F');
            doc.setFillColor(...C.purple);
            doc.roundedRect(W - 74, ty + 32, 4, 13, 2, 2, 'F');
            f('bold', 10, C.white);
            doc.text('TOTAL', W - 67, ty + 39.5);
            doc.text(r.total + ' €', W - 16, ty + 39.5, {
                align: 'right'
            });

            // Payment box
            doc.setFillColor(...C.light);
            doc.roundedRect(14, ty + 10, 62, 30, 3, 3, 'F');
            doc.setFillColor(...C.purple);
            doc.roundedRect(14, ty + 10, 3, 30, 1.5, 1.5, 'F');
            f('bold', 7, C.muted);
            doc.text('PAYMENT DETAILS', 21, ty + 18);
            f('bold', 9.5, C.dark);
            doc.text('Visa  ····  4242', 21, ty + 26);
            f('normal', 8, C.muted);
            doc.text('Processed via Stripe', 21, ty + 32);

            hline(14, H - 16, W - 14);
            f('normal', 7.5, C.muted);
            doc.text('Thank you for your purchase!', 14, H - 9);
            doc.text('gamestore.com', W - 14, H - 9, {
                align: 'right'
            });

            return doc;
        }

        /* ════════════════════════  MULTI — Landscape Report  ═══════════════ */
        const revenue = rows.reduce((s, r) => s + parseFloat(r.total.replace(',', '.')), 0);
        const baseRev = parseFloat((revenue / 1.21).toFixed(2));
        const ivaRev = parseFloat((revenue - baseRev).toFixed(2));
        const okCount = rows.filter(r => r.estado === 'Successful').length;
        const penCount = rows.filter(r => r.estado === 'Pending').length;
        const errCount = rows.filter(r => r.estado === 'Error').length;

        // ── Page chrome (header + footer bars) ───────────────────────────────
        function chrome() {
            // Header
            doc.setFillColor(...C.dark);
            doc.rect(0, 0, W, 18, 'F');
            doc.setFillColor(...C.purple);
            doc.rect(0, 0, 5, 18, 'F');
            // Footer
            doc.setFillColor(...C.dark);
            doc.rect(0, H - 10, W, 10, 'F');
            doc.setFillColor(...C.purple);
            doc.rect(0, H - 10, 5, 10, 'F');
        }
        chrome();

        // ── Header text ───────────────────────────────────────────────────────
        f('bold', 10, C.white);
        doc.text('GameStore', 10, 11);
        f('normal', 8, [180, 180, 230]);
        doc.text('Admin Panel', 35, 11);
        f('normal', 7, [150, 150, 200]);
        doc.text('Orders Report  ·  <?= date('d M Y H:i') ?>', W / 2, 11, {
            align: 'center'
        });
        f('normal', 7, [150, 150, 200]);
        doc.text(rows.length + ' orders exported', W - 10, 11, {
            align: 'right'
        });

        // ── Title + subtitle ──────────────────────────────────────────────────
        f('bold', 18, C.dark);
        doc.text('Orders', 10, 31);
        f('bold', 18, C.purple);
        doc.text('Report', 38, 31);
        f('normal', 8, C.muted);
        doc.text(rows.length + ' transaction' + (rows.length !== 1 ? 's' : '') + '  ·  Period: all time', 10, 38);

        // ── 4 summary cards ───────────────────────────────────────────────────
        const cards = [{
                label: 'Total Revenue',
                value: revenue.toFixed(2) + ' €',
                sub: 'IVA included',
                bg: C.purple
            },
            {
                label: 'Successful',
                value: okCount,
                sub: okCount + ' paid',
                bg: C.green
            },
            {
                label: 'Pending',
                value: penCount,
                sub: penCount + ' orders',
                bg: C.amber
            },
            {
                label: 'Errors',
                value: errCount,
                sub: errCount + ' orders',
                bg: C.red
            },
        ];
        const cW = 46,
            cH = 22,
            cGap = 3;
        const cStart = W - 10 - cards.length * (cW + cGap) + cGap;
        cards.forEach((card, i) => {
            const cx = cStart + i * (cW + cGap);
            const cy = 20;
            // Card bg
            doc.setFillColor(...card.bg);
            doc.roundedRect(cx, cy, cW, cH, 3, 3, 'F');
            // Subtle right stripe
            doc.setFillColor(255, 255, 255);
            doc.setGState(doc.GState({
                opacity: 0.07
            }));
            doc.roundedRect(cx + cW - 10, cy, 12, cH, 3, 3, 'F');
            doc.setGState(doc.GState({
                opacity: 1
            }));
            // Texts
            f('normal', 6, C.white);
            doc.text(card.label, cx + cW / 2, cy + 6.5, {
                align: 'center'
            });
            f('bold', 11, C.white);
            doc.text(String(card.value), cx + cW / 2, cy + 15, {
                align: 'center'
            });
            f('normal', 5.5, [220, 220, 255]);
            doc.text(card.sub, cx + cW / 2, cy + 21, {
                align: 'center'
            });
        });

        // ── IVA breakdown band ────────────────────────────────────────────────
        const bandY = 46;
        doc.setFillColor(...C.lightPurple);
        doc.roundedRect(10, bandY, W - 20, 9, 2, 2, 'F');
        doc.setFillColor(...C.purple);
        doc.roundedRect(10, bandY, 4, 9, 2, 2, 'F');
        f('bold', 6.5, C.purple);
        doc.text('IVA BREAKDOWN', 18, bandY + 5.8);
        f('normal', 6.5, C.dark);
        doc.text('Base: ' + baseRev.toFixed(2) + ' €', 62, bandY + 5.8);
        doc.text('IVA 21%: ' + ivaRev.toFixed(2) + ' €', 105, bandY + 5.8);
        f('bold', 6.5, C.purple);
        doc.text('Total (IVA inc.): ' + revenue.toFixed(2) + ' €', 155, bandY + 5.8);

        hline(10, 58, W - 10, C.border, 0.3);

        // ── Table ─────────────────────────────────────────────────────────────
        doc.autoTable({
            startY: 61,
            head: [
                ['Stripe Reference', 'Customer', 'Email', 'Base', 'IVA 21%', 'Total', 'Status', 'Date']
            ],
            body: rows.map(r => {
                const tot = parseFloat(r.total.replace(',', '.'));
                const base = (tot / 1.21).toFixed(2);
                const iva = (tot - parseFloat(base)).toFixed(2);
                return [
                    r.stripe.length > 26 ? r.stripe.slice(0, 26) + '…' : r.stripe,
                    r.nombre, r.email,
                    base + ' €', iva + ' €', r.total + ' €',
                    r.estado, r.fecha,
                ];
            }),
            headStyles: {
                fillColor: C.dark,
                textColor: C.white,
                fontSize: 7.5,
                fontStyle: 'bold',
                cellPadding: {
                    top: 4,
                    bottom: 4,
                    left: 5,
                    right: 5
                },
            },
            bodyStyles: {
                fontSize: 7.5,
                textColor: C.dark,
                cellPadding: {
                    top: 3.5,
                    bottom: 3.5,
                    left: 5,
                    right: 5
                }
            },
            alternateRowStyles: {
                fillColor: [248, 247, 255]
            },
            columnStyles: {
                0: {
                    cellWidth: 48,
                    fontSize: 6.8,
                    textColor: C.muted
                },
                1: {
                    cellWidth: 28
                },
                2: {
                    cellWidth: 46
                },
                3: {
                    cellWidth: 22,
                    halign: 'right'
                },
                4: {
                    cellWidth: 20,
                    halign: 'right',
                    textColor: C.muted
                },
                5: {
                    cellWidth: 24,
                    halign: 'right',
                    fontStyle: 'bold'
                },
                6: {
                    cellWidth: 24,
                    halign: 'center'
                },
                7: {
                    cellWidth: 30
                },
            },
            margin: {
                left: 10,
                right: 10
            },
            tableLineColor: C.border,
            tableLineWidth: 0.2,

            // Coloured status badge
            didDrawCell(data) {
                if (data.section !== 'body' || data.column.index !== 6) return;
                doc.setFillColor(...sBg(data.cell.raw));
                doc.roundedRect(data.cell.x + 2, data.cell.y + 2, data.cell.width - 4, data.cell.height - 4, 1.5, 1.5, 'F');
                f('bold', 6, C.white);
                doc.text(data.cell.raw,
                    data.cell.x + data.cell.width / 2,
                    data.cell.y + data.cell.height / 2 + 0.6, {
                        align: 'center'
                    });
                f('normal', 7.5, C.dark);
            },

            foot: [
                ['', '', 'TOTALS', baseRev.toFixed(2) + ' €', ivaRev.toFixed(2) + ' €', revenue.toFixed(2) + ' €', '', '']
            ],
            footStyles: {
                fillColor: C.lightPurple,
                textColor: C.purple,
                fontStyle: 'bold',
                fontSize: 8,
                cellPadding: {
                    top: 4,
                    bottom: 4,
                    left: 5,
                    right: 5
                },
            },
            showFoot: 'lastPage',
        });

        // ── Footer text on every page ─────────────────────────────────────────
        const tp = doc.internal.getNumberOfPages();
        for (let i = 1; i <= tp; i++) {
            doc.setPage(i);
            if (i > 1) chrome();
            f('normal', 6, [160, 160, 210]);
            doc.text('GameStore · Confidential · gamestore.com', 10, H - 4);
            doc.text('Page ' + i + ' / ' + tp, W - 10, H - 4, {
                align: 'right'
            });
        }

        return doc;
    }

    /* ── Export helpers ─────────────────────────────────────────────────────── */
    function getAllRows() {
        return [...document.querySelectorAll('#ordersTable tbody tr[data-row]')]
            .filter(tr => tr.style.display !== 'none')
            .map(tr => JSON.parse(tr.dataset.row));
    }

    function getSelectedRows() {
        return [...document.querySelectorAll('#ordersTable tbody tr[data-row]')]
            .filter(tr => tr.querySelector('.row-check')?.checked)
            .map(tr => JSON.parse(tr.dataset.row));
    }
    document.getElementById('exportBtn').addEventListener('click', () => {
        const rows = getAllRows();
        if (!rows.length) {
            alert('No orders to export.');
            return;
        }
        buildPDF(rows).save('orders_<?= date('Y-m-d') ?>.pdf');
    });

    function exportSelectedPDF() {
        const rows = getSelectedRows();
        if (!rows.length) return;
        buildPDF(rows).save('orders_selected_<?= date('Y-m-d') ?>.pdf');
    }

    function exportSinglePDF(btn) {
        const r = JSON.parse(btn.closest('tr').dataset.row);
        buildPDF([r]).save('invoice_' + r.id + '.pdf');
    }

    function exportRowPDF(row) {
        buildPDF([row]).save('invoice_' + row.id + '.pdf');
    }
</script>

<?php
$content = ob_get_clean();
include BASE_PATH . '/app/Views/layouts/admin.php';
