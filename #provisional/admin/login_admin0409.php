<?php
session_start();

// Incluir la conexión a la base de datos 
require_once '../config/conection.php';

// Si ya está logueado como admin, redirigir al dashboard
if (isset($_SESSION['user_id']) && isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin') {
    header('Location: ./dashboard.php');
    exit;
}

// Inicializar variables
$errores = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim(filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL));
    $password = $_POST['password'] ?? '';

    // Validaciones básicas
    if (empty($email)) {
        $errores[] = "Email is mandatory.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "The email format is invalid.";
    }

    if (empty($password)) {
        $errores[] = "Password is required.";
    }

    // Si no hay errores de validación, intentar iniciar sesión
    if (empty($errores)) {
        try {
            $stmt = $pdo->prepare("
            SELECT id_usuario, nombre, email, contrasena, rol
            FROM usuarios
            WHERE email = ?
            LIMIT 1
        ");
            $stmt->execute([$email]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usuario && password_verify($password, $usuario['contrasena'])) {

                // Verificamos que sea ADMIN
                if ($usuario['rol'] !== 'admin') {
                    $errores[] = "You do not have permission to access the administration panel.";
                } else {

                    // Crear sesión
                    $_SESSION['user_id'] = $usuario['id_usuario'];
                    $_SESSION['user_name'] = $usuario['nombre'];
                    $_SESSION['email'] = $usuario['email'];
                    $_SESSION['rol'] = $usuario['rol'];

                    header('Location: ./dashboard.php');
                    exit;
                }
            } else {
                $errores[] = "Incorrect email or password.";
            }
        } catch (PDOException $e) {
            $errores[] = "Error logging in. Please try again.";
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
    <title>Sign in</title>
</head>

<body class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 flex items-center justify-center px-4">

    <div class="w-full max-w-md">

        <!-- Logo -->
        <div class="text-center mb-8">
            <img src="../assets/img/logo.png" alt="Logo" class="mx-auto h-28 md:h-48 w-auto">
            <h1 class="text-3xl font-bold text-white tracking-wide">Admin Panel</h1>
            <p class="text-gray-400 text-sm mt-2">Restricted Access</p>
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

                <!-- Email -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Email*</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($email) ?>" placeholder="admin@email.com"
                        class="w-full px-4 py-3 rounded-lg bg-gray-900 border border-gray-600 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-sky-500 transition">
                </div>


                <!-- Password -->
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Password</label>
                    <input type="password"
                        name="password"
                        class="w-full px-4 py-3 rounded-lg bg-gray-900 border border-gray-600 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-sky-500 transition"
                        placeholder="••••••••">
                </div>

                <!-- Código Admin -->
                <!-- <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1">Admin Code</label>
                    <input type="password"
                        name="codigo_admin"
                        class="w-full px-4 py-3 rounded-lg bg-gray-900 border border-gray-600 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-sky-500 transition"
                        placeholder="Enter admin code">
                </div> -->

                <!-- Botón -->
                <button type="submit"
                    class="w-full bg-sky-600 hover:bg-sky-500 text-white font-semibold py-3 rounded-lg transition duration-200 shadow-lg">
                    Sign in
                </button>

            </form>
        </div>

        <!-- Footer text -->
        <p class="text-center text-gray-500 text-xs mt-6">
            © <?= date('Y') ?> Game Store Admin System
        </p>

    </div>

</body>


</html>