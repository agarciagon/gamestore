<?php
// app/Views/admin/clientes.php
$pageTitle  = 'Customers';
$activePage = 'clientes';
$toastSuccess = $_SESSION['success'] ?? null;
$toastError   = $_SESSION['error']   ?? null;
unset($_SESSION['success'], $_SESSION['error']);
ob_start();
?>
<?php if ($toastSuccess): ?><script>document.addEventListener('DOMContentLoaded',()=>showToast('success',<?= json_encode($toastSuccess) ?>));</script><?php endif; ?>
<?php if ($toastError): ?><script>document.addEventListener('DOMContentLoaded',()=>showToast('error',<?= json_encode($toastError) ?>));</script><?php endif; ?>

<!-- Header -->
<div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:28px;flex-wrap:wrap;gap:16px;">
    <div>
        <h1 style="font-size:22px;font-weight:700;color:var(--text);">Customers</h1>
        <p style="font-size:13px;color:var(--muted);margin-top:4px;"><?= number_format($total) ?> registered users</p>
    </div>
    <form method="GET" action="/gamestore/public/admin/clientes" style="display:flex;gap:8px;align-items:center;">
        <div style="position:relative;">
            <i class="fa-solid fa-magnifying-glass" style="position:absolute;left:11px;top:50%;transform:translateY(-50%);color:var(--muted);font-size:12px;pointer-events:none;"></i>
            <input type="search" name="q" value="<?= htmlspecialchars($search) ?>"
                   placeholder="Search by name or email…"
                   style="padding:8px 14px 8px 34px;border:1px solid var(--border);border-radius:8px;font-size:13px;color:var(--text);background:var(--surface);outline:none;width:280px;transition:border-color .15s;"
                   onfocus="this.style.borderColor='var(--accent)';this.style.boxShadow='0 0 0 3px rgba(99,91,255,.1)'"
                   onblur="this.style.borderColor='var(--border)';this.style.boxShadow='none'">
        </div>
        <button class="btn-primary" type="submit" style="padding:8px 16px;">
            <i class="fa-solid fa-magnifying-glass"></i> Search
        </button>
        <?php if ($search): ?>
            <a href="/gamestore/public/admin/clientes" class="btn-secondary" style="padding:8px 14px;">
                <i class="fa-solid fa-xmark"></i>
            </a>
        <?php endif; ?>
    </form>
</div>

<?php if ($search): ?>
    <div style="margin-bottom:16px;padding:10px 16px;background:#eef2ff;border:1px solid #c7d2fe;border-radius:8px;font-size:13px;color:#635bff;display:flex;align-items:center;gap:8px;">
        <i class="fa-solid fa-filter"></i>
        Showing results for "<strong><?= htmlspecialchars($search) ?></strong>" — <?= $total ?> found
    </div>
<?php endif; ?>

<!-- Table -->
<div style="background:var(--surface);border:1px solid var(--border);border-radius:12px;overflow:hidden;">
    <!-- Table header info -->
    <div style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;">
        <span style="font-size:13px;font-weight:600;color:var(--text);"><?= number_format($total) ?> customers</span>
    </div>

    <div style="overflow-x:auto;">
        <table class="stripe-table">
            <thead>
                <tr>
                    <th style="width:48px;">#</th>
                    <th>Customer</th>
                    <th>Email</th>
                    <th>Registered</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clientes as $c): ?>
                    <tr>
                        <td style="color:var(--muted);font-size:12px;font-weight:500;"><?= $c['id_usuario'] ?></td>
                        <td>
                            <div style="display:flex;align-items:center;gap:10px;">
                                <img src="https://ui-avatars.com/api/?name=<?= urlencode($c['nombre']) ?>&background=635bff&color=fff&size=34"
                                     style="width:34px;height:34px;border-radius:50%;flex-shrink:0;" alt="">
                                <div>
                                    <div style="font-size:13px;font-weight:600;color:var(--text);"><?= htmlspecialchars($c['nombre']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span style="font-size:13px;color:var(--muted);"><?= htmlspecialchars($c['email']) ?></span>
                        </td>
                        <td>
                            <span style="font-size:12px;color:var(--muted);"><?= date('M d, Y', strtotime($c['fecha_creacion'])) ?></span>
                        </td>
                        <td style="text-align:right;">
                            <form id="del-c-<?= $c['id_usuario'] ?>" method="POST"
                                  action="/gamestore/public/admin/clientes/delete" style="display:inline;">
                                <?= \Csrf::field() ?>
                                <input type="hidden" name="id" value="<?= $c['id_usuario'] ?>">
                                <button type="button"
                                        onclick="confirmDeleteCliente(<?= $c['id_usuario'] ?>, '<?= addslashes($c['nombre']) ?>')"
                                        style="padding:5px 12px;background:#fef2f2;border:1px solid #fca5a5;color:#c0392b;border-radius:7px;font-size:12px;font-weight:600;cursor:pointer;transition:background .15s;display:inline-flex;align-items:center;gap:5px;"
                                        onmouseover="this.style.background='#fee2e2'" onmouseout="this.style.background='#fef2f2'">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (empty($clientes)): ?>
                    <tr>
                        <td colspan="5" style="text-align:center;padding:56px 20px;">
                            <div style="display:inline-flex;flex-direction:column;align-items:center;gap:10px;">
                                <div style="width:52px;height:52px;background:var(--bg);border-radius:50%;display:flex;align-items:center;justify-content:center;">
                                    <i class="fa-solid fa-users" style="font-size:20px;color:var(--muted);opacity:.5;"></i>
                                </div>
                                <div style="font-size:14px;font-weight:600;color:var(--text);">No customers found</div>
                                <?php if ($search): ?>
                                    <div style="font-size:13px;color:var(--muted);">No results for "<?= htmlspecialchars($search) ?>"</div>
                                    <a href="/gamestore/public/admin/clientes" class="btn-secondary" style="margin-top:4px;">Clear search</a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Mobile cards -->
<div id="mobileCards" style="display:none;margin-top:16px;">
    <?php foreach ($clientes as $c): ?>
        <div style="background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:14px;margin-bottom:10px;display:flex;align-items:center;gap:12px;">
            <img src="https://ui-avatars.com/api/?name=<?= urlencode($c['nombre']) ?>&background=635bff&color=fff&size=40"
                 style="width:40px;height:40px;border-radius:50%;flex-shrink:0;" alt="">
            <div style="flex:1;min-width:0;">
                <div style="font-size:13px;font-weight:600;color:var(--text);"><?= htmlspecialchars($c['nombre']) ?></div>
                <div style="font-size:12px;color:var(--muted);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($c['email']) ?></div>
                <div style="font-size:11px;color:var(--muted);margin-top:2px;"><?= date('M d, Y', strtotime($c['fecha_creacion'])) ?></div>
            </div>
            <form id="del-cm-<?= $c['id_usuario'] ?>" method="POST" action="/gamestore/public/admin/clientes/delete">
                <?= \Csrf::field() ?>
                <input type="hidden" name="id" value="<?= $c['id_usuario'] ?>">
                <button type="button" onclick="confirmDeleteCliente(<?= $c['id_usuario'] ?>, '<?= addslashes($c['nombre']) ?>')"
                        style="padding:8px 11px;background:#fef2f2;border:1px solid #fca5a5;color:#c0392b;border-radius:8px;cursor:pointer;">
                    <i class="fa-solid fa-trash" style="font-size:13px;"></i>
                </button>
            </form>
        </div>
    <?php endforeach; ?>
</div>

<script>
if (window.innerWidth <= 768) {
    document.querySelector('[style*="border-radius:12px"]').style.display = 'none';
    document.getElementById('mobileCards').style.display = 'block';
}

function confirmDeleteCliente(id, name) {
    showConfirmModal(`Delete customer "${name}"? This action cannot be undone.`, () => {
        const f = document.getElementById('del-c-' + id) ?? document.getElementById('del-cm-' + id);
        if (f) f.submit();
    });
}
</script>

<?php
$content = ob_get_clean();
include BASE_PATH . '/app/Views/layouts/admin.php';