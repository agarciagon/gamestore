<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

// FIX: las credenciales se leen desde .env, no van en código fuente.
// Asegúrate de tener en tu .env:
//   MAIL_USER=tu@gmail.com
//   MAIL_PASS=tu_app_password
//   MAIL_FROM_NAME=GameStore
//
// Y de cargar el .env al inicio (p.ej. en index.php o conection.php):
//   $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
//   $dotenv->load();

function crearMailer(): PHPMailer {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host       = $_ENV['MAIL_HOST']      ?? 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = $_ENV['MAIL_USER']      ?? '';
    $mail->Password   = $_ENV['MAIL_PASS']      ?? '';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;
    $mail->CharSet    = 'UTF-8';
    $mail->setFrom(
        $_ENV['MAIL_USER']      ?? '',
        $_ENV['MAIL_FROM_NAME'] ?? 'GameStore'
    );
    return $mail;
}