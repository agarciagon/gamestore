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
       PDF ENGINE — Professional redesign
    ══════════════════════════════════════════════════════════════════════════ */

    // ── Palette ──────────────────────────────────────────────────────────────
    const INK = [10, 12, 20];
    const SLATE = [28, 33, 54];
    const STEEL = [68, 78, 115];
    const SMOKE = [130, 140, 170];
    const MIST = [218, 222, 238];
    const PAPER = [247, 248, 252];
    const WHITE = [255, 255, 255];
    const GOLD = [212, 175, 55];
    const GOLD_L = [255, 223, 100];
    const TEAL = [32, 178, 150];
    const GREEN = [16, 185, 129];
    const AMBER = [245, 158, 11];
    const ROSE = [244, 63, 98];
    const VIOLET = [99, 91, 255];

    function statusRGB(s) {
        if (s === 'Successful') return GREEN;
        if (s === 'Pending') return AMBER;
        if (s === 'Error') return ROSE;
        return VIOLET;
    }

    function calcTax(totalStr) {
        const t = parseFloat(totalStr.replace(',', '.'));
        const base = +(t / 1.21).toFixed(2);
        const iva = +(t - base).toFixed(2);
        return {
            t,
            base,
            iva
        };
    }

    function fmtEur(n) {
        return n.toFixed(2) + ' \u20AC';
    }

    // ════════════════════════════════════════════════════════════════════════
    //  INVOICE INDIVIDUAL — Diseño minimalista premium, estilo Stripe/Linear
    //  Portrait A4 (210×297mm)
    // ════════════════════════════════════════════════════════════════════════
    function buildSingleInvoice(doc, r) {
        const W = 210,
            H = 297;
        const {
            t,
            base,
            iva
        } = calcTax(r.total);
        const L = 18,
            R = W - 18; // margins
        const sc = statusRGB(r.estado);
        const genDate = r.fecha.split(' ').slice(0, 3).join(' ');
        const orderNum = '#' + String(r.id).padStart(8, '0');
        const stripeId = (r.stripe && r.stripe.charAt(0) !== '#') ? r.stripe : null;

        const f = (...c) => doc.setFillColor(...c);
        const dk = (...c) => doc.setDrawColor(...c);
        const tx = (s, x, y, o) => doc.text(String(s), x, y, o || {});
        const ft = (style, size, color) => {
            doc.setFont('helvetica', style);
            doc.setFontSize(size);
            doc.setTextColor(...color);
        };
        const hl = (x1, y, x2, col, w) => {
            dk(...(col || MIST));
            doc.setLineWidth(w || 0.25);
            doc.line(x1, y, x2, y);
        };

        // ── 1. Header band: thin accent bar + brand block ─────────────────
        // Thin color bar at very top
        f(...sc);
        doc.rect(0, 0, W, 1.5, 'F');

        // Clean white header area
        f(250, 251, 255);
        doc.rect(0, 1.5, W, 44, 'F');

        // Brand: wordmark left
        ft('bold', 16, INK);
        tx('Game', L, 22);
        ft('bold', 16, VIOLET);
        tx('Store', L + 25, 22);

        // Tagline
        ft('normal', 7, SMOKE);
        tx('Digital Marketplace', L, 28);

        // Right side: INVOICE label + number
        ft('normal', 7, SMOKE);
        tx('FACTURA', R, 16, {
            align: 'right'
        });
        ft('bold', 18, INK);
        tx(orderNum, R, 26, {
            align: 'right'
        });

        // Status badge — outline style, no heavy fill
        dk(...sc);
        doc.setLineWidth(0.5);
        doc.roundedRect(R - 38, 30, 38, 8, 2, 2, 'S');
        ft('bold', 6.5, sc);
        tx(r.estado.toUpperCase(), R - 19, 35.5, {
            align: 'center'
        });

        // ── 2. Divider ────────────────────────────────────────────────────
        hl(L, 46, R, MIST, 0.4);

        // ── 3. Meta row: fecha · referencia ──────────────────────────────
        ft('normal', 7, SMOKE);
        tx('Fecha de emisión', L, 55);
        tx('Referencia de pago', W / 2, 55);

        ft('bold', 8, INK);
        tx(genDate, L, 61);
        if (stripeId) {
            ft('normal', 6.5, STEEL);
            tx(stripeId.slice(0, 34), W / 2, 61);
        } else {
            ft('bold', 8, INK);
            tx(orderNum, W / 2, 61);
        }

        // ── 4. Divider ────────────────────────────────────────────────────
        hl(L, 68, R, MIST, 0.4);

        // ── 5. Bill-to / Payment — two-column layout ──────────────────────
        const secY = 78;

        // LEFT: Facturado a
        ft('normal', 6.5, SMOKE);
        tx('FACTURADO A', L, secY);
        hl(L, secY + 1.5, L + 28, sc, 0.6);

        ft('bold', 11, INK);
        tx(r.nombre, L, secY + 10);
        ft('normal', 8, STEEL);
        tx(r.email, L, secY + 17);
        ft('normal', 7, SMOKE);
        tx('ID de cliente: ' + String(r.id).padStart(8, '0'), L, secY + 24);

        // RIGHT: Método de pago
        const rpX = W / 2 + 4;
        ft('normal', 6.5, SMOKE);
        tx('MÉTODO DE PAGO', rpX, secY);
        hl(rpX, secY + 1.5, rpX + 35, sc, 0.6);

        const payRows = [
            ['Método', 'Visa  ···· 4242'],
            ['Procesador', 'Stripe Payments'],
            ['Moneda', 'EUR — Euro'],
        ];
        payRows.forEach(([lbl, val], i) => {
            const py = secY + 10 + i * 8;
            ft('normal', 7, SMOKE);
            tx(lbl, rpX, py);
            ft('bold', 7.5, INK);
            tx(val, R, py, {
                align: 'right'
            });
        });

        // ── 6. Items table ────────────────────────────────────────────────
        const tableY = 122;
        ft('normal', 6.5, SMOKE);
        tx('DETALLE DEL PEDIDO', L, tableY - 4);
        hl(L, tableY - 2.5, R, MIST, 0.3);

        // Table header row
        f(245, 246, 251);
        doc.rect(L, tableY, R - L, 8, 'F');
        ft('bold', 6.5, SMOKE);
        tx('DESCRIPCIÓN', L + 4, tableY + 5.5);
        tx('CANT.', 132, tableY + 5.5, {
            align: 'right'
        });
        tx('BASE', 154, tableY + 5.5, {
            align: 'right'
        });
        tx('IVA 21%', 172, tableY + 5.5, {
            align: 'right'
        });
        tx('TOTAL', R - 2, tableY + 5.5, {
            align: 'right'
        });

        hl(L, tableY + 8, R, MIST, 0.3);

        // Table body row
        const desc = stripeId ? stripeId : ('Compra digital — ' + orderNum);
        ft('normal', 8, INK);
        tx(desc.length > 42 ? desc.slice(0, 41) + '…' : desc, L + 4, tableY + 16);
        ft('normal', 8, STEEL);
        tx('1', 132, tableY + 16, {
            align: 'right'
        });
        tx(fmtEur(base), 154, tableY + 16, {
            align: 'right'
        });
        tx(fmtEur(iva), 172, tableY + 16, {
            align: 'right'
        });
        ft('bold', 8, INK);
        tx(fmtEur(t), R - 2, tableY + 16, {
            align: 'right'
        });

        hl(L, tableY + 20, R, MIST, 0.3);

        // ── 7. Totals block ───────────────────────────────────────────────
        const totY = tableY + 28;

        // Subtotal row
        ft('normal', 8, SMOKE);
        tx('Subtotal sin IVA', 130, totY);
        ft('normal', 8, INK);
        tx(fmtEur(base), R - 2, totY, {
            align: 'right'
        });

        // IVA row
        ft('normal', 8, SMOKE);
        tx('IVA 21%', 130, totY + 9);
        ft('normal', 8, INK);
        tx(fmtEur(iva), R - 2, totY + 9, {
            align: 'right'
        });

        hl(130, totY + 13, R, [200, 204, 220], 0.3);

        // TOTAL row — highlighted
        f(245, 246, 251);
        doc.rect(128, totY + 15, R - 128 + 2, 12, 'F');
        dk(...sc);
        doc.setLineWidth(0.4);
        doc.rect(128, totY + 15, R - 128 + 2, 12, 'S');
        ft('bold', 8, STEEL);
        tx('TOTAL (IVA incl.)', 132, totY + 22.5);
        ft('bold', 11, INK);
        tx(fmtEur(t), R - 2, totY + 23, {
            align: 'right'
        });

        // ── 8. Footer info row ────────────────────────────────────────────
        const footInfoY = H - 38;
        hl(L, footInfoY, R, MIST, 0.3);

        ft('normal', 7, SMOKE);
        tx('Este documento es un justificante oficial de pago generado automáticamente.', L, footInfoY + 8);
        tx('No requiere firma ni sello para ser válido.', L, footInfoY + 14);

        // ── 9. Footer ─────────────────────────────────────────────────────
        f(245, 246, 251);
        doc.rect(0, H - 18, W, 18, 'F');
        f(...sc);
        doc.rect(0, H - 18, W, 1, 'F');

        ft('bold', 7, STEEL);
        tx('GAMESTORE', L, H - 9);
        ft('normal', 6.5, SMOKE);
        tx('gamestore.com  ·  soporte@gamestore.com  ·  CIF ES-B12345678', L + 28, H - 9);
        ft('normal', 6, SMOKE);
        tx('Generado el ' + new Date().toLocaleDateString('es-ES', {
            day: '2-digit',
            month: 'long',
            year: 'numeric'
        }), R, H - 9, {
            align: 'right'
        });
    }

    // ════════════════════════════════════════════════════════════════════════
    //  REPORTE COLECTIVO — Landscape A4 (297×210mm)
    //  Diseño editorial financiero limpio
    //  Área útil de tabla: 297 - 14(L) - 14(R) = 269mm
    //  Columnas: 10+38+56+24+22+26+26+67 = 269 ✓
    // ════════════════════════════════════════════════════════════════════════
    function buildMultiReport(doc, rows) {
        const W = 297,
            H = 210;
        const L = 14,
            R = W - 14;
        const revenue = rows.reduce((s, r) => s + parseFloat(r.total.replace(',', '.')), 0);
        const baseRev = +(revenue / 1.21).toFixed(2);
        const ivaRev = +(revenue - baseRev).toFixed(2);
        const ok = rows.filter(r => r.estado === 'Successful').length;
        const pen = rows.filter(r => r.estado === 'Pending').length;
        const err = rows.filter(r => r.estado === 'Error').length;
        const now = new Date().toLocaleDateString('es-ES', {
            day: '2-digit',
            month: 'short',
            year: 'numeric'
        }); 

        const f = (...c) => doc.setFillColor(...c);
        const dk = (...c) => doc.setDrawColor(...c);
        const tx = (s, x, y, o) => doc.text(String(s), x, y, o || {});
        const ft = (style, size, color) => {
            doc.setFont('helvetica', style);
            doc.setFontSize(size);
            doc.setTextColor(...color);
        };
        const hl = (x1, y, x2, col, w) => {
            dk(...(col || MIST));
            doc.setLineWidth(w || 0.25);
            doc.line(x1, y, x2, y);
        };

        function chrome(pg, total) {
            // Top bar
            f(...INK);
            doc.rect(0, 0, W, 12, 'F');
            f(...VIOLET);
            doc.rect(0, 0, 3, 12, 'F');

            ft('bold', 8, WHITE);
            tx('GameStore', 8, 8);
            ft('normal', 6.5, [160, 165, 210]);
            tx('Reporte de pedidos  ·  ' + now, 38, 8);
            ft('normal', 6, [130, 135, 180]);
            tx('Pág. ' + pg + ' / ' + total, R, 8, {
                align: 'right'
            });

            // Bottom bar
            f(245, 246, 251);
            doc.rect(0, H - 9, W, 9, 'F');
            hl(0, H - 9, W, MIST, 0.3);
            ft('normal', 5.5, SMOKE);
            tx('Documento confidencial — solo uso interno  ·  gamestore.com', W / 2, H - 3.5, {
                align: 'center'
            });
        }

        chrome(1, 1);

        // ── Title section ─────────────────────────────────────────────────
        ft('bold', 20, INK);
        tx('Reporte de', L, 30);
        ft('bold', 20, VIOLET);
        tx('Pedidos', L + 47, 30);
        ft('normal', 7, SMOKE);
        tx(rows.length + ' transacciones  ·  Todos los estados', L, 37);
        hl(L, 40, L + 90, MIST, 0.3);

        // ── Summary band ──────────────────────────────────────────────────
        const bandY = 44;
        f(245, 246, 251);
        doc.rect(L, bandY, R - L, 8, 'F');
        hl(L, bandY, R, MIST, 0.3);
        hl(L, bandY + 8, R, MIST, 0.3);

        ft('bold', 6, SMOKE);
        tx('RESUMEN FISCAL:', L + 2, bandY + 5.5);
        ft('normal', 6.5, INK);
        tx('Base: ' + fmtEur(baseRev), L + 32, bandY + 5.5);
        tx('+  IVA 21%: ' + fmtEur(ivaRev), L + 75, bandY + 5.5);
        ft('bold', 6.5, VIOLET);
        tx('=  Total (IVA incl.): ' + fmtEur(revenue), L + 130, bandY + 5.5);
        ft('normal', 6, SMOKE);

        // ── Table ─────────────────────────────────────────────────────────
        // Cols: 10+38+56+24+22+26+26+67 = 269 = 297-14-14 ✓
        doc.autoTable({
            startY: 56,
            margin: {
                left: L,
                right: L
            },
            tableWidth: 269,
            head: [
                ['#', 'CLIENTE', 'EMAIL', 'BASE', 'IVA 21%', 'TOTAL', 'ESTADO', 'FECHA']
            ],
            body: rows.map((r, idx) => {
                const {
                    t,
                    base,
                    iva
                } = calcTax(r.total);
                const em = r.email.length > 30 ? r.email.slice(0, 29) + '…' : r.email;
                return [
                    String(idx + 1).padStart(2, '0'),
                    r.nombre,
                    em,
                    fmtEur(base),
                    fmtEur(iva),
                    fmtEur(t),
                    r.estado,
                    r.fecha,
                ];
            }),
            headStyles: {
                fillColor: INK,
                textColor: [160, 165, 210],
                fontSize: 6,
                fontStyle: 'bold',
                cellPadding: {
                    top: 4,
                    bottom: 4,
                    left: 4,
                    right: 4
                },
            },
            bodyStyles: {
                fontSize: 7,
                textColor: INK,
                cellPadding: {
                    top: 4,
                    bottom: 4,
                    left: 4,
                    right: 4
                },
                overflow: 'hidden',
                minCellHeight: 0,
            },
            alternateRowStyles: {
                fillColor: [248, 249, 252]
            },
            columnStyles: {
                0: {
                    cellWidth: 10,
                    halign: 'center',
                    textColor: SMOKE,
                    fontSize: 6
                },
                1: {
                    cellWidth: 38,
                    fontStyle: 'bold',
                    overflow: 'hidden'
                },
                2: {
                    cellWidth: 56,
                    fontSize: 6.5,
                    textColor: STEEL,
                    overflow: 'hidden'
                },
                3: {
                    cellWidth: 24,
                    halign: 'right'
                },
                4: {
                    cellWidth: 22,
                    halign: 'right',
                    textColor: SMOKE
                },
                5: {
                    cellWidth: 26,
                    halign: 'right',
                    fontStyle: 'bold'
                },
                6: {
                    cellWidth: 26,
                    halign: 'center',
                    textColor: WHITE
                }, // overridden in didDrawCell
                7: {
                    cellWidth: 67,
                    fontSize: 6.5
                },
            },
            tableLineColor: MIST,
            tableLineWidth: 0.2,

            didDrawCell(d) {
                if (d.section !== 'body' || d.column.index !== 6) return;
                const sc2 = statusRGB(d.cell.raw);
                const pad = 2.5;
                f(...sc2);
                doc.roundedRect(
                    d.cell.x + pad,
                    d.cell.y + pad,
                    d.cell.width - pad * 2,
                    d.cell.height - pad * 2,
                    1.5, 1.5, 'F'
                );
                doc.setFont('helvetica', 'bold');
                doc.setFontSize(5.5);
                doc.setTextColor(255, 255, 255);
                doc.text(
                    d.cell.raw,
                    d.cell.x + d.cell.width / 2,
                    d.cell.y + d.cell.height / 2 + 0.5, {
                        align: 'center'
                    }
                );
                doc.setFont('helvetica', 'normal');
                doc.setFontSize(7);
                doc.setTextColor(...INK);
            },

            foot: [
                ['', 'TOTALES', '', fmtEur(baseRev), fmtEur(ivaRev), fmtEur(revenue), '', '']
            ],
            footStyles: {
                fillColor: [240, 241, 248],
                textColor: VIOLET,
                fontStyle: 'bold',
                fontSize: 7.5,
                cellPadding: {
                    top: 5,
                    bottom: 5,
                    left: 4,
                    right: 4
                },
            },
            showFoot: 'lastPage',
            willDrawCell(d) {
                if (d.section === 'foot') {
                    dk(...VIOLET);
                    doc.setLineWidth(0.4);
                    doc.line(d.cell.x, d.cell.y, d.cell.x + d.cell.width, d.cell.y);
                }
            },
        });

        // Re-draw chrome on all pages with correct count
        const np = doc.internal.getNumberOfPages();
        for (let i = 1; i <= np; i++) {
            doc.setPage(i);
            chrome(i, np);
        }
    }

    /* ══════════════════════════════════════════════════════════════════════════
       DISPATCH
    ══════════════════════════════════════════════════════════════════════════ */
    function buildPDF(rows) {
        const {
            jsPDF
        } = window.jspdf;
        if (rows.length === 1) {
            const doc = new jsPDF({
                orientation: 'portrait',
                unit: 'mm',
                format: 'a4'
            });
            buildSingleInvoice(doc, rows[0]);
            return doc;
        } else {
            const doc = new jsPDF({
                orientation: 'landscape',
                unit: 'mm',
                format: 'a4'
            });
            buildMultiReport(doc, rows);
            return doc;
        }
    }

    /* ── Export helpers ── */
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


/* hola caracola  */