# QA Report

Audit date: 2026-09-01

## Executive Summary

Overall status: **FAIL**

This checkout is not ready for production. Repository and server-side discovery found 268 non-vendor web routes (188 admin, 40 student, 12 lecturer, and 28 public/shared). The local configuration points to a MySQL database named `myhep`, so destructive browser/CRUD testing was not attempted without an isolated E2E database. The required browser runtime exposed no Chromium instance; consequently no page may be represented as browser-tested, and desktop/mobile screenshots, traces, console monitoring, and network monitoring remain blocked.

One reproducible PWA defect was fixed: `public/sw.js` required `/offline.html` during installation, but the file was absent. Because `cache.addAll()` rejects when a required asset is missing, this could prevent the service worker from installing. A dedicated offline page and regression coverage were added.

- Routes discovered: 268
- Pages browser-tested: 0 (browser unavailable)
- Roles identified: student plus 7 administrative/staff roles
- Browser/device profiles tested: 0
- New focused tests: 2 passed, 13 assertions
- Defects fixed: 1
- Remaining release blockers: browser E2E not executed; full Laravel suite not green; isolated E2E database/accounts not configured

## Environment

- Application: MyHEP / StudentEdge
- Laravel: 13.24.0
- PHP: 8.4.0
- Database: MySQL (`myhep`) for local runtime; SQLite in-memory for PHPUnit
- Node: 24.14.0
- npm: 11.9.0
- Frontend: Blade, Vite 8.2.1, Tailwind CSS 4, application JavaScript
- Authentication: custom session authentication (`auth.session:*` middleware)
- Authorization: role/ability matrix through `EnsureAdminScope`, `AdminPermissions`, feature gates, and lecturer page gates
- Queue: database locally; synchronous in PHPUnit
- Playwright/Chromium: unavailable in the supplied browser runtime

## Roles Tested

Server-side automated coverage exists for:

- Student
- System Admin (`system_admin`)
- Student Affairs Head (`student_affairs_head`)
- Scholarship Admin (`scholarship_admin`)
- Discipline Admin (`discipline_admin`)
- Lecturer (`lecturer`), including discipline/scholarship staff categories
- Guard (`guard`)

No role was tested through a real browser in this run.

## Pages Tested

### Public/shared

- Home/login/password recovery/report-problem: **WARNING** — server-rendered feature coverage exists; browser interactions not executed.
- Manifest, service worker, icons, offline fallback: **PASS** for static integrity after the fix; runtime registration/offline behavior remains unverified.

### Admin and staff

- 188 routes discovered: **WARNING** — authorization and many feature workflows have PHPUnit coverage, but no browser page-by-page pass was possible.

### Student

- 40 routes discovered: **WARNING** — server-side coverage exists for documents, programs, attendance, certificates, Food Bank, settings, and authorization; no browser page-by-page pass was possible.

### Lecturer

- 12 lecturer-prefixed routes discovered, in addition to authorized admin-prefix workflows: **WARNING** — server-side coverage exists; browser interactions not executed.

## Functional Tests

The existing suite exercised account sessions, admin profiles, feature controls, appearance settings, imports, bug reports, Food Bank, laptop borrowing, program management/operations, certificates, surveys, attendance, security headers, staff/guard management, student data permissions, documents, and push administration. The baseline run showed broad feature-level passes but did not finish cleanly and therefore cannot be reported as a passing regression run.

Focused post-fix validation:

- `php artisan test tests/Feature/PwaAssetsTest.php --compact`: **PASS**, 2 tests / 13 assertions
- `npm run build`: **PASS**
- `php artisan view:cache`: **PASS**
- `git diff --check`: **PASS**

## Authentication Tests

Existing PHPUnit coverage confirms several server-side behaviors, including admin email login, rejection of NRIC as the admin identifier, inactive-account rejection, password reset delivery, session revocation, and guest redirects. Browser validation of field behavior, validation presentation, remember-me behavior, logout navigation, and session expiry was not executed.

## Authorization Tests

The application uses backend middleware and an explicit ability matrix rather than navigation hiding alone. Existing tests passed for multiple cross-role restrictions, sensitive student data, document access, Food Bank access, program ownership, and system-only controls during the observed baseline run. Direct URL attempts in Chromium remain required before release.

## Responsive Testing

- Desktop 1920x1080: **NOT RUN**
- Desktop 1440x900: **NOT RUN**
- Desktop 1366x768: **NOT RUN**
- Tablet 768x1024: **NOT RUN**
- Pixel 7: **NOT RUN**
- iPhone 15 equivalent: **NOT RUN**

No responsive readiness claim can be made without browser screenshots and interaction checks.

## PWA Testing

- Manifest JSON parses and includes name, short name, `/` start URL, standalone display, and existing 192/512 icons: **PASS** (automated static check)
- Service-worker registration code exists: **PASS** (source inspection)
- Service-worker pre-cache assets exist: **PASS** after fix
- Offline fallback document exists and provides a keyboard-focusable retry action: **PASS**
- Service-worker install/activate state: **NOT RUN**
- Offline navigation in Chromium: **NOT RUN**
- Standalone safe-area/layout behavior: **NOT RUN**
- Native installation prompt and physical-device installation: **MANUAL TEST REQUIRED**

## Console Errors

Not captured because Chromium was unavailable.

## Network Errors

Not captured because Chromium was unavailable.

## Accessibility Findings

The new offline fallback has a viewport declaration, semantic main heading/content, a named retry button, a 44px minimum target, and a visible focus indicator. Full keyboard navigation, dialog focus management, accessible names, heading structure, and contrast checks across application pages remain unverified.

## Performance Findings

The production Vite build succeeds. It emits a 1.26 MB PDF worker and a 432 KB certificate editor JavaScript asset before compression; these are reasonable candidates for route-level lazy loading review. No browser performance timings or layout-shift observations were captured.

## Bugs Found

### QA-PWA-001 — Service worker install can fail because required offline asset is missing

- Severity: HIGH
- Page: PWA/service worker
- Role: All
- Steps: inspect `STATIC_ASSETS` in `public/sw.js`; request `/offline.html`
- Expected: every `cache.addAll()` dependency exists and offline navigation has a fallback
- Actual: `/offline.html` did not exist
- Root cause: service-worker pre-cache list referenced an untracked/missing public asset
- Fix: added `public/offline.html`
- Regression test: `tests/Feature/PwaAssetsTest.php`
- Status: FIXED (static verification); browser install/offline verification pending

### QA-REG-002 — Full Laravel regression suite is not green

- Severity: HIGH
- Page: AI workspace, floating AI chat, password reset email, system-admin shell
- Role: Admin, lecturer, student
- Steps: run `php artisan test`
- Expected: all tests pass
- Actual: failures were observed in `AdminAiChatboxViewTest`, multiple `AiHelperViewTest` cases, `PasswordResetCodeMailTest`, and `SystemAdminLiquidHeaderTest`; the lengthy run was stopped after it failed to complete in a reasonable audit window
- Root cause: expected UI/mail source contracts have drifted from the current templates; browser behavior is not available to determine whether source or assertions are authoritative
- Fix: none; changing UI or assertions without browser evidence would be unsafe
- Regression test: existing failing tests
- Status: OPEN

### QA-ENV-003 — No isolated browser-test database or dedicated E2E accounts

- Severity: HIGH
- Page: All state-changing workflows
- Role: All
- Expected: destructive CRUD and role-flow tests run against disposable fixtures
- Actual: local runtime uses MySQL database `myhep`; only PHPUnit is isolated through in-memory SQLite
- Fix: none in this run
- Status: OPEN

### QA-ENV-004 — Browser runtime has no available Chromium instance

- Severity: HIGH (QA coverage blocker)
- Page: All
- Role: All
- Expected: Playwright controls a real Chromium browser and records failures
- Actual: browser discovery returned no available browser
- Fix: none available inside the repository
- Status: BLOCKED

## Fixes Applied

- `public/offline.html`: added the service-worker offline fallback
- `tests/Feature/PwaAssetsTest.php`: verifies all pre-cache paths and manifest icons exist and checks core manifest installability fields

## Remaining Issues

- Restore a green full PHPUnit suite and classify each UI contract failure against real browser behavior.
- Configure a disposable E2E database and dedicated accounts for every role.
- Run page-by-page browser coverage across all route groups and record console/network failures.
- Run desktop, tablet, mobile, and standalone PWA workflows with screenshots/traces on failure.
- Verify service-worker registration, activation, cache updates, and offline navigation in Chromium.
- Run accessibility automation plus keyboard/manual checks.
- Run dependency audits separately to completion; the combined baseline command was interrupted before reliable audit totals were produced.

## Manual Tests Required

- Physical-device PWA installation and launch
- Push-notification permission and delivery on supported devices
- Camera/QR and geolocation permission flows
- Email delivery through the configured provider
- Real Gemini/provider behavior without exposing private student data
- Certificate PDF visual accuracy and printing

## Final Result

- Development: **YES**, with the listed blockers visible
- Internal testing: **YES**, only with an isolated test database and browser enabled
- Pilot testing: **NO**
- Production: **NO**

The next QA run should begin by providing an available Chromium browser and a disposable E2E database. It must then execute the requested role-by-role desktop/mobile flows, capture console/network evidence, and finish with a clean Laravel and Playwright regression run.
