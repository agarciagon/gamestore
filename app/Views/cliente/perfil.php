<?php
// app/Views/cliente/perfil.php
$pageTitle = 'My Profile';
$usuario   = $usuario ?? ['nombre' => $_SESSION['user_name'] ?? '', 'email' => $_SESSION['email'] ?? '', 'fecha_creacion' => ''];
$pedidos   = $pedidos ?? [];
$success   = $_SESSION['success'] ?? null;
$errors    = $_SESSION['errors']  ?? [];
unset($_SESSION['success'], $_SESSION['errors']);
ob_start();
?>

<?php if ($success): ?>
    <div id="toast-success" style="position:fixed;bottom:1.5rem;left:50%;transform:translateX(-50%);z-index:9999;display:flex;align-items:center;gap:.75rem;background:#16a34a;color:#fff;padding:1rem 1.25rem;border-radius:.75rem;box-shadow:0 10px 25px rgba(0,0,0,.4);white-space:nowrap;">
        <i class="fa-solid fa-circle-check text-xl"></i>
        <span class="font-semibold"><?= htmlspecialchars($success) ?></span>
        <button onclick="dismissToast('toast-success')" style="background:none;border:none;color:rgba(255,255,255,.7);cursor:pointer;margin-left:.5rem;"><i class="fa-solid fa-times"></i></button>
    </div>
<?php endif; ?>
<?php if ($errors): ?>
    <div id="toast-error" style="position:fixed;bottom:1.5rem;left:50%;transform:translateX(-50%);z-index:9999;display:flex;align-items:center;gap:.75rem;background:#dc2626;color:#fff;padding:1rem 1.25rem;border-radius:.75rem;box-shadow:0 10px 25px rgba(0,0,0,.4);white-space:nowrap;">
        <i class="fa-solid fa-circle-xmark text-xl"></i>
        <span class="font-semibold"><?= htmlspecialchars(implode(' · ', $errors)) ?></span>
        <button onclick="dismissToast('toast-error')" style="background:none;border:none;color:rgba(255,255,255,.7);cursor:pointer;margin-left:.5rem;"><i class="fa-solid fa-times"></i></button>
    </div>
<?php endif; ?>

<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12" style="min-height:calc(100vh - 280px);">

    <!-- Header -->
    <div class="flex flex-col md:flex-row items-center md:items-start gap-6 bg-gray-800 rounded-xl p-6 shadow-lg mb-8">
        <img src="https://ui-avatars.com/api/?name=<?= urlencode($usuario['nombre']) ?>&background=0D8ABC&color=fff&size=128"
            class="w-24 h-24 rounded-full" style="border:3px solid #38bdf8;" alt="">
        <div class="text-center md:text-left">
            <h1 class="text-2xl font-bold text-white"><?= htmlspecialchars($usuario['nombre']) ?></h1>
            <?php if (!empty($usuario['fecha_creacion'])): ?>
                <p class="text-gray-400 text-sm mt-1">
                    <i class="fa-regular fa-calendar mr-1"></i>Member since: <?= date('M Y', strtotime($usuario['fecha_creacion'])) ?>
                </p>
            <?php endif; ?>
            <p class="text-gray-400 text-sm mt-0.5">
                <i class="fa-regular fa-envelope mr-1"></i><?= htmlspecialchars($usuario['email']) ?>
            </p>
        </div>
    </div>

    <!-- Tabs -->
    <div class="flex gap-2 mb-6 border-b border-gray-700">
        <button onclick="showTab('profile')" id="tab-profile"
            class="tab-btn px-5 py-2 text-sm font-semibold rounded-t-lg transition border-b-2 border-sky-500 text-sky-400">
            <i class="fa-solid fa-user mr-2"></i>Profile
        </button>
        <button onclick="showTab('orders')" id="tab-orders"
            class="tab-btn px-5 py-2 text-sm font-semibold rounded-t-lg transition border-b-2 border-transparent text-gray-400 hover:text-white">
            <i class="fa-solid fa-receipt mr-2"></i>My Orders
            <?php if (!empty($pedidos)): ?>
                <span class="ml-1 px-2 py-0.5 bg-sky-600 text-white text-xs rounded-full"><?= count($pedidos) ?></span>
            <?php endif; ?>
        </button>
    </div>

    <!-- Tab: Profile -->
    <div id="tab-content-profile">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

            <!-- Edit Profile -->
            <div class="bg-gray-800 rounded-xl p-6" style="border:1px solid #374151;">
                <div class="flex items-center gap-3 mb-5">
                    <i class="fa-solid fa-user-pen text-sky-400 text-lg mb-2"></i>
                    <h3 class="text-base font-semibold text-white mb-2">Edit Profile</h3>
                </div>
                <form method="POST" action="/gamestore/public/perfil/update" class="space-y-4" novalidate>
                    <?= \Csrf::field() ?>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Full Name</label>
                        <input type="text" name="nombre" value="<?= htmlspecialchars($usuario['nombre']) ?>"
                            class="w-full px-3 py-2 rounded-lg bg-gray-700 border border-gray-600 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-sky-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Email</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($usuario['email']) ?>"
                            class="w-full px-3 py-2 rounded-lg bg-gray-700 border border-gray-600 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-sky-500 text-sm">
                    </div>
                    <button type="submit"
                        class="w-full py-2 bg-sky-600 hover:bg-sky-500 text-white font-semibold rounded-lg transition text-sm">
                        <i class="fa-solid fa-floppy-disk mr-2"></i>Save Changes
                    </button>
                </form>
            </div>

            <!-- Change Password -->
            <div class="bg-gray-800 rounded-xl p-6" style="border:1px solid #374151;">
                <div class="flex items-center gap-3 mb-5">
                    <i class="fa-solid fa-lock text-green-400 text-lg mb-2"></i>
                    <h3 class="text-base font-semibold text-white mb-2">Change Password</h3>
                </div>
                <form method="POST" action="/gamestore/public/perfil/password" class="space-y-4">
                    <?= \Csrf::field() ?>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Current Password</label>
                        <input type="password" name="current_password" placeholder="••••••••"
                            class="w-full px-3 py-2 rounded-lg bg-gray-700 border border-gray-600 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-sky-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">New Password</label>
                        <input type="password" name="new_password" placeholder="Min. 8 characters"
                            class="w-full px-3 py-2 rounded-lg bg-gray-700 border border-gray-600 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-sky-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-400 mb-1">Confirm New Password</label>
                        <input type="password" name="confirm_password" placeholder="Repeat password"
                            class="w-full px-3 py-2 rounded-lg bg-gray-700 border border-gray-600 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-sky-500 text-sm">
                    </div>
                    <button type="submit"
                        class="w-full py-2 bg-green-600 hover:bg-green-500 text-white font-semibold rounded-lg transition text-sm">
                        <i class="fa-solid fa-shield-halved mr-2"></i>Update Password
                    </button>
                </form>
            </div>
        </div>

        <!-- Delete Account -->
        <div class="bg-gray-800 rounded-xl p-6 mt-2" style="border:1px solid rgba(185,28,28,0.4);">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
                <div>
                    <p class="text-white font-semibold text-sm flex items-center gap-2">
                        <i class="fa-solid fa-triangle-exclamation text-red-400"></i>Delete Account
                    </p>
                    <p class="text-gray-400 text-xs mt-1">This action is <strong class="text-gray-300">irreversible</strong>. All your data will be permanently deleted.</p>
                </div>
                <form id="deleteAccountForm" method="POST" action="/gamestore/public/perfil/delete" style="flex-shrink:0;">
                    <?= \Csrf::field() ?>
                    <button type="button" onclick="confirmDeleteAccount()"
                        class="px-4 py-2 bg-red-700 hover:bg-red-600 text-white rounded-lg font-semibold transition text-sm">
                        <i class="fa-solid fa-trash mr-2"></i>Delete Account
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Tab: Orders -->
    <div id="tab-content-orders" class="hidden">
        <table id="ordersTable" class="display w-full text-sm">
            <thead>
                <tr>
                    <th></th>
                    <th>Order</th>
                    <th>Date</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Invoice</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pedidos as $p): ?>
                    <tr data-items="<?= htmlspecialchars(json_encode($p['items']), ENT_QUOTES, 'UTF-8') ?>">
                        <td class="details-control cursor-pointer text-sky-400 font-bold">+</td>
                        <td>#<?= str_pad($p['id'], 5, '0', STR_PAD_LEFT) ?></td>
                        <td><?= date('d M Y', strtotime($p['fecha_pedido'])) ?></td>
                        <td><?= $p['num_juegos'] ?></td>
                        <td><?= number_format($p['total'], 2) ?>€</td>
                        <td>
                            <a href="/gamestore/public/factura?id=<?= $p['id'] ?>"
                                target="_blank"
                                class="px-3 py-1 border border-red-500 text-red-400 rounded-md 
                                    hover:bg-red-500 hover:text-white transition duration-200">
                                <i class="fa-solid fa-file-pdf mr-1"></i> PDF
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

<script src="/gamestore/public/js/order.js"></script>
<script src="/gamestore/public/js/table.js"></script>

<?php
$content = ob_get_clean();
include BASE_PATH . '/app/Views/layouts/main.php';
