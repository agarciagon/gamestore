<?php
session_start();
require_once '../config/conection.php';
require_once '../config/mailer.php';

$cartCount = 0;
if (!empty($_SESSION['carrito_guest'])) {
    $cartCount = array_sum($_SESSION['carrito_guest']);
}

$toastSuccess = null;
$toastError   = null;
$emailValue   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email      = trim(filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL));
    $emailValue = $email;

    if (empty($email)) {
        $toastError = "Email is required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $toastError = "Invalid email format.";
    }

    if (!$toastError) {
        $stmt = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        $toastSuccess = "If that email exists, you'll receive a reset link shortly.";

        if ($user) {
            try {
                $token   = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

                $pdo->prepare("UPDATE password_resets SET used = 1 WHERE email = ?")
                    ->execute([$email]);
                $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)")
                    ->execute([$email, $token, $expires]);

                $link = "http://localhost/CRUD_AGG/auth/reset_password.php?token=$token";

                $mail = crearMailer();
                $mail->addAddress($email);
                $mail->isHTML(true);
                $mail->Subject = 'Reset your password – GameStore';
                $mail->Body = "
                    <div style='font-family:sans-serif;max-width:480px;margin:auto;padding:32px;background:#f9fafb;border-radius:8px;'>
                        <h2 style='color:#111827;margin-bottom:8px;'>Password Reset</h2>
                        <p style='color:#6b7280;'>Click the button below to reset your password. The link expires in <strong>1 hour</strong>.</p>
                        <a href='$link' style='display:inline-block;margin-top:16px;padding:10px 24px;background:#0284c7;color:#fff;border-radius:6px;text-decoration:none;font-weight:600;'>Reset Password</a>
                        <p style='margin-top:24px;font-size:12px;color:#9ca3af;'>If you didn't request this, you can safely ignore this email.</p>
                    </div>";
                $mail->AltBody = "Reset your password here: $link";
                $mail->send();

            } catch (Exception $e) {
                error_log("Mailer error: " . $e->getMessage());
            }
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
    <title>Forgot Password</title>
</head>
<body class="bg-gray-100">

    <!-- TOASTS -->
    <?php if ($toastSuccess): ?>
        <div id="toast-success" style="position:fixed;bottom:1.5rem;left:50%;transform:translateX(-50%);z-index:9999;display:flex;align-items:center;gap:.75rem;background:#16a34a;color:#fff;padding:1rem 1.25rem;border-radius:.75rem;box-shadow:0 10px 25px rgba(0,0,0,.4);transition:opacity .4s,transform .4s;white-space:nowrap;">
            <i class="fa-solid fa-circle-check" style="font-size:1.25rem;"></i>
            <span style="font-weight:600;"><?= htmlspecialchars($toastSuccess) ?></span>
            <button onclick="dismissToast('toast-success')" style="background:none;border:none;color:rgba(255,255,255,.7);cursor:pointer;margin-left:.5rem;"><i class="fa-solid fa-times"></i></button>
        </div>
    <?php endif; ?>
    <?php if ($toastError): ?>
        <div id="toast-error" style="position:fixed;bottom:1.5rem;left:50%;transform:translateX(-50%);z-index:9999;display:flex;align-items:center;gap:.75rem;background:#dc2626;color:#fff;padding:1rem 1.25rem;border-radius:.75rem;box-shadow:0 10px 25px rgba(0,0,0,.4);transition:opacity .4s,transform .4s;white-space:nowrap;">
            <i class="fa-solid fa-circle-xmark" style="font-size:1.25rem;"></i>
            <span style="font-weight:600;"><?= htmlspecialchars($toastError) ?></span>
            <button onclick="dismissToast('toast-error')" style="background:none;border:none;color:rgba(255,255,255,.7);cursor:pointer;margin-left:.5rem;"><i class="fa-solid fa-times"></i></button>
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
                    </div>
                </div>
                <div class="hidden md:flex items-center space-x-4">
                    <a href="../cliente/shopping.php" class="relative flex items-center justify-center">
                        <i class="fa-solid fa-cart-shopping text-white text-xl"></i>
                        <?php if ($cartCount > 0): ?>
                            <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center font-bold"><?= $cartCount ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="../auth/login.php" class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium">Sign in</a>
                    <a href="../auth/register.php" class="flex items-center gap-2 px-4 py-2 bg-sky-500 text-white font-semibold rounded-md hover:bg-sky-600 transition">Sign up</a>
                </div>
                <div class="-mr-2 flex md:hidden">
                    <button id="mobile-menu-btn" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-white hover:bg-gray-700">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
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
                    <a href="../auth/login.php" class="text-gray-300 hover:bg-gray-700 hover:text-white block px-3 py-2 rounded-md text-base font-medium">Sign in</a>
                    <a href="../auth/register.php" class="flex items-center gap-2 px-4 py-2 bg-sky-500 text-white font-semibold rounded-md hover:bg-sky-600 transition">Sign up</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="flex min-h-full flex-col justify-center px-6 pt-34 pb-34 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-sm">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="mx-auto h-10 w-10">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
            </svg>
            <h2 class="mt-4 text-center text-2xl/9 font-bold tracking-tight text-gray-900">Forgot your password?</h2>
            <p class="mt-2 text-center text-sm text-gray-500">Enter your email and we'll send you a reset link.</p>
        </div>
        <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">
            <form action="" method="POST" class="space-y-6" novalidate>
                <div>
                    <label for="email" class="block text-sm/6 font-medium text-gray-900">Email Address</label>
                    <div class="mt-2">
                        <input id="email" type="email" name="email" value="<?= htmlspecialchars($emailValue) ?>" placeholder="example@gmail.com" autocomplete="email"
                            class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" />
                    </div>
                </div>
                <div>
                    <button type="submit" class="flex w-full justify-center rounded-md bg-sky-600 px-3 py-1.5 text-sm/6 font-semibold text-white shadow-xs hover:bg-sky-500">Send reset link</button>
                </div>
            </form>
            <p class="mt-6 text-center text-sm text-gray-500">
                Remembered it? <a href="./login.php" class="font-semibold text-sky-700 hover:text-sky-500">Sign in</a>
            </p>
        </div>
    </div>

    <?php include "../includes/footer.php" ?>
    <script src="../js/mobile_menu.js"></script>
    <script src="../js/toast.js"></script>
</body>
</html>