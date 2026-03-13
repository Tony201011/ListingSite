<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
</head>
<body style="margin:0;padding:0;background-color:#f8fafc;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color:#f8fafc;padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="640" cellspacing="0" cellpadding="0" style="max-width:640px;width:100%;background:#ffffff;border:1px solid #f1f5f9;border-radius:14px;overflow:hidden;">
                    <tr>
                        <td style="background:linear-gradient(90deg,#e04ecb,#c13ab0);padding:24px 28px;">
                            <div style="font-size:28px;font-weight:700;line-height:1.2;color:#ffffff;">HOTESCORT</div>
                            <div style="font-size:14px;color:#fdf2f8;margin-top:6px;">Password reset request</div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:28px;">
                            <h1 style="margin:0 0 10px 0;font-size:24px;line-height:1.3;color:#111827;">Hi {{ $name }},</h1>
                            <p style="margin:0 0 18px 0;font-size:15px;line-height:1.7;color:#4b5563;">
                                We received a request to reset your password. Click the button below to create a new password.
                            </p>

                            <table role="presentation" cellspacing="0" cellpadding="0" style="margin:0 0 20px 0;">
                                <tr>
                                    <td style="background:#e04ecb;border-radius:999px;">
                                        <a href="{{ $resetUrl }}" style="display:inline-block;padding:12px 22px;font-size:14px;font-weight:700;color:#ffffff;text-decoration:none;">Reset Password</a>
                                    </td>
                                </tr>
                            </table>

                            <div style="background:#fdf2f8;border:1px solid #fbcfe8;border-radius:10px;padding:14px 16px;margin-bottom:18px;">
                                <div style="font-size:12px;text-transform:uppercase;letter-spacing:0.08em;color:#9d174d;margin-bottom:6px;font-weight:700;">Account Email</div>
                                <div style="font-size:14px;color:#111827;font-weight:600;">{{ $email }}</div>
                            </div>

                            <p style="margin:0 0 8px 0;font-size:13px;line-height:1.7;color:#6b7280;">
                                If you did not request this password reset, you can safely ignore this email.
                            </p>
                            <p style="margin:0;font-size:13px;line-height:1.7;color:#6b7280;word-break:break-all;">
                                If the button does not work, use this link:<br>
                                <a href="{{ $resetUrl }}" style="color:#c13ab0;text-decoration:underline;">{{ $resetUrl }}</a>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:18px 28px;background:#f9fafb;border-top:1px solid #e5e7eb;">
                            <p style="margin:0;font-size:12px;line-height:1.6;color:#6b7280;">
                                This email was sent by HOTESCORT. Please do not reply to this automated message.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
