# StudentEdge Project Progress and Next Steps

Last updated: 2026-08-02

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

## 3. Current role and privacy model

| Ability | Roles |
| --- | --- |
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

The current focused test set covers active-session management, student-data permission boundaries, private documents, admin permissions, identity masking, admin profile photos, Head of Student Affairs access, and the virtualized movement feed. Manual mobile/PWA and storage-isolation checks are still required.

## 5. P0 release blockers

### Dependency security

The 2026-08-02 `composer audit --locked` run reported 28 advisories affecting 10 locked packages, including high-severity Laravel/Symfony findings. Update vulnerable dependencies in a dedicated change and repeat the complete verification suite. Do not mix dependency upgrades with unrelated feature work.

### Movement concurrency

QR validation/rotation and active-movement creation still require transaction/locking guarantees and concurrency-focused tests. A student must not consume one scan token twice or create two active outside-campus records.

### Production configuration

Production requires HTTPS, `APP_ENV=production`, `APP_DEBUG=false`, secure cookies, least-privilege database credentials, protected secrets, mail, queue worker supervision, scheduler, logging, backups, correct trusted proxies, and writable storage.

### Private-file operations

Confirm `storage/app/private/student_documents` is writable by PHP, excluded from web serving, included in encrypted backups, and restored with metadata/file consistency. Test ownership and role boundaries with real files.

## 6. P1 acceptance and hardening

- Run UAT with Student, Guard, Scholarship Admin, Discipline Admin, Head of Student Affairs, and System Admin accounts.
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
