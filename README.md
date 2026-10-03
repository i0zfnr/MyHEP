# MyHEP

MyHEP is a student affairs management system for Politeknik Besut. Students, HEP staff, administrators, and security guards use it to manage scholarships and welfare, discipline and fines, campus movement, laptop loans, programs, attendance, questionnaires, participation points, certificates, documents, and notifications. The interface supports English and Bahasa Melayu and includes responsive browser and PWA layouts.

## Technology

- PHP 8.3+, Laravel 13, MySQL or MariaDB
- Vite 8, Tailwind CSS 4, Blade, and JavaScript
- Database queues for background jobs, including bulk certificate generation and backups
- PHPWord and Dompdf for reports; FPDI for certificate templates

## Local installation

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
npm ci
npm run build
php artisan migrate
php artisan storage:link
composer run dev
```

Set the database connection in `.env` before migrating. Start a queue worker for queued features:

```powershell
php artisan queue:work
```

The development `.env.example` is a template. For HTTPS production, set `APP_ENV=production`, `APP_DEBUG=false`, the real `APP_URL` and `ALLOWED_HOSTS`, and `SESSION_SECURE_COOKIE=true`. Configure mail, Web Push, AI, and Google Drive backups only for the features in use. Keep credentials, student documents, private reports, and backup archives outside Git.

## Validation

```powershell
php artisan test
composer audit
npm audit --audit-level=low
npm run build
php artisan route:list
php artisan migrate:status
```

Final local check on **3 October 2026**: 263 PHP tests passed (1,533 assertions), Composer and npm audits reported no known advisories, the production asset build succeeded, and all local migrations completed. The repository currently registers 282 routes and contains 45 test files. Automated tests cover many role and workflow boundaries; connected services such as live SMS, email, payment, AI, push, and Google Drive still need environment-specific acceptance checks. Verify the production migration status, queue workers, scheduler, and backups after deployment.

## Documentation

See [docs/README.md](docs/README.md) for the module overview, role access matrix, workflows, operations, and UAT checklist. Some detailed documents describe an earlier August 2026 baseline; check the current code and this README for the latest validation results.
