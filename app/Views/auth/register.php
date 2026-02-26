<?php
// app/Views/auth/register.php
$pageTitle = 'Sign Up';
$cartCount  = array_sum($_SESSION['carrito_guest'] ?? []);
$errors    = $_SESSION['errors'] ?? [];
unset($_SESSION['errors']);

// Nueva línea: obtiene si el registro fue exitoso
$registroExitoso = $_SESSION['registro_exitoso'] ?? false;
unset($_SESSION['registro_exitoso']); // limpia la sesión para la próxima vez

ob_start();
?>

<?php if ($errors): ?>
    <div id="toast-error" style="position:fixed;bottom:1.5rem;left:1rem;right:1rem;transform:none;z-index:9999;display:flex;align-items:flex-start;gap:.75rem;background:#dc2626;color:#fff;padding:1rem 1.25rem;border-radius:.75rem;box-shadow:0 10px 25px rgba(0,0,0,.4);"> <i class="fa-solid fa-circle-xmark text-xl"></i>
        <span class="font-semibold"><?= htmlspecialchars(implode(' · ', $errors)) ?></span>
        <button onclick="dismissToast('toast-error')" style="background:none;border:none;color:rgba(255,255,255,.7);cursor:pointer;margin-left:.5rem;"><i class="fa-solid fa-times"></i></button>
    </div>
<?php endif; ?>

<div class="flex min-h-full flex-col justify-center px-6 pt-24 pb-24 lg:px-8 bg-gray-100">
    <div class="sm:mx-auto sm:w-full sm:max-w-sm">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-10 mx-auto h-10 w-auto text-gray-900">
            <path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" />
        </svg>
        <h2 class="mt-4 text-center text-2xl/9 font-bold tracking-tight text-gray-900">Create your account</h2>
        <?php if ($cartCount > 0): ?>
            <p class="mt-2 text-center text-sm text-yellow-600">
                <i class="fa-solid fa-cart-shopping mr-1"></i>
                You have <?= $cartCount ?> item(s) in your cart — sign up to save them.
            </p>
        <?php endif; ?>
    </div>

    <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">
        <form action="" method="POST" class="space-y-6" novalidate>
            <?= Csrf::field() ?>
            <div>
                <label for="name" class="block text-sm/6 font-medium text-gray-900">User Name*</label>
                <div class="mt-2 mb-4">
                    <input id="name" type="text" name="name" placeholder="User Name"
                        class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" />
                </div>
            </div>

            <div>
                <label for="email" class="block text-sm/6 font-medium text-gray-900">Email Address*</label>
                <div class="mt-2 mb-4">
                    <input id="email" type="email" name="email" placeholder="example@gmail.com"
                        class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" />
                </div>
            </div>

            <div class="flex gap-6 mb-2">
                <div class="mt-2">
                    <label for="password" class="block text-sm font-medium text-gray-900 mb-2">Password*</label>
                    <div class="relative mt-1">
                        <input id="password" type="password" name="password" placeholder="••••••••"
                        class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" />
                        <button type="button" class="toggle-password absolute inset-y-0 right-3 flex items-center text-gray-500">
                            <svg class="w-5 h-5 eye-open" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg class="w-5 h-5 eye-closed hidden" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="mt-2 mb-2">
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-900 mb-2">Repeat Password*</label>
                    <div class="relative mt-1">
                        <input id="password_confirmation" type="password" name="password_confirmation" placeholder="••••••••"
                        class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" />
                        <button type="button" class="toggle-password absolute inset-y-0 right-3 flex items-center text-gray-500">
                            <svg class="w-5 h-5 eye-open" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <svg class="w-5 h-5 eye-closed hidden" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <p class="text-sm text-gray-500 mb-4">Minimum 8 characters</p>

            <div>
                <div>
                    <button type="submit" <?= !empty($registroExitoso) ? 'disabled' : '' ?>
                        class="flex w-full justify-center rounded-md bg-sky-600 px-3 py-1.5 text-sm/6 font-semibold text-white shadow-xs hover:bg-sky-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-sky-600 disabled:opacity-50 disabled:cursor-not-allowed">
                        Sign up
                    </button>
                </div>
            </div>
        </form>
        <p class="mt-10 text-center text-sm/6 text-gray-500">
            Have an account?
            <a href="/gamestore/public/auth/login" class="font-semibold text-sky-700 hover:text-sky-500">Sign in</a>
        </p>
    </div>
</div>

<?php
$content = ob_get_clean();
include BASE_PATH . '/app/Views/layouts/main.php';
