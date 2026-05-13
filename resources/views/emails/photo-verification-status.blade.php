<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        @if ($status === 'note_added')
            Photo Verification Note Update
        @else
            Photo Verification {{ ucfirst($status) }}
        @endif
    </title>
</head>
<body style="margin:0;padding:0;background-color:#f8fafc;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background-color:#f8fafc;padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="640" cellspacing="0" cellpadding="0" style="max-width:640px;width:100%;background:#ffffff;border:1px solid #f1f5f9;border-radius:14px;overflow:hidden;">
                    <tr>
                        <td style="background:linear-gradient(90deg,#e04ecb,#c13ab0);padding:24px 28px;">
                            <div style="font-size:28px;font-weight:700;line-height:1.2;color:#ffffff;">HOTESCORT</div>
                            <div style="font-size:14px;color:#fdf2f8;margin-top:6px;">Photo verification status update</div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:28px;">
                            <h1 style="margin:0 0 10px 0;font-size:24px;line-height:1.3;color:#111827;">Hi {{ $name }},</h1>

                            @if ($status === 'approved')
                                <p style="margin:0 0 18px 0;font-size:15px;line-height:1.7;color:#4b5563;">
                                    Great news! Your photo verification has been <strong style="color:#15803d;">approved</strong>. Your profile now displays a verified badge.
                                </p>

                                <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:14px 16px;margin-bottom:18px;">
                                    <div style="font-size:12px;text-transform:uppercase;letter-spacing:0.08em;color:#166534;margin-bottom:6px;font-weight:700;">Verification Status</div>
                                    <div style="font-size:14px;color:#111827;font-weight:600;">Approved ✓</div>
                                </div>

                                <table role="presentation" cellspacing="0" cellpadding="0" style="margin:0 0 20px 0;">
                                    <tr>
                                        <td style="background:#e04ecb;border-radius:999px;">
                                            <a href="{{ $signinUrl }}" style="display:inline-block;padding:12px 22px;font-size:14px;font-weight:700;color:#ffffff;text-decoration:none;">View Your Profile</a>
                                        </td>
                                    </tr>
                                </table>
                            @elseif ($status === 'rejected')
                                <p style="margin:0 0 18px 0;font-size:15px;line-height:1.7;color:#4b5563;">
                                    Unfortunately, your photo verification has been <strong style="color:#b91c1c;">rejected</strong>.
                                </p>

                                <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:14px 16px;margin-bottom:18px;">
                                    <div style="font-size:12px;text-transform:uppercase;letter-spacing:0.08em;color:#991b1b;margin-bottom:6px;font-weight:700;">Verification Status</div>
                                    <div style="font-size:14px;color:#111827;font-weight:600;">Rejected</div>
                                </div>

                                @if (! empty($adminNote))
                                    <div style="background:#fef2f2;border:1px solid #fecaca;border-radius:10px;padding:14px 16px;margin-bottom:18px;">
                                        <div style="font-size:12px;text-transform:uppercase;letter-spacing:0.08em;color:#991b1b;margin-bottom:6px;font-weight:700;">Reason</div>
                                        <div style="font-size:14px;color:#111827;">{{ $adminNote }}</div>
                                    </div>
                                @endif

                                <p style="margin:0 0 18px 0;font-size:14px;line-height:1.7;color:#6b7280;">
                                    Please review the reason above and re-submit your verification photos.
                                </p>

                                <table role="presentation" cellspacing="0" cellpadding="0" style="margin:0 0 20px 0;">
                                    <tr>
                                        <td style="background:#e04ecb;border-radius:999px;">
                                            <a href="{{ $signinUrl }}" style="display:inline-block;padding:12px 22px;font-size:14px;font-weight:700;color:#ffffff;text-decoration:none;">Sign In To Resubmit</a>
                                        </td>
                                    </tr>
                                </table>
                            @else
                                <p style="margin:0 0 18px 0;font-size:15px;line-height:1.7;color:#4b5563;">
                                    An admin added a new note to your photo verification request.
                                </p>

                                @if (! empty($verificationStatus))
                                    <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:10px;padding:14px 16px;margin-bottom:18px;">
                                        <div style="font-size:12px;text-transform:uppercase;letter-spacing:0.08em;color:#1d4ed8;margin-bottom:6px;font-weight:700;">Current Verification Status</div>
                                        <div style="font-size:14px;color:#111827;font-weight:600;">{{ ucfirst($verificationStatus) }}</div>
                                    </div>
                                @endif

                                <table role="presentation" cellspacing="0" cellpadding="0" style="margin:0 0 20px 0;">
                                    <tr>
                                        <td style="background:#e04ecb;border-radius:999px;">
                                            <a href="{{ $signinUrl }}" style="display:inline-block;padding:12px 22px;font-size:14px;font-weight:700;color:#ffffff;text-decoration:none;">Sign In To Review</a>
                                        </td>
                                    </tr>
                                </table>
                            @endif

                            @if (filled($adminNote) && $status !== 'rejected')
                                <div style="background:#f8fafc;border:1px solid #e5e7eb;border-radius:10px;padding:14px 16px;margin-bottom:18px;">
                                    <div style="font-size:12px;text-transform:uppercase;letter-spacing:0.08em;color:#4b5563;margin-bottom:6px;font-weight:700;">Admin Note</div>
                                    <div style="font-size:14px;color:#111827;">{{ $adminNote }}</div>
                                </div>
                            @endif

                            <p style="margin:0;font-size:13px;line-height:1.7;color:#6b7280;">
                                If you did not expect this email, please contact support.
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
