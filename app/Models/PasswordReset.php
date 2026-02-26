<?php
// app/Models/PasswordReset.php

class PasswordReset
{
    public function __construct(private PDO $pdo) {}

    public function crear(string $email): string
    {
        $token   = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Invalida tokens anteriores del mismo email
        $this->pdo->prepare('UPDATE password_resets SET used=1 WHERE email=?')->execute([$email]);

        $this->pdo->prepare(
            'INSERT INTO password_resets (email,token,expires_at,used) VALUES (?,?,?,0)'
        )->execute([$email, $token, $expires]);

        return $token;
    }

    // Devuelve el email si el token es valido, null si no
    public function validar(string $token): ?string
    {
        $stmt = $this->pdo->prepare(
            'SELECT email FROM password_resets WHERE token=? AND expires_at>NOW() AND used=0 LIMIT 1'
        );
        $stmt->execute([$token]);
        $row = $stmt->fetch();
        return $row ? $row['email'] : null;
    }

    public function marcarUsado(string $token): void
    {
        $this->pdo->prepare('UPDATE password_resets SET used=1 WHERE token=?')->execute([$token]);
    }
}
