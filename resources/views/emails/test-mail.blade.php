<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Email</title>
</head>
<body style="margin:0;padding:0;background-color:#f8fafc;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color:#f8fafc;padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="620" cellspacing="0" cellpadding="0" style="max-width:620px;width:100%;background:#ffffff;border:1px solid #f1f5f9;border-radius:14px;overflow:hidden;">
                    <tr>
                        <td style="background:linear-gradient(90deg,#e04ecb,#c13ab0);padding:22px 26px;">
                            <div style="font-size:26px;font-weight:700;color:#ffffff;">{{ $appName }}</div>
                            <div style="font-size:14px;color:#fdf2f8;margin-top:4px;">Mail configuration test</div>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:26px;">
                            <h1 style="margin:0 0 10px 0;font-size:22px;color:#111827;">Test Mail Delivered</h1>
                            <p style="margin:0 0 14px 0;font-size:15px;line-height:1.7;color:#4b5563;">
                                This is a test email sent from the admin Mail Settings page.
                            </p>
                            <div style="background:#fdf2f8;border:1px solid #fbcfe8;border-radius:10px;padding:12px 14px;">
                                <div style="font-size:13px;color:#9d174d;font-weight:700;margin-bottom:5px;">Details</div>
                                <div style="font-size:13px;color:#111827;line-height:1.6;">
                                    Recipient: {{ $recipientEmail }}<br>
                                    Sent at: {{ $sentAt }}
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
