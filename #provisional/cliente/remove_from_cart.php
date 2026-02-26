<?php
session_start();
require_once("../config/conection.php");

if (!isset($_POST['id_videojuego'])) {
    header('Location: shopping.php');
    exit;
}

$id_juego = (int)$_POST['id_videojuego'];

// ─── USUARIO LOGUEADO ────────────────────────────────────────────────────────
if (isset($_SESSION['user_id'])) {
    $id_usuario = $_SESSION['user_id'];

    // Obtener la cantidad antes de borrar para restaurar el stock
    $stmt = $pdo->prepare("SELECT cantidad FROM carrito WHERE id_usuario = :uid AND id_videojuego = :jid");
    $stmt->execute([':uid' => $id_usuario, ':jid' => $id_juego]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($item) {
        // Borrar del carrito
        $pdo->prepare("DELETE FROM carrito WHERE id_usuario = :uid AND id_videojuego = :jid")
            ->execute([':uid' => $id_usuario, ':jid' => $id_juego]);

        // Restaurar el stock exacto que tenía reservado
        $pdo->prepare("UPDATE videojuego SET stock = stock + :cantidad WHERE id = :id")
            ->execute([':cantidad' => $item['cantidad'], ':id' => $id_juego]);
    }

    $_SESSION['success'] = "Product removed from cart.";
    header('Location: shopping.php');
    exit;
}

// ─── INVITADO ────────────────────────────────────────────────────────────────
// El stock en BD nunca se tocó para invitados, solo borramos de sesión.
if (isset($_SESSION['carrito_guest'][$id_juego])) {
    unset($_SESSION['carrito_guest'][$id_juego]);
    $_SESSION['success'] = "Product removed from cart.";
}

header('Location: shopping.php');
exit;