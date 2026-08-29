# StudentEdge

StudentEdge is a Laravel 13 student-affairs platform for Politeknik Besut. It combines student records, scholarship and welfare management, discipline, student movement, JHEP laptop loans, program attendance, questionnaires, participation points, certificates, AI-assisted reporting, notifications, and operational administration.

## Current baseline

- PHP 8.3+, Laravel 13, MySQL/MariaDB
- Python 3 with PyMuPDF and OpenCV for one-time certificate-template cleaning
- Vite 8 and Tailwind CSS 4
- PHPWord for editable DOCX reports and Dompdf for PDF output
- Queue-backed bulk certificate generation and email/push integrations
- English and Bahasa Melayu user interfaces
- 220 registered routes and 37 automated test files as of 14 August 2026

## Local setup

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
php artisan migrate
npm install
npm run build
php artisan storage:link
python -m pip install pymupdf opencv-python-headless numpy
```

For development, run `composer run dev`. A queue worker must be running for queued work such as bulk certificate generation.

## Validation

```powershell
php artisan view:clear
php artisan test
npm run build
php artisan route:list
```

Never commit `.env`, production credentials, generated private reports, student documents, or database backups.

## Documentation

Start at [docs/README.md](docs/README.md). The documentation is source-aligned and split into system overview, access control, workflows, developer architecture, operations, testing, and current status.
