<?php
// app/Controllers/FacturaController.php
// GET /factura?id=X  → genera y descarga el PDF de la factura

require_once BASE_PATH . '/app/Models/Pedido.php';
require_once BASE_PATH . '/app/Models/Usuario.php';
require_once BASE_PATH . '/app/Helpers/Auth.php';
require_once BASE_PATH . '/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

class FacturaController
{
    private Pedido  $pedidos;
    private Usuario $usuarios;

    public function __construct(private PDO $pdo)
    {
        $this->pedidos  = new Pedido($pdo);
        $this->usuarios = new Usuario($pdo);
    }

    public function descargar(): void
    {
        Auth::requireLogin();

        $uid      = (int) $_SESSION['user_id'];
        $idPedido = (int) ($_GET['id'] ?? 0);
        $pedido   = $this->pedidos->findWithItems($idPedido);

        // Verificar que el pedido pertenece al usuario
        if (!$pedido || (int) $pedido['id_usuario'] !== $uid) {
            http_response_code(403);
            echo 'Access denied.';
            exit;
        }

        $usuario  = $this->usuarios->findById($uid);
        $subtotal = $pedido['total'] / 1.21;
        $iva      = $pedido['total'] - $subtotal;

        $html = $this->buildHtml($pedido, $usuario, $subtotal, $iva);

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', false);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'invoice-' . $idPedido . '-' . date('Ymd') . '.pdf';
        $dompdf->stream($filename, ['Attachment' => true]);
        exit;
    }

    private function buildHtml(array $pedido, array $usuario, float $subtotal, float $iva): string
    {
        $items = '';
        foreach ($pedido['items'] as $item) {
            $linea  = $item['precio_unidad'] * $item['cantidad'];
            $items .= "
            <tr>
                <td style='padding:10px 12px;border-bottom:1px solid #e5e7eb;color:#111827;'>" . htmlspecialchars($item['titulo']) . "</td>
                <td style='padding:10px 12px;border-bottom:1px solid #e5e7eb;text-align:center;color:#374151;'>" . (int)$item['cantidad'] . "</td>
                <td style='padding:10px 12px;border-bottom:1px solid #e5e7eb;text-align:right;color:#374151;'>" . number_format($item['precio_unidad'], 2) . "€</td>
                <td style='padding:10px 12px;border-bottom:1px solid #e5e7eb;text-align:right;font-weight:600;color:#111827;'>" . number_format($linea, 2) . "€</td>
            </tr>";
        }

        return "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size:13px; color:#374151; background:#f9fafb; }
        .page { background:#fff; padding:48px; max-width:700px; margin:0 auto; }
        .header { display:table; width:100%; margin-bottom:40px; }
        .header-left { display:table-cell; vertical-align:top; }
        .header-right { display:table-cell; vertical-align:top; text-align:right; }
        .logo { font-size:22px; font-weight:700; color:#0f172a; }
        .logo span { color:#0284c7; }
        .invoice-title { font-size:28px; font-weight:700; color:#0f172a; margin-bottom:4px; }
        .badge { display:inline-block; padding:4px 12px; background:#dcfce7; color:#16a34a; border-radius:20px; font-size:11px; font-weight:600; }
        .meta-table { width:100%; margin-bottom:32px; }
        .meta-table td { padding:4px 0; font-size:12px; }
        .meta-label { color:#6b7280; width:120px; }
        .meta-value { color:#111827; font-weight:500; }
        .section-title { font-size:11px; font-weight:600; text-transform:uppercase; color:#6b7280; letter-spacing:1px; margin-bottom:8px; }
        .items-table { width:100%; border-collapse:collapse; margin-bottom:24px; }
        .items-table th { background:#f1f5f9; padding:10px 12px; text-align:left; font-size:11px; color:#64748b; font-weight:600; text-transform:uppercase; }
        .items-table th:not(:first-child) { text-align:right; }
        .items-table th:nth-child(2) { text-align:center; }
        .totals-table { width:260px; margin-left:auto; border-collapse:collapse; }
        .totals-table td { padding:6px 12px; font-size:13px; }
        .totals-table .label { color:#6b7280; }
        .totals-table .value { text-align:right; color:#374151; }
        .totals-table .total-row td { font-weight:700; font-size:15px; color:#0f172a; border-top:2px solid #e5e7eb; padding-top:10px; }
        .totals-table .total-row .value { color:#0284c7; }
        .footer { margin-top:48px; padding-top:16px; border-top:1px solid #e5e7eb; text-align:center; font-size:11px; color:#9ca3af; }
        .divider { border:none; border-top:1px solid #e5e7eb; margin:24px 0; }
    </style>
</head>
<body>
<div class='page'>

    <div class='header'>
        <div class='header-left'>
            <div class='logo'>🎮 Game<span>Store</span></div>
            <div style='margin-top:4px;font-size:12px;color:#6b7280;'>support@videogame.com</div>
        </div>
        <div class='header-right'>
            <div class='invoice-title'>INVOICE</div>
            <div class='badge'>PAID</div>
        </div>
    </div>

    <table class='meta-table'>
        <tr>
            <td class='meta-label'>Invoice No.</td>
            <td class='meta-value'>#" . str_pad($pedido['id'], 6, '0', STR_PAD_LEFT) . "</td>
            <td style='width:40px;'></td>
            <td class='meta-label'>Bill to</td>
            <td class='meta-value'>" . htmlspecialchars($usuario['nombre']) . "</td>
        </tr>
        <tr>
            <td class='meta-label'>Date</td>
            <td class='meta-value'>" . date('d M Y', strtotime($pedido['fecha_pedido'])) . "</td>
            <td></td>
            <td class='meta-label'>Email</td>
            <td class='meta-value'>" . htmlspecialchars($usuario['email']) . "</td>
        </tr>
    </table>

    <hr class='divider'>

    <div class='section-title'>Order Items</div>
    <table class='items-table'>
        <thead>
            <tr>
                <th style='text-align:left;'>Game</th>
                <th style='text-align:center;'>Qty</th>
                <th style='text-align:right;'>Unit Price</th>
                <th style='text-align:right;'>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            $items
        </tbody>
    </table>

    <table class='totals-table'>
        <tr>
            <td class='label'>Subtotal (excl. VAT)</td>
            <td class='value'>" . number_format($subtotal, 2) . "€</td>
        </tr>
        <tr>
            <td class='label'>VAT (21%)</td>
            <td class='value'>" . number_format($iva, 2) . "€</td>
        </tr>
        <tr class='total-row'>
            <td class='label'>Total</td>
            <td class='value'>" . number_format($pedido['total'], 2) . "€</td>
        </tr>
    </table>

    <div class='footer'>
        Thank you for your purchase · &copy; " . date('Y') . " GameStore · All rights reserved
    </div>
</div>
</body>
</html>";
    }
}
