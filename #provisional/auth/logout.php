<?php
session_start();

// Guardar a dónde redirigir antes de destruir la sesión
$from = $_GET['from'] ?? '';

// Limpiar solo los datos de usuario, NO el carrito guest
// (aunque al estar logueado no debería haber carrito guest, por seguridad lo mantenemos)
unset(
    $_SESSION['user_id'],
    $_SESSION['user_name'],
    $_SESSION['email'],
    $_SESSION['rol'],
    $_SESSION['success'],
    $_SESSION['error']
);

// Si queda algo en sesión, destruirla completamente; si no, solo vaciar datos de usuario
// El carrito de BD persiste en la tabla 'carrito' y se cargará en el próximo login
session_destroy();

// Eliminar la cookie de sesión
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

if ($from === 'cliente') {
    header('Location: ../index.php');
} else {
    header('Location: ../admin/login_admin0409.php');
}
exit;