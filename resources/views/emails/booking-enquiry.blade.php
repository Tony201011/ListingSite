<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Booking Enquiry</title>
</head>
<body style="margin:0;padding:0;background-color:#f8fafc;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color:#f8fafc;padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="640" cellspacing="0" cellpadding="0" style="max-width:640px;width:100%;background:#ffffff;border:1px solid #f1f5f9;border-radius:14px;overflow:hidden;">

                    <!-- Header -->
                    <tr>
                        <td style="background:linear-gradient(90deg,#e04ecb,#c13ab0);padding:24px 28px;">
                            <div style="font-size:28px;font-weight:700;color:#ffffff;">HOTESCORT</div>
                            <div style="font-size:14px;color:#fdf2f8;margin-top:6px;">New booking enquiry received</div>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding:28px;">
                            <h1 style="margin:0 0 10px 0;font-size:22px;color:#111827;">
                                New Booking Request
                            </h1>

                            <p style="margin:0 0 18px 0;font-size:14px;color:#4b5563;">
                                You have received a new booking enquiry. Details are below:
                            </p>

                            <!-- Details Box -->
                            <div style="background:#fdf2f8;border:1px solid #fbcfe8;border-radius:10px;padding:16px;margin-bottom:18px;">

                                <p style="margin:6px 0;"><strong>Name:</strong> {{ $enquiry->name ?? '-' }}</p>
                                <p style="margin:6px 0;"><strong>Email:</strong> {{ $enquiry->email }}</p>
                                <p style="margin:6px 0;"><strong>Phone:</strong> {{ $enquiry->phone ?? '-' }}</p>

                                <hr style="border:none;border-top:1px solid #fbcfe8;margin:12px 0;">

                                <p style="margin:6px 0;"><strong>Date & Time:</strong> {{ $enquiry->booking_datetime ?? '-' }}</p>
                                <p style="margin:6px 0;"><strong>Services:</strong> {{ $enquiry->services ?? '-' }}</p>
                                <p style="margin:6px 0;"><strong>Duration:</strong> {{ $enquiry->duration ?? '-' }}</p>
                                <p style="margin:6px 0;"><strong>Location:</strong> {{ $enquiry->location ?? '-' }}</p>

                                <hr style="border:none;border-top:1px solid #fbcfe8;margin:12px 0;">

                                <p style="margin:6px 0;"><strong>Message:</strong></p>
                                <p style="margin:6px 0;color:#374151;">
                                    {{ $enquiry->message ?? '-' }}
                                </p>
                            </div>

                            <!-- CTA -->
                            <table role="presentation" cellspacing="0" cellpadding="0" style="margin-top:10px;">
                                <tr>
                                    <td style="background:#e04ecb;border-radius:999px;">
                                        <a href="mailto:{{ $enquiry->email }}" style="display:inline-block;padding:10px 18px;font-size:14px;font-weight:700;color:#ffffff;text-decoration:none;">
                                            Reply to Enquiry
                                        </a>
                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding:18px 28px;background:#f9fafb;border-top:1px solid #e5e7eb;">
                            <p style="margin:0;font-size:12px;color:#6b7280;">
                                This email was sent from HOTESCORT booking system.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
