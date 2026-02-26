<?php
// app/Services/MailService.php
// Wrapper de PHPMailer. Carga credenciales desde $_ENV (.env).
// Uso:
//   $mail = new MailService();
//   $mail->enviar('user@ejemplo.com', 'Asunto', 'reset_password', ['link' => $url, 'nombre' => 'Ana']);
//   $mail->enviar('user@ejemplo.com', 'Asunto', 'confirmacion_compra', ['pedido' => $data]);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailService
{
    // Directorio donde viven las plantillas HTML de email
    private string $templatesDir;

    public function __construct()
    {
        $this->templatesDir = dirname(__DIR__) . '/Views/emails/';
    }

    // $template = nombre del archivo en Views/emails/ sin extension .php
    // $vars     = variables que se inyectan en la plantilla con extract()
    public function enviar(string $para, string $asunto, string $template, array $vars = []): void
    {
        $body = $this->render($template, $vars);

        $mail = $this->mailer();
        $mail->addAddress($para);
        $mail->Subject = $asunto;
        $mail->Body    = $body;
        $mail->AltBody = strip_tags($body);

        try {
            $mail->send();
        } catch (Exception $e) {
            // Loguear sin romper el flujo de la app
            error_log('[MailService] No se pudo enviar a ' . $para . ': ' . $e->getMessage());
        }
    }

    private function mailer(): PHPMailer
    {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = $_ENV['MAIL_HOST']      ?? 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = $_ENV['MAIL_USER']      ?? '';
        $mail->Password   = $_ENV['MAIL_PASS']      ?? '';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';
        $mail->isHTML(true);
        $mail->setFrom(
            $_ENV['MAIL_USER']      ?? '',
            $_ENV['MAIL_FROM_NAME'] ?? 'GameStore'
        );
        return $mail;
    }

    // Renderiza una plantilla PHP en un string HTML
    private function render(string $template, array $vars): string
    {
        $file = $this->templatesDir . $template . '.php';
        if (!file_exists($file)) {
            error_log('[MailService] Plantilla no encontrada: ' . $file);
            return '';
        }
        extract($vars, EXTR_SKIP);
        ob_start();
        include $file;
        return ob_get_clean();
    }
}
