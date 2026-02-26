<?php
// app/Helpers/Auth.php
// Funciones de control de acceso. Incluir en cada controlador que lo necesite.

class Auth
{
    // Redirige al login si el usuario no esta autenticado
    public static function requireLogin(string $redirect = '/gamestore/public/auth/login'): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['user_id'])) {
            header('Location: ' . $redirect);
            exit;
        }
    }

    // Redirige si no tiene rol de admin
    public static function requireAdmin(string $redirect = '/gamestore/public/admin/login'): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['user_id']) || ($_SESSION['rol'] ?? '') !== 'admin') {
            header('Location: ' . $redirect);
            exit;
        }
    }

    // Comprueba si hay sesion activa sin redirigir
    public static function isLogged(): bool
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        return !empty($_SESSION['user_id']);
    }

    // Devuelve el rol actual o null
    public static function rol(): ?string
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        return $_SESSION['rol'] ?? null;
    }

    // Inicia sesion de usuario y limpia el carrito guest de la sesion
    // (el merge de carrito lo hace CarritoController)
    public static function loginSession(array $usuario): void
    {
        session_regenerate_id(true); // previene session fixation
        $_SESSION['user_id']   = $usuario['id_usuario'];
        $_SESSION['user_name'] = $usuario['nombre'];
        $_SESSION['email']     = $usuario['email'];
        $_SESSION['rol']       = $usuario['rol'];
    }

    // Destruye la sesion correctamente manteniendo la cookie caducada
    public static function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }
}
