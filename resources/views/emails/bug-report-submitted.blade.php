<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('New StudentEdge report') }}</title>
</head>
<body style="margin:0;padding:0;background:#f3eee8;color:#241b15;font-family:Arial,'Helvetica Neue',sans-serif;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;background:#f3eee8;">
    <tr><td align="center" style="padding:32px 16px;">
        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="width:100%;max-width:620px;border:1px solid #dfd2c5;border-radius:20px;background:#fff;overflow:hidden;">
            <tr><td style="padding:24px 30px;background:linear-gradient(135deg,#3b291d,#8f6745);color:#fff;">
                <div style="font-size:20px;font-weight:800;">StudentEdge</div>
                <div style="padding-top:4px;color:#f2d5b5;font-size:12px;font-weight:700;letter-spacing:.7px;">{{ __('SYSTEM ADMIN ALERT') }}</div>
            </td></tr>
            <tr><td style="padding:30px;">
                <div style="display:inline-block;padding:6px 10px;border-radius:999px;background:#f5ebe1;color:#765237;font-size:11px;font-weight:800;">NEW REPORT #{{ $report['id'] }}</div>
                <h1 style="margin:16px 0 10px;font-size:25px;line-height:1.25;">{{ $report['subject'] }}</h1>
                <p style="margin:0 0 20px;color:#6f6156;line-height:1.6;">A user submitted a new {{ str_replace('_', ' ', $report['category']) }} report for system-admin review.</p>
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border:1px solid #e6d9cc;border-radius:12px;background:#faf7f3;">
                    <tr><td style="padding:16px;color:#6f6156;font-size:14px;line-height:1.7;">
                        <strong style="color:#241b15;">{{ __('Reporter:') }}</strong> {{ $report['reporter_name'] }} ({{ $report['reporter_email'] }})<br>
                        <strong style="color:#241b15;">{{ __('Page:') }}</strong> {{ $report['page_url'] ?: 'Not provided' }}<br>
                        <strong style="color:#241b15;">{{ __('Screenshot:') }}</strong> {{ $report['has_screenshot'] ? 'Attached to the internal report' : 'Not provided' }}
                    </td></tr>
                </table>
                <p style="margin:20px 0;color:#4f4035;line-height:1.7;white-space:pre-line;">{{ $report['description'] }}</p>
                <a href="{{ route('admin.bug-reports.index') }}" style="display:inline-block;padding:13px 18px;border-radius:10px;background:#765237;color:#fff;text-decoration:none;font-weight:800;">{{ __('Review in StudentEdge') }}</a>
            </td></tr>
            <tr><td style="padding:18px 30px;border-top:1px solid #eee5dc;background:#faf7f3;color:#8b7b6d;font-size:12px;line-height:1.5;">{{ __('This administrative alert contains user-submitted content. Review it inside StudentEdge before taking action.') }}</td></tr>
        </table>
    </td></tr>
</table>
</body>
</html>
