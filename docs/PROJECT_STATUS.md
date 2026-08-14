# Project status

Baseline date: **14 August 2026**.

## Implemented

- Role-scoped student-affairs portal and student self-service.
- Student profile/status completion gate and welfare-oriented information.
- Scholarship/B40, discipline, movement, vehicle, document, laptop, notification, monitoring, settings, and account-management modules.
- Program ownership, attendance-only or questionnaire participation, optional geofence, public/internal attendance, responses, internal-student points, AI-assisted official reports, staged review, and matric-linked certificate records.
- Official program-report DOCX/PDF generation with multiple source images, drag-and-drop, external DOCX editing/upload, and persistent completion confirmation.
- English/Bahasa Melayu catalogues, responsive light/dark role-accent UI, and automated regression coverage.

## Known limitations and review requirements

- Generated program reports remain drafts. The reviewed AKJ PDF opens, but pages 2, 3, 5, and 9 appear blank/mostly blank and require confirmation against the official template before submission.
- DOCX generation now produces an openable package, but page-by-page visual QA requires Microsoft Word or LibreOffice in the validation environment.
- Official certificate artwork/templates are not final. The system can store the design choice/preview state and process eligible records, but final institutional certificate assets must be supplied and approved.
- AI provider availability, quotas, privacy terms, and billing are external operational dependencies. Do not include unnecessary sensitive student data in prompts.
- Some large legacy workflows remain as route closures in `routes/web.php`; future refactoring should move them into tested controllers/services without changing authorization.

## Recommended next work

1. Correct/confirm intentional blank pages in the official report template and add golden-file visual regression samples.
2. Finalize approved certificate designs, signatures, serial-number rules, and revocation/reissue handling.
3. Complete production UAT for every role and device size.
4. Review dependency/security audit results and production provider configuration.
5. Gradually refactor legacy route closures and add missing failure-path tests.

