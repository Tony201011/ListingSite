<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Profile Report</title>
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
                                    <td style="font-size:14px;color:#fdf2f8;padding-top:6px;font-family:Arial,Helvetica,sans-serif;">New profile report received</td>
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
                                        New Profile Report
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-size:14px;color:#4b5563;padding-bottom:20px;font-family:Arial,Helvetica,sans-serif;">
                                        A user has submitted a report against a provider profile. Details are below:
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
                                                                <strong style="color:#111827;">Reported Profile:</strong>&nbsp; {{ $report->providerProfile?->name ?? '-' }}
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td style="font-size:15px;color:#1f2937;padding:6px 0;font-family:Arial,Helvetica,sans-serif;">
                                                                <strong style="color:#111827;">Reporter Name:</strong>&nbsp; {{ $report->reporter_name ?? 'Anonymous' }}
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td style="font-size:15px;color:#1f2937;padding:6px 0;font-family:Arial,Helvetica,sans-serif;">
                                                                <strong style="color:#111827;">Reporter Email:</strong>&nbsp; {{ $report->reporter_email ?? '-' }}
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td style="font-size:15px;color:#1f2937;padding:6px 0;font-family:Arial,Helvetica,sans-serif;">
                                                                <strong style="color:#111827;">Reason:</strong>&nbsp; {{ ucwords(str_replace('_', ' ', $report->reason)) }}
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td style="padding:12px 0;">
                                                                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                                                    <tr><td style="border-top:1px solid #fbcfe8;font-size:1px;line-height:1px;">&nbsp;</td></tr>
                                                                </table>
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td style="font-size:15px;color:#111827;padding:6px 0;font-weight:700;font-family:Arial,Helvetica,sans-serif;">
                                                                Additional Details:
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td style="font-size:15px;color:#374151;padding:6px 0;line-height:1.6;font-family:Arial,Helvetica,sans-serif;">
                                                                {{ $report->description ?? 'No additional details provided.' }}
                                                            </td>
                                                        </tr>
                                                    </table>
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
                                        This email was sent from the HOTESCORT report system. Please review this report in the admin panel.
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
