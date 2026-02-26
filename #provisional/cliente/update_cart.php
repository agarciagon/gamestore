<?php
session_start();
require_once("../config/conection.php");

// Verificar autenticación y datos necesarios
if (!isset($_SESSION['user_id']) || !isset($_POST['id_videojuego']) || !isset($_POST['action'])) {
    header('Location: shopping.php');
    exit;
}

$userId  = $_SESSION['user_id'];
$idJuego = (int)$_POST['id_videojuego'];
$action  = $_POST['action'];

// Solo aceptar acciones válidas
if (!in_array($action, ['increase', 'decrease'])) {
    header('Location: shopping.php');
    exit;
}

try {
    // Obtener cantidad actual en el carrito
    $stmt = $pdo->prepare("SELECT cantidad FROM carrito WHERE id_usuario = :user_id AND id_videojuego = :juego_id");
    $stmt->execute([':user_id' => $userId, ':juego_id' => $idJuego]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$item) {
        header('Location: shopping.php');
        exit;
    }

    if ($action === 'increase') {
        // Verificar que hay stock disponible antes de incrementar
        $stmtStock = $pdo->prepare("SELECT stock FROM videojuego WHERE id = :id");
        $stmtStock->execute([':id' => $idJuego]);
        $juego = $stmtStock->fetch(PDO::FETCH_ASSOC);

        if (!$juego || $juego['stock'] <= 0) {
            $_SESSION['error'] = "No more stock available for this game.";
            header('Location: shopping.php');
            exit;
        }

        // Incrementar cantidad en carrito y restar 1 del stock
        $stmt = $pdo->prepare("UPDATE carrito SET cantidad = cantidad + 1 WHERE id_usuario = :user_id AND id_videojuego = :juego_id");
        $stmt->execute([':user_id' => $userId, ':juego_id' => $idJuego]);

        $stmt = $pdo->prepare("UPDATE videojuego SET stock = stock - 1 WHERE id = :id");
        $stmt->execute([':id' => $idJuego]);

    } elseif ($action === 'decrease') {
        $nuevaCantidad = $item['cantidad'] - 1;

        if ($nuevaCantidad <= 0) {
            // Si queda en 0, eliminar del carrito y restaurar todo el stock
            $stmt = $pdo->prepare("DELETE FROM carrito WHERE id_usuario = :user_id AND id_videojuego = :juego_id");
            $stmt->execute([':user_id' => $userId, ':juego_id' => $idJuego]);

            $stmt = $pdo->prepare("UPDATE videojuego SET stock = stock + :cantidad WHERE id = :id");
            $stmt->execute([':cantidad' => $item['cantidad'], ':id' => $idJuego]);
        } else {
            // Decrementar cantidad en carrito y devolver 1 al stock
            $stmt = $pdo->prepare("UPDATE carrito SET cantidad = cantidad - 1 WHERE id_usuario = :user_id AND id_videojuego = :juego_id");
            $stmt->execute([':user_id' => $userId, ':juego_id' => $idJuego]);

            $stmt = $pdo->prepare("UPDATE videojuego SET stock = stock + 1 WHERE id = :id");
            $stmt->execute([':id' => $idJuego]);
        }
    }

    header('Location: shopping.php');
    exit;

} catch (PDOException $e) {
    $_SESSION['error'] = "Error updating cart. Please try again.";
    header('Location: shopping.php');
    exit;
}