<?php
// app/Views/auth/forgot_password.php
$pageTitle = 'Forgot Password';
$bodyClass = 'bg-gray-100';
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

<div class="flex min-h-full flex-col justify-center mt-10 px-6 pt-24 pb-24 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-sm">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="mx-auto h-10 w-10">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
        </svg>
        <h2 class="mt-4 text-center text-2xl/9 font-bold tracking-tight text-gray-900">Forgot your password?</h2>
        <p class="mt-2 text-center text-sm text-gray-500">Enter your email and we'll send you a reset link.</p>
    </div>
    <div class="mt-6 sm:mx-auto sm:w-full sm:max-w-sm">
        <form action="/gamestore/public/auth/forgot-password" method="POST" class="space-y-6" novalidate>
            <?= Csrf::field(); ?>
            <div>
                <label for="email" class="block text-sm/6 font-medium text-gray-900">Email Address</label>
                <div class="mt-2">
                    <input id="email" type="email" name="email" placeholder="example@gmail.com" autocomplete="email"
                        class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" />
                </div>
            </div>
            <div>
                <button type="submit" class="flex w-full justify-center rounded-md bg-sky-600 px-3 py-1.5 text-sm/6 font-semibold text-white shadow-xs hover:bg-sky-500">Send reset link</button>
            </div>
        </form>
        <p class="mt-6 text-center text-sm text-gray-500">
            Remembered it? <a href="/gamestore/public/auth/login" class="font-semibold text-sky-700 hover:text-sky-500">Sign in</a>
        </p>
    </div>
</div>

<?php
$content = ob_get_clean();
include BASE_PATH . '/app/Views/layouts/main.php';
