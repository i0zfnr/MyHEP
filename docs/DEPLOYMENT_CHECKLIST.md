# Deployment Checklist (Laravel + Laragon)

## Pre-deploy
- [ ] Confirm `.env` points to production DB and correct `APP_URL`.
- [ ] Run DB backup.
- [ ] Import latest SQL schema changes (including new indexes/audit table).

## Deploy
- [ ] `composer install --no-dev --optimize-autoloader`
- [ ] `php artisan config:cache`
- [ ] `php artisan route:cache`
- [ ] `php artisan view:cache`
- [ ] `php artisan optimize`

## Post-deploy
- [ ] Test login for student/admin.
- [ ] Test 1 create/update/delete transaction in each major module.
- [ ] Verify exports and monthly report.
- [ ] Verify audit logs are created.

## Rollback
- [ ] Restore DB backup.
- [ ] Revert code to previous release tag.
- [ ] Clear caches: `php artisan optimize:clear`.
