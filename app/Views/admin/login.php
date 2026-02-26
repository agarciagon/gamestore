<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="/gamestore/dist/output.css">
    <title>Admin Login — GameStore</title>
</head>

<body class="bg-[#0f1623] min-h-screen flex items-center justify-center">

    <?php
    $errors  = $_SESSION['errors'] ?? [];
    $success = $_SESSION['success'] ?? null;
    unset($_SESSION['errors'], $_SESSION['success']);
    ?>

    <?php if ($errors): ?>
        <div id="toast-error" style="position:fixed;bottom:1.5rem;left:50%;transform:translateX(-50%);z-index:9999;display:flex;align-items:center;gap:.75rem;background:#dc2626;color:#fff;padding:1rem 1.25rem;border-radius:.75rem;box-shadow:0 10px 25px rgba(0,0,0,.4);white-space:nowrap;">
            <i class="fa-solid fa-circle-xmark text-xl"></i>
            <span class="font-semibold"><?= htmlspecialchars(implode(' · ', $errors)) ?></span>
            <button onclick="dismissToast('toast-error')" style="background:none;border:none;color:rgba(255,255,255,.7);cursor:pointer;margin-left:.5rem;"><i class="fa-solid fa-times"></i></button>
        </div>
    <?php endif; ?>
    
    <div class="w-full max-w-md px-4">

        <!-- Logo -->
        <div class="text-center mb-8">
            <img src="/gamestore/public/assets/img/logo.png" alt="Logo" class="mx-auto h-28 md:h-48 w-auto">
            <h1 class="text-3xl font-bold text-white tracking-wide">Admin Panel</h1>
            <p class="text-gray-400 text-sm mt-1">Sign in with your admin credentials</p>
        </div>

        <!-- Card -->
        <div class="bg-gray-800/70 backdrop-blur-lg border border-gray-700 rounded-2xl shadow-2xl p-8">

            <form method="POST" class="space-y-6" novalidate>


                <!-- Email -->
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Email</label>
                    <input type="email" name="email" placeholder="admin@example.com"
                        class="w-full px-4 py-3 rounded-lg bg-gray-900 border border-gray-600 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-sky-500 transition">
                </div>

                <!-- Password -->
                <div>
                    <label class="block text-sm text-gray-400 mb-1">Password</label>
                    <div class="relative">
                        <input id="password" type="password" name="password" placeholder="••••••••" autocomplete="current-password"
                            class="w-full pr-12 px-4 py-3 rounded-lg bg-gray-900 border border-gray-600 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-sky-500 transition">
                        <button type="button"
                            class="toggle-password absolute inset-y-0 right-3 flex items-center text-gray-500 hover:text-gray-300">
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

                <!-- Botón -->
                <button type="submit"
                    class="w-full bg-sky-600 hover:bg-sky-500 text-white font-semibold py-3 rounded-lg transition duration-200 shadow-lg">
                    Sign in to Admin Panel
                </button>

                <p class="text-center text-xs text-gray-500">
                    <a href="/gamestore/public/" class="hover:text-gray-400">← Back to store</a>
                </p>
            </form>
        </div>

        <p class="text-center text-gray-500 text-xs mt-6">
            &copy; <?= date('Y') ?> Game Store Admin System
        </p>
    </div>

    <script src="/gamestore/public/js/app.js"></script>
</body>

</html>