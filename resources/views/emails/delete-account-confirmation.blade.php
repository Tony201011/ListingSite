<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Account Deletion</title>
</head>
<body style="margin:0;padding:0;background-color:#f8fafc;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color:#f8fafc;padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="640" cellspacing="0" cellpadding="0" style="max-width:640px;width:100%;background:#ffffff;border:1px solid #f1f5f9;border-radius:14px;overflow:hidden;">
                    <tr>
                        <td style="background:linear-gradient(90deg,#ef4444,#dc2626);padding:24px 28px;">
                            <div style="font-size:28px;font-weight:700;line-height:1.2;color:#ffffff;">HOTESCORT</div>
                            <div style="font-size:14px;color:#fee2e2;margin-top:6px;">Confirm your account deletion request</div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:28px;">
                            <h1 style="margin:0 0 10px 0;font-size:24px;line-height:1.3;color:#111827;">Hi {{ $name }},</h1>
                            <p style="margin:0 0 18px 0;font-size:15px;line-height:1.7;color:#4b5563;">
                                We received a request to delete your account. To continue, click the button below.
                            </p>

                            <table role="presentation" cellspacing="0" cellpadding="0" style="margin:0 0 20px 0;">
                                <tr>
                                    <td style="background:#dc2626;border-radius:999px;">
                                        <a href="{{ $confirmationUrl }}" style="display:inline-block;padding:12px 22px;font-size:14px;font-weight:700;color:#ffffff;text-decoration:none;">Confirm Account Deletion</a>
                                    </td>
                                </tr>
                            </table>

                            <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:14px 16px;margin-bottom:18px;">
                                <div style="font-size:12px;text-transform:uppercase;letter-spacing:0.08em;color:#b91c1c;margin-bottom:6px;font-weight:700;">Important</div>
                                <div style="font-size:14px;color:#111827;">
                                    This link expires at {{ $expiresAt->format('M d, Y h:i A') }}.
                                </div>
                            </div>

                            <p style="margin:0;font-size:13px;line-height:1.7;color:#6b7280;">
                                If you did not request this, please ignore this email and your account will stay active.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
