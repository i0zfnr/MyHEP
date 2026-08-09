# StudentEdge

StudentEdge is a Laravel student-affairs management system for Politeknik Besut. It brings scholarship, discipline, fines, vehicle stickers, campus movement, student documents, JHEP laptop lending, notifications, reporting, and role-scoped administration into one responsive installable web application.

## Documentation

- [System documentation](docs/SYSTEM_DOCUMENTATION.md)
- [Implemented function inventory - 167 functions](docs/FUNCTION_INVENTORY.md)
- [Word function inventory (reference snapshot)](docs/StudentEdge_Function_Inventory.docx)
- [Project progress and next steps](docs/PROJECT_PROGRESS_AND_NEXT_STEPS.md)
- [Team presentation guide](docs/TEAM_PRESENTATION_GUIDE.md)
- [Panel coding Q&A guide](docs/PANEL_CODING_QA_GUIDE.md)
- [UAT checklist](docs/UAT_CHECKLIST.md)
- [Deployment checklist](docs/DEPLOYMENT_CHECKLIST.md)
- [Backup and restore SOP](docs/BACKUP_RESTORE_SOP.md)
- [Product and engineering context](Document%20Context/PRD.md)

## Main capabilities

- Custom student/admin authentication, Resend-backed password reset, active-device tracking, remote session revocation, and System Admin Active Visitors monitoring
- Central admin permissions with separate student list, sensitive-data, export, management, document, scholarship, discipline, movement, back-office, and system abilities
- 167 named application functions: 26 public/shared, 21 student, and 120 admin
- Lecturer/staff accounts with category-derived scholarship, discipline, and movement access plus System Admin-controlled page gates
- Student CRUD, profile photos, search, CSV/XLSX import, CSV export, identity masking, pagination, session revocation, and guarded System Admin bulk deletion
- Scholarship records, B40 TVET import/export, announcements, declarations, and private offer-letter upload/review
- Offenses, evidence, fines, payment receipts, rules, discipline announcements, and vehicle-sticker decisions
- QR-based campus checkout/return, guard views, movement reports, cursor batches, and virtualized long lists
- Private Student Document Centre with review, authenticated downloads, and a system-admin feature toggle
- English/Malay UI, light/dark theme, beta accent themes, Live Glass transparency, responsive PWA shell, Android/iPhone install guidance, notifications, browser push, and reduced-motion support
- Public and authenticated problem reporting with screenshots, email delivery state, System Admin review, status notes, and push updates
- Staff/guard account management, public and authenticated QR laptop borrowing/return, borrower CSV import, printable QR labels, monthly analytics, monitoring, maintenance/cache controls, configurable session lifetime, push broadcasts, email-delivery testing, and audit logs

## Technology

| Technology | Requirement / version |
| --- | --- |
| PHP | `^8.3` |
| Laravel | `^13.0` |
| MySQL / MariaDB | Supported relational database |
| Node.js | Current LTS recommended |
| Vite | `^8.0` |
| Tailwind CSS | `^4.0` |
| Cropper.js | `^1.6.2` |
| Lenis | `1.3.25` |

## Local installation

```powershell
git clone https://github.com/i0zfnr/MyHEP.git
Set-Location MyHEP
composer install
Copy-Item .env.example .env
php artisan key:generate
```

Create the database from `StudentEdge.sql`, configure `.env`, then apply incremental migrations and build the frontend:

```powershell
php artisan migrate
php artisan storage:link
npm install
npm run build
php artisan serve
```

Private student documents are stored under `storage/app/private/student_documents`; do not expose that directory through `public/storage`.

## Production readiness notes

- Import `StudentEdge.sql` before incremental migrations; migrations do not reconstruct the complete original schema by themselves.
- Remove the temporary student IC-number password fallback before using real client data.
- Review the service worker so authenticated pages and private downloads are never retained in browser Cache Storage.
- Treat the public laptop NRIC flow and the global student-deletion action as high-risk workflows requiring explicit institutional approval, throttling, backup, audit review, and UAT.
- Rotate production secrets, set `APP_DEBUG=false`, run the full test suite, and review all Composer advisories before deployment.

## Verification

```powershell
php artisan test
php artisan route:list --except-vendor
php artisan view:cache
npm run build
git diff --check
```

See the deployment checklist before production use. Never commit `.env`, production credentials, personal student data, or uploaded documents.
