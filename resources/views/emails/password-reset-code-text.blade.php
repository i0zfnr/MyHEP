MYHEP PASSWORD RESET

Hello {{ $recipientName }},

We received a request to reset your MyHEP password.

Your one-time verification code is: {{ $code }}

This code expires in 15 minutes at {{ $expiresAt->format('h:i A') }}.

Enter the code on the MyHEP verification page. Keep it private; MyHEP staff will never ask you to share it.

If you did not request this reset, ignore this email. Your password will remain unchanged.

Reference: {{ strtoupper(substr($reference, 0, 8)) }}
© {{ now()->year }} MyHEP
