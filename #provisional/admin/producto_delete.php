<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header('Location: login_admin.php');
    exit;
}

require_once '../config/conection.php';

header('Location: gestion_productos.php');
exit;
