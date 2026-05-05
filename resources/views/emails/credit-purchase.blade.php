<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Credit Purchase Confirmation</title>
</head>
<body style="margin:0;padding:0;background-color:#f8fafc;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color:#f8fafc;padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="640" cellspacing="0" cellpadding="0" style="max-width:640px;width:100%;background:#ffffff;border:1px solid #f1f5f9;border-radius:14px;overflow:hidden;">
                    <tr>
                        <td style="background:linear-gradient(90deg,#e04ecb,#c13ab0);padding:24px 28px;">
                            <div style="font-size:28px;font-weight:700;line-height:1.2;color:#ffffff;">HOTESCORT</div>
                            <div style="font-size:14px;color:#fdf2f8;margin-top:6px;">Credit Purchase Confirmation</div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:28px;">
                            <h1 style="margin:0 0 10px 0;font-size:24px;line-height:1.3;color:#111827;">Thank you, {{ $name }}!</h1>
                            <p style="margin:0 0 18px 0;font-size:15px;line-height:1.7;color:#4b5563;">
                                Your credit purchase was successful. The credits have been added to your account.
                            </p>

                            <div style="background:#fdf2f8;border:1px solid #fbcfe8;border-radius:10px;padding:14px 16px;margin-bottom:18px;">
                                <div style="font-size:12px;text-transform:uppercase;letter-spacing:0.08em;color:#9d174d;margin-bottom:10px;font-weight:700;">Purchase Details</div>
                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                    <tr>
                                        <td style="font-size:14px;color:#6b7280;padding:4px 0;">Credits Purchased</td>
                                        <td style="font-size:14px;color:#111827;font-weight:600;text-align:right;">{{ $credits }} credits</td>
                                    </tr>
                                    <tr>
                                        <td style="font-size:14px;color:#6b7280;padding:4px 0;">Amount Paid</td>
                                        <td style="font-size:14px;color:#111827;font-weight:600;text-align:right;">AUD ${{ number_format($amount, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td style="font-size:14px;color:#6b7280;padding:4px 0;">Invoice Name</td>
                                        <td style="font-size:14px;color:#111827;font-weight:600;text-align:right;">{{ $invoiceName }}</td>
                                    </tr>
                                </table>
                            </div>

                            <table role="presentation" cellspacing="0" cellpadding="0" style="margin:0 0 20px 0;">
                                <tr>
                                    <td style="background:#e04ecb;border-radius:999px;">
                                        <a href="{{ $historyUrl }}" style="display:inline-block;padding:12px 22px;font-size:14px;font-weight:700;color:#ffffff;text-decoration:none;">View Purchase History</a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0;font-size:13px;line-height:1.7;color:#6b7280;">
                                If you did not make this purchase, please contact support immediately.
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
