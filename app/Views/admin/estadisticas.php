<?php
// app/Views/admin/estadisticas.php
$pageTitle  = 'Statistics';
$activePage = 'estadisticas';
$colors     = ['#635bff','#1a56db','#1a9e6e','#b7791f','#ef4444','#ec4899'];
ob_start();
?>

<!-- Header -->
<div style="margin-bottom:28px;">
    <h1 style="font-size:22px;font-weight:700;color:var(--text);">Statistics</h1>
    <p style="font-size:13px;color:var(--muted);margin-top:4px;">Overview of your store performance.</p>
</div>

<!-- KPIs -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:28px;">
    <?php
    $kpis = [
        ['label'=>'Total Customers', 'val'=>number_format($totalClientes),                        'icon'=>'fa-users',         'bg'=>'#ebf5ff','ic'=>'#1a56db'],
        ['label'=>'Products',        'val'=>number_format($statsJuegos['total_productos']),        'icon'=>'fa-gamepad',       'bg'=>'#eef2ff','ic'=>'#635bff'],
        ['label'=>'Items in Carts',  'val'=>number_format($itemsEnCarritos),                      'icon'=>'fa-cart-shopping', 'bg'=>'#fef3c7','ic'=>'#b7791f'],
        ['label'=>'Inventory Value', 'val'=>number_format($statsJuegos['valor_inventario'],0).'€','icon'=>'fa-euro-sign',     'bg'=>'#d4f5e9','ic'=>'#1a9e6e'],
    ];
    foreach ($kpis as $k): ?>
        <div style="background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:20px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                <span style="font-size:11px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;"><?= $k['label'] ?></span>
                <div style="width:34px;height:34px;background:<?= $k['bg'] ?>;border-radius:8px;display:flex;align-items:center;justify-content:center;">
                    <i class="fa-solid <?= $k['icon'] ?>" style="color:<?= $k['ic'] ?>;font-size:13px;"></i>
                </div>
            </div>
            <div style="font-size:30px;font-weight:700;color:var(--text);line-height:1;"><?= $k['val'] ?></div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Row 1: bar chart + genre -->
<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;margin-bottom:20px;">

    <!-- New customers chart -->
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:12px;overflow:hidden;">
        <div style="padding:18px 24px;border-bottom:1px solid var(--border);">
            <div style="font-size:14px;font-weight:700;color:var(--text);">New Customers</div>
            <div style="font-size:12px;color:var(--muted);margin-top:2px;">Last 6 months</div>
        </div>
        <div class="items-center" style="padding:24px;">
            <div style="display:flex;align-items:flex-end;gap:12px;height:160px;">
                <?php foreach ($registrosMes as $i => $m):
                    $pct = round($m['total'] / max($maxMes, 1) * 100);
                    $h   = max($pct * 1.4, 6);
                    $c   = $colors[$i % count($colors)];
                ?>
                    <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:6px;">
                        <span style="font-size:12px;font-weight:700;color:var(--text);"><?= $m['total'] ?></span>
                        <div style="width:100%;position:relative;">
                            <div style="width:100%;height:<?= $h ?>px;background:<?= $c ?>;border-radius:6px 6px 0 0;transition:opacity .2s;"
                                 onmouseover="this.style.opacity='.75'" onmouseout="this.style.opacity='1'"></div>
                        </div>
                        <span style="font-size:11px;color:var(--muted);white-space:nowrap;"><?= $m['mes'] ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- By genre donut-style list -->
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:12px;overflow:hidden;">
        <div style="padding:18px 24px;border-bottom:1px solid var(--border);">
            <div style="font-size:14px;font-weight:700;color:var(--text);">By Genre</div>
            <div style="font-size:12px;color:var(--muted);margin-top:2px;"><?= array_sum(array_column($porGenero,'qty')) ?> products total</div>
        </div>
        <div style="padding:12px 0;">
            <?php foreach ($porGenero as $i => $g):
                $pct = round($g['qty'] / max($maxGenero,1) * 100);
                $c   = $colors[$i % count($colors)];
            ?>
                <div style="padding:10px 20px;">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:5px;">
                        <div style="display:flex;align-items:center;gap:7px;">
                            <span style="width:8px;height:8px;border-radius:50%;background:<?= $c ?>;flex-shrink:0;display:inline-block;"></span>
                            <span style="font-size:13px;font-weight:500;color:var(--text);"><?= htmlspecialchars($g['genero']) ?></span>
                        </div>
                        <span style="font-size:12px;font-weight:700;color:<?= $c ?>;"><?= $g['qty'] ?></span>
                    </div>
                    <div style="height:4px;background:var(--bg);border-radius:4px;">
                        <div style="height:4px;background:<?= $c ?>;border-radius:4px;width:<?= $pct ?>%;transition:width .4s;"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Row 2: top in carts + price stats -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

    <!-- Top games in carts -->
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:12px;overflow:hidden;">
        <div style="padding:18px 24px;border-bottom:1px solid var(--border);">
            <div style="font-size:14px;font-weight:700;color:var(--text);">Top Games in Carts</div>
            <div style="font-size:12px;color:var(--muted);margin-top:2px;">Currently added by users</div>
        </div>
        <div style="padding:8px 0;">
            <?php foreach ($masEnCarrito as $i => $item):
                $c = $colors[$i % count($colors)];
            ?>
                <div style="display:flex;align-items:center;gap:12px;padding:11px 20px;border-bottom:1px solid var(--border);">
                    <div style="width:28px;height:28px;background:<?= $c ?>18;border-radius:7px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <span style="font-size:11px;font-weight:800;color:<?= $c ?>;"><?= $i+1 ?></span>
                    </div>
                    <div style="flex:1;min-width:0;">
                        <div style="font-size:13px;font-weight:500;color:var(--text);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($item['titulo']) ?></div>
                    </div>
                    <span style="font-size:12px;font-weight:700;padding:3px 10px;border-radius:20px;background:<?= $c ?>15;color:<?= $c ?>;flex-shrink:0;">
                        <?= $item['total'] ?> units
                    </span>
                </div>
            <?php endforeach; ?>
            <?php if (empty($masEnCarrito)): ?>
                <div style="text-align:center;padding:32px;color:var(--muted);font-size:13px;">No data yet</div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Price statistics -->
    <div style="background:var(--surface);border:1px solid var(--border);border-radius:12px;overflow:hidden;">
        <div style="padding:18px 24px;border-bottom:1px solid var(--border);">
            <div style="font-size:14px;font-weight:700;color:var(--text);">Price Statistics</div>
            <div style="font-size:12px;color:var(--muted);margin-top:2px;">Across all products</div>
        </div>
        <div style="padding:24px;display:flex;flex-direction:column;gap:20px;">
            <?php
            $priceStats = [
                ['label'=>'Minimum price',  'val'=>number_format($statsJuegos['precio_min'],2).'€', 'color'=>'#1a9e6e','bg'=>'#d4f5e9','icon'=>'fa-arrow-down'],
                ['label'=>'Average price',  'val'=>number_format($statsJuegos['precio_avg'],2).'€', 'color'=>'#635bff','bg'=>'#eef2ff','icon'=>'fa-minus'],
                ['label'=>'Maximum price',  'val'=>number_format($statsJuegos['precio_max'],2).'€', 'color'=>'#b7791f','bg'=>'#fef3c7','icon'=>'fa-arrow-up'],
            ];
            foreach ($priceStats as $ps): ?>
                <div style="display:flex;align-items:center;gap:14px;padding:14px;background:<?= $ps['bg'] ?>;border-radius:10px;">
                    <div style="width:38px;height:38px;background:<?= $ps['color'] ?>;border-radius:9px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="fa-solid <?= $ps['icon'] ?>" style="color:#fff;font-size:13px;"></i>
                    </div>
                    <div>
                        <div style="font-size:11px;font-weight:600;color:<?= $ps['color'] ?>;text-transform:uppercase;letter-spacing:.05em;"><?= $ps['label'] ?></div>
                        <div style="font-size:24px;font-weight:700;color:var(--text);line-height:1.2;margin-top:2px;"><?= $ps['val'] ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>

<?php
$content = ob_get_clean();
include BASE_PATH . '/app/Views/layouts/admin.php';