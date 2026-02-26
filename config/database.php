<?php
// config/database.php
// Reemplaza config/conection.php.
// Requiere: composer require vlucas/phpdotenv

if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

// Cargar vendor/autoload si aun no esta cargado
if (file_exists(BASE_PATH . '/vendor/autoload.php')) {
    require_once BASE_PATH . '/vendor/autoload.php';
}

// Cargar .env
if (class_exists('Dotenv\Dotenv')) {
    $dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
    $dotenv->safeLoad();
    $dotenv->required(['DB_HOST', 'DB_NAME', 'DB_USER']);
}

$dsn = sprintf(
    'mysql:host=%s;dbname=%s;charset=utf8mb4',
    $_ENV['DB_HOST'] ?? 'localhost',
    $_ENV['DB_NAME'] ?? 'videojuegos'
);

try {
    $pdo = new PDO($dsn, $_ENV['DB_USER'] ?? 'root', $_ENV['DB_PASS'] ?? '', [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    error_log('[DB] ' . $e->getMessage());
    http_response_code(500);
    die('Service temporarily unavailable.');
}
