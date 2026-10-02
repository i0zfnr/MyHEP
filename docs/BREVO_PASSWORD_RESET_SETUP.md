# Brevo email delivery for password resets

MyHEP sends password reset codes through Laravel's configured mailer. Student requests use the student's registered `students.email` address; the request form does not accept a replacement destination. Admin recovery continues to verify the submitted email against the admin account.

## Configure Brevo

1. In Brevo, authenticate the sender domain and create a verified sender address.
2. Open the SMTP page and create an SMTP key. Use the SMTP login shown there and the SMTP key; do not use an API key.
3. Set these values in Ryaze's server-side environment editor:

   ```dotenv
   MAIL_MAILER=smtp
   MAIL_SCHEME=null
   MAIL_HOST=smtp-relay.brevo.com
   MAIL_PORT=587
   MAIL_USERNAME=your-brevo-smtp-login
   MAIL_PASSWORD=your-brevo-smtp-key
   MAIL_ENCRYPTION=tls
   MAIL_FROM_ADDRESS=your-verified-sender@example.com
   MAIL_FROM_NAME=MyHEP
   ```

4. Save the environment values and clear/rebuild Laravel's configuration cache using the deployment panel's supported process.
5. Submit a reset request for a test student whose account has a valid email address. Confirm receipt, code expiry after 15 minutes, successful verification, and password update.

Keep SMTP credentials only in the host's secret environment settings. Do not add them to Git or frontend `VITE_` variables. If a student account has no valid email address, the reset request cannot be sent and the student must contact an administrator to update the account record.
