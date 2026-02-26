<?php
// app/Controllers/AuthController.php
// Gestiona: login, register (cliente + admin), logout, forgot_password, reset_password

require_once BASE_PATH . '/app/Models/Usuario.php';
require_once BASE_PATH . '/app/Models/Carrito.php';
require_once BASE_PATH . '/app/Models/Videojuego.php';
require_once BASE_PATH . '/app/Models/PasswordReset.php';
require_once BASE_PATH . '/app/Helpers/Auth.php';
require_once BASE_PATH . '/app/Helpers/Csrf.php';
require_once BASE_PATH . '/app/Services/MailService.php';

class AuthController
{
    private Usuario       $usuarios;
    private Carrito       $carrito;
    private Videojuego    $juegos;
    private PasswordReset $resets;
    private MailService   $mailer;

    public function __construct(private PDO $pdo)
    {
        $this->usuarios = new Usuario($pdo);
        $this->carrito  = new Carrito($pdo);
        $this->juegos   = new Videojuego($pdo);
        $this->resets   = new PasswordReset($pdo);
        $this->mailer   = new MailService();
    }

    // ── POST /auth/login.php ─────────────────────────────────────────────────
    public function login(): void
    {
        $email    = trim(filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL));
        $password = $_POST['password'] ?? '';
        $errores  = [];

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'Enter a valid email.';
        }
        if (empty($password)) {
            $errores[] = 'Password is required.';
        }

        if ($errores) {
            $_SESSION['errors'] = $errores;
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/gamestore/public/auth/login'));
            exit;
        }

        $usuario = $this->usuarios->findByEmail($email);

        if (!$usuario || !password_verify($password, $usuario['contrasena'])) {
            $_SESSION['errors'] = ['Incorrect email or password.'];
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/gamestore/public/auth/login'));
            exit;
        }

        // Guardar carrito guest antes de sobreescribir la sesion
        $guestCart = $_SESSION['carrito_guest'] ?? [];

        Auth::loginSession($usuario);
        unset($_SESSION['carrito_guest']);

        // Fusionar carrito guest con el carrito BD del usuario
        if (!empty($guestCart)) {
            $this->carrito->fusionarGuest($usuario['id_usuario'], $guestCart, $this->juegos);
        }

        header('Location: ' . ($usuario['rol'] === 'admin' ? '/gamestore/public/admin/dashboard' : '/gamestore/public'));
        exit;
    }

    // ── POST /auth/register.php ──────────────────────────────────────────────
    public function register(): void
    {
        Csrf::verify();

        $nombre    = trim($_POST['name']                  ?? '');
        $email     = trim(filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL));
        $password  = $_POST['password']                   ?? '';
        $confirm   = $_POST['password_confirmation']      ?? '';
        $errores   = [];

        if (strlen($nombre) < 2)                                     $errores[] = 'Name must be at least 2 characters.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL))              $errores[] = 'Invalid email format.';
        if (strlen($password) < 8)                                   $errores[] = 'Password must be at least 8 characters.';
        if ($password !== $confirm)                                   $errores[] = 'Passwords do not match.';
        if (empty($errores) && $this->usuarios->emailExists($email)) $errores[] = 'This email is already registered.';

        if ($errores) {
            $_SESSION['errors'] = $errores;
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? '/gamestore/public/auth/register'));
            exit;
        }

        $guestCart = $_SESSION['carrito_guest'] ?? [];
        $nuevoId   = $this->usuarios->create($nombre, $email, password_hash($password, PASSWORD_DEFAULT));

        Auth::loginSession([
            'id_usuario' => $nuevoId,
            'nombre'     => $nombre,
            'email'      => $email,
            'rol'        => 'cliente',
        ]);
        unset($_SESSION['carrito_guest']);

        if (!empty($guestCart)) {
            $this->carrito->fusionarGuest($nuevoId, $guestCart, $this->juegos);
        }

        header('Location: /gamestore/public');
        exit;
    }

    // ── POST /admin/register.php ───────────────────────────────────────
    public function registerAdmin(): void
    {
        Csrf::verify();

        $nombre   = trim($_POST['name']             ?? '');
        $email    = trim(filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL));
        $password = $_POST['password']              ?? '';
        $confirm  = $_POST['password_confirmation'] ?? '';
        $errores  = [];

        if (strlen($nombre) < 2)                                     $errores[] = 'Name must be at least 2 characters.';
        if (!filter_var($email, FILTER_VALIDATE_EMAIL))              $errores[] = 'Invalid email.';
        if (strlen($password) < 8)                                   $errores[] = 'Password must be at least 8 characters.';
        if ($password !== $confirm)                                   $errores[] = 'Passwords do not match.';
        if (empty($errores) && $this->usuarios->emailExists($email)) $errores[] = 'Email already registered.';

        if ($errores) {
            $_SESSION['errors'] = $errores;
            header('Location: /gamestore/public/admin/register');
            exit;
        }

        $this->usuarios->create($nombre, $email, password_hash($password, PASSWORD_DEFAULT), 'admin');
        $_SESSION['success'] = "Admin registered successfully. You can now log in.";
        header('Location: /gamestore/public/admin/login');
        exit;
    }

    // ── GET /auth/logout.php ─────────────────────────────────────────────────
    public function logout(): void
    {
        $from = $_GET['from'] ?? 'cliente';
        Auth::logout();
        header('Location: ' . ($from === 'admin' ? '/gamestore/public/admin/login' : '/gamestore/public'));
        exit;
    }

    // ── POST /auth/forgot_password.php ───────────────────────────────────────
    public function forgotPassword(): void
    {
        Csrf::verify();

        $email = trim(filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['errors'] = ['Invalid email format.'];
            header('Location: /gamestore/public/auth/forgot-password');
            exit;
        }

        // Siempre mostramos el mismo mensaje (evita enumeracion de emails)
        $_SESSION['success'] = "If that email exists, you'll receive a reset link shortly.";

        $usuario = $this->usuarios->findByEmail($email);
        if ($usuario) {
            try {
                $token = $this->resets->crear($email);
                $link  = rtrim($_ENV['APP_URL'] ?? 'http://localhost/gamestore/public', '/') . "/auth/reset-password?token=$token";

                $this->mailer->enviar(
                    $email,
                    'Reset your password – GameStore',
                    'reset_password',
                    ['nombre' => $usuario['nombre'], 'link' => $link]
                );
            } catch (\Throwable $e) {
                error_log('[AuthController::forgotPassword] ' . $e->getMessage());
            }
        }

        header('Location: /gamestore/public/auth/forgot-password');
        exit;
    }

    // ── POST /auth/reset_password.php ────────────────────────────────────────
    public function resetPassword(): void
    {
        Csrf::verify();

        $token   = trim($_POST['token']            ?? '');
        $pass    = $_POST['password']              ?? '';
        $confirm = $_POST['password_confirmation'] ?? '';
        $errores = [];

        if (strlen($pass) < 8)    $errores[] = 'Password must be at least 8 characters.';
        if ($pass !== $confirm)   $errores[] = 'Passwords do not match.';

        if ($errores) {
            $_SESSION['errors'] = $errores;
            header("Location: /gamestore/public/auth/reset-password?token=" . urlencode($token));
            exit;
        }

        $email = $this->resets->validar($token);
        if (!$email) {
            $_SESSION['errors'] = ['This link is invalid or has expired.'];
            header('Location: /gamestore/public/auth/forgot-password');
            exit;
        }

        $this->usuarios->updatePasswordByEmail($email, password_hash($pass, PASSWORD_DEFAULT));
        $this->resets->marcarUsado($token);

        $_SESSION['success'] = 'Password updated successfully. You can now sign in.';
        header('Location: /gamestore/public/auth/login');
        exit;
    }

    // ── Perfil: cambiar nombre/email ─────────────────────────────────────────
    public function updateProfile(): void
    {
        Auth::requireLogin();


        $uid   = (int) $_SESSION['user_id'];
        $name  = trim($_POST['nombre'] ?? '');
        $email = trim(filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL));

        if (!$name || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['errors'] = ['Please fill in all fields with valid data.'];
            header('Location: /gamestore/public/perfil');
            exit;
        }

        $this->usuarios->updateNombre($uid, $name);
        $this->usuarios->updateEmail($uid, $email);
        $_SESSION['user_name'] = $name;
        $_SESSION['email']     = $email;

        $_SESSION['success'] = 'Profile updated successfully.';
        header('Location: /gamestore/public/perfil');
        exit;
    }

    // ── Perfil: cambiar contrasena ───────────────────────────────────────────
    public function changePassword(): void
    {
        Auth::requireLogin();
        Csrf::verify();

        $uid     = (int) $_SESSION['user_id'];
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password']     ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        $usuario = $this->usuarios->findById($uid);
        // Necesitamos la contrasena — hacemos una query especifica
        $stmt = $this->pdo->prepare('SELECT contrasena FROM usuarios WHERE id_usuario=?');
        $stmt->execute([$uid]);
        $hash = $stmt->fetchColumn();

        if (!$hash || !password_verify($current, $hash)) {
            $_SESSION['errors'] = ['Current password is incorrect.'];
            header('Location: /gamestore/public/perfil');
            exit;
        }
        if (strlen($new) < 8 || $new !== $confirm) {
            $_SESSION['errors'] = ['New passwords must match and be at least 8 characters.'];
            header('Location: /gamestore/public/perfil');
            exit;
        }

        $this->usuarios->updatePassword($uid, password_hash($new, PASSWORD_DEFAULT));
        $_SESSION['success'] = 'Password changed successfully.';
        header('Location: /gamestore/public/perfil');
        exit;
    }

    // app/Controllers/AuthController_extra.php
    // ────────────────────────────────────────────────────────────────────────────
    // PARCHE — añade estos métodos al AuthController.php existente (fases 2-3-4).
    // Pega este contenido dentro de la clase AuthController, antes del cierre '}'.
    // ────────────────────────────────────────────────────────────────────────────
    //
    // ── Formularios GET (devuelven array → router incluye la vista) ──────────────

    // GET /auth/login
    public function loginForm(): array
    {
        if (Auth::isLogged()) {
            header('Location: ' . ($_SESSION['rol'] === 'admin' ? '/gamestore/public/admin' : '/gamestore/public'));
            exit;
        }
        return [];
    }

    // GET /auth/register
    public function registerForm(): array
    {
        if (Auth::isLogged()) {
            header('Location: /gamestore/public');
            exit;
        }
        return [];
    }

    // GET /auth/forgot-password
    public function forgotForm(): array
    {
        return [];
    }

    // GET /auth/reset-password?token=...
    public function resetForm(): array
    {
        $token = trim($_GET['token'] ?? '');
        if (empty($token)) {
            header('Location: /gamestore/public/auth/forgot-password');
            exit;
        }
        $email       = $this->resets->validar($token);
        $tokenValido = (bool) $email;
        return compact('token', 'tokenValido');
    }

    // GET /perfil
    public function perfilForm(): array
    {
        Auth::requireLogin();
        $uid         = (int) $_SESSION['user_id'];
        $usuario     = $this->usuarios->findById($uid);
        require_once BASE_PATH . '/app/Models/Pedido.php';
        $modelPedido = new Pedido($this->pdo);

        // Cargar pedidos con items desglosados
        $pedidosRaw = $modelPedido->getByUsuario($uid);
        $pedidos    = [];
        foreach ($pedidosRaw as $p) {
            $conItems   = $modelPedido->findWithItems($p['id']);
            $p['items'] = $conItems['items'] ?? [];
            $pedidos[]  = $p;
        }

        return compact('usuario', 'pedidos');
    }

    // GET /admin/login
    public function adminLoginForm(): array
    {
        if (Auth::isLogged() && Auth::rol() === 'admin') {
            header('Location: /gamestore/public/admin');
            exit;
        }
        return [];
    }

    // POST /admin/login
    public function adminLogin(): void
    {
        $email    = trim(filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL));
        $password = $_POST['password'] ?? '';

        if (!$email || !$password) {
            $_SESSION['errors'] = ['Email and password are required.'];
            header('Location: /gamestore/public/admin/login');
            exit;
        }

        $usuario = $this->usuarios->findByEmail($email);
        if (!$usuario || $usuario['rol'] !== 'admin' || !password_verify($password, $usuario['contrasena'])) {
            $_SESSION['errors'] = ['Incorrect credentials or insufficient permissions.'];
            header('Location: /gamestore/public/admin/login');
            exit;
        }

        Auth::loginSession($usuario);
        header('Location: /gamestore/public/admin');
        exit;
    }

    // GET /admin/register
    public function adminRegisterForm(): array
    {
        return [];
    }

    // DELETE /perfil/delete — eliminar cuenta
    public function deleteAccount(): void
    {
        Auth::requireLogin();
        Csrf::verify();
        $uid = (int) $_SESSION['user_id'];
        Auth::logout();
        $this->usuarios->delete($uid);
        header('Location: /gamestore/public');
        exit;
    }
}
