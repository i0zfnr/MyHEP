# StudentEdge / e-Biasiswa System Documentation

Last updated: 2026-07-16

## 1. System Overview

StudentEdge, also referred to in the project directory as `e-biasiswa`, is a Laravel-based student affairs management system for scholarship, discipline, student profile, vehicle sticker, fine payment, announcement, reporting, and guard-house movement operations.

The system is intended for use by students, scholarship administrators, discipline administrators, system administrators, and guard-house users. It centralizes student affairs records and gives each role access to the workflows they need.

## 2. Main Objectives

- Maintain student profile and account records.
- Record and review scholarship status and scholarship-related announcements.
- Manage student discipline records, fines, rules, evidence, and payment verification.
- Process student vehicle sticker applications.
- Track student movement in and out of campus using QR checkpoint scanning.
- Provide administrative dashboards, CSV exports, reports, notifications, and audit logging.
- Support bilingual frontend content in English and Malay.

## 3. Technology Stack

| Layer | Technology |
| --- | --- |
| Backend framework | Laravel 13 |
| PHP runtime | PHP 8.3 or higher, project requirement is `^8.3` |
| Database | MySQL or compatible MariaDB |
| Frontend build | Vite 8, Tailwind CSS 4 |
| Package managers | Composer, npm |
| PDF generation | `dompdf/dompdf` |
| Web push | `minishlink/web-push` |
| Local development target | Laragon / Windows-friendly PHP stack |

## 4. User Roles and Access

The application uses a custom session-based authentication structure stored in `session('auth_user')`.

### Student

Students log in using:

- Username: `matric_no`
- Default password: `ic_no`, when the `students.password` field is empty
- Custom password: hashed value in `students.password`, after the student changes password

Student capabilities include:

- View dashboard.
- Update profile and password.
- Submit scholarship status.
- View scholarship records and announcements.
- View discipline offenses.
- Submit fine payment receipt.
- Apply for vehicle sticker.
- View rules and discipline announcements.
- Record campus movement through QR scanning.
- Manage settings such as language and theme.

### Admin

Admins log in using:

- Username: IC number, or full name
- Password: hashed value in `admins.password`

Admin role values:

| Role | Scope |
| --- | --- |
| `scholarship_admin` | Scholarship records, scholarship announcements, scholarship status review |
| `discipline_admin` | Discipline records, rules, fines, vehicle stickers, discipline announcements, movement |
| `guard` | Movement-related access |
| `system_admin` | Full system access, admin management, maintenance, system monitoring |

Admin access control is enforced by:

- `auth.session:admin`
- `admin.scope:scholarship`
- `admin.scope:discipline`
- `admin.scope:movement`
- `admin.scope:backoffice`
- `admin.scope:system`

## 5. Main Modules

### 5.1 Public and Shared Module

Public pages and shared functions include:

- Home page with live system overview counts.
- Login and logout.
- Forgot password, verification code, and password reset.
- Problem reporting form.
- Language switching between English and Malay.
- Theme switching between light and dark.
- Notification feed and push subscription endpoints.

Important routes:

- `GET /`
- `GET /system-overview/live`
- `GET|POST /login`
- `GET|POST /password/forgot`
- `GET|POST /password/verify`
- `GET|POST /password/reset`
- `POST /logout`
- `POST /locale`
- `POST /theme`
- `GET|POST /settings`
- `GET /notifications/feed`
- `POST /push/subscribe`
- `POST /push/unsubscribe`
- `GET|POST /report-problem`

### 5.2 Student Dashboard and Profile

The student dashboard summarizes:

- Total offenses.
- Unpaid offenses.
- Active scholarships.
- Pending fine applications.
- Latest vehicle sticker status.
- Scholarship status submission status.
- Current campus movement status.

Students can update:

- Profile details.
- Contact and address information.
- Password.

Important routes:

- `GET /student/dashboard`
- `GET|POST /student/profile`
- `POST /student/profile/password`

### 5.3 Scholarship Module

The scholarship module manages student scholarship records and announcements.

Student functions:

- Submit scholarship status form.
- View submitted scholarship records.
- View scholarship announcements.

Admin functions:

- View, create, edit, delete, and export scholarship records.
- View student scholarship status submissions.
- Manage scholarship announcements.

Important routes:

- `GET|POST /student/scholarship-status`
- `GET /student/scholarships`
- `GET /student/scholarship-announcements`
- `GET|POST /admin/scholarships`
- `GET /admin/scholarships/export`
- `GET|PUT|DELETE /admin/scholarships/{id}`
- `GET /admin/student-scholarship-status`
- `GET|POST /admin/scholarship-announcements`
- `GET /admin/scholarship-announcements/export`
- `GET|PUT|DELETE /admin/scholarship-announcements/{id}`

### 5.4 Discipline and Offense Module

The discipline module records student offenses and handles fine payment workflow.

Student functions:

- View offense history.
- Print offense record.
- Submit fine payment application with receipt upload.
- View campus rules and discipline announcements.

Admin functions:

- Create, edit, delete, filter, print, export, and generate PDF for offenses.
- Attach and manage offense evidence photos.
- Mark offense as paid.
- Review fine payment applications.
- Approve or reject fine payment receipt.
- Set meeting date when payment is approved.
- Manage rules and rule categories.
- Manage discipline announcements.

Important routes:

- `GET /student/offenses`
- `GET /student/offenses/{id}/print`
- `POST /student/fine-applications`
- `GET /student/rules`
- `GET /student/discipline-announcements`
- `GET|POST /admin/offenses`
- `GET /admin/offenses/export`
- `GET /admin/offenses/{id}/print`
- `GET /admin/offenses/{id}/pdf`
- `GET|PUT|DELETE /admin/offenses/{id}`
- `POST /admin/offenses/{id}/mark-paid`
- `GET /admin/fine-applications`
- `POST /admin/fine-applications/{id}/decision`
- `GET|POST /admin/rules`
- `GET /admin/rules/export`
- `GET|PUT|DELETE /admin/rules/{id}`
- `GET|POST /admin/discipline-announcements`
- `GET /admin/discipline-announcements/export`
- `GET|PUT|DELETE /admin/discipline-announcements/{id}`

### 5.5 Vehicle Sticker Module

Students can submit vehicle sticker applications with required image uploads:

- License card image.
- Parent permission image.
- Vehicle plate image.

Discipline admins can:

- Review applications.
- Approve or reject applications.
- Export application list.
- Delete application records and uploaded files.

Important routes:

- `GET|POST /student/vehicle-stickers`
- `GET /admin/vehicle-stickers`
- `GET /admin/vehicle-stickers/export`
- `POST /admin/vehicle-stickers/{id}/decision`
- `DELETE /admin/vehicle-stickers/{id}`

### 5.6 Student Movement Module

The movement module records students checking out from and returning to campus.

Flow:

1. Admin or guard displays the active checkpoint QR code.
2. Student scans the QR code.
3. The QR token is validated and rotated.
4. A short-lived scan pass is stored in the student session.
5. Student records checkout or return within the scan pass time window.
6. The system calculates expected return time using movement settings.
7. Late returns are marked and can trigger push notifications.

Movement types seeded by default:

- Day Out
- Return to Campus
- Overnight Stay
- Official Programme
- Emergency Leave

Admin functions:

- View movement records.
- Export movement records.
- View students currently outside campus.
- View late-return violations.
- Manage QR status, rotation, activation, deactivation, and extension.
- Manage curfew, GPS validation, checkpoint, and movement type settings.

Important routes:

- `GET|POST /student/movements`
- `GET /admin/movements`
- `GET /admin/movements/export`
- `GET /admin/movements/outside`
- `GET /admin/movements/violations`
- `GET|POST /admin/movements/qr`
- `GET /admin/movements/qr/status`
- `GET /admin/movements/qr/display`
- `GET|POST /admin/movements/settings`

### 5.7 Student and Admin User Management

System admins can manage student accounts and admin accounts.

Student management:

- List, search, filter, create, edit, delete, and export students.
- Reset student password to IC fallback by clearing `students.password`.
- Track whether student uses default IC login or custom password.

Admin user management:

- List, create, edit, delete admin accounts.
- Assign admin role.
- Reset admin password to `Admin@12345`.
- Prevent admin from deleting their own account.

Important routes:

- `GET|POST /admin/students`
- `GET /admin/students/search`
- `GET /admin/students/export`
- `GET|PUT|DELETE /admin/students/{id}`
- `POST /admin/students/{id}/reset-password`
- `GET|POST /admin/admin-users`
- `GET|PUT|DELETE /admin/admin-users/{id}`
- `POST /admin/admin-users/{id}/reset-password`

### 5.8 Reports, Monitoring, and Maintenance

The system provides:

- Monthly report page.
- Admin dashboard metrics.
- Live system monitoring for system admins.
- Maintenance mode controls.
- System cache controls.
- CSV exports across major modules.

Important routes:

- `GET /admin/dashboard`
- `GET /admin/system-monitoring/live`
- `GET /admin/reports/monthly`
- `GET|POST /admin/maintenance`

## 6. Data Model Summary

The root SQL dump `e-biasiswa.sql` contains the original schema and seed data. Laravel migrations add newer tables and columns.

Core tables:

| Table | Purpose |
| --- | --- |
| `students` | Student identity, login, profile, contact, program, residence, guardian, and demographic data |
| `admins` | Admin identity, login, role, and profile data |
| `scholarships` | Student scholarship, welfare, sponsorship, or no-scholarship records |
| `student_scholarship_status_forms` | Student-submitted scholarship status form |
| `scholarship_announcements` | Scholarship-related announcements |
| `offense_types` | Lookup records for offense categories/rules |
| `offenses` | Student offense/summon records |
| `offense_items` | Offense-to-offense-type pivot records |
| `offense_evidence_photos` | Additional evidence photos for offenses |
| `fine_payment_applications` | Fine payment receipt submissions and review status |
| `vehicle_sticker_applications` | Vehicle sticker applications and uploaded documents |
| `rule_categories` | Rule category lookup |
| `rules` | Campus rules shown to students |
| `discipline_announcements` | Discipline-related announcements |
| `movement_checkpoints` | QR checkpoint configuration |
| `movement_types` | Checkout/return movement type definitions |
| `movement_settings` | Curfew, QR validity, GPS validation, and related settings |
| `student_movements` | Checkout, return, status, late return, GPS, and vehicle plate records |
| `password_reset_codes` | Password reset code, verification, expiry, and usage tracking |
| `push_subscriptions` | Browser push subscription data |
| `bug_reports` | Public problem reports |
| `audit_logs` | Critical action trace records |
| `sessions` | Laravel database session storage |
| `cache`, `cache_locks` | Laravel database cache storage |
| `jobs`, `job_batches`, `failed_jobs` | Laravel queue storage |

## 7. Authentication and Security Design

Authentication is implemented through `LoginController` and custom session middleware.

Security-related behavior:

- Login attempts are rate limited by role, username, and IP.
- Student default login falls back to IC number only when no custom password exists.
- Admin passwords are stored as hashes.
- Password reset uses email verification code records with expiry and single-use marking.
- Role access is checked with session middleware and admin scope middleware.
- Critical create, delete, reset, payment decision, QR, and movement actions write audit logs.
- Push subscriptions are keyed by endpoint hash.
- Upload validation limits file type and size for receipt, sticker, and evidence files.

Known security and maintainability risks are listed in section 14.

## 8. Notifications and PWA Support

The application includes PWA assets and browser push notification support.

Relevant files:

- `public/manifest.webmanifest`
- `public/sw.js`
- `public/offline.html`
- `public/images/pwa/*`
- `app/Support/helpers.php`
- `config/services.php`

Push notifications are used for workflows such as:

- Fine receipt submitted for admin review.
- Fine payment decision sent to student.
- Vehicle sticker decision sent to student.
- Late movement return detected.
- Admin movement violation alert.

Required environment variables depend on `config/services.php`, including Web Push VAPID subject, public key, and private key.

## 9. Localization and Theme

The system supports English and Malay.

Relevant files:

- `lang/en.json`
- `lang/ms.json`
- `lang/en/*.php`
- `lang/ms/*.php`
- `app/Http/Middleware/SetLocale.php`
- `app/Http/Middleware/TranslateFrontendContent.php`
- `resources/views/settings/index.blade.php`

Users can change locale and theme from settings. The selected values are stored in session.

## 10. File Uploads and Storage

The system stores uploaded files on Laravel's public disk.

Examples:

- Vehicle sticker license card images.
- Vehicle sticker parent permission images.
- Vehicle plate images.
- Fine payment receipts.
- Offense evidence photos.
- Bug report screenshots.

Deployment must ensure the public storage link exists:

```bash
php artisan storage:link
```

## 11. Installation and Local Setup

Recommended setup:

```bash
composer install
npm install
copy .env.example .env
php artisan key:generate
php artisan migrate
php artisan storage:link
npm run build
```

For local development:

```bash
composer run dev
```

Or run backend/frontend separately:

```bash
php artisan serve
npm run dev
```

If restoring from the SQL dump instead of migrations:

```bash
mysql -u root -p e_biasiswa < e-biasiswa.sql
php artisan migrate
```

The SQL dump contains important original schema and seed data that migrations may not fully recreate from an empty database.

## 12. Verification Commands

Use these checks before deployment or after major changes:

```bash
php artisan test
php artisan route:list --except-vendor
php artisan migrate:status
php artisan view:cache
npm run build
composer audit
```

Operational checklists already exist:

- `docs/DEPLOYMENT_CHECKLIST.md`
- `docs/BACKUP_RESTORE_SOP.md`
- `docs/UAT_CHECKLIST.md`

## 13. Deployment Notes

Before production deployment:

- Set `APP_ENV=production`.
- Set `APP_DEBUG=false`.
- Set the correct `APP_URL`.
- Configure database credentials.
- Configure mail settings for password reset.
- Configure Web Push VAPID keys if push notifications are required.
- Run database backup before deployment.
- Run Composer install without dev dependencies.
- Cache config, routes, views, and optimized files.
- Verify storage link and upload permissions.

Typical production commands:

```bash
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
npm run build
```

## 14. Known Risks and Technical Debt

The current project is functional, but several areas should be handled before production hardening:

- `routes/web.php` contains substantial inline business logic and is difficult to maintain safely.
- Password verification code attempts are not throttled as strongly as login attempts.
- Admin authorization trusts role data stored in session and should revalidate current database role for high-risk routes.
- Movement QR token validation and rotation should be made atomic to prevent concurrent reuse.
- Active movement creation should use transaction or locking protection to avoid duplicate active checkout records.
- Test coverage is minimal and should be expanded for real business workflows.
- Composer dependency advisories should be reviewed and fixed in a dedicated dependency update.
- `.env` must not be committed or exposed, and debug mode must not be enabled in production.

## 15. Suggested Future Improvements

- Move large route closures into dedicated controllers.
- Add Form Request classes for repeated validation rules.
- Add service classes for scholarship, offense, fine payment, sticker, and movement workflows.
- Add feature tests for authentication, admin scopes, uploads, exports, QR scanning, and payment decisions.
- Add database constraints where business rules require uniqueness or single active records.
- Improve password reset attempt throttling and reset transaction atomicity.
- Add audit log viewer for system admins if operational review is required.
- Add scheduled cleanup for expired password reset codes and stale push subscriptions.

