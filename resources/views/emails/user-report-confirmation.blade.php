<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Received</title>
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
                                    <td style="font-size:14px;color:#fdf2f8;padding-top:6px;font-family:Arial,Helvetica,sans-serif;">Report submission confirmation</td>
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
                                        Thank You for Your Report
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-size:14px;color:#4b5563;padding-bottom:20px;font-family:Arial,Helvetica,sans-serif;">
                                        @if(filled($report->reporter_name))Hi {{ $report->reporter_name }},<br><br>@endif
                                        We have received your report and our admin team will review it as soon as possible. No immediate action has been taken at this time.
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:0;">
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#fdf2f8;border:1px solid #fbcfe8;border-radius:10px;">
                                            <tr>
                                                <td style="padding:20px;">
                                                    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                                        <tr>
                                                            <td style="font-size:15px;color:#1f2937;padding:6px 0;font-family:Arial,Helvetica,sans-serif;">
                                                                <strong style="color:#111827;">Reported Profile:</strong>&nbsp; {{ $report->providerProfile?->name ?? '—' }}
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td style="font-size:15px;color:#1f2937;padding:6px 0;font-family:Arial,Helvetica,sans-serif;">
                                                                <strong style="color:#111827;">Reason:</strong>&nbsp; {{ ucwords(str_replace('_', ' ', $report->reason)) }}
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td style="font-size:15px;color:#1f2937;padding:6px 0;font-family:Arial,Helvetica,sans-serif;">
                                                                <strong style="color:#111827;">Status:</strong>&nbsp; Pending review
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-size:14px;color:#4b5563;padding-top:20px;font-family:Arial,Helvetica,sans-serif;">
                                        If you have any further questions, please contact our support team.
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
                                        This is an automated confirmation from HOTESCORT. Please do not reply to this email.
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
