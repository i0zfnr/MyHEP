<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('MyHEP Email Delivery Test') }}</title>
</head>
<body style="margin:0;padding:24px;background:#f5f1eb;color:#2d1f14;font-family:Arial,sans-serif;">
    <div style="max-width:600px;margin:0 auto;padding:28px;border:1px solid #e4d7c8;border-radius:16px;background:#ffffff;">
        <h1 style="margin:0 0 14px;font-size:24px;">{{ __('Email delivery is connected') }}</h1>
        <p style="margin:0 0 18px;line-height:1.6;">{{ __('MyHEP successfully submitted this test message through the configured email provider.') }}</p>
        <div style="padding:14px;border-radius:10px;background:#f8f5f1;line-height:1.7;">
            <strong>{{ __('Reference:') }}</strong> {{ $reference }}<br>
            <strong>{{ __('Sent at:') }}</strong> {{ $sentAt->format('Y-m-d H:i:s T') }}
        </div>
        <p style="margin:18px 0 0;color:#74675d;font-size:13px;line-height:1.5;">{{ __('This is an administrative delivery test. No action is required.') }}</p>
    </div>
</body>
</html>
