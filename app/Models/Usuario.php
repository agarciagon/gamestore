<?php
// app/Models/Usuario.php

class Usuario
{
    public function __construct(private PDO $pdo) {}

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id_usuario, nombre, email, contrasena, rol FROM usuarios WHERE email = ? LIMIT 1'
        );
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id_usuario, nombre, email, rol, fecha_creacion FROM usuarios WHERE id_usuario = ? LIMIT 1'
        );
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public function emailExists(string $email): bool
    {
        $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM usuarios WHERE email = ?');
        $stmt->execute([$email]);
        return (bool) $stmt->fetchColumn();
    }

    public function create(string $nombre, string $email, string $hash, string $rol = 'cliente'): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO usuarios (nombre, email, contrasena, rol, fecha_creacion) VALUES (?,?,?,?,NOW())'
        );
        $stmt->execute([$nombre, $email, $hash, $rol]);
        return (int) $this->pdo->lastInsertId();
    }

    public function updateNombre(int $id, string $nombre): void
    {
        $this->pdo->prepare('UPDATE usuarios SET nombre=? WHERE id_usuario=?')->execute([$nombre, $id]);
    }

    public function updateEmail(int $id, string $email): void
    {
        $this->pdo->prepare('UPDATE usuarios SET email=? WHERE id_usuario=?')->execute([$email, $id]);
    }

    public function updatePassword(int $id, string $hash): void
    {
        $this->pdo->prepare('UPDATE usuarios SET contrasena=? WHERE id_usuario=?')->execute([$hash, $id]);
    }

    public function updatePasswordByEmail(string $email, string $hash): void
    {
        $this->pdo->prepare('UPDATE usuarios SET contrasena=? WHERE email=?')->execute([$hash, $email]);
    }

    public function delete(int $id): void
    {
        $this->pdo->prepare('DELETE FROM usuarios WHERE id_usuario=?')->execute([$id]);
    }

    // ── ADMIN ────────────────────────────────────────────────────

    public function getAllClientes(string $search = ''): array
    {
        if ($search !== '') {
            $stmt = $this->pdo->prepare(
                "SELECT id_usuario, nombre, email, fecha_creacion
                 FROM usuarios WHERE rol='cliente' AND (nombre LIKE ? OR email LIKE ?)
                 ORDER BY fecha_creacion DESC"
            );
            $stmt->execute(["%$search%", "%$search%"]);
        } else {
            $stmt = $this->pdo->query(
                "SELECT id_usuario, nombre, email, fecha_creacion
                 FROM usuarios WHERE rol='cliente' ORDER BY fecha_creacion DESC"
            );
        }
        return $stmt->fetchAll();
    }

    public function countClientes(): int
    {
        return (int) $this->pdo->query("SELECT COUNT(*) FROM usuarios WHERE rol='cliente'")->fetchColumn();
    }

    public function getRecentClientes(int $limit = 6): array
    {
        return $this->pdo->query(
            "SELECT nombre, email, fecha_creacion FROM usuarios WHERE rol='cliente'
             ORDER BY fecha_creacion DESC LIMIT $limit"
        )->fetchAll();
    }

    public function getRegistrosPorMes(): array
    {
        return $this->pdo->query(
            "SELECT DATE_FORMAT(fecha_creacion,'%b %Y') AS mes,
                    DATE_FORMAT(fecha_creacion,'%Y-%m') AS mk,
                    COUNT(*) AS total
             FROM usuarios WHERE rol='cliente'
               AND fecha_creacion >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
             GROUP BY mk ORDER BY mk ASC"
        )->fetchAll();
    }
}
