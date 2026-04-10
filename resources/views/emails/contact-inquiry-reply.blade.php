<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Re: {{ $inquiry->subject ?: 'Your Inquiry' }}</title>
</head>
<body style="margin:0;padding:0;background-color:#f8fafc;font-family:Arial,Helvetica,sans-serif;color:#1f2937;-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#f8fafc;padding:24px 0;">
        <tr>
            <td align="center" style="padding:0;">
                <table role="presentation" width="640" cellspacing="0" cellpadding="0" border="0" style="max-width:640px;width:100%;background-color:#ffffff;border:1px solid #f1f5f9;border-radius:14px;overflow:hidden;">

                    <!-- Header -->
                    <tr>
                        <td style="background-color:#e04ecb;padding:24px 28px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td style="font-size:28px;font-weight:700;color:#ffffff;font-family:Arial,Helvetica,sans-serif;">HOTESCORT</td>
                                </tr>
                                <tr>
                                    <td style="font-size:14px;color:#fdf2f8;padding-top:6px;font-family:Arial,Helvetica,sans-serif;">Response to your inquiry</td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding:28px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td style="font-size:22px;font-weight:700;color:#111827;padding-bottom:10px;font-family:Arial,Helvetica,sans-serif;">
                                        Hello, {{ $inquiry->name ?? 'there' }}!
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-size:14px;color:#4b5563;padding-bottom:20px;font-family:Arial,Helvetica,sans-serif;">
                                        Thank you for contacting us. Here is our response to your inquiry:
                                    </td>
                                </tr>

                                <!-- Original inquiry summary -->
                                <tr>
                                    <td style="padding-bottom:20px;">
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#f9fafb;border:1px solid #e5e7eb;border-radius:10px;">
                                            <tr>
                                                <td style="padding:16px 20px;">
                                                    <p style="font-size:13px;color:#6b7280;margin:0 0 6px 0;font-family:Arial,Helvetica,sans-serif;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;">Your original message</p>
                                                    @if(filled($inquiry->subject))
                                                    <p style="font-size:14px;color:#374151;margin:0 0 4px 0;font-family:Arial,Helvetica,sans-serif;">
                                                        <strong>Subject:</strong> {{ $inquiry->subject }}
                                                    </p>
                                                    @endif
                                                    <p style="font-size:14px;color:#374151;margin:0;line-height:1.6;font-family:Arial,Helvetica,sans-serif;">{{ $inquiry->message ?? '-' }}</p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>

                                <!-- Admin reply -->
                                <tr>
                                    <td style="padding:0;">
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#fdf2f8;border:1px solid #fbcfe8;border-radius:10px;">
                                            <tr>
                                                <td style="padding:20px;">
                                                    <p style="font-size:13px;color:#9d174d;margin:0 0 10px 0;font-family:Arial,Helvetica,sans-serif;font-weight:600;text-transform:uppercase;letter-spacing:0.05em;">Our reply</p>
                                                    <p style="font-size:15px;color:#374151;margin:0;line-height:1.6;font-family:Arial,Helvetica,sans-serif;">{{ $inquiry->admin_reply }}</p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding:18px 28px;background-color:#f9fafb;border-top:1px solid #e5e7eb;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td style="font-size:12px;color:#6b7280;font-family:Arial,Helvetica,sans-serif;">
                                        This email was sent from the HOTESCORT support team.
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
