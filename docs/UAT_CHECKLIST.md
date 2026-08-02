# StudentEdge UAT Checklist

Last updated: 2026-08-02

Record tester, account/role, browser/device, build commit, date, evidence, and defect ID for every run.

## Authentication and sessions

- [ ] Student default/custom password and admin login succeed; wrong credentials and throttling behave safely.
- [ ] Password reset expiry, attempt limits, one-time consumption, and audit behavior work.
- [ ] Logout invalidates the current tracked session.
- [ ] Settings lists the current and other devices with useful labels and activity time.
- [ ] A user can revoke one other session and all other sessions, but cannot revoke the current session through the single-device action.
- [ ] Deleted accounts and admin role changes invalidate affected sessions.

## Role boundaries and privacy

- [ ] Scholarship Admin can use scholarship and student list/search, but not sensitive detail/export/manage/documents/system.
- [ ] Discipline Admin can use discipline, movement, sensitive student detail/export/manage, but not documents/system.
- [ ] Guard can use movement and student list/search only; IC is masked and detail/export/mutation is forbidden.
- [ ] Head of Student Affairs can use scholarship, discipline, movement, sensitive students, management/export, and documents, but not system administration.
- [ ] System Admin can use all registered abilities and feature controls.
- [ ] Students cannot access admin routes or another student's records/files.

## Student workflows

- [ ] Dashboard, profile/password, scholarships, announcements, offenses/fines, vehicle sticker, rules, and movement history work.
- [ ] QR scan works in supported mobile browsers; denial, invalid token, expired pass, GPS failure, duplicate checkout, and return paths are clear.
- [ ] Scholarship declaration requires an offer letter when applicable and supports allowed PDF/image types up to the configured limit.
- [ ] Student Document Centre shows only owned files with correct status/expiry and authenticated downloads.
- [ ] Disabling `document_centre` blocks the route and removes/hides the entry appropriately.

## Administration

- [ ] Student CRUD/import/search/export respects each separate student ability.
- [ ] Scholarship CRUD, B40 import/export, announcements, declarations, and offer-letter download work.
- [ ] Offense/evidence/fine, receipt decision, vehicle sticker, rules, and discipline announcement workflows work.
- [ ] Document filters/counts, private download, approval, rejection-with-required-note, and already-reviewed conflict behavior work.
- [ ] Feature toggle changes are audited and take effect server-side.
- [ ] Profile photo crop, zoom, rotate, reset, cancel/apply, upload validation, replacement, and fallback work.
- [ ] Critical create/update/delete/reset/decision/download actions write appropriate audit logs.

## UI, performance, and accessibility

- [ ] Malay/English and light/dark work on desktop Chrome/Edge, Android Chrome, and iPhone Safari/PWA.
- [ ] Keyboard focus, labels, errors, touch targets, reduced motion, and reduced transparency remain usable.
- [ ] Notifications, filters, dialogs, tables, scanner, cropper, and other nested regions retain native scrolling.
- [ ] Main mouse-wheel scrolling is smooth without changing native touch behavior.
- [ ] Long movement feeds load near the bottom, preserve ordering, show errors/retry, and keep a bounded DOM without frame drops.
- [ ] PWA install prompt appears only on the student/admin dashboards.

## Operations and failure paths

- [ ] CSV/PDF/print endpoints, notification categories, push subscription, mail, queue, and scheduler work.
- [ ] Missing private file returns a safe error without revealing a path.
- [ ] Network interruption, duplicate submission, invalid upload, and stale review state do not silently corrupt data.
- [ ] Backup/restore rehearsal includes database, public uploads, and private documents.
- [ ] Department owners sign off and every P0/P1 defect is closed before release.
