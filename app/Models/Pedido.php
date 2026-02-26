<?php
// app/Models/Pedido.php

class Pedido
{
    public function __construct(private PDO $pdo) {}

    public function crear(int $uid, float $total, array $items, string $stripeSid = ''): int
    {
        $this->pdo->beginTransaction();
        try {
            $this->pdo->prepare(
                "INSERT INTO pedidos (id_usuario, stripe_sid, total, estado, fecha_pedido)
                 VALUES (?, ?, ?, 'pagado', NOW())"
            )->execute([$uid, $stripeSid, $total]);

            $id = (int) $this->pdo->lastInsertId();

            $stmtItem = $this->pdo->prepare(
                'INSERT INTO pedido_items (id_pedido, id_videojuego, cantidad, precio_unidad)
                 VALUES (?, ?, ?, ?)'
            );
            foreach ($items as $item) {
                $stmtItem->execute([
                    $id,
                    $item['id_videojuego'],
                    $item['cantidad'],
                    $item['precio_unidad'],
                ]);
            }

            $this->pdo->commit();
            return $id;
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            error_log('[Pedido::crear] ' . $e->getMessage());
            throw $e;
        }
    }

    public function findByStripeSid(string $sid): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM pedidos WHERE stripe_sid = ? LIMIT 1');
        $stmt->execute([$sid]);
        return $stmt->fetch() ?: null;
    }

    public function existeConStripeSid(string $sid): bool
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM pedidos WHERE stripe_sid = ?');
        $stmt->execute([$sid]);
        return (bool) $stmt->fetchColumn();
    }

    public function findWithItems(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM pedidos WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        $pedido = $stmt->fetch();
        if (!$pedido) return null;

        $stmt2 = $this->pdo->prepare(
            'SELECT pi.cantidad, pi.precio_unidad, v.titulo, v.imagen_portada
             FROM pedido_items pi
             JOIN videojuego v ON pi.id_videojuego = v.id
             WHERE pi.id_pedido = ?'
        );
        $stmt2->execute([$id]);
        $pedido['items'] = $stmt2->fetchAll();
        return $pedido;
    }

    public function getByUsuario(int $uid): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT p.id, p.total, p.estado, p.fecha_pedido,
                    COUNT(pi.id) AS num_juegos
             FROM pedidos p
             LEFT JOIN pedido_items pi ON pi.id_pedido = p.id
             WHERE p.id_usuario = ?
             GROUP BY p.id
             ORDER BY p.fecha_pedido DESC'
        );
        $stmt->execute([$uid]);
        $pedidos = $stmt->fetchAll();

        foreach ($pedidos as &$p) {
            $s = $this->pdo->prepare(
                'SELECT pi.cantidad, pi.precio_unidad, v.titulo, v.imagen_portada
                 FROM pedido_items pi
                 JOIN videojuego v ON pi.id_videojuego = v.id
                 WHERE pi.id_pedido = ?'
            );
            $s->execute([$p['id']]);
            $p['items'] = $s->fetchAll();
        }
        unset($p);

        return $pedidos;
    }

    public function getAll(): array
    {
        return $this->pdo->query(
            'SELECT p.id, p.total, p.estado, p.fecha_pedido, p.stripe_sid,
                    u.nombre AS cliente_nombre, u.email AS cliente_email,
                    COUNT(pi.id) AS num_items
             FROM pedidos p
             LEFT JOIN usuarios u ON u.id_usuario = p.id_usuario
             LEFT JOIN pedido_items pi ON pi.id_pedido = p.id
             GROUP BY p.id
             ORDER BY p.fecha_pedido DESC'
        )->fetchAll();
    }

    public function getStats(): array
    {
        return $this->pdo->query(
            "SELECT
                COUNT(*)                                                       AS total_pedidos,
                COALESCE(SUM(total), 0)                                        AS ingresos_totales,
                COALESCE(SUM(CASE WHEN estado='pagado' THEN total END), 0)     AS ingresos_pagados,
                COUNT(CASE WHEN estado='pagado'      THEN 1 END)               AS pagados,
                COUNT(CASE WHEN estado='pendiente'   THEN 1 END)               AS pendientes,
                COUNT(CASE WHEN estado='reembolsado' THEN 1 END)               AS reembolsados
             FROM pedidos"
        )->fetch();
    }

    public function updateEstado(int $id, string $estado): void
    {
        $this->pdo->prepare(
            "UPDATE pedidos SET estado = ? WHERE id = ?"
        )->execute([$estado, $id]);
    }
}
