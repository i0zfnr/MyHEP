# Testing and user acceptance

## Automated validation

Run the sequential Laravel suite on Windows to avoid concurrent Blade-cache rename failures:

```powershell
php artisan view:clear
php artisan test
npm run build
php artisan route:list
```

The 37 test files cover authentication/session behavior, permissions, student data, profile and appearance, scholarship imports, staff/guard administration, movements, laptop borrowing, documents, programs, questionnaires, attendance, reports, AI feature controls, notifications, dashboards, localization, and security headers.

## Critical UAT journeys

### Student

- Incomplete profile/status form blocks normal use but optional residence fields do not.
- Student can see programs, submit attendance according to participation mode, and view points.
- Certificate status is visible and only Ready records download.
- Private documents and records cannot be opened by another student.

### Program director

- Create both approved-paperwork and attendance-only programs.
- Save participation mode; questionnaire editor appears only when required.
- Open/close attendance with and without optional coordinates.
- Drag/drop multiple report images, generate DOCX/PDF, and confirm the completion dialog remains until dismissed or navigated.
- Open generated DOCX and PDF; review every page for blank pages, image placement, content, and signatures.
- Submit an edited report through each configured reviewer stage.
- Generate certificates only when enabled and confirm matric-linked status/download.

### Scholarship/welfare and discipline

- Scholarship administrator sees required sensitive detail but cannot mutate/export students beyond scope.
- Welfare filters/categories and B40 TVET import/export produce correct records.
- Discipline workflows correctly enforce registration, decisions, payment, movement, points, and exports.

### System administration

- Feature flags and session lifetime apply correctly.
- Staff/guard/admin management denies unauthorized roles.
- Monitoring, maintenance, notifications, localization, light/dark themes, mobile layout, pagination, and nested scrolling work.

## Release evidence

Record test/build results, migration status, queue health, role-based UAT sign-off, a backup/restore check, and sample DOCX/PDF/certificate output. A generated file opening successfully is not enough; visually inspect all pages.

