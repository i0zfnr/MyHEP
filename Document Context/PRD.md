# StudentEdge Product Requirements

Last updated: 2026-07-23

## Product Summary

StudentEdge is a centralized student-affairs system for Politeknik Besut. It gives students one place to manage scholarship, discipline, fine, vehicle, profile, and campus-movement tasks while giving authorized staff role-specific administrative control.

## Product Goals

- Replace fragmented student-affairs records with one auditable system.
- Let students complete common tasks from a phone without visiting an office unnecessarily.
- Give scholarship, discipline, guard, and system administrators only the workflows relevant to their role.
- Support large student datasets through import, search, filtering, pagination, and export.
- Preserve privacy, accountability, and reliable operational records.
- Provide a bilingual, responsive, installable web experience.

## Users

| User | Primary needs |
| --- | --- |
| Student | View status, submit forms and evidence, scan movement QR, update profile, receive decisions |
| Scholarship admin | Manage scholarship records, B40 TVET data, announcements, and student submissions |
| Discipline admin | Manage offenses, rules, evidence, fines, stickers, announcements, and movement issues |
| Guard | Operate and review campus movement workflows |
| System admin | Manage accounts, configuration, monitoring, maintenance, and full operational access |

## Required Capabilities

### Shared

- Login, logout, forgot-password verification, and password reset
- English and Malay locales
- Light and dark themes
- In-app notifications and optional browser push
- Responsive web and PWA support
- Problem reporting and accessible account settings

### Student

- Dashboard with urgent status and key actions
- Profile completion and password change
- Scholarship-status submission, records, and announcements
- Offense and fine review, printing, and receipt submission
- Vehicle-sticker application and status
- Rules and discipline announcements
- Campus checkout/return through QR with movement history

### Administration

- Role-scoped dashboards and navigation
- Student CRUD, search, pagination, CSV/XLSX import, and CSV export
- Scholarship CRUD, B40 TVET import/export, and announcement management
- Offense creation, evidence, fine decisions, printing, and PDF output
- Vehicle-sticker decisions
- Movement QR, checkpoint, curfew, violation, export, and guard views
- Administrator management, maintenance, monitoring, reports, bug reports, and audit logging

## Key Functional Rules

- Every protected request must require a valid current account session.
- Admin capabilities must be enforced server-side by role scope.
- Students may access only their own records and submissions.
- Critical administrative decisions and destructive actions must be auditable.
- Uploaded evidence must be validated by type and size.
- Imports must validate headers and rows, report failures clearly, and avoid silent partial corruption.
- Password-reset codes must expire, be attempt limited, and be consumed once.
- Privileged role-mode switching must require a verified linked account and server-side authorization.

## Non-Functional Requirements

### Security

- CSRF protection, password hashing, session regeneration, rate limiting, input validation, and authorization are mandatory.
- Production must use HTTPS, `APP_DEBUG=false`, protected environment secrets, and restricted upload execution.
- IC-number fallback is temporary and should be replaced by forced password enrollment when operationally possible.

### Performance

- Student lists must be paginated and queried through indexed searchable fields.
- A 50,000-student deployment must not render or load the full dataset in one request.
- Large imports should use chunked processing and, for production scale, queued jobs with progress reporting.
- Mobile scrolling must avoid heavy filters and animated effects on repeated content cards.

### Accessibility and UX

- Interactive targets are at least 44 by 44 CSS pixels.
- Core workflows work with keyboard navigation and visible focus.
- Text supports Dynamic Type-like browser scaling and long English/Malay content.
- Reduced-motion and reduced-transparency preferences are respected.

### Reliability

- Database backups and restore tests are required before production releases.
- Destructive bulk operations require explicit scope and confirmation.
- Movement and payment decisions should use transactions where concurrent writes can conflict.

## Success Criteria

- Students can complete each primary workflow on a 375-412px phone viewport.
- Role users cannot access routes outside their authorized scope.
- Import, search, export, and pagination remain usable with at least 50,000 student records.
- Critical actions appear in audit logs.
- Production build, view compilation, migrations, and automated tests pass before deployment.

## Out of Scope Until Reviewed

- Autonomous AI write actions
- Sending full student datasets or private uploads to external AI services
- Native iOS or Android clients
- Direct integration with the institution database without an agreed synchronization contract

See `docs/UAT_CHECKLIST.md` for acceptance testing and `docs/SYSTEM_DOCUMENTATION.md` for full module details.
