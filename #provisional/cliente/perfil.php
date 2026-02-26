<?php
session_start();
require_once("../config/conection.php");

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit;
}

$nombre = $_SESSION['user_name'] ?? 'usuario';
$userId = $_SESSION['user_id'];


// Manejar formularios
$successMsg = '';
$errorMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $newName = trim($_POST['nombre'] ?? '');
        $newEmail = trim($_POST['email'] ?? '');

        if ($newName === '' || $newEmail === '') {
            $errorMsg = "Please fill in all profile fields.";
        } elseif (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            $errorMsg = "Enter a valid email address.";
        } else {
            $stmt = $pdo->prepare("UPDATE usuarios SET nombre=:nombre, email=:email WHERE id_usuario=:id");
            $stmt->execute([
                ':nombre' => $newName,
                ':email' => $newEmail,
                ':id' => $userId
            ]);

            $_SESSION['user_name'] = $newName;
            $successMsg = "Profile updated successfully.";
        }
    } elseif ($action === 'change_password') {
        $current = $_POST['current_password'] ?? '';
$new     = $_POST['new_password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';

        if ($current === '' || $new === '' || $confirm === '') {
            $errorMsg = "Please fill in all password fields.";
        } elseif (strlen($new) < 8) {
            $errorMsg = "New password must be at least 8 characters.";
        } elseif ($new !== $confirm) {
            $errorMsg = "New passwords do not match.";
        } else {
            $stmt = $pdo->prepare("SELECT contrasena FROM usuarios WHERE id_usuario=:id");
            $stmt->execute([':id' => $userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($current, $user['contrasena'])) {
                $newHash = password_hash($new, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE usuarios SET contrasena=:pass WHERE id_usuario=:id");
                $stmt->execute([':pass' => $newHash, ':id' => $userId]);
                $successMsg = "Password updated successfully.";
            } else {
                $errorMsg = "Current password is incorrect.";
            }
        }
    } elseif ($action === 'delete_account') {

        // Eliminar cuenta
        try {
            $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id_usuario=:id");
            $stmt->execute([':id' => $userId]);

            session_destroy();
            header('Location: ../index.php?deleted=1');
            exit;
        } catch (PDOException $e) {
            $errorMsg = "Error deleting account. Please try again.";
        }
    }
}

// Obtener info usuario
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id_usuario = :id");
$stmt->execute([':id' => $userId]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

// Contar items del carrito
$stmtCarrito = $pdo->prepare("SELECT SUM(cantidad) AS total_productos FROM carrito WHERE id_usuario=:id_usuario");
$stmtCarrito->execute([':id_usuario' => $userId]);
$statsCarrito = $stmtCarrito->fetch(PDO::FETCH_ASSOC);
$cartCount = $statsCarrito['total_productos'] ?? 0;
?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil - Game Store</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <link rel="stylesheet" href="../dist/output.css">
</head>

<body class="bg-gray-900 text-white">

    <!-- Navbar -->
    <nav class="bg-gray-800">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">

                <!-- Logo + Links -->
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <a href="../index.php">
                            <img class="h-36 w-36" src="../assets/img/logo.png" alt="Logo">
                        </a>
                    </div>
                    <div class="hidden md:flex ml-10 space-x-4">
                        <a href="../index.php" class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium content-center">Home</a> <span class="text-gray-300 content-center">|</span>
                        <a href="../cliente/catalogo.php" class="text-gray-300 hover:bg-gray-700 hover:text-white px-3 py-2 rounded-md text-sm font-medium content-center">Catalog</a>
                    </div>
                </div>

                <!-- Button sign in and sign up -->
                <div class="hidden md:flex md:flex-row items-center space-x-4">
                    <!-- Shopping -->
                    <a href="../cliente/shopping.php" class="relative flex items-center justify-center">
                        <i class="fa-solid fa-cart-shopping text-white text-xl"></i>
                        <?php if ($cartCount > 0): ?>
                            <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center font-bold">
                                <?= $cartCount ?>
                            </span>
                        <?php endif; ?>
                    </a>

                    <!-- User -->
                    <div class="relative">
                        <button id="user-menu-button" type="button"
                            class="relative flex rounded-full bg-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800"
                            aria-expanded="false" aria-haspopup="true">
                            <img class="h-8 w-8 rounded-full"
                                src="https://ui-avatars.com/api/?name=<?= urlencode($nombre) ?>&background=0D8ABC&color=fff"
                                alt="<?= htmlspecialchars($nombre) ?>">
                        </button>

                        <!-- Dropdown menu -->
                        <div id="user-menu"
                            class="hidden absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
                            role="menu" aria-orientation="vertical" aria-labelledby="user-menu-button">

                            <a href="./perfil.php"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                role="menuitem">
                                My profile
                            </a>

                            <a href="./shopping.php"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                role="menuitem">
                                My cart
                            </a>

                            <hr class="my-1 border-gray-200">

                            <a href="../auth/logout.php?from=cliente"
                                class="block px-4 py-2 text-sm text-red-700 hover:bg-red-50"
                                role="menuitem">
                                Log out
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Mobile menu button -->
                <div class="-mr-2 flex md:hidden">
                    <button id="mobile-menu-btn" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobile-menu" class="hidden md:hidden">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                <a href="../index.php" class="text-gray-300 hover:bg-gray-700 hover:text-white block px-3 py-2 rounded-md text-base font-medium">Home</a>
                <a href="./catalogo.php" class="text-gray-300 hover:bg-gray-700 hover:text-white block px-3 py-2 rounded-md text-base font-medium">Catalog</a>
            </div>

            <div class="border-t border-gray-700 pt-4 pb-3 flex items-center space-x-4 px-4 justify-end">
                <!-- Shopping -->
                <a href="./shopping.php" class="relative flex items-center justify-center">
                    <i class="fa-solid fa-cart-shopping text-white text-xl"></i>
                    <?php if ($cartCount > 0): ?>
                        <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center font-bold">
                            <?= $cartCount ?>
                        </span>
                    <?php endif; ?>
                </a>

                <!-- User -->
                <div class="relative">
                    <button id="user-menu-button-mobile" type="button"
                        class="relative flex rounded-full bg-gray-800 text-sm focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-gray-800"
                        aria-expanded="false" aria-haspopup="true">
                        <img class="h-8 w-8 rounded-full"
                            src="https://ui-avatars.com/api/?name=<?= urlencode($nombre) ?>&background=0D8ABC&color=fff"
                            alt="<?= htmlspecialchars($nombre) ?>">
                    </button>

                    <!-- Dropdown menu -->
                    <div id="user-menu-mobile"
                        class="hidden absolute right-0 z-10 mt-2 w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none"
                        role="menu" aria-orientation="vertical" aria-labelledby="user-menu-button">

                        <a href="./perfil.php"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                            role="menuitem">
                            My profile
                        </a>

                        <a href="./shopping.php"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                            role="menuitem">
                            My cart
                        </a>

                        <hr class="my-1 border-gray-200">

                        <a href="../auth/logout.php?from=cliente"
                            class="block px-4 py-2 text-sm text-red-700 hover:bg-red-50"
                            role="menuitem">
                            Log out
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>


    <!-- Main-->
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        <!-- Header perfil -->
        <div class="flex flex-col md:flex-row items-center md:items-start gap-6 bg-gray-800 rounded-xl p-6 shadow-lg">
            <img src="https://ui-avatars.com/api/?name=<?= urlencode($nombre) ?>&background=0D8ABC&color=fff&size=128" class="w-24 h-24 rounded-full border-2 border-gray-600" alt="<?= htmlspecialchars($nombre) ?>">
            <div>
                <h1 class="text-2xl font-bold pb-3"><?= htmlspecialchars($usuario['nombre']) ?></h1>
                <p class="text-gray-500 text-sm">Member since: <?= date('M Y', strtotime($usuario['fecha_creacion'])) ?></p>
                <p class="text-gray-500 text-sm">Email: <?= htmlspecialchars($usuario['email']) ?></p>
                <p class="text-gray-500 text-sm">Products in cart: <?= $statsCarrito['total_productos'] ?? 0 ?></p>
            </div>
        </div>

        <!-- Formularios -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Editar perfil -->
            <div class="bg-gray-800 rounded-xl p-6">
                <h3 class="text-lg font-semibold mb-4">Edit Profile</h3>
                <form method="POST" class="space-y-3">
                    <input type="hidden" name="action" value="update_profile">
                    <input type="text" name="nombre" value="<?= htmlspecialchars($usuario['nombre']) ?>" placeholder="Name" class="w-full px-3 py-2 rounded bg-gray-700 border border-gray-600 text-white">
                    <input type="email" name="email" value="<?= htmlspecialchars($usuario['email']) ?>" placeholder="Email" class="w-full px-3 py-2 rounded bg-gray-700 border border-gray-600 text-white">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 py-2 rounded font-bold transition">Save Changes</button>
                </form>
            </div>

            <!-- Cambiar contraseña -->
            <div class="bg-gray-800 rounded-xl p-6">
                <h3 class="text-lg font-semibold mb-4">Change Password</h3>
                <form method="POST" class="space-y-3">
                    <input type="hidden" name="action" value="change_password">
                    <input type="password" name="current_password" placeholder="Current password" class="w-full px-3 py-2 rounded bg-gray-700 border border-gray-600 text-white">
                    <input type="password" name="new_password" placeholder="New password" class="w-full px-3 py-2 rounded bg-gray-700 border border-gray-600 text-white">
                    <input type="password" name="confirm_password" placeholder="Confirm new password" class="w-full px-3 py-2 rounded bg-gray-700 border border-gray-600 text-white">
                    <button type="submit" class="w-full bg-green-600 hover:bg-green-700 py-2 rounded font-bold transition">Update Password</button>
                </form>
            </div>
        </div>

        <!-- Eliminar cuenta -->
        <div class="mt-8 bg-gray-800 rounded-xl p-6 text-center border border-red-600">
            <form id="deleteAccountForm" method="POST">
                <input type="hidden" name="action" value="delete_account">
                <button type="button" onclick="confirmDeleteAccount()"
                    class="text-white bg-red-700 rounded-lg hover:text-red-100 p-2 hover:bg-red-500 transition">
                    <i class="fa-solid fa-trash text-sm"></i> Delete Account
                </button>
            </form>
            <p class="text-gray-400 mb-3 mt-3">This action is irreversible.</p>
        </div>


    </div>

    <!-- Footer -->
    <?php include "../includes/footer.php"; ?>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="../js/mobile_menu.js"></script>
    <script src="../js/user_drop.js"></script>
    <script src="../js/modal.js"></script>

    <!-- Modales y Confirmaciones -->
    <script>
        <?php if (!empty($successMsg)): ?>
            showSuccessModal(<?= json_encode($successMsg) ?>);
        <?php endif; ?>

        <?php if (!empty($errorMsg)): ?>
            showErrorModal(<?= json_encode($errorMsg) ?>); 
        <?php endif; ?>
    </script>
</body>

</html>