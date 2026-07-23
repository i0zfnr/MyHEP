# StudentEdge Project Progress and Next Steps

Last reviewed: 2026-07-24  
Project path: `C:\laragon\www\e-biasiswa`  
Branch: `main`

## 1. Executive Status

| Measure | Progress | Meaning |
| --- | ---: | --- |
| Functional feature completion | **88%** | Most planned user-facing modules and workflows exist and are usable. |
| Production readiness | **72%** | The system still needs security updates, deeper testing, concurrency fixes, UAT, and deployment hardening. |

The recommended project headline is **72% complete toward a production-ready release**.

This estimate deliberately gives significant weight to testing and security. A module appearing in the interface does not make it production-ready until its permissions, validation, failure paths, and operational recovery have been verified.

## 2. Scoring Basis

| Workstream | Weight | Current maturity | Weighted result |
| --- | ---: | ---: | ---: |
| Core student-affairs modules | 25% | 92% | 23% |
| Authentication and role access | 15% | 85% | 13% |
| Data import, exports, and reporting | 15% | 80% | 12% |
| Responsive UX, PWA, and language support | 15% | 85% | 13% |
| Automated testing and regression protection | 15% | 40% | 6% |
| Security, deployment, and operations | 15% | 35% | 5% |
| **Total production readiness** | **100%** |  | **72%** |

## 3. Verified Baseline

The following checks were run on 2026-07-24:

- `php artisan test`: **PASS**
  - 10 tests
  - 35 assertions
- `npm run build`: **PASS**
  - Vite 8.0.9 production build completed
- `php artisan migrate:status`: **PASS**
  - All 20 migrations are marked as run
- `php artisan route:list --except-vendor --json`: **127 application routes**
- Student database:
  - 602 real student records imported
  - The source workbook was previously checked for blank names, blank IC numbers, duplicate IC numbers, and duplicate registration numbers
- Admin database:
  - 5 admin accounts
  - The `student_affairs_head` role migration is applied
- Safety backup available:
  - `storage/app/private/backups/before_real_student_import_20260723_151716.sql`
- `composer audit --locked`: **NOT READY**
  - 28 security advisories affect 10 packages
  - The report includes high-severity advisories
- Current environment: **development only**
  - `APP_ENV=local`
  - `APP_DEBUG=true`
  - Database-backed session, cache, and queue drivers are configured

## 4. Completed Capabilities

### Student and account management

- Student login and profile management
- Password change and password-reset workflow
- Student Excel/CSV import
- Official student-list headers supported, including `Nama`, `No KP`, `No Pend`, `Kod Kursus`, and `Sesi Semasa`
- Student export and administrative student management
- Profile completeness enforcement
- English and Malay interface support

### Scholarship

- Scholarship records and status workflow
- Student scholarship-status form
- Scholarship announcements
- B40 TVET import flow
- Scholarship exports and dashboard summaries

### Discipline

- Offense records, rules, evidence, fines, and payment applications
- Vehicle-sticker applications and approval workflow
- Discipline announcements
- Print, PDF, and CSV outputs
- Discipline dashboard and notification items

### Movement and guard house

- Movement types, checkpoints, settings, and reports
- Student checkout and return workflow
- QR scanning, mobile camera support, and fallback scanning
- Guard role and movement administration
- Late-return and outside-campus monitoring

### Administration and system support

- Admin dashboard and scoped access middleware
- Roles:
  - Guard
  - Scholarship Admin
  - Discipline Admin
  - Ketua Hal Ehwal Pelajar / Head of Student Affairs
  - System Admin
- Head of Student Affairs can access scholarship and discipline operations but not system administration
- Notifications, web-push subscriptions, audit logs, maintenance controls, bug reports, and system monitoring
- Responsive desktop/mobile interface and student PWA navigation
- Admin AI Helper is available only when a provider is configured; student AI remains disabled

## 5. Current Uncommitted Work

The working tree contains intentional changes from the latest work:

- Student import header compatibility and localized import messages
- Head of Student Affairs role, permissions, labels, and migration
- Regression tests for import headers and the new admin role

Do not reset or discard the working tree. Before starting another feature:

```powershell
git status --short
git diff --check
php artisan test
```

Review and commit these changes as one verified checkpoint before dependency upgrades or large refactors.

## 6. Remaining Work by Priority

## P0 — Release blockers

### 6.1 Update vulnerable Composer dependencies

Current state: 28 advisories across 10 packages.

Actions:

1. Create a dedicated dependency-update checkpoint.
2. Update Laravel, Symfony, Dompdf, Guzzle, and other affected packages within compatible constraints.
3. Review breaking changes and migration notes.
4. Rerun:

```powershell
composer audit --locked
php artisan test
php artisan route:list --except-vendor
npm run build
php artisan migrate:status
```

Acceptance criteria:

- No critical or high-severity advisory remains.
- Preferably, `composer audit` reports no known advisories.
- All automated and manual regression checks pass.

### 6.2 Make QR validation and rotation atomic

Risk: concurrent requests may validate the same QR token before it rotates.

Primary file:

- `app/Http/Controllers/Student/MovementController.php`

Actions:

- Put token validation, consumption, and rotation in one database transaction.
- Lock the checkpoint/token row with `lockForUpdate()`, or use a conditional atomic update.
- Add a regression test proving the same scan token cannot be consumed twice.

Acceptance criteria:

- One token produces at most one valid scan pass.
- Concurrent duplicate submissions produce one success and one safe rejection.

### 6.3 Prevent duplicate active movements

Risk: two concurrent checkout submissions may both pass the active-movement check.

Actions:

- Put the active-record check and insert in one transaction.
- Use row locking where possible.
- Add a database-level invariant if a safe MySQL design is practical.
- Add tests for duplicate and concurrent submissions.

Acceptance criteria:

- A student can never have more than one active outside-campus movement.
- Repeated requests are idempotent or safely rejected.

### 6.4 Expand high-risk feature tests

The current 10 tests cover only a small portion of 127 routes.

Minimum release suite:

- Student and admin login success/failure/throttling
- Deleted account and changed-role session invalidation
- Password-reset request, verification, expiry, throttling, and reuse prevention
- All admin roles against allowed and forbidden scopes
- Student import success, malformed workbook, duplicate IC, duplicate registration number, and update behavior
- Scholarship CRUD and authorization
- Offense, fine, evidence, and payment approval workflows
- Vehicle-sticker upload and approval workflows
- QR single-use behavior and movement invariants
- Upload validation and storage behavior
- CSV, PDF, and print endpoints
- English/Malay switching and key validation messages

Acceptance criteria:

- Every privileged module has positive and negative authorization tests.
- Every upload and financial/disciplinary state change has a feature test.
- Release-critical workflows pass in CI or a repeatable local command.

## P1 — Production hardening and acceptance

### 6.5 Create production environment configuration

Required production values include:

```dotenv
APP_ENV=production
APP_DEBUG=false
```

Also verify:

- HTTPS and trusted proxy configuration
- Secure session cookie settings
- Unique production `APP_KEY`
- Production database credentials with least privilege
- Mail transport
- Queue worker and restart policy
- Scheduler/cron configuration
- Log rotation and monitoring
- Cache strategy
- Web-push keys
- AI provider disabled unless privacy approval and a production key exist

Never commit `.env`.

### 6.6 Complete backup and restore rehearsal

Actions:

- Define automatic database backup frequency and retention.
- Include uploaded files under `storage/app/public`.
- Encrypt or access-restrict backups because they contain personal student data.
- Perform a restore into a separate test database.
- Record restoration time and verification results.

Acceptance criteria:

- A fresh environment can be restored from documented backups.
- Restored student counts, admin access, uploads, and core workflows are verified.

### 6.7 Run structured UAT with real roles

Use at least these accounts:

- Student
- Guard
- Scholarship Admin
- Discipline Admin
- Head of Student Affairs
- System Admin

Test on:

- Desktop Chrome/Edge
- Android Chrome
- iPhone Safari/PWA

UAT must cover:

- Login/logout and password reset
- Student profile completion
- Scholarship records/status/announcements
- Offense, payment, and vehicle-sticker workflows
- QR checkout and return
- Role boundaries and forbidden pages
- Notifications
- Exports, print, and PDF
- Malay and English
- Camera denial, expired QR, bad upload, and network interruption

Acceptance criteria:

- All P0/P1 defects are closed.
- Department owners sign off on scholarship, discipline, and movement behavior.

### 6.8 Validate imported student data

The database currently contains 602 students.

Actions:

- Reconcile the database count with the official source.
- Confirm course-code interpretation and whether `Kod Kursus` is sufficient as the displayed program.
- Confirm whether `Kelas`, `Nama Status`, `Jabatan`, and `Sesi Ambilan` need dedicated database fields.
- Verify students can log in using the approved initial credential policy.
- Confirm inactive or industrial-training statuses should have access.

Acceptance criteria:

- Data owner signs off on record count and field mapping.
- No unnecessary personal field is collected.
- Access rules for non-active students are explicitly documented.

## P2 — Maintainability and future features

### 6.9 Refactor `routes/web.php`

Current size: approximately 1,684 lines.

Move inline business workflows gradually into:

- Controllers
- Form Request validators
- Service classes
- Policy or permission helpers

Do not perform a single large rewrite. Refactor one module at a time with tests.

Acceptance criteria:

- Routes primarily declare routing and middleware.
- Business logic is independently testable.
- Behavior remains unchanged.

### 6.10 Consolidate role definitions

Role lists currently appear in middleware, helpers, controllers, and views.

Actions:

- Introduce a central enum or role/access configuration.
- Generate role options and labels from the same source.
- Keep database enum changes synchronized through migrations.

Acceptance criteria:

- Adding a role requires changing one authoritative definition plus its migration.

### 6.11 Decide the AI Helper scope

Recommended near-term decision:

- Keep student AI disabled.
- Keep admin AI read-only.
- Do not send raw student datasets, uploads, credentials, or session payloads to external providers.

Before expansion:

- Complete a privacy review.
- Define prompt/data boundaries.
- Add auditability and human approval for any proposed write.
- Add tests for authorization and data minimization.

## 7. Recommended Execution Sequence

Follow this order:

1. Review and commit the current import-language and Head of Student Affairs changes.
2. Update vulnerable Composer dependencies in a separate commit.
3. Fix QR token concurrency.
4. Fix duplicate active-movement concurrency.
5. Build the P0 feature-test suite.
6. Validate all 602 imported student records with the data owner.
7. Prepare production environment, queues, scheduler, mail, HTTPS, logs, and secure backups.
8. Run role-based desktop/mobile UAT.
9. Fix UAT defects and rerun the complete verification suite.
10. Create a release tag and deployment/rollback record.
11. Refactor route closures only after release blockers are closed.

## 8. Definition of Done

The project reaches 100% production readiness only when:

- All required modules have department-owner sign-off.
- No unresolved P0 defect remains.
- No critical or high-severity dependency advisory remains.
- Production uses `APP_ENV=production` and `APP_DEBUG=false`.
- Authorization tests cover every role and privileged module.
- QR and active-movement concurrency protections are verified.
- Imported student data and status rules are approved.
- Backup and restore have been rehearsed successfully.
- Queue, scheduler, mail, logs, HTTPS, and storage are operational.
- Desktop, Android, and iPhone UAT pass.
- Deployment and rollback steps are documented and tested.
- The release is committed and tagged with a clean working tree.

## 9. Start Here in the Next Development Session

```powershell
git status --short --branch
git diff --check
php artisan test
composer audit --locked
```

First objective: preserve the current verified changes in a checkpoint commit.  
Second objective: remove dependency security blockers without mixing them with feature work.

