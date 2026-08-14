# Deployment and operations

## Required services

- PHP 8.3+ with required Laravel, image, ZIP, XML, and database extensions
- Composer, Node.js/npm, MySQL/MariaDB
- Web server configured to serve `public/`
- Queue worker for background jobs
- Optional document conversion service/application when PDF must be produced from DOCX
- Configured mail, web-push, and AI provider credentials for enabled integrations

## Production preparation

1. Deploy application files and install production dependencies.
2. Create `.env` from the example without copying development secrets.
3. Configure `APP_ENV=production`, `APP_DEBUG=false`, canonical URL, database, session, queue, cache, mail, push, and AI settings.
4. Generate the application key, migrate with `--force`, link storage, and build frontend assets.
5. Run persistent queue workers under a process supervisor and configure the Laravel scheduler.
6. Cache configuration/routes/views only after environment validation.
7. Verify trusted hosts, HTTPS, writable storage/cache paths, private-file authorization, and security headers.

## Typical commands

```powershell
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan storage:link
php artisan optimize
php artisan queue:work --tries=3 --timeout=300
```

## Backup and recovery

Back up the database, `.env`/secret inventory through an approved secret store, and required private storage. Do not place backups in the public web root or Git. Recovery testing must restore to an isolated environment, validate migrations and private downloads, then run smoke tests before a production cutover.

## Monitoring

Monitor application and web-server logs, failed jobs, queue depth, database health, storage capacity, mail/push failures, report/certificate generation failures, and unusual authentication activity. Use the maintenance controls only for planned work and communicate downtime.

## Security minimums

- Keep secrets server-side and rotate exposed credentials.
- Use request headers—not URL query strings—for API keys where supported.
- Apply least privilege to staff roles and private downloads.
- Validate file MIME type, size, ownership, and storage location.
- Keep dependencies patched and rerun Composer/npm audits.
- Avoid sending NRIC, guardian, welfare, or other sensitive student data to AI providers.

