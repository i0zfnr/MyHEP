# System overview

## Purpose

StudentEdge provides one authenticated workspace for student-affairs operations. It reduces repeated data entry, preserves auditable records, and connects student self-service with authorized staff workflows.

## Account types

- **Student**: profile, scholarship-status form, programs, attendance, questionnaire responses, points, certificates, offenses, applications, movement, documents, announcements, and AI Helper.
- **Lecturer/staff**: permitted reporting tools, program management, offense registration, laptop use, and explicitly enabled pages.
- **Scholarship administrator**: scholarship, welfare, scholarship-status, and sensitive student information needed for B40, OKU, guardian, and welfare work.
- **Discipline administrator**: offenses, fines, vehicle stickers, movements, participation points, relevant student data, and exports.
- **Head of Student Affairs**: broad student-affairs oversight and account management.
- **System administrator**: platform configuration, accounts, features, monitoring, maintenance, and all operational scopes.
- **Guard**: movement and checkpoint duties plus permitted student lookup and laptop operations.

## Functional modules

1. Authentication, password recovery, locale, theme, sessions, notifications, and support reports.
2. Student records, profile completion, guardian and household information, imports, and protected documents.
3. Scholarship records, B40 TVET import/export, status forms, welfare records, and announcements.
4. Discipline offenses, evidence, payments and applications, rules, announcements, and vehicle stickers.
5. Student movement, checkpoint QR, active/outside lists, violations, and movement settings.
6. JHEP laptop inventory, printable QR labels, borrowing, returns, and loan monitoring.
7. Program registration, approved-paperwork storage, attendance modes, questionnaires, geofence option, responses, points, reports, approvals, and certificates.
8. AI Helper conversations and an official program-report workflow using program data and uploaded source material.
9. Dashboards, cards/graphs, monthly reports, active sessions, monitoring, maintenance, feature controls, and audit-oriented administration.

## Architecture

The browser uses server-rendered Blade views enhanced with JavaScript and CSS. Laravel controllers and route closures apply session authentication and scoped middleware. Eloquent/query-builder operations persist data in MySQL/MariaDB. Private downloads are served through authorized controller routes. Queue workers perform scalable background work. External AI, mail, and push providers are accessed server-side.

## User Experience & Professional Iconography
- **Professional Vector Iconography:** The system enforces a cohesive, high-definition SVG vector icon system across student, lecturer, and admin interfaces, maintaining crisp visual clarity on high-DPI and mobile displays.
- **Role-Based Theming:** Light and Dark mode support with customized, institutional color tokens tailored to role hierarchies.
- **Progressive Web App (PWA):** Mobile-first interfaces with haptic responses, offline-ready shell, and hardware-accelerated QR scanning.

## Application scale

At this baseline the application registers 220 routes: 151 under `/admin`, 31 under `/student`, 7 under `/lecturer`, with remaining public and account routes. The migration history creates 38 framework and domain tables, and the automated suite contains 37 test files.

