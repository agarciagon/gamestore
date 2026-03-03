<?php
// app/Helpers/Csrf.php
// Proteccion CSRF sencilla basada en token de sesion.
/* Uso en formularios: <?= Csrf::field()?>*/
// Validacion en POST:  Csrf::verify() — lanza excepcion si falla

class Csrf
{
    private const KEY = '_csrf_token';

    public static function generate(): string
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION[self::KEY])) {
            $_SESSION[self::KEY] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::KEY];
    }

    // Devuelve un <input type="hidden"> listo para poner en el form
    public static function field(): string
    {
        return '<input type="hidden" name="_csrf" value="' . htmlspecialchars(self::generate()) . '">';
    }

    // Lanza RuntimeException si el token no es valido
    public static function verify(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $token    = $_POST['_csrf'] ?? '';
        $expected = $_SESSION[self::KEY] ?? '';
        if (!hash_equals($expected, $token)) {
            http_response_code(403);
            throw new \RuntimeException('CSRF token invalid.');
        }
        // En vez de unset, regenera inmediatamente
        $_SESSION[self::KEY] = bin2hex(random_bytes(32));
    }

    // Version "soft" — devuelve true/false sin lanzar excepcion
    public static function check(): bool
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $token    = $_POST['_csrf'] ?? '';
        $expected = $_SESSION[self::KEY] ?? '';
        if (hash_equals($expected, $token)) {
            unset($_SESSION[self::KEY]);
            return true;
        }
        return false;
    }
}
