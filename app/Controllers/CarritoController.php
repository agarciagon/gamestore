<?php
// app/Controllers/CarritoController.php

require_once BASE_PATH . '/app/Models/Carrito.php';
require_once BASE_PATH . '/app/Models/Videojuego.php';

class CarritoController
{
    private Carrito    $carrito;
    private Videojuego $juegos;

    public function __construct(private PDO $pdo)
    {
        $this->carrito = new Carrito($pdo);
        $this->juegos  = new Videojuego($pdo);
    }

    public function add(): void
    {
        $jid = (int) ($_POST['id_juego']  ?? 0);
        $qty = max(1, (int) ($_POST['cantidad'] ?? 1));

        if ($jid <= 0) {
            $_SESSION['error'] = 'Invalid game.';
            header('Location: /gamestore/public/');
            exit;
        }

        $juego = $this->juegos->findById($jid);
        if (!$juego) {
            $_SESSION['error'] = 'Game not found.';
            header('Location: /gamestore/public/');
            exit;
        }

        $redirectBack = '/gamestore/public/detalle?id=' . $jid;
        $uid          = $_SESSION['user_id'] ?? null;

        if ($uid) {
            if ($juego['stock'] <= 0) {
                $_SESSION['error'] = "No stock available for \"{$juego['titulo']}\".";
                header('Location: ' . $redirectBack);
                exit;
            }
            $qty = min($qty, $juego['stock']);
            $this->carrito->addItem($uid, $jid, $qty);
            $this->juegos->decrementStock($jid, $qty);
            $_SESSION['success'] = "{$qty} x \"{$juego['titulo']}\" added to your cart.";
        } else {
            // Invitado: stock BD es la fuente de verdad
            if ($juego['stock'] <= 0) {
                $_SESSION['error'] = "No stock available for \"{$juego['titulo']}\".";
                header('Location: ' . $redirectBack);
                exit;
            }
            $qty                             = min($qty, $juego['stock']);
            $reservada                       = (int) ($_SESSION['carrito_guest'][$jid] ?? 0);
            $_SESSION['carrito_guest'][$jid] = $reservada + $qty;
            $this->juegos->decrementStock($jid, $qty);
            $_SESSION['success'] = "{$qty} x \"{$juego['titulo']}\" added. Sign in to save it permanently.";
        }

        header('Location: ' . $redirectBack);
        exit;
    }

    public function remove(): void
    {
        $jid = (int) ($_POST['id_videojuego'] ?? 0);
        $uid = $_SESSION['user_id'] ?? null;

        if ($uid) {
            $item = $this->carrito->getItem($uid, $jid);
            if ($item) {
                $this->carrito->removeItem($uid, $jid);
                $this->juegos->incrementStock($jid, $item['cantidad']);
            }
        } else {
            $qty = (int) ($_SESSION['carrito_guest'][$jid] ?? 0);
            if ($qty > 0) {
                $this->juegos->incrementStock($jid, $qty);
            }
            unset($_SESSION['carrito_guest'][$jid]);
        }

        $_SESSION['success'] = 'Product removed from cart.';
        header('Location: /gamestore/public/carrito');
        exit;
    }

    public function update(): void
    {
        $uid    = $_SESSION['user_id'] ?? null;
        $jid    = (int) ($_POST['id_videojuego'] ?? 0);
        $action = $_POST['action'] ?? '';

        if (!in_array($action, ['increase', 'decrease'], true)) {
            echo json_encode(['ok' => false, 'error' => 'Invalid action.']);
            exit;
        }

        // ── Invitado ─────────────────────────────────────────────────────────
        if (!$uid) {
            $qty = (int) ($_SESSION['carrito_guest'][$jid] ?? 0);

            if ($action === 'increase') {
                $juego = $this->juegos->findById($jid);
                if (!$juego || $juego['stock'] <= 0) {
                    echo json_encode(['ok' => false, 'error' => 'No more stock available.']);
                    exit;
                }
                $_SESSION['carrito_guest'][$jid] = $qty + 1;
                $this->juegos->decrementStock($jid, 1);
            } else {
                if ($qty <= 1) {
                    unset($_SESSION['carrito_guest'][$jid]);
                    $this->juegos->incrementStock($jid, 1);
                } else {
                    $_SESSION['carrito_guest'][$jid] = $qty - 1;
                    $this->juegos->incrementStock($jid, 1);
                }
            }

            echo json_encode(['ok' => true]);
            exit;
        }

        // ── Usuario logueado ─────────────────────────────────────────────────
        $item = $this->carrito->getItem($uid, $jid);
        if (!$item) {
            echo json_encode(['ok' => false, 'error' => 'Item not found.']);
            exit;
        }

        try {
            if ($action === 'increase') {
                $juego = $this->juegos->findById($jid);
                if (!$juego || $juego['stock'] <= 0) {
                    echo json_encode(['ok' => false, 'error' => 'No more stock available.']);
                    exit;
                }
                $this->carrito->updateCantidad($uid, $jid, $item['cantidad'] + 1);
                $this->juegos->decrementStock($jid, 1);
            } else {
                $nueva = $item['cantidad'] - 1;
                if ($nueva <= 0) {
                    $this->carrito->removeItem($uid, $jid);
                    $this->juegos->incrementStock($jid, $item['cantidad']);
                } else {
                    $this->carrito->updateCantidad($uid, $jid, $nueva);
                    $this->juegos->incrementStock($jid, 1);
                }
            }
            echo json_encode(['ok' => true]);
        } catch (\Throwable $e) {
            error_log('[CarritoController::update] ' . $e->getMessage());
            echo json_encode(['ok' => false, 'error' => 'Error updating cart.']);
        }
        exit;
    }

    public function shopping(): array
    {
        $uid = $_SESSION['user_id'] ?? null;

        if ($uid) {
            $items = $this->carrito->getByUsuario($uid);
            $total = $this->carrito->getTotal($uid);
        } else {
            $items = [];
            $total = 0.0;
            foreach ($_SESSION['carrito_guest'] ?? [] as $jid => $qty) {
                $juego = $this->juegos->findById((int) $jid);
                if (!$juego) continue;
                $juego['cantidad'] = $qty;
                $items[]           = $juego;
                $total            += $juego['precio'] * $qty;
            }
        }

        $toastSuccess = $_SESSION['success'] ?? null;
        $toastError   = $_SESSION['error']   ?? null;
        unset($_SESSION['success'], $_SESSION['error']);

        return [
            'items'        => $items,
            'total'        => $total,
            'cartCount'    => $uid ? $this->carrito->countItems($uid) : array_sum($_SESSION['carrito_guest'] ?? []),
            'toastSuccess' => $toastSuccess,
            'toastError'   => $toastError,
            'rolActual'    => $_SESSION['rol'] ?? 'invitado',
        ];
    }
}
