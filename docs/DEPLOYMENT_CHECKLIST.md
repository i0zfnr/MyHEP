# StudentEdge Deployment Checklist

Last updated: 2026-08-02

## Pre-deployment

- [ ] Confirm the exact release commit/tag and a clean working tree.
- [ ] Set production `APP_URL`, database, mail, queue, cache, and Web Push configuration without committing `.env`.
- [ ] Confirm `APP_ENV=production`, `APP_DEBUG=false`, HTTPS, trusted proxies, secure cookies, and a unique protected `APP_KEY`.
- [ ] Run `composer audit --locked` and resolve or formally accept every advisory.
- [ ] Back up the database, public uploads, and `storage/app/private/student_documents`; encrypt/restrict the backup.
- [ ] Confirm a tested rollback target and migration rollback/restore decision.

## Deploy

```powershell
composer install --no-dev --optimize-autoloader
php artisan migrate --force
npm ci
npm run build
php artisan storage:link
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

- [ ] Ensure `storage`, `bootstrap/cache`, public uploads, and the private document directory are writable by PHP.
- [ ] Confirm `storage/app/private/student_documents` is not reachable by URL.
- [ ] Start/restart queue workers and confirm scheduler execution.

## Post-deployment

- [ ] Run student/admin login, logout, password reset, active-device display, and other-session revocation smoke tests.
- [ ] Test one allowed and one forbidden action for every admin role, including guard and Head of Student Affairs.
- [ ] Test student-owned document download, admin review/download, scholarship offer letter, and disabled Document Centre behavior.
- [ ] Verify scholarship, discipline, movement, exports, PDF/print, notifications, push, and audit logs.
- [ ] Verify Malay/English, light/dark, desktop/mobile, PWA install, scanner, cropper, nested scrolling, and movement feed loading.
- [ ] Monitor application, web-server, queue, scheduler, and failed-job logs.

## Rollback

- [ ] Put the application into an agreed maintenance window if data writes must stop.
- [ ] Restore the previous release code and compatible database/file backup.
- [ ] Run `php artisan optimize:clear`, rebuild/cache as needed, and restart workers.
- [ ] Repeat login, role, private-document, and core-module smoke checks.
- [ ] Record the reason, affected release, restoration time, and follow-up owner.
