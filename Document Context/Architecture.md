# StudentEdge Architecture

Last updated: 2026-07-23

## Purpose

This file is the compact technical context for StudentEdge. The authoritative implementation is the codebase; update this document whenever a change alters a major boundary, request flow, role, or integration.

## System Shape

StudentEdge is a server-rendered Laravel 13 application using PHP 8.3+, Blade, MySQL/MariaDB, Vite 8, and Tailwind CSS 4. It is a modular monolith: student and administration workflows share one Laravel deployment and one relational database.

```text
Browser / installed PWA
        |
        v
Laravel routes + middleware
        |
        v
Controllers and route actions
        |
        +----> MySQL / MariaDB
        +----> public storage uploads
        +----> mail password-reset codes
        +----> Web Push subscriptions
        +----> PDF and CSV exports
```

## Main Layers

| Layer | Location | Responsibility |
| --- | --- | --- |
| Routing | `routes/web.php` | Public, student, admin, settings, notification, and export endpoints |
| Middleware | `app/Http/Middleware` | Session authentication, role scope, locale, and translated frontend content |
| Controllers | `app/Http/Controllers` | Request validation, workflow coordination, persistence, and responses |
| Support | `app/Support` | Shared helpers, audit behavior, role-mode session behavior, and utility code |
| Views | `resources/views` | Blade pages, shared layout, partials, and responsive navigation |
| UI system | `resources/css/design-system.css` | Shared tokens, themes, Liquid Glass materials, responsive layout, and motion |
| Client behavior | `resources/js/app.js` and layout scripts | PWA registration, notifications, menus, dialogs, and mobile interactions |
| Persistence | `StudentEdge.sql` and `database/migrations` | Original schema plus incremental schema changes |

## Request Flow

1. A browser request enters `routes/web.php`.
2. Middleware restores locale and checks `session('auth_user')` for protected routes.
3. Role middleware verifies the current account and its database-backed permissions.
4. A controller or route action validates input and performs the workflow.
5. Data is read or written through Laravel's database layer.
6. A Blade view, redirect, JSON response, CSV, or PDF is returned.
7. Critical operations write an `audit_logs` record.

## Authentication and Authorization

- Authentication is custom and session based, not Laravel Breeze, Jetstream, or Sanctum.
- Students normally authenticate with matric number and either a custom password hash or temporary IC-number fallback.
- Admins authenticate with an identifier and hashed password.
- Protected requests revalidate that the account still exists.
- Admin access is constrained by scholarship, discipline, movement, back-office, or system scope.
- A linked system-admin/student account may switch role mode; this is privileged behavior and must never be inferred from client input alone.
- CSRF protection, rate limiting, password hashing, upload validation, and audit logging must remain enabled.

## Major Modules

- Student dashboard and profile
- Scholarship status, records, announcements, and B40 TVET import/export
- Discipline offenses, evidence, rules, announcements, fines, and receipts
- Vehicle sticker applications and decisions
- Campus movement, QR checkpoints, GPS/curfew settings, and guard workflows
- Student and administrator management
- Notifications, browser push, reports, monitoring, maintenance, and audit logs
- Language, theme, account settings, and optional role-mode controls

## Frontend Architecture

The shared shell is `resources/views/layouts/app.blade.php`. Student mobile pages use a sticky topbar, sticky Current Page header, bottom navigation, and overlay sidebar. Student module pages retain a desktop sidebar, while the desktop student dashboard uses the full workspace. Admin pages retain the desktop sidebar.

Transient surfaces may use strong glass materials. Scrolling content cards stay lightweight to preserve mobile performance. All interactive controls should meet a 44px minimum touch target.

## External and Operational Boundaries

- Uploaded files use Laravel's public storage disk.
- Password-reset delivery depends on configured mail transport.
- Browser push uses VAPID configuration and `minishlink/web-push`.
- PDF output uses DOMPDF.
- Queue tables exist; production workers are required for queued work.
- PWA behavior depends on `manifest.webmanifest`, `sw.js`, and HTTPS outside localhost.

## Known Architecture Debt

- `routes/web.php` still contains large inline workflow closures.
- Feature-test coverage is limited relative to the number of business workflows.
- Movement token use and active movement creation need stronger transaction/locking guarantees.
- The root SQL dump remains necessary because migrations do not fully reconstruct every original table from an empty database.

See `docs/SYSTEM_DOCUMENTATION.md` for the expanded system description and `PROJECT_HANDOFF.txt` for handoff details.
