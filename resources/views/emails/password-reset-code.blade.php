<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>{{ __('Your StudentEdge password reset code') }}</title>
</head>
<body style="margin:0;padding:0;background:#f3eee8;color:#241b15;font-family:Arial,'Helvetica Neue',sans-serif;">
    <div style="display:none;max-height:0;overflow:hidden;opacity:0;">Use {{ $code }} to reset your StudentEdge password. This code expires in 15 minutes.</div>
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;background:#f3eee8;">
        <tr>
            <td align="center" style="padding:32px 16px;">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;max-width:620px;border:1px solid #dfd2c5;border-radius:22px;background:#ffffff;box-shadow:0 18px 45px rgba(64,45,31,.10);overflow:hidden;">
                    <tr>
                        <td style="padding:24px 32px;background:#1f5559;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td width="50" valign="middle">
                                        <div style="width:44px;height:44px;border-radius:13px;background:#ffffff;color:#1f5559;font-size:17px;font-weight:800;line-height:44px;text-align:center;">{{ __('SE') }}</div>
                                    </td>
                                    <td valign="middle" style="padding-left:12px;color:#ffffff;">
                                        <div style="font-size:19px;font-weight:800;letter-spacing:-.2px;">StudentEdge</div>
                                        <div style="padding-top:3px;color:#d7ebec;font-size:12px;letter-spacing:.5px;">{{ __('SECURE ACCOUNT RECOVERY') }}</div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:36px 32px 32px;">
                            <div style="display:inline-block;padding:6px 10px;border:1px solid #b9ddde;border-radius:999px;background:#edf8f8;color:#1f5559;font-size:11px;font-weight:800;letter-spacing:.8px;">{{ __('PASSWORD RESET') }}</div>
                            <h1 style="margin:18px 0 10px;color:#241b15;font-size:30px;line-height:1.2;letter-spacing:-.7px;">{{ __('Your verification code') }}</h1>
                            <p style="margin:0 0 20px;color:#6f6156;font-size:15px;line-height:1.7;">Hello {{ $recipientName }}, we received a request to reset your StudentEdge password. Enter the code below on the verification page.</p>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin:24px 0;">
                                <tr>
                                    <td align="center" style="padding:24px 14px;border:1px solid #c9dfdf;border-radius:16px;background:#f1f8f8;">
                                        <div style="color:#1f5559;font-size:12px;font-weight:800;letter-spacing:1px;">{{ __('YOUR ONE-TIME CODE') }}</div>
                                        <div style="padding-top:10px;color:#173f42;font-family:'Courier New',monospace;font-size:36px;font-weight:800;letter-spacing:9px;line-height:1;">{{ $code }}</div>
                                        <div style="padding-top:13px;color:#6d7f80;font-size:12px;">Expires in 15 minutes at {{ $expiresAt->format('h:i A') }}</div>
                                    </td>
                                </tr>
                            </table>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">
                                <tr>
                                    <td style="padding:15px 16px;border-left:4px solid #d2a168;border-radius:8px;background:#fff8ef;color:#6c4a28;font-size:13px;line-height:1.6;">
                                        <strong>{{ __('Keep this code private.') }}</strong> {{ __('StudentEdge staff will never ask you to share it by phone, message, or email.') }}
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:24px 0 0;color:#6f6156;font-size:14px;line-height:1.7;">{{ __('If you did not request this reset, you can safely ignore this email. Your password will remain unchanged.') }}</p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:20px 32px;border-top:1px solid #eee5dc;background:#faf7f3;color:#8b7b6d;font-size:11px;line-height:1.6;">
                            <strong style="color:#5d4c40;">StudentEdge</strong><br>
                            Automated security email · Reference {{ strtoupper(substr($reference, 0, 8)) }}<br>
                            © {{ now()->year }} StudentEdge. All rights reserved.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
