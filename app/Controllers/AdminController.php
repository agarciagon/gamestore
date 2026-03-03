<?php
// app/Controllers/AdminController.php

require_once BASE_PATH . '/app/Models/Usuario.php';
require_once BASE_PATH . '/app/Models/Videojuego.php';
require_once BASE_PATH . '/app/Models/Carrito.php';
require_once BASE_PATH . '/app/Models/Pedido.php';
require_once BASE_PATH . '/app/Helpers/Auth.php';
require_once BASE_PATH . '/app/Helpers/Csrf.php';

class AdminController
{
    private Usuario    $usuarios;
    private Videojuego $juegos;
    private Carrito    $carrito;
    private Pedido     $pedidos;

    public function __construct(private PDO $pdo)
    {
        Auth::requireAdmin();
        $this->usuarios = new Usuario($pdo);
        $this->juegos   = new Videojuego($pdo);
        $this->carrito  = new Carrito($pdo);
        $this->pedidos  = new Pedido($pdo);
    }

    public function dashboard(): array
    {
        return [
            'adminName'      => $_SESSION['user_name'] ?? 'Admin',
            'activePage'     => 'dashboard',
            'totalClientes'  => $this->usuarios->countClientes(),
            'statsJuegos'    => $this->juegos->getStats(),
            'recentClientes' => $this->usuarios->getRecentClientes(6),
            'stockBajo'      => $this->juegos->getStockBajo(6),
        ];
    }

    public function clientes(): array
    {
        $search   = trim($_GET['q'] ?? '');
        $clientes = $this->usuarios->getAllClientes($search);

        return [
            'adminName'  => $_SESSION['user_name'] ?? 'Admin',
            'activePage' => 'clientes',
            'clientes'   => $clientes,
            'total'      => count($clientes),
            'search'     => $search,
        ];
    }

    public function deleteCliente(): void
    {
        Csrf::verify();
        $id = (int) ($_POST['id'] ?? 0);
        if ($id > 0) {
            if ($id === (int) ($_SESSION['user_id'] ?? 0)) {
                $_SESSION['error'] = 'You cannot delete your own account from here.';
                header('Location: /gamestore/public/admin/clientes');
                exit;
            }
            $this->usuarios->delete($id);
            $_SESSION['success'] = 'Customer deleted successfully.';
        }
        header('Location: /gamestore/public/admin/clientes');
        exit;
    }

    public function estadisticas(): array
    {
        $statsJuegos   = $this->juegos->getStats();
        $porGenero     = $this->juegos->getPorGenero();
        $porPlataforma = $this->juegos->getPorPlataforma();
        $masEnCarrito  = $this->carrito->getMasAnadidos(6);
        $registrosMes  = $this->usuarios->getRegistrosPorMes();

        $maxCarrito = !empty($masEnCarrito) ? max(array_column($masEnCarrito, 'total')) : 1;
        $maxGenero  = !empty($porGenero)    ? max(array_column($porGenero, 'qty'))      : 1;
        $maxMes     = !empty($registrosMes) ? max(array_column($registrosMes, 'total')) : 1;

        return [
            'adminName'          => $_SESSION['user_name'] ?? 'Admin',
            'activePage'         => 'estadisticas',
            'totalClientes'      => $this->usuarios->countClientes(),
            'statsJuegos'        => $statsJuegos,
            'itemsEnCarritos'    => $this->pdo->query('SELECT COALESCE(SUM(cantidad),0) FROM carrito')->fetchColumn(),
            'usuariosConCarrito' => $this->pdo->query('SELECT COUNT(DISTINCT id_usuario) FROM carrito')->fetchColumn(),
            'porGenero'          => $porGenero,
            'porPlataforma'      => $porPlataforma,
            'masEnCarrito'       => $masEnCarrito,
            'registrosMes'       => $registrosMes,
            'maxCarrito'         => $maxCarrito,
            'maxGenero'          => $maxGenero,
            'maxMes'             => $maxMes,
            'colors'             => ['#748ffc', '#7c3aed', '#0ea5e9', '#10b981', '#f59e0b', '#ef4444', '#ec4899'],
        ];
    }

    public function pedidos(): array
    {
        return [
            'adminName'  => $_SESSION['user_name'] ?? 'Admin',
            'activePage' => 'pedidos',
            'pedidos'    => $this->pedidos->getAll(),
            'stats'      => $this->pedidos->getStats(),
        ];
    }

    public function updateEstadoPedido(): void
    {
        Csrf::verify();
        $id     = (int) ($_POST['id']     ?? 0);
        $estado = trim($_POST['estado']   ?? 'pagado');

        $allowed = ['pagado'];
        if ($id > 0 && in_array($estado, $allowed)) {
            $this->pedidos->updateEstado($id, $estado);
            $_SESSION['success'] = 'Order status updated.';
        }
        header('Location: /gamestore/public/admin/pedidos');
        exit;
    }
}
