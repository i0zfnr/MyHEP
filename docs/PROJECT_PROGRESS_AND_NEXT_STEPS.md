# StudentEdge Project Progress and Next Steps

Last updated: 2026-08-13

## 1. Executive status

StudentEdge has a broad working feature baseline for student affairs, plus recent hardening for role permissions, active sessions, identity privacy, private student documents, feature controls, profile-photo cropping, and large movement feeds. It is suitable for continued UAT, but it is not yet documented as production-ready.

The codebase remains a modular Laravel monolith. `StudentEdge.sql` is still required as the original schema baseline, with `database/migrations` providing incremental changes.

## 2. Newly completed capabilities

- Central `AdminPermissions` ability map with dedicated student list, sensitive-data, export, management, and document abilities
- Guard access limited to student list/search, with masked IC numbers and no student mutation/export/detail permission
- Active-device registry in `account_sessions`, current-device display, single-session revocation, and revoke-all-other-sessions
- Account session invalidation when student/admin records are deleted or privileged account state changes
- Private `student_documents` storage, student-owned downloads, admin filters, approval/rejection, expiry states, and audited downloads
- Scholarship offer letters linked into the Document Centre and reviewed from scholarship administration
- `document_centre` feature availability controlled by System Admin and enforced by middleware
- Notification categories for scholarship and discipline events
- Admin profile-photo upload with crop, zoom, rotate, reset, preview, and validation
- Cursor-paginated, incrementally fetched, virtualized movement history with bounded DOM rendering
- Wheel-only Lenis smoothing with native touch/nested scrolling and reduced-motion handling
- PWA install prompt limited to the student/admin dashboards
- Audited inventory of 160 named application functions: 23 public/shared, 21 student, and 116 admin
- Lecturer role with per-account offense page controls and limited AJAX student lookup
- AJAX-only offense student picker and compact, AJAX-filtered movement monitoring
- Configurable session lifetime and normalized student program identifiers
- Monthly operational analytics UI and System Admin push test/maintenance broadcast controls
- Resend API mail transport for password-reset codes plus a throttled System Admin email-delivery test with reference-based error logging
- Dedicated lecturer/JHEP staff and guard account management with active-state enforcement, scoped page access, audited changes, and session revocation
- Transactional QR laptop borrowing/return with inventory and loan history
- Partial dashboard visualization: one responsive 3D-styled summary graph and a persistent toggle are implemented, but graph mode currently leaves the metric cards visible
- Discipline Admin-style aggregate dashboard and Discipline Monthly Analytics access for every Lecturer, while retaining individual offense page controls
- Lightweight accent-led Monthly Report KPI cards, six-month trends, status donuts,
  compact empty states, dark-theme surfaces, and flat A4 print fallbacks; decorative
  corner curves and the extra module-heading container were removed
- Restored default violated-rule catalogue through an idempotent migration for installations missing the original SQL seed
- Debounced automatic AJAX filtering for Student List, Offense List, and Vehicle Sticker, complementing the existing asynchronous Student Movement feed
- Name, matric-number, and permission-aware NRIC lookup across the requested student-related administration lists
- High-transparency light-theme sidebar text contrast correction without changing the liquid design
- Program Management workspace UI alignment with Liquid Glass tokens, 44px touch targets, light/dark theme badge styling, and role-restricted accent color authorization (Student and System Admin only)
- English/Malay catalogue synchronization across 100 Blade templates, incremental
  unresolved-value translation tooling, reviewed terminology overrides, and zero
  missing literal keys or placeholder mismatches in the 2026-08-14 audit

## 3. Current role and privacy model

| Ability | Roles |
| --- | --- |
| Limited student lookup and enabled offense pages | Lecturer |
| Scholarship | Scholarship Admin, Head of Student Affairs, System Admin |
| Discipline | Discipline Admin, Head of Student Affairs, System Admin |
| Movement | Guard, Discipline Admin, Head of Student Affairs, System Admin |
| Student list/search | Scholarship Admin, Discipline Admin, Head of Student Affairs, Guard, System Admin |
| Sensitive student detail | Discipline Admin, Head of Student Affairs, System Admin |
| Student export/manage | Discipline Admin, Head of Student Affairs, System Admin |
| Document review | Head of Student Affairs, System Admin |
| System controls | System Admin |

All route permissions are server-side. Identity masking and hidden navigation improve privacy but do not replace middleware authorization.

## 4. Verification baseline

Before release, rerun and record:

```powershell
php artisan test
php artisan route:list --except-vendor
php artisan migrate:status
php artisan view:cache
npm run build
composer audit --locked
git diff --check
```

The 2026-08-09 working-tree baseline is 98 passing tests with 325 assertions. It covers active sessions, student-data permission boundaries, lecturer lookup/offense access, staff/guard management, laptop borrowing, private documents, admin permissions, identity masking, admin profile photos, Head of Student Affairs access, movement feeds, monthly-report empty states, program normalization, System Admin push controls, branded password-reset mail rendering/delivery, and email-test validation. Manual mobile/PWA, real Web Push delivery, production-domain email delivery, and storage-isolation checks are still required.

## 5. P0 release blockers

### Dependency security

The 2026-08-02 `composer audit --locked` run reported 28 advisories affecting 10 locked packages, including high-severity Laravel/Symfony findings. Update vulnerable dependencies in a dedicated change and repeat the complete verification suite. Do not mix dependency upgrades with unrelated feature work.

### Movement concurrency

QR validation/rotation and active-movement creation still require transaction/locking guarantees and concurrency-focused tests. A student must not consume one scan token twice or create two active outside-campus records.

### Production configuration

Production requires HTTPS, `APP_ENV=production`, `APP_DEBUG=false`, secure cookies, least-privilege database credentials, protected secrets, a verified Resend sender domain, queue worker supervision, scheduler, logging, backups, correct trusted proxies, and writable storage.

### Private-file operations

Confirm `storage/app/private/student_documents` is writable by PHP, excluded from web serving, included in encrypted backups, and restored with metadata/file consistency. Test ownership and role boundaries with real files.

## 6. P1 acceptance and hardening

- Run UAT with Student, Lecturer, Guard, Scholarship Admin, Discipline Admin, Head of Student Affairs, and System Admin accounts.
- Test desktop Chrome/Edge, Android Chrome, and iPhone Safari/PWA.
- Verify active-session labels/revocation, role-mode changes, and deleted-account behavior.
- Verify student document ownership, offer-letter replacement/removal, rejection notes, expiry filters, feature disabling, and audited downloads.
- Expand positive/negative authorization tests across every privileged route.
- Reconcile imported student counts and fields against the approved institutional source.
- Rehearse database plus public/private upload backup and restoration in an isolated environment.
- Confirm English/Malay UI and validation messages for new document/session screens.

## 7. P2 maintainability

- Move the remaining large closures from `routes/web.php` into controllers, Form Requests, services, and policies in small tested changes.
- Move all role labels/options as well as abilities toward one authoritative role definition.
- Add cleanup for expired account-session records and orphaned uploads.
- Add database constraints where movement and other invariants require uniqueness.
- Keep admin AI read-only and student AI disabled until privacy, prompt, permission, and audit boundaries are approved.

## 8. Planned functions, not implemented

### Dashboard visualizations and display toggle — revision required

- Current state: Admin and Lecturer dashboards render one responsive 3D-styled horizontal summary graph. Every Lecturer receives the same aggregate discipline/movement overview presentation as Discipline Admin, while protected page links still follow individual controls.
- Current defect: enabling visualization adds that graph above the complete metric-card grid. The 2026-08-10 screenshots `201342` and `201348` confirm that cards and graph remain visible together.
- Required behavior: the toggle must switch between mutually exclusive Card and Graph modes. Card mode displays the existing metric cards and no dashboard graphs. Graph mode hides those metric cards and replaces them with a multi-graph analytics layout.
- Graph mode should provide several complementary views, for example a six-month offense trend, offense/payment status distribution, movement activity comparison, and unresolved-workload chart. Use only role-authorized aggregate data, avoid redundant charts, retain useful zero-data states, and preserve the saved per-account preference.
- The replacement layout must remain responsive, readable in light/dark themes, keyboard and screen-reader understandable, reduced-motion safe, and usable by every Lecturer without broadening offense/student page permissions.

### Discipline UI and automatic list search — implemented 2026-08-10

- An idempotent migration restores the 23 default `offense_types` rows when an installation is missing the original SQL seed. Existing matching rows are retained, and the migration does not remove referenced rules on rollback.
- Student List, Offense List, and Vehicle Sticker now update result regions through debounced asynchronous requests without Filter/Reset buttons. Pagination remains asynchronous and stale requests are aborted. Student Movement retains its cursor-based asynchronous feed and automatic search.
- Offense status remains a dropdown and updates results immediately. Name and matric searches are supported throughout; NRIC search is available only where the signed-in role has sensitive-student permission.

### Light-theme sidebar readability — implemented 2026-08-10

The light-theme sidebar now switches to stronger text/icon colors when liquid transparency is 70% or higher. The application slider currently has a safe maximum of 80%, so this covers the reported near-maximum condition while preserving the slider behavior, layout, and liquid material.

### Password change behavior confirmed from the current implementation

- Student: while signed in, the Student Profile form accepts the current password plus a confirmed new password of at least eight characters. It changes the password directly without an email verification code and revokes the student's other active sessions.
- Admin and Lecturer: the current Admin Profile supports account information and profile-photo management but has no self-service password-change form. If the user uses Forgot Password, the shared recovery flow requires the emailed six-digit verification code before accepting a new password.
- Administrative reset: an authorized account manager can set or reset admin/lecturer passwords from account management without sending the user a verification code. This is an administrator-controlled action, not the user's own in-system change flow.

Violated-rules restoration, automatic list searches, sidebar contrast correction, Lecturer aggregate analytics access, and the initial 3D visualization are implemented. The dashboard visualization mode still requires the card-to-multiple-graphs replacement described above. The admin/lecturer self-service password-change form also remains planned.

### iPayment receipt authenticity verification

Receipt QR/OCR inspection alone cannot prove authenticity. Automated iPayment verification is only a planned investigation until the institution provides an official API, a documented/verifiable QR contract, or another approved source of truth. Until then, uploaded receipts remain subject to human review and the system must not label them authentic automatically.

### Future AI expansion

Any student-facing AI or AI-assisted write action requires privacy review, data minimization, explicit authorization, human approval, logging, and focused tests. Full student datasets, credentials, session payloads, and private uploads must not be sent to external providers.

## 9. Definition of done

- All P0 defects are closed and dependency audit findings are resolved or formally accepted.
- Role owners sign off on scholarship, discipline, movement, document, and system behavior.
- Automated authorization tests cover every privileged module in both allowed and forbidden cases.
- Private and public files survive a rehearsed encrypted backup/restore.
- Desktop and mobile UAT pass in Malay and English.
- Queue, scheduler, mail, HTTPS, logs, storage, and rollback procedures are operational.
- Release verification passes, the working tree is clean, and the release is tagged.
