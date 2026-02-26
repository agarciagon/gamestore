<?php
// app/Controllers/HomeController.php

require_once BASE_PATH . '/app/Models/Videojuego.php';
require_once BASE_PATH . '/app/Models/Carrito.php';
require_once BASE_PATH . '/app/Helpers/Auth.php';

class HomeController
{
    private Videojuego $videojuego;
    private Carrito    $carrito;

    public function __construct(private PDO $pdo)
    {
        $this->videojuego = new Videojuego($pdo);
        $this->carrito    = new Carrito($pdo);
    }

    public function index(): array
    {
        if (Auth::isLogged() && Auth::rol() === 'admin') {
            header('Location: /gamestore/public/admin');
            exit;
        }

        $uid       = $_SESSION['user_id'] ?? null;
        $cartCount = $uid
            ? $this->carrito->countItems($uid)
            : array_sum($_SESSION['carrito_guest'] ?? []);

        return [
            'destacados' => $this->videojuego->getDestacados(8),
            'recientes'  => $this->videojuego->getRecientes(4),
            'topVentas'  => $this->videojuego->getTopVentas(5),
            'ofertas'    => $this->videojuego->getOfertas(4),
            'generos'    => $this->videojuego->getGeneros(),
            'cartCount'  => $cartCount,
            'rolActual'  => $_SESSION['rol'] ?? 'invitado',
        ];
    }
}