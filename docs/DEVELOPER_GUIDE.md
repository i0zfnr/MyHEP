# Developer guide

## Stack

- Laravel 13 / PHP 8.3+
- MySQL or MariaDB through Laravel database configuration
- Blade, Vite 8, Tailwind CSS 4, project design-system CSS, and progressive JavaScript
- PHPWord 1.3 for DOCX; Dompdf 3.1 for PDF
- Laravel queue tables/jobs, Resend mail, Minishlink Web Push

## Structure

- `routes/web.php`: web routes and several legacy inline workflow handlers.
- `app/Http/Controllers`: module controllers.
- `app/Http/Middleware`: session authentication, scoped authorization, feature flags, localization, security headers, and session tracking.
- `app/Support/AdminPermissions.php`: ability-to-role matrix.
- `app/Services/AiProvider.php`: server-side provider abstraction.
- `app/Services/ProgramReportContent.php`: validated AI report structure and deterministic fallback.
- `app/Services/OfficialProgramReportExporter.php`: official-template replacement, photo sheet, DOCX creation, edited-DOCX handling, and PDF conversion.
- `app/Jobs/GenerateProgramCertificate.php`: background certificate generation.
- `resources/views`: server-rendered pages and shared layouts.
- `resources/css/design-system.css` and `resources/css/app.css`: shared visual rules and application styles.
- `lang/en.json` and `lang/ms.json`: UI catalogues.
- `database/migrations`: authoritative schema evolution.
- `tests/Feature` and `tests/Unit`: regression suite.

## Data domains

Core identities use `users` plus account sessions and reset codes. Student-affairs domains include scholarships/status forms, offenses and evidence, student documents, movement/checkpoints/settings, JHEP laptops/loans/staff, programs/paperwork/reviewers/attendance/surveys/questions/responses/reports/certificates, AI conversations/messages, notifications/subscriptions, feature and system settings, bug reports, and audit logs.

Never infer the current schema from an old SQL export; replay and inspect migrations.

## Program report implementation

`ProgramOperationController::generateReport` validates access and inputs, gathers stored program records and temporary uploads, asks `AiProvider` for structured content, passes the result through `ProgramReportContent`, and exports requested files through `OfficialProgramReportExporter`. The response stores file paths on the program report and flashes a `generated_report` payload. AI Helper renders this payload as a persistent completion dialog with authorized download URLs.

AI does not control the document layout. The backend template/exporter owns layout; AI supplies bounded content. A deterministic fallback keeps the workflow available when AI is disabled or returns unusable output. Generated files are drafts and require human review.

## Localization

Wrap user-visible server text in `__()`, add values to both JSON catalogues, and translate stored enums at display time rather than changing stored values. Protect script/style content during automated catalogue work. Run route/localization tests and render relevant pages after changes.

## Frontend behavior

Reusable pages should use design-system tokens so each account role receives its accent color. Native nested scrolling must be preserved for dialogs, tables, file drop zones, scanners, forms, and other `[data-lenis-prevent]` regions. Respect reduced-motion preferences.

## Adding a feature

1. Define authorization and ownership rules.
2. Add migration/model changes.
3. Add controller/service behavior and named routes.
4. Build Blade/UI with both locales and responsive states.
5. Add feature and unit tests, including unauthorized cases.
6. Update relevant documentation and operational requirements.

