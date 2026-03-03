<?php

// public/index.php

define('BASE_PATH', dirname(__DIR__));

session_start();

if (!isset($_SESSION['guest_last_activity'])) {
    $_SESSION['guest_last_activity'] = time();
} elseif (time() - $_SESSION['guest_last_activity'] > 7200) {
    require_once BASE_PATH . '/config/database.php';
    require_once BASE_PATH . '/app/Models/Videojuego.php';
    $modelJuego = new Videojuego($pdo);
    foreach ($_SESSION['carrito_guest'] ?? [] as $jid => $qty) {
        $modelJuego->incrementStock((int)$jid, (int)$qty);
    }
    $_SESSION['carrito_guest'] = [];
    $_SESSION['guest_last_activity'] = time();
} else {
    $_SESSION['guest_last_activity'] = time();
}

require_once BASE_PATH . '/vendor/autoload.php';

if (file_exists(BASE_PATH . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
    $dotenv->safeLoad();
}

require_once BASE_PATH . '/config/database.php';

$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$uri      = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path     = '/' . ltrim(str_replace($basePath, '', $uri), '/');
$method   = $_SERVER['REQUEST_METHOD'];

$routes = [
    ['GET',  '/',                          'HomeController',     'index'],
    ['GET',  '/index.php',                 'HomeController',     'index'],
    ['GET',  '/auth/login',                'AuthController',     'loginForm'],
    ['POST', '/auth/login',                'AuthController',     'login'],
    ['GET',  '/auth/register',             'AuthController',     'registerForm'],
    ['POST', '/auth/register',             'AuthController',     'register'],
    ['GET',  '/auth/logout',               'AuthController',     'logout'],
    ['GET',  '/auth/forgot-password',      'AuthController',     'forgotForm'],
    ['POST', '/auth/forgot-password',      'AuthController',     'forgotPassword'],
    ['GET',  '/auth/reset-password',       'AuthController',     'resetForm'],
    ['POST', '/auth/reset-password',       'AuthController',     'resetPassword'],
    ['GET',  '/catalogo',                  'ProductoController', 'catalogo'],
    ['GET',  '/detalle',                   'ProductoController', 'detalle'],
    ['GET',  '/buscar',                    'ProductoController', 'buscar'],
    ['GET',  '/carrito',                   'CarritoController',  'shopping'],
    ['POST', '/carrito/add',               'CarritoController',  'add'],
    ['POST', '/carrito/remove',            'CarritoController',  'remove'],
    ['POST', '/carrito/update',            'CarritoController',  'update'],
    ['GET',  '/perfil',                    'AuthController',     'perfilForm'],
    ['POST', '/perfil/update',             'AuthController',     'updateProfile'],
    ['POST', '/perfil/password',           'AuthController',     'changePassword'],
    ['POST', '/perfil/delete',             'AuthController',     'deleteAccount'],
    ['POST', '/pago/checkout',             'PagoController',     'checkout'],
    ['GET',  '/pago/success',              'PagoController',     'success'],
    ['POST', '/pago/webhook',              'PagoController',     'webhook'],
    ['GET',  '/admin',                     'AdminController',    'dashboard'],
    ['GET',  '/admin/login',               'AuthController',     'adminLoginForm'],
    ['POST', '/admin/login',               'AuthController',     'adminLogin'],
    ['GET',  '/admin/register',            'AuthController',     'adminRegisterForm'],
    ['POST', '/admin/register',            'AuthController',     'registerAdmin'],
    ['GET',  '/admin/clientes',            'AdminController',    'clientes'],
    ['POST', '/admin/clientes/delete',     'AdminController',    'deleteCliente'],
    ['GET',  '/admin/estadisticas',        'AdminController',    'estadisticas'],
    ['GET',  '/admin/productos',           'ProductoController', 'adminIndex'],
    ['GET',  '/admin/productos/form',      'ProductoController', 'adminForm'],
    ['POST', '/admin/productos/save',      'ProductoController', 'adminSave'],
    ['POST', '/admin/productos/delete',    'ProductoController', 'adminDelete'],
    ['GET',  '/admin/pedidos',             'AdminController',    'pedidos'],
    ['POST', '/admin/pedidos/update',      'AdminController',    'updateEstadoPedido'],
    ['GET',  '/factura',                   'FacturaController',  'descargar'],
];

$matched = false;
foreach ($routes as [$rMethod, $rPath, $controller, $action]) {
    if ($method === $rMethod && $path === $rPath) {
        $matched = true;

        $file = BASE_PATH . "/app/Controllers/{$controller}.php";
        if (!file_exists($file)) {
            http_response_code(500);
            die("Controller not found: $controller");
        }
        require_once $file;

        if ($path === '/pago/webhook') {
            (new PagoController($pdo))->webhook();
            exit;
        }

        $ctrl   = new $controller($pdo);
        $result = $ctrl->$action();

        if (is_array($result)) {
            extract($result);
            $view = BASE_PATH . viewPath($controller, $action);
            if (file_exists($view)) {
                include $view;
            } else {
                http_response_code(500);
                die("View not found: $view");
            }
        }
        break;
    }
}

if (!$matched) {
    http_response_code(404);
    include BASE_PATH . '/app/Views/404.php';
}

function viewPath(string $controller, string $action): string
{
    $map = [
        'HomeController:index'             => '/app/Views/cliente/home.php',
        'AuthController:loginForm'         => '/app/Views/auth/login.php',
        'AuthController:registerForm'      => '/app/Views/auth/register.php',
        'AuthController:forgotForm'        => '/app/Views/auth/forgot_password.php',
        'AuthController:resetForm'         => '/app/Views/auth/reset_password.php',
        'AuthController:perfilForm'        => '/app/Views/cliente/perfil.php',
        'AuthController:adminLoginForm'    => '/app/Views/admin/login.php',
        'AuthController:adminRegisterForm' => '/app/Views/admin/register.php',
        'ProductoController:catalogo'      => '/app/Views/cliente/catalogo.php',
        'ProductoController:detalle'       => '/app/Views/cliente/detalle.php',
        'ProductoController:buscar'        => '/app/Views/cliente/buscar.php',
        'CarritoController:shopping'       => '/app/Views/cliente/shopping.php',
        'PagoController:success'           => '/app/Views/cliente/checkout_success.php',
        'AdminController:dashboard'        => '/app/Views/admin/dashboard.php',
        'AdminController:clientes'         => '/app/Views/admin/clientes.php',
        'AdminController:estadisticas'     => '/app/Views/admin/estadisticas.php',
        'AdminController:pedidos'          => '/app/Views/admin/pedidos.php',
        'ProductoController:adminIndex'    => '/app/Views/admin/productos.php',
        'ProductoController:adminForm'     => '/app/Views/admin/producto_form.php',
    ];
    return $map["$controller:$action"] ?? '';
}