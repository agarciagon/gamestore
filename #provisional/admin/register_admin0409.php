<?php
require_once '../config/conection.php';
session_start();

// Redirigir si ya está logueado
if (isset($_SESSION['user_id']) && isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin') {
    header('Location: ./dashboard.php');
    exit;
}

$errores = [];
$success = '';
$nombre = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['name'] ?? '');
    $email = trim(filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL));
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirmation'] ?? '';
    /* $admin_code_input = $_POST['codigo_admin'] ?? '';
    $admin_code_real = 'ADMIN123'; */

    // Validaciones
    /* if ($admin_code_input !== $admin_code_real) $errores[] = "Código de administrador inválido."; */
    if (empty($nombre) || strlen($nombre) < 2) $errores[] = "The name must be at least 2 characters long.";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errores[] = "Invalid email.";
    if (empty($password) || strlen($password) < 8) $errores[] = "The password must be at least 8 characters long.";
    if ($password !== $password_confirm) $errores[] = "The passwords do not match.";

    // Verificar email duplicado
    if (empty($errores)) {
        try {
            $stmt = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetchColumn()) $errores[] = "This email is already registered. <a href='login_admin0409.php' class='text-sky-400 underline hover:text-sky-500'>Do you want to log in?</a>";
        } catch (PDOException $e) {
            $errores[] = "Error verifying email.";
        }
    }

    // Insertar usuario
    if (empty($errores)) {
        try {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, email, rol, contrasena, fecha_creacion) VALUES (?, ?, 'admin', ?, NOW())");
            $stmt->execute([$nombre, $email, $password_hash]);
            $success = "¡Registration successful, $nombre! You can now log in..";
            $nombre = $email = '';
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
    <title>Admin Sign Up</title>

</head>

<body class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 flex items-center justify-center px-4">

    <div class="w-full max-w-md">

        <!-- Logo -->
        <div class="text-center mb-8">
            <img src="../assets/img/logo.png" alt="Logo" class="mx-auto h-28 md:h-48 w-auto">
            <h1 class="text-3xl font-bold text-white tracking-wide">Admin Panel</h1>
            <p class="text-gray-400 text-sm mt-2">Create your admin account</p>
        </div>

        <!-- Card -->
        <div class="bg-gray-800/70 backdrop-blur-lg border border-gray-700 rounded-2xl shadow-2xl p-8">

            <!-- Errores -->
            <?php if (!empty($errores)): ?>
                <div class="mb-6 bg-red-500/10 border border-red-500 text-red-400 p-4 rounded-lg text-sm">
                    <ul class="space-y-1">
                        <?php foreach ($errores as $error): ?>
                            <li>• <?= $error ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6 novalidate">
                            
                <!-- Name -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Admin Name*</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($nombre) ?>" placeholder="Name"
                        class="w-full px-4 py-3 rounded-lg bg-gray-900 border border-gray-600 text-white placeholder-gray-500 focus:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-sky-500 transition">
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Email*</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="admin@email.com"
                        class="w-full px-4 py-3 rounded-lg bg-gray-900 border border-gray-600 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-sky-500 transition">
                </div>

                <!-- Password -->
                <div class="flex gap-6 mb-2">
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-300 mb-1">Password*</label>
                        <div class="relative mt-1">
                            <input id="password" type="password" name="password" placeholder="********"
                                class="w-full px-4 py-3 rounded-lg bg-gray-900 border border-gray-600 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-sky-500 transition">
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

                    <div class="mb-2">
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-300 mb-1">Repeat Password*</label>
                        <div class="relative mt-1">
                            <input id="password_confirmation" type="password" name="password_confirmation" placeholder="********"
                                class="w-full px-4 py-3 rounded-lg bg-gray-900 border border-gray-600 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-sky-500 transition">
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

                <p class="text-sm text-gray-500 mt-1">Minimum 8 characters</p>

                <!-- <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Código de Admin</label>
                    <input type="password" name="codigo_admin" placeholder="Ingresa tu código secreto"
                        class="w-full px-4 py-3 rounded-lg bg-gray-900 border border-gray-600 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-sky-500 transition"/>
                </div> -->

                <button type="submit"
                    class="w-full bg-sky-600 hover:bg-sky-500 text-white font-semibold py-3 rounded-lg transition duration-200 shadow-lg">
                    Register
                </button>

            </form>
        </div>
    </div>

    <script src="../js/password.js"></script>
    <script src="../js/toast.js"></script>

</body>

</html>