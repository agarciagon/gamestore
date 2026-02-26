<?php
session_start();
require_once '../config/conection.php';

$userId   = $_SESSION['user_id'] ?? null;
$userName = $_SESSION['user_name'] ?? '';

if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$cartCount = 0;
if (!empty($_SESSION['carrito_guest'])) {
    $cartCount = array_sum($_SESSION['carrito_guest']);
}

$errores          = [];
$nombre           = '';
$email            = '';
$registroExitoso  = false;
$redirectUrl      = '../index.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre           = trim($_POST['name'] ?? '');
    $email            = trim(filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL));
    $password         = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirmation'] ?? '';
    $rol              = 'cliente';

    if (empty($nombre))              $errores[] = "Name is required.";
    elseif (strlen($nombre) < 2)     $errores[] = "Name must be at least 2 characters.";

    if (empty($email))               $errores[] = "Email is required.";
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errores[] = "Invalid email format.";

    if (empty($password))            $errores[] = "Password is required.";
    elseif (strlen($password) < 8)   $errores[] = "Password must be at least 8 characters.";

    if (empty($password_confirm))    $errores[] = "Please confirm your password.";
    elseif ($password !== $password_confirm) $errores[] = "Passwords do not match.";

    if (empty($errores)) {
        try {
            $stmt = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetchColumn()) {
                $errores[] = "This email is already registered.";
            }
        } catch (PDOException $e) {
            $errores[] = "Error verifying email.";
        }
    }

    if (empty($errores)) {
        try {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, rol, contrasena, fecha_creacion) VALUES (?, ?, ?, ?, NOW())");
            $stmt->execute([$nombre, $email, $rol, $password_hash]);
            $nuevoUserId = $pdo->lastInsertId();

            $carritoGuest = $_SESSION['carrito_guest'] ?? [];

            $_SESSION['user_id']   = $nuevoUserId;
            $_SESSION['user_name'] = $nombre;
            $_SESSION['email']     = $email;
            $_SESSION['rol']       = $rol;
            unset($_SESSION['carrito_guest']);

            if (!empty($carritoGuest)) {
                foreach ($carritoGuest as $id_juego => $cantidad) {
                    $id_juego = (int)$id_juego;
                    $cantidad = (int)$cantidad;

                    $stmtJuego = $pdo->prepare("SELECT stock FROM videojuego WHERE id = :id");
                    $stmtJuego->execute([':id' => $id_juego]);
                    $juego = $stmtJuego->fetch(PDO::FETCH_ASSOC);
                    if (!$juego) continue;

                    $cantidadFinal = min($cantidad, $juego['stock']);
                    if ($cantidadFinal > 0) {
                        $pdo->prepare("INSERT INTO carrito (id_usuario, id_videojuego, cantidad) VALUES (:uid, :jid, :cantidad)")
                            ->execute([':uid' => $nuevoUserId, ':jid' => $id_juego, ':cantidad' => $cantidadFinal]);
                        $pdo->prepare("UPDATE videojuego SET stock = stock - :cantidad WHERE id = :id")
                            ->execute([':cantidad' => $cantidadFinal, ':id' => $id_juego]);
                    }
                }
                $redirectUrl = '../cliente/shopping.php';
            } else {
                $redirectUrl = '../index.php';
            }

            $registroExitoso = true;

        } catch (PDOException $e) {
            $errores[] = "Error registering user. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="../dist/output.css">
    <title>Sign up</title>
</head>
<body class="bg-gray-100">

    <!-- TOAST ERROR -->
    <?php if (!empty($errores)): ?>
        <div id="toast-error" style="position:fixed;bottom:1.5rem;left:50%;transform:translateX(-50%);z-index:9999;display:flex;align-items:center;gap:.75rem;background:#dc2626;color:#fff;padding:1rem 1.25rem;border-radius:.75rem;box-shadow:0 10px 25px rgba(0,0,0,.4);transition:opacity .4s,transform .4s;white-space:nowrap;max-width:90vw;flex-wrap:wrap;">
            <i class="fa-solid fa-circle-xmark" style="font-size:1.25rem;flex-shrink:0;"></i>
            <span style="font-weight:600;"><?= htmlspecialchars(implode(' · ', $errores)) ?></span>
            <button onclick="dismissToast('toast-error')" style="background:none;border:none;color:rgba(255,255,255,.7);cursor:pointer;margin-left:.5rem;">
                <i class="fa-solid fa-times"></i>
            </button>
        </div>
    <?php endif; ?>

    <!-- TOAST ÉXITO -->
    <?php if ($registroExitoso): ?>
        <div id="toast-success" style="position:fixed;bottom:1.5rem;left:50%;transform:translateX(-50%);z-index:9999;display:flex;align-items:center;gap:.75rem;background:#16a34a;color:#fff;padding:1rem 1.25rem;border-radius:.75rem;box-shadow:0 10px 25px rgba(0,0,0,.4);transition:opacity .4s,transform .4s;white-space:nowrap;">
            <i class="fa-solid fa-circle-check" style="font-size:1.25rem;"></i>
            <span style="font-weight:600;">Account created! Redirecting...</span>
        </div>
    <?php endif; ?>

    <nav class="bg-gray-800">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <a href="../index.php"><img class="h-36 w-36" src="../assets/img/logo.png" alt="Logo"></a>
                    </div>
                    <div class="hidden md:flex ml-10 space-x-4">
                        <a href="../index.php" class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium content-center">Home</a>
                        <span class="text-gray-300 content-center">|</span>
                        <a href="../cliente/catalogo.php" class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium content-center">Catalog</a>
                        <form role="search" action="../cliente/catalogo.php" method="get" class="ml-4">
                            <div class="relative">
                                <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none"></i>
                                <input type="search" name="q" placeholder="Search products..."
                                    class="w-full pl-10 pr-4 py-2 rounded-full bg-gray-100 border border-gray-200 focus:bg-white focus:ring-2 focus:ring-indigo-500 focus:outline-none transition">
                            </div>
                        </form>
                    </div>
                </div>
                <div class="hidden md:flex items-center gap-4">
                    <a href="../cliente/shopping.php" class="relative flex items-center justify-center">
                        <i class="fa-solid fa-cart-shopping text-white text-xl"></i>
                        <?php if ($cartCount > 0): ?>
                            <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center font-bold"><?= $cartCount ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="../auth/login.php" class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Sign in</a>
                    <a href="../auth/register.php" class="px-4 py-2 bg-sky-500 text-white font-semibold rounded-md hover:bg-sky-600 transition">Sign up</a>
                </div>
                <div class="-mr-2 flex md:hidden">
                    <button id="mobile-menu-btn" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-white hover:bg-gray-700">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        <div id="mobile-menu" class="hidden md:hidden">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                <a href="../index.php" class="text-gray-300 hover:bg-gray-700 hover:text-white block px-3 py-2 rounded-md text-base font-medium">Home</a>
                <a href="../cliente/catalogo.php" class="text-gray-300 hover:bg-gray-700 hover:text-white block px-3 py-2 rounded-md text-base font-medium">Catalog</a>
            </div>
            <div class="border-t border-gray-700 pt-4 pb-3">
                <div class="flex items-center justify-end px-5 gap-4">
                    <a href="../cliente/shopping.php" class="relative flex items-center justify-center">
                        <i class="fa-solid fa-cart-shopping text-white text-xl"></i>
                        <?php if ($cartCount > 0): ?>
                            <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center font-bold"><?= $cartCount ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="../auth/login.php" class="text-gray-300 hover:bg-gray-700 hover:text-white block px-3 py-2 rounded-md text-base font-medium">Sign in</a>
                    <a href="../auth/register.php" class="flex items-center gap-2 px-4 py-2 bg-sky-500 text-white font-semibold rounded-md hover:bg-sky-600 transition">Sign up</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="flex min-h-full flex-col justify-center px-6 pt-10 pb-20 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-sm">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-10 mx-auto h-10 w-auto">
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
                <div>
                    <label for="name" class="block text-sm/6 font-medium text-gray-900">User Name*</label>
                    <div class="mt-2 mb-4">
                        <input id="name" type="text" name="name" value="<?= htmlspecialchars($nombre) ?>" placeholder="User Name"
                            class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" />
                    </div>
                </div>

                <div>
                    <label for="email" class="block text-sm/6 font-medium text-gray-900">Email Address*</label>
                    <div class="mt-2 mb-4">
                        <input id="email" type="email" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="example@gmail.com"
                            class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" />
                    </div>
                </div>

                <div class="flex gap-6 mb-2">
                    <div class="mt-2">
                        <label for="password" class="block text-sm font-medium text-gray-900 mb-2">Password*</label>
                        <div class="relative mt-1">
                            <input id="password" type="password" name="password" placeholder="********"
                                class="w-full pr-10 px-3 py-2 rounded-md bg-white outline-1 -outline-offset-1 outline-gray-300 focus:ring-2 focus:ring-indigo-600 focus:outline-none">
                            <button type="button" class="toggle-password absolute inset-y-0 right-3 flex items-center text-gray-500">
                                <svg class="w-5 h-5 eye-open" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                <svg class="w-5 h-5 eye-closed hidden" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>
                            </button>
                        </div>
                    </div>
                    <div class="mt-2 mb-2">
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-900 mb-2">Repeat Password*</label>
                        <div class="relative mt-1">
                            <input id="password_confirmation" type="password" name="password_confirmation" placeholder="********"
                                class="w-full pr-10 px-3 py-2 rounded-md bg-white outline-1 -outline-offset-1 outline-gray-300 focus:ring-2 focus:ring-indigo-600 focus:outline-none">
                            <button type="button" class="toggle-password absolute inset-y-0 right-3 flex items-center text-gray-500">
                                <svg class="w-5 h-5 eye-open" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                <svg class="w-5 h-5 eye-closed hidden" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <p class="text-sm text-gray-500 mb-4">Minimum 8 characters</p>

                <div>
                    <button type="submit" <?= $registroExitoso ? 'disabled' : '' ?>
                        class="flex w-full justify-center rounded-md bg-sky-600 px-3 py-1.5 text-sm/6 font-semibold text-white shadow-xs hover:bg-sky-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-sky-600 disabled:opacity-50 disabled:cursor-not-allowed">
                        Sign up
                    </button>
                </div>
            </form>
            <p class="mt-10 text-center text-sm/6 text-gray-500">
                Have an account?
                <a href="./login.php" class="font-semibold text-sky-700 hover:text-sky-500">Sign in</a>
            </p>
        </div>
    </div>

    <?php include "../includes/footer.php" ?>
    <script src="../js/mobile_menu.js"></script>
    <script src="../js/password.js"></script>
    <script src="../js/toast.js"></script>
    <script>
        <?php if (!empty($errores)): ?>
            // El toast de error desaparece a los 4s pero NO redirige — el usuario corrige los campos
            setTimeout(() => dismissToast('toast-error'), 4000);
        <?php endif; ?>

        <?php if ($registroExitoso): ?>
            // Cuenta creada: toast 3s y luego redirect
            setTimeout(() => {
                dismissToast('toast-success');
                setTimeout(() => window.location.href = '<?= $redirectUrl ?>', 400);
            }, 3000);
        <?php endif; ?>
    </script>
</body>
</html>