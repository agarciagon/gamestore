<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Reset your password – GameStore</title>
</head>

<body style="margin:0;padding:0;background:#f3f4f6;font-family:'Segoe UI',sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background:#f3f4f6;padding:40px 0;">
        <tr>
            <td align="center">
                <table width="480" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.08);">

                    <!-- Header -->
                    <tr>
                        <td style="background:#1e293b;padding:28px 40px;text-align:center;">
                            <span style="font-size:22px;font-weight:700;color:#ffffff;letter-spacing:1px;">
                                🎮 GameStore
                            </span>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding:40px 40px 32px;">
                            <h2 style="margin:0 0 12px;font-size:22px;color:#111827;">Password Reset</h2>
                            <p style="margin:0 0 8px;color:#6b7280;font-size:15px;line-height:1.6;">
                                Hi <?= htmlspecialchars($nombre ?? 'there') ?>,
                            </p>
                            <p style="margin:0 0 24px;color:#6b7280;font-size:15px;line-height:1.6;">
                                We received a request to reset your password. Click the button below.
                                The link expires in <strong>1 hour</strong>.
                            </p>
                            <a href="<?= htmlspecialchars($link) ?>"
                                style="display:inline-block;padding:13px 28px;background:#0284c7;color:#ffffff;
                                font-size:15px;font-weight:600;text-decoration:none;border-radius:8px;">
                                Reset Password
                            </a>
                            <p style="margin:28px 0 0;font-size:13px;color:#9ca3af;line-height:1.6;">
                                If you didn't request this, you can safely ignore this email.<br>
                                The link will expire automatically.
                            </p>
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