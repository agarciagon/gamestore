<?php
// app/Views/auth/reset_password.php
// El token viene via GET (?token=...) y se valida en AuthController::resetForm()
// $tokenValido y $token son pasados por el controlador

$pageTitle   = 'Reset Password';
$bodyClass   = 'bg-gray-100';
$tokenValido = $tokenValido ?? false;
$token       = $token       ?? '';
$errors      = $_SESSION['errors'] ?? [];
unset($_SESSION['errors']);
ob_start();
?>

<?php if ($errors): ?>
    <div id="toast-error" style="position:fixed;bottom:1.5rem;left:50%;transform:translateX(-50%);z-index:9999;display:flex;align-items:center;gap:.75rem;background:#dc2626;color:#fff;padding:1rem 1.25rem;border-radius:.75rem;box-shadow:0 10px 25px rgba(0,0,0,.4);max-width:90vw;flex-wrap:wrap;">
        <i class="fa-solid fa-circle-xmark text-xl"></i>
        <span class="font-semibold"><?= htmlspecialchars(implode(' · ', $errors)) ?></span>
        <button onclick="dismissToast('toast-error')" style="background:none;border:none;color:rgba(255,255,255,.7);cursor:pointer;margin-left:.5rem;"><i class="fa-solid fa-times"></i></button>
    </div>
<?php endif; ?>

<div class="flex min-h-full flex-col justify-center px-6 pt-24 pb-24 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-sm text-center">
        <i class="fa-solid fa-key text-4xl text-gray-700 mb-4"></i>
        <?php if ($tokenValido): ?>
            <h2 class="text-2xl font-bold text-gray-900">Reset your password</h2>
            <p class="mt-2 text-sm text-gray-500">Choose a new password for your account.</p>
        <?php else: ?>
            <h2 class="text-2xl font-bold text-gray-900">Link expired</h2>
            <p class="mt-2 text-sm text-gray-500">This reset link is invalid or has expired.</p>
            <a href="/gamestore/public/auth/forgot-password" class="mt-4 inline-block font-semibold text-sky-700 hover:text-sky-500">
                Request a new link
            </a>
        <?php endif; ?>
    </div>

    <?php if ($tokenValido): ?>
        <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">
            <form action="/gamestore/public/auth/reset-password" method="POST" class="space-y-4">
                <?= \Csrf::field() ?>
                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                <div>
                    <label for="password" class="block text-sm/6 font-medium text-gray-900">New Password</label>
                    <div class="relative mt-2">
                        <input id="password" type="password" name="password" placeholder="Min. 8 characters" autocomplete="new-password"
                            class="block w-full pr-10 px-3 py-1.5 rounded-md bg-white outline-1 -outline-offset-1 outline-gray-300 text-base text-gray-900 placeholder:text-gray-400 focus:ring-2 focus:ring-indigo-600 focus:outline-none">
                        <button type="button" class="toggle-password absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-700">
                            <svg class="w-5 h-5 eye-open" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg class="w-5 h-5 eye-closed hidden" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div>
                    <label for="password_confirm" class="block text-sm/6 font-medium text-gray-900">Confirm Password</label>
                    <div class="relative mt-2">
                        <input id="password_confirm" type="password" name="password_confirmation" placeholder="Repeat your password" autocomplete="new-password"
                            class="block w-full pr-10 px-3 py-1.5 rounded-md bg-white outline-1 -outline-offset-1 outline-gray-300 text-base text-gray-900 placeholder:text-gray-400 focus:ring-2 focus:ring-indigo-600 focus:outline-none">
                        <button type="button" class="toggle-password absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-700">
                            <svg class="w-5 h-5 eye-open" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg class="w-5 h-5 eye-closed hidden" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div>
                    <button type="submit" class="flex w-full justify-center rounded-md bg-sky-600 px-3 py-1.5 text-sm/6 font-semibold text-white shadow-xs hover:bg-sky-500">Update Password</button>
                </div>
            </form>
        </div>

        <script src="/gamestore/public/js/app.js"></script>
    <?php endif; ?>
</div>


<?php
$content = ob_get_clean();
include BASE_PATH . '/app/Views/layouts/main.php';
