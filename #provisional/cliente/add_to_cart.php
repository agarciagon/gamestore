<?php
session_start();
require_once("../config/conection.php");

$id_juego = isset($_POST['id_juego']) ? (int)$_POST['id_juego'] : 0;
$cantidad  = isset($_POST['cantidad']) ? max(1, (int)$_POST['cantidad']) : 1;

if ($id_juego <= 0) {
    $_SESSION['error'] = "Invalid game.";
    header('Location: ../index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT stock, titulo FROM videojuego WHERE id = :id");
$stmt->execute([':id' => $id_juego]);
$juego = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$juego) {
    $_SESSION['error'] = "Game not found.";
    header('Location: ../index.php');
    exit;
}

// ─── LOGGED USER ─────────────────────────────────────────────────────────────
if (isset($_SESSION['user_id'])) {
    $id_usuario = $_SESSION['user_id'];
    $stockReal  = (int)$juego['stock'];

    if ($stockReal <= 0) {
        $_SESSION['error'] = "No stock available for \"{$juego['titulo']}\".";
        header('Location: ../cliente/detalle_juego.php?id=' . $id_juego);
        exit;
    }

    $cantidad = min($cantidad, $stockReal);

    $stmt2 = $pdo->prepare("SELECT cantidad FROM carrito WHERE id_usuario = :uid AND id_videojuego = :jid");
    $stmt2->execute([':uid' => $id_usuario, ':jid' => $id_juego]);
    $enCarrito = $stmt2->fetch(PDO::FETCH_ASSOC);

    if ($enCarrito) {
        $nuevaCantidad = $enCarrito['cantidad'] + $cantidad;
        $pdo->prepare("UPDATE carrito SET cantidad = :cantidad WHERE id_usuario = :uid AND id_videojuego = :jid")
            ->execute([':cantidad' => $nuevaCantidad, ':uid' => $id_usuario, ':jid' => $id_juego]);
    } else {
        $pdo->prepare("INSERT INTO carrito (id_usuario, id_videojuego, cantidad) VALUES (:uid, :jid, :cantidad)")
            ->execute([':uid' => $id_usuario, ':jid' => $id_juego, ':cantidad' => $cantidad]);
    }

    $pdo->prepare("UPDATE videojuego SET stock = stock - :cantidad WHERE id = :id")
        ->execute([':cantidad' => $cantidad, ':id' => $id_juego]);

    $_SESSION['success'] = "{$cantidad} x \"{$juego['titulo']}\" added to your cart.";
    header('Location: ../cliente/detalle_juego.php?id=' . $id_juego);
    exit;
}

// ─── GUEST ────────────────────────────────────────────────────────────────────
$reservadaGuest  = (int)($_SESSION['carrito_guest'][$id_juego] ?? 0);
$stockDisponible = max(0, (int)$juego['stock'] - $reservadaGuest);

if ($stockDisponible <= 0) {
    $_SESSION['error'] = "No stock available for \"{$juego['titulo']}\".";
    header('Location: ../cliente/detalle_juego.php?id=' . $id_juego);
    exit;
}

$cantidad = min($cantidad, $stockDisponible);

if (!isset($_SESSION['carrito_guest'])) {
    $_SESSION['carrito_guest'] = [];
}
$_SESSION['carrito_guest'][$id_juego] = $reservadaGuest + $cantidad;

$_SESSION['success'] = "{$cantidad} x \"{$juego['titulo']}\" added to your cart. Sign in to save it permanently.";
header('Location: ../cliente/detalle_juego.php?id=' . $id_juego);
exit;