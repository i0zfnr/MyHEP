# StudentEdge / e-Biasiswa System Documentation

Last updated: 2026-08-08

## 1. System Overview

StudentEdge is a Laravel-based student affairs management system for scholarship, discipline, student profile, vehicle sticker, fine payment, announcement, reporting, and guard-house movement operations.

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
- Download owned private documents and track scholarship offer-letter review.
- View authenticated devices and revoke other sessions.
- Manage settings such as language and theme.

### Admin

Admins log in using:

- Username: IC number, or full name
- Password: hashed value in `admins.password`

Admin role values:

| Role | Scope |
| --- | --- |
| `lecturer` | Limited AJAX student lookup and only the offense register/list pages individually enabled by a System Admin |
| `scholarship_admin` | Scholarship records, scholarship announcements, scholarship status review |
| `discipline_admin` | Discipline records, rules, fines, vehicle stickers, discipline announcements, movement |
| `guard` | Movement-related access |
| `student_affairs_head` | Scholarship, discipline, movement, sensitive student data, exports, student management, and document review |
| `system_admin` | Full system access, admin management, maintenance, system monitoring |

The audited release exposes **142 named application functions**: 23 public/shared, 21 student, and 98 admin. See `docs/FUNCTION_INVENTORY.md` for the complete module breakdown, role coverage, methodology, and recount command.

Admin access control is enforced by:

- `auth.session:admin`
- `admin.scope:scholarship`
- `admin.scope:discipline`
- `admin.scope:movement`
- `admin.scope:backoffice`
- `admin.scope:system`
- `admin.scope:students.list`, `students.sensitive`, `students.export`, and `students.manage`
- `admin.scope:documents`
- `admin.scope:offense.register`, `admin.scope:students.lookup`, and `admin.scope:lecturers.manage`
- `lecturer.page:offense_register` and `lecturer.page:offense_list`

The ability-to-role map is centralized in `app/Support/AdminPermissions.php`. A menu item is never the security boundary; routes enforce every ability on the server.

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
- `DELETE /settings/sessions`
- `DELETE /settings/sessions/{publicId}`
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
- Upload a required private offer letter when declaring an active scholarship.
- View submitted scholarship records.
- View scholarship announcements.

Admin functions:

- View, create, edit, delete, and export scholarship records.
- View student scholarship status submissions.
- Download linked offer letters through an authenticated private route.
- Manage scholarship announcements.

Important routes:

- `GET|POST /student/scholarship-status`
- `GET /student/scholarships`
- `GET /student/scholarship-announcements`
- `GET|POST /admin/scholarships`
- `GET /admin/scholarships/export`
- `GET|PUT|DELETE /admin/scholarships/{id}`
- `GET /admin/student-scholarship-status`
- `GET /admin/student-scholarship-status/documents/{id}/download`
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
2. Student opens the dedicated student Scan QR page and scans the guard-house code.
3. The QR token is validated against the active checkpoint and rotated immediately after a successful scan.
4. A short-lived scan pass is stored in the student session.
5. Student records checkout or return within the scan pass time window.
6. The system calculates expected return time using movement settings.
7. Late returns are marked and can trigger push notifications.

The checkpoint QR itself remains usable while the checkpoint is active. Time limits apply to the student session scan pass, not to the displayed checkpoint QR token.

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
- Manage QR status, rotation, activation, and deactivation.
- Manage curfew, GPS validation, checkpoint, and movement type settings.

Important routes:

- `GET|POST /student/movements`
- `GET /student/movements/scan`
- `GET /admin/movements`
- `GET /admin/movements/export`
- `GET /admin/movements/outside`
- `GET /admin/movements/violations`
- `GET|POST /admin/movements/qr`
- `GET /admin/movements/qr/status`
- `GET /admin/movements/qr/display`
- `GET|POST /admin/movements/settings`

### 5.7 Student and Admin User Management

Authorized admins can manage student accounts according to distinct abilities; system admins manage admin accounts.

Student management:

- List, search, filter, create, edit, delete, and export students.
- Reset student password to IC fallback by clearing `students.password`.
- Track whether student uses default IC login or custom password.
- Mask IC numbers in list and print contexts for roles without sensitive-data access.
- Restrict the guard role to student list/search; guards cannot export, open sensitive detail, or mutate students.

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

### 5.8 Student Document Centre and Feature Controls

The Document Centre stores private student files outside public storage. Students can list and download only their own documents. Head of Student Affairs and System Admin roles can filter, download, approve, or reject documents; a rejection requires a review note. Scholarship status submissions can create or replace a linked offer-letter document.

Document categories are letters, receipts, scholarship, official notices, and other. Review states are pending, approved, and rejected. Expiry filters distinguish no-expiry, valid, expiring within 30 days, and expired records.

The `document_centre` feature is enabled by default and can be changed by a System Admin. Student routes are protected by `feature.enabled:document_centre`; disabling it prevents access, not only navigation visibility.

Important routes:

- `GET /student/documents`
- `GET /student/documents/{id}/download`
- `GET /admin/documents`
- `GET /admin/documents/{id}/download`
- `PATCH /admin/documents/{id}/review`
- `GET /admin/features`
- `PATCH /admin/features/{feature}`

### 5.9 Reports, Monitoring, and Maintenance

The system provides:

- Monthly report page.
- Admin dashboard metrics.
- Live system monitoring for system admins.
- Maintenance mode controls.
- System cache controls.
- Configurable authenticated-session lifetime.
- System Admin push test and scheduled maintenance announcement controls.
- CSV exports across major modules.

Important routes:

- `GET /admin/dashboard`
- `GET /admin/system-monitoring/live`
- `GET /admin/reports/monthly`
- `GET|POST /admin/maintenance`
- `POST /admin/maintenance/push/test`
- `POST /admin/maintenance/push/broadcast`
- `PATCH /admin/system-settings/session-lifetime`

### 5.10 AI Helper / Agent Integration

The application currently contains an admin-facing AI Helper and a disabled student AI Helper entry point.

Current behavior:

- Student route `GET /student/ai-helper` redirects back to the student dashboard with an unavailable message.
- Student sidebar navigation shows AI Helper as unavailable instead of linking to a working tool.
- Admin route `GET /admin/ai-helper` shows the admin helper page.
- Admin route `POST /admin/ai-helper` sends a validated prompt plus limited system context to the configured AI provider.
- Admin AI access is protected by `auth.session:admin` and `admin.scope:backoffice`.

Provider selection is controlled by configured API keys in this priority order:

1. Gemini, when `GEMINI_API_KEY` is set.
2. OpenAI, when `OPENAI_API_KEY` is set and Gemini is not configured.
3. DeepSeek, when `DEEPSEEK_API_KEY` is set and neither Gemini nor OpenAI is configured.

Relevant configuration keys:

- `GEMINI_API_KEY`, `GEMINI_API_URL`, `GEMINI_MODEL`
- `OPENAI_API_KEY`, `OPENAI_API_URL`, `OPENAI_MODEL`
- `DEEPSEEK_API_KEY`, `DEEPSEEK_API_URL`, `DEEPSEEK_MODEL`

Relevant files:

- `app/Http/Controllers/Admin/AiHelperController.php`
- `app/Http/Controllers/Student/AiHelperController.php`
- `resources/views/admin/ai_helper/index.blade.php`
- `resources/views/layouts/app.blade.php`
- `config/services.php`
- `routes/web.php`

Agent change constraints:

- Do not expose student AI features until the data boundaries, prompt behavior, safety messages, and permission model are explicitly defined.
- Keep admin AI context read-only unless a future task adds a reviewed action/approval workflow.
- Do not send secrets, raw session payloads, uploaded files, or full student datasets to an external AI provider.
- Keep provider-specific request code isolated in the admin helper controller or move it into a dedicated service before expanding the feature.
- Add feature tests before enabling any new student-facing agent or any AI-assisted write action.

## 6. Data Model Summary

The root SQL dump `StudentEdge.sql` contains the original schema and seed data. Laravel migrations add newer tables and columns.

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
| `movement_settings` | Curfew, GPS validation, and related movement settings |
| `student_movements` | Checkout, return, status, late return, GPS, and vehicle plate records |
| `student_documents` | Private document metadata, source link, review state, expiry, and storage path |
| `system_features` | Registered feature availability and updating admin |
| `account_sessions` | Authenticated device/session registry used for visibility and remote revocation |
| `password_reset_codes` | Password reset code, verification, expiry, and usage tracking |
| `push_subscriptions` | Browser push subscription data |
| `push_notification_markers` | Idempotency markers for event-driven push notifications |
| `system_settings` | System-level operational settings such as session lifetime |
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
- Password reset requests are limited to three per role/identifier/email/IP combination per 15 minutes; verification codes are limited to five attempts per reset reference/IP combination per 15 minutes.
- Password reset consumption uses a database transaction and row lock so a verified code is consumed only once.
- Session middleware verifies that the student/admin account still exists on every protected request. Admin scope middleware also verifies the current database role and invalidates stale sessions.
- Web middleware registers authenticated devices and updates their activity at most once per minute. Settings show active devices and permit revoking another device or all other sessions.
- Student list, sensitive identity, export, management, and document permissions are independently enforced.
- Critical create, delete, reset, payment decision, QR, and movement actions write audit logs.
- Push subscriptions are keyed by endpoint hash.
- Upload validation limits file type and size for receipts, stickers, evidence, photos, and scholarship offer letters.

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
- Scholarship declaration and offer-letter workflow events.
- Vehicle sticker application submitted for admin review.
- System Admin test notification.
- Scheduled maintenance announcement broadcast to subscribed students and admins.

The maintenance broadcast is sent immediately and describes the selected future start/end schedule. It does not schedule delayed delivery and does not enable Laravel maintenance mode automatically.

Required environment variables depend on `config/services.php`, including Web Push VAPID subject, public key, and private key.

### 8.1 Student Mobile/PWA Interface Direction

The student-facing mobile/PWA experience should be treated as a presentation layer on top of the existing student routes and workflows. Desktop and tablet layouts should remain stable unless a task explicitly requests a broader redesign.

Current design direction:

- Keep the existing StudentEdge color palette and glass/warm SaaS visual language.
- Use a mobile-only bottom navigation for student accounts.
- Target bottom navigation order:
  - Home
  - Fines
  - Scan QR
  - Aid
  - More
- Do not include Profile as a bottom navigation tab. Student profile access should come from the top-right header user/avatar menu.
- Make Scan QR the central primary action in the bottom navigation.
- Use More for secondary destinations such as Campus Movement, Vehicle Sticker, Rules, Announcements, and Settings.
- Keep the mobile Student Dashboard lightweight. It should show urgent alerts, compact welcome/status information, and key actions instead of repeating every module card.
- Improve student content pages incrementally with mobile-only CSS where needed:
  - Offense and fine records.
  - Campus movement and QR scanning.
  - Scholarship and aid.
  - Profile and settings if mobile spacing is poor.

Implementation constraints:

- Do not change student routes, controllers, validation rules, database schema, permissions, or business logic for presentation-only mobile/PWA work.
- Use responsive CSS and existing Blade route names.
- Preserve desktop behavior while applying mobile-only changes with media queries.
- Test with `php artisan view:cache`, `npm run build`, and manual mobile viewport/PWA checks before committing.

### 8.2 Shared UI Shell and Motion

The shared application shell is implemented in `resources/views/layouts/app.blade.php`, with visual rules in `resources/css/design-system.css` and interactive behavior in `resources/js/app.js`.

Desktop student navigation:

- The student dashboard does not show a desktop sidebar and uses the full workspace.
- Student module pages retain the normal sticky sidebar on desktop for frequent module navigation.
- Admin desktop navigation retains the shared sidebar.

Mobile navigation:

- The student bottom navigation and More sheet remain the primary mobile navigation controls.
- The student dashboard header includes a hamburger control that opens its sidebar as an overlay drawer on smaller screens; it is hidden from assistive technology until opened.
- Student module pages also retain the sidebar as an overlay drawer on smaller screens.

Popup and card behavior:

- Notifications, confirmation dialogs, media previews, filter sheets, account menus, and the mobile More sheet use reversible opacity and transform transitions.
- Notification, media, and filter surfaces calculate their transform origin from the trigger where available.
- Content cards are intentionally lightweight. They do not use pointer-tracked lighting, reflection sweeps, entrance pop animations, or mobile backdrop filters.
- Desktop cards have only a small reversible hover elevation. Rich depth and blur are reserved for transient overlays.

Accessibility and performance constraints:

- Touch-oriented controls use a 44px minimum target where applicable.
- Reduced-motion and reduced-transparency preferences disable or simplify visual effects.
- Do not add per-card pointer listeners, heavy backdrop filters, or large scale animations to scrolling content without profiling the result on a mobile device.

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

General uploads use Laravel's public disk. Student documents use the private `student_documents` disk rooted at `storage/app/private/student_documents`.

Examples:

- Vehicle sticker license card images.
- Vehicle sticker parent permission images.
- Vehicle plate images.
- Fine payment receipts.
- Offense evidence photos.
- Bug report screenshots.
- Admin profile photos, with client-side crop/rotate/zoom before upload.

Private examples:

- Scholarship offer letters.
- Document Centre letters, receipts, official notices, and other reviewed documents.

Private files are returned by authenticated download controllers with ownership/ability checks and `private, no-store` response headers. Never copy the private document directory into `public/storage`.

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
mysql -u root -p StudentEdge < StudentEdge.sql
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
- Verify the private document directory is writable but not publicly web-accessible.

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
- Movement QR token validation and rotation should be made atomic to prevent concurrent reuse.
- Active movement creation should use transaction or locking protection to avoid duplicate active checkout records.
- Coverage now includes active sessions, permission boundaries, identity masking, admin profile upload, student documents, and movement-feed behavior, but many legacy workflows still need broader tests.
- Composer dependency advisories should be reviewed and fixed in a dedicated dependency update.
- `.env` must not be committed or exposed, and debug mode must not be enabled in production.
- AI provider keys must remain environment-only. Agent changes should be reviewed for privacy, authorization, and prompt-injection risks before public use.
- Receipt QR/OCR inspection is not an authenticity guarantee. Official iPayment API/QR behavior must be confirmed before any automated verification feature is described as implemented.

## 15. Suggested Future Improvements

- Move large route closures into dedicated controllers.
- Add Form Request classes for repeated validation rules.
- Add service classes for scholarship, offense, fine payment, sticker, and movement workflows.
- Add feature tests for authentication, admin scopes, uploads, exports, QR scanning, and payment decisions.
- Add database constraints where business rules require uniqueness or single active records.
- Add audit log viewer for system admins if operational review is required.
- Add scheduled cleanup for expired password reset codes and stale push subscriptions.
- Add scheduled cleanup for expired `account_sessions` and orphaned private document files.
- Planned only: evaluate iPayment receipt authenticity through an approved official API or documented QR contract; retain human review until that evidence exists.
- Finish the student mobile/PWA app shell after visual approval, then tune each
  student content page for mobile without altering desktop workflows.
- Move AI provider calls into a service layer and define a formal agent policy before
  re-enabling student AI helper functionality.
