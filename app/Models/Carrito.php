<?php
// app/Models/Carrito.php

class Carrito
{
    public function __construct(private PDO $pdo) {}

    public function getByUsuario(int $uid): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT c.id_carrito, c.id_videojuego, c.cantidad, c.fecha_agregado,
                    v.titulo, v.precio, v.stock, v.imagen_portada, v.plataforma
             FROM carrito c JOIN videojuego v ON c.id_videojuego=v.id
             WHERE c.id_usuario=? ORDER BY c.fecha_agregado DESC'
        );
        $stmt->execute([$uid]);
        return $stmt->fetchAll();
    }

    public function countItems(int $uid): int
    {
        $stmt = $this->pdo->prepare('SELECT COALESCE(SUM(cantidad),0) FROM carrito WHERE id_usuario=?');
        $stmt->execute([$uid]);
        return (int) $stmt->fetchColumn();
    }

    public function getTotal(int $uid): float
    {
        $stmt = $this->pdo->prepare(
            'SELECT COALESCE(SUM(c.cantidad*v.precio),0)
             FROM carrito c JOIN videojuego v ON c.id_videojuego=v.id
             WHERE c.id_usuario=?'
        );
        $stmt->execute([$uid]);
        return (float) $stmt->fetchColumn();
    }

    public function getItem(int $uid, int $jid): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM carrito WHERE id_usuario=? AND id_videojuego=? LIMIT 1'
        );
        $stmt->execute([$uid, $jid]);
        return $stmt->fetch() ?: null;
    }

    public function addItem(int $uid, int $jid, int $qty): void
    {
        if ($this->getItem($uid, $jid)) {
            $this->pdo->prepare(
                'UPDATE carrito SET cantidad=cantidad+? WHERE id_usuario=? AND id_videojuego=?'
            )->execute([$qty, $uid, $jid]);
        } else {
            $this->pdo->prepare(
                'INSERT INTO carrito (id_usuario,id_videojuego,cantidad) VALUES (?,?,?)'
            )->execute([$uid, $jid, $qty]);
        }
    }

    public function updateCantidad(int $uid, int $jid, int $qty): void
    {
        $this->pdo->prepare(
            'UPDATE carrito SET cantidad=? WHERE id_usuario=? AND id_videojuego=?'
        )->execute([$qty, $uid, $jid]);
    }

    public function removeItem(int $uid, int $jid): void
    {
        $this->pdo->prepare(
            'DELETE FROM carrito WHERE id_usuario=? AND id_videojuego=?'
        )->execute([$uid, $jid]);
    }

    public function vaciar(int $uid): void
    {
        $this->pdo->prepare('DELETE FROM carrito WHERE id_usuario=?')->execute([$uid]);
    }

    // Fusiona el carrito de sesion (guest) con el carrito BD del usuario tras login/register
    public function fusionarGuest(int $uid, array $guestCart, Videojuego $modelJuego): void
    {
        foreach ($guestCart as $jid => $qty) {
            $jid   = (int) $jid;
            $qty   = (int) $qty;
            $juego = $modelJuego->findById($jid);
            if (!$juego || $juego['stock'] <= 0) continue;

            $qtyClamped = min($qty, $juego['stock']);
            $existing   = $this->getItem($uid, $jid);

            if ($existing) {
                $nueva = min($existing['cantidad'] + $qtyClamped, $juego['stock']);
                $diff  = $nueva - $existing['cantidad'];
                if ($diff > 0) {
                    $this->updateCantidad($uid, $jid, $nueva);
                    $modelJuego->decrementStock($jid, $diff);
                }
            } else {
                $this->addItem($uid, $jid, $qtyClamped);
                $modelJuego->decrementStock($jid, $qtyClamped);
            }
        }
    }

    // Stats admin
    public function getMasAnadidos(int $limit = 6): array
    {
        return $this->pdo->query(
            "SELECT v.titulo, SUM(c.cantidad) AS total
             FROM carrito c JOIN videojuego v ON c.id_videojuego=v.id
             GROUP BY v.id ORDER BY total DESC LIMIT $limit"
        )->fetchAll();
    }
}
