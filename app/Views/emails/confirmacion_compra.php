<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Order confirmed – GameStore</title>
</head>

<body style="margin:0;padding:0;background:#f3f4f6;font-family:'Segoe UI',sans-serif;">
    <?php
    // URL para “View My Orders” según si el usuario está logueado
    $appUrl    = rtrim($_ENV['APP_URL'] ?? 'http://localhost', '/');
    $ordersUrl = $appUrl . '/perfil#orders';
    ?>
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#f3f4f6;padding:40px 0;">
        <tr>
            <td align="center">
                <table width="520" cellpadding="0" cellspacing="0"
                    style="background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.08);">

                    <!-- Header -->
                    <tr>
                        <td style="background:#1e293b;padding:28px 40px;text-align:center;">
                            <span style="font-size:22px;font-weight:700;color:#ffffff;letter-spacing:1px;">
                                🎮 GameStore
                            </span>
                        </td>
                    </tr>

                    <!-- Confirmation banner -->
                    <tr>
                        <td style="background:#dcfce7;padding:20px 40px;border-bottom:1px solid #bbf7d0;">
                            <p style="margin:0;font-size:16px;font-weight:600;color:#166534;">
                                ✅ Your order has been confirmed!
                            </p>
                        </td>
                    </tr>

                    <!-- Greeting -->
                    <tr>
                        <td style="padding:32px 40px 16px;">
                            <p style="margin:0;color:#374151;font-size:15px;line-height:1.6;">
                                Hi <strong><?= htmlspecialchars($nombre ?? 'there') ?></strong>,
                                thank you for your purchase. Here's your order summary:
                            </p>
                        </td>
                    </tr>

                    <!-- Order meta -->
                    <tr>
                        <td style="padding:0 40px 16px;">
                            <table width="100%" cellpadding="8" cellspacing="0"
                                style="background:#f9fafb;border-radius:8px;border:1px solid #e5e7eb;font-size:13px;color:#6b7280;">
                                <tr>
                                    <td><strong style="color:#111827;">Order #</strong></td>
                                    <td align="right"><?= (int)($pedido_id ?? 0) ?></td>
                                </tr>
                                <tr style="border-top:1px solid #e5e7eb;">
                                    <td><strong style="color:#111827;">Date</strong></td>
                                    <td align="right"><?= date('d M Y, H:i') ?></td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Items -->
                    <tr>
                        <td style="padding:0 40px 8px;">
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr style="background:#f1f5f9;">
                                    <th style="padding:10px 12px;text-align:left;font-size:12px;color:#64748b;font-weight:600;text-transform:uppercase;">Game</th>
                                    <th style="padding:10px 12px;text-align:center;font-size:12px;color:#64748b;font-weight:600;text-transform:uppercase;">Qty</th>
                                    <th style="padding:10px 12px;text-align:right;font-size:12px;color:#64748b;font-weight:600;text-transform:uppercase;">Price</th>
                                    <th style="padding:10px 12px;text-align:right;font-size:12px;color:#64748b;font-weight:600;text-transform:uppercase;">Subtotal</th>
                                </tr>
                                <?php foreach (($items ?? []) as $item): ?>
                                    <tr style="border-bottom:1px solid #e5e7eb;">
                                        <td style="padding:12px;font-size:14px;color:#111827;"><?= htmlspecialchars($item['titulo']) ?></td>
                                        <td style="padding:12px;text-align:center;font-size:14px;color:#374151;"><?= (int)$item['cantidad'] ?></td>
                                        <td style="padding:12px;text-align:right;font-size:14px;color:#374151;"><?= number_format($item['precio_unidad'], 2) ?>€</td>
                                        <td style="padding:12px;text-align:right;font-size:14px;font-weight:600;color:#111827;">
                                            <?= number_format($item['precio_unidad'] * $item['cantidad'], 2) ?>€
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <!-- Total row -->
                                <tr>
                                    <td colspan="3" style="padding:14px 12px;text-align:right;font-size:15px;font-weight:700;color:#111827;">Total</td>
                                    <td style="padding:14px 12px;text-align:right;font-size:16px;font-weight:700;color:#0284c7;">
                                        <?= number_format($total ?? 0, 2) ?>€
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- CTA -->
                    <tr>
                        <td style="padding:24px 40px 32px;text-align:center;">
                            <a href="<?= $ordersUrl ?>"
                                style="display:inline-block;padding:12px 28px;background:#0284c7;color:#fff;
                               font-size:14px;font-weight:600;text-decoration:none;border-radius:8px;">
                                View My Orders
                            </a>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background:#f9fafb;padding:20px 40px;border-top:1px solid #e5e7eb;">
                            <p style="margin:0;font-size:12px;color:#9ca3af;text-align:center;">
                                &copy; <?= date('Y') ?> GameStore. All rights reserved.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>

</html>