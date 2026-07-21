# StudentEdge Team Presentation Guide

This file is made for team members who need to explain how the system is built, how the code works, and how the database connects to the features.

The system is a Laravel web application for Student Affairs. The main modules are:

- Student management
- Scholarship management
- SCHOLARSHIP B40 TVET import and export
- Discipline and offense management
- Announcements
- Movement and guard checking
- Admin management

## 1. Simple System Explanation

StudentEdge helps admin manage student affairs data in one system. Before this, staff need to search manually inside Google Sheet or Excel. Now, admin can upload student data, the system reads the file, filters the needed data, saves it into the database, and allows admin to export the data back to CSV.

For SCHOLARSHIP B40 TVET, the important automation is:

1. Scholarship admin uploads CSV or Excel file.
2. System reads all rows in the file.
3. System checks the institution column.
4. Only rows that contain `POLITEKNIK BESUT` are accepted.
5. System saves or updates the student record.
6. System creates or updates the scholarship record.
7. Admin can export the filtered B40 TVET data to CSV.

Important note: matric number is not generated automatically anymore. If the uploaded file has matric number, the system saves it. If the file does not have matric number, the system leaves it blank.

## 2. Technology Used

- PHP: Main backend language
- Laravel: Web framework
- MySQL: Database
- Blade: Laravel view/template files
- HTML/CSS/JavaScript: Frontend interface
- Vite/npm: Frontend build tool
- CSV/XLSX import: Used to read uploaded spreadsheet files

## 3. How To Run The System

Use this setup when presenting or installing on a local computer.

1. Install Laragon, XAMPP, or another local PHP/MySQL server.
2. Put the project folder inside:

```text
C:\laragon\www\e-biasiswa
```

3. Open terminal inside the project folder.
4. Install PHP dependencies:

```bash
composer install
```

5. Install frontend dependencies:

```bash
npm install
```

6. Copy `.env.example` to `.env` if `.env` does not exist.
7. Set database details inside `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=StudentEdge
DB_USERNAME=root
DB_PASSWORD=
```

8. Generate Laravel app key:

```bash
php artisan key:generate
```

9. Create the database in MySQL named `StudentEdge`.
10. Import `StudentEdge.sql` using phpMyAdmin, or run migrations:

```bash
php artisan migrate
```

11. Build frontend assets:

```bash
npm run build
```

12. Start the system:

```bash
php artisan serve
```

13. Open:

```text
http://127.0.0.1:8000
```

If the page has styling issues during development, run:

```bash
npm run dev
```

## 4. Laravel Code Structure

Laravel uses MVC:

- Model means database data.
- View means page design.
- Controller means logic that receives request, processes data, and returns a view.

In this project, most database work uses Laravel `DB::table(...)` queries instead of full Eloquent models.

Important folders:

```text
routes/web.php
```

Stores all web page routes. A route connects a URL to a controller function.

```text
app/Http/Controllers
```

Stores controller code. Controllers handle actions like list data, create data, import file, export CSV, update record, and delete record.

```text
app/Http/Controllers/Admin
```

Stores admin-side controllers, for example student, scholarship, movement, maintenance, and admin user controllers.

```text
resources/views
```

Stores Blade files. Blade files are the HTML pages shown to users.

```text
database/migrations
```

Stores database structure changes. Migrations are used to create or modify database tables.

```text
StudentEdge.sql
```

SQL backup/schema file that can be imported into MySQL.

```text
app/Http/Middleware
```

Stores middleware. Middleware checks access before the user enters a page.

## 5. Request Flow Example

Example: admin opens the B40 TVET page.

1. Browser goes to `/admin/scholarships/b40-tvet`.
2. `routes/web.php` matches the route.
3. Route calls `ScholarshipController::b40Tvet`.
4. Controller gets data from the database.
5. Controller sends data to `resources/views/admin/scholarships/b40_tvet.blade.php`.
6. Blade file displays the page.

Example: admin imports B40 TVET file.

1. Admin chooses CSV/XLSX file.
2. Form sends file to `/admin/scholarships/b40-tvet/import`.
3. Route calls `ScholarshipController::importB40Tvet`.
4. Controller validates the file type.
5. Controller reads the spreadsheet rows.
6. Controller detects header names such as student name, IC number, program, institution, amount, and optional matric number.
7. Controller only accepts rows where institution contains `POLITEKNIK BESUT`.
8. Controller saves the student data into `students`.
9. Controller saves the scholarship data into `scholarships`.
10. System redirects back with import summary.

## 6. Important Routes

Student management:

```text
GET    /admin/students
POST   /admin/students
POST   /admin/students/import
GET    /admin/students/export
GET    /admin/students/{id}
GET    /admin/students/{id}/edit
PUT    /admin/students/{id}
DELETE /admin/students/{id}
```

B40 TVET scholarship:

```text
GET  /admin/scholarships/b40-tvet
POST /admin/scholarships/b40-tvet/import
GET  /admin/scholarships/b40-tvet/export
```

Scholarship records:

```text
GET    /admin/scholarships
POST   /admin/scholarships
GET    /admin/scholarships/export
GET    /admin/scholarships/{id}/edit
PUT    /admin/scholarships/{id}
DELETE /admin/scholarships/{id}
```

## 7. Important Controllers

```text
app/Http/Controllers/Admin/StudentController.php
```

Used for student CRUD:

- View student list
- Search student
- Filter by matric number
- Add student
- Edit student
- Delete student
- Import student from CSV/XLSX
- Export student to CSV

```text
app/Http/Controllers/Admin/ScholarshipController.php
```

Used for scholarship CRUD and B40 TVET:

- View scholarship records
- Add scholarship
- Edit scholarship
- Delete scholarship
- Import B40 TVET file
- Export B40 TVET data to CSV

```text
app/Http/Middleware/EnsureAdminScope.php
```

Used to control which admin role can access which module.

## 8. Important Views

```text
resources/views/admin/students/index.blade.php
```

Student management page. It has compact content, search, matric number filter, import, export, CRUD actions, and previous/next pagination.

```text
resources/views/admin/students/create.blade.php
resources/views/admin/students/edit.blade.php
```

Forms for adding and editing student data.

```text
resources/views/admin/scholarships/b40_tvet.blade.php
```

SCHOLARSHIP B40 TVET page. It has file upload, import summary, B40 TVET data table, filters, and export button.

```text
resources/views/layouts/app.blade.php
```

Main layout and sidebar navigation.

It also owns the responsive student shell: a full-width dashboard, a sticky sidebar for module pages, mobile bottom navigation, and shared notification, confirmation, media-preview, and filter popup behavior.

## 9. Database Overview

The database name is normally:

```text
StudentEdge
```

Main tables:

### students

Stores student profile data.

Important columns:

- `id`
- `full_name`
- `matric_no`
- `ic_no`
- `email`
- `program`
- `phone`
- `residence_status`
- `room_number`
- `password`
- `created_at`
- `updated_at`

Important rule: `matric_no` can be blank/null. The system does not generate random matric number anymore. If import file has matric number, it will be saved. If not, it stays blank.

### admins

Stores admin login accounts.

Important columns:

- `id`
- `full_name`
- `ic_no`
- `password`
- `role`
- `created_at`
- `updated_at`

Common admin roles:

- `system_admin`
- `scholarship_admin`
- `discipline_admin`
- `guard`

### scholarships

Stores scholarship records for students.

Important columns:

- `id`
- `student_id`
- `type`
- `provider_name`
- `amount`
- `status`
- `created_at`
- `updated_at`

Relationship: `scholarships.student_id` connects to `students.id`.

For B40 TVET, `provider_name` is saved as:

```text
SCHOLARSHIP B40 TVET
```

### student_scholarship_status_forms

Stores student scholarship status form answers.

### scholarship_announcements

Stores announcements created by scholarship admin.

### offenses

Stores student discipline/offense records.

Relationship: `offenses.student_id` connects to `students.id`.

### offense_types

Stores types or categories of discipline offenses.

### fine_payment_applications

Stores student applications related to fine payment.

### rules and rule_categories

Stores discipline rules shown to students/admins.

### student_movements

Stores student movement records for out/in tracking.

### vehicle_sticker_applications

Stores vehicle sticker applications by students.

### audit_logs

Stores important admin actions such as import, delete, update, or reset password.

## 10. Database Relationship Explanation

One student can have many scholarship records:

```text
students.id -> scholarships.student_id
```

One student can have many offense records:

```text
students.id -> offenses.student_id
```

One student can have many movement records:

```text
students.id -> student_movements.student_id
```

One student can have vehicle sticker applications:

```text
students.id -> vehicle_sticker_applications.student_id
```

This relationship is useful because the system can show complete student history from one student record.

## 11. SCHOLARSHIP B40 TVET Import Logic

The B40 TVET page is made to solve the manual Ctrl+F problem.

Before:

- Staff open Google Sheet or Excel.
- Staff search `Politeknik Besut` manually.
- Staff copy the matching student data.

Now:

- Staff upload the file.
- System reads the file automatically.
- System finds rows where institution contains `POLITEKNIK BESUT`.
- System saves matching students into the database.
- System creates scholarship record automatically.
- Staff can export the result as CSV.

Fields normally read from the file:

- Student name
- IC number
- Program
- Institution
- Matric number, if available
- Amount, if available

The import does not depend on matric number because some source files do not have real matric number. IC number is more reliable for matching student identity.

## 12. Student Management Import Logic

Student Management also supports CSV/XLSX import.

Purpose:

- Admin can import student list in bulk.
- Admin does not need to key in each student one by one.

Rules:

- If the file has matric number, system saves it.
- If the file does not have matric number, system leaves it blank.
- System does not create random matric number.
- Room number is not forced during import.
- Student can later fill room number manually in their profile.

## 13. CSV Export Explanation

CSV export is used because it can be opened using Microsoft Excel.

When admin clicks export:

1. Controller gets data from database.
2. Controller formats the data into CSV rows.
3. Browser downloads the CSV file.
4. User can open it in Excel.

The export includes a UTF-8 BOM so names with Malaysian characters are more likely to open correctly in Excel.

## 14. Admin Access Control

The system uses admin role and middleware to control access.

Example:

- `scholarship_admin` can access scholarship pages and student list.
- `discipline_admin` can access discipline pages and student management.
- `guard` can access movement-related pages and student list.
- `system_admin` can access system management and admin management.

The access logic is handled in:

```text
app/Http/Middleware/EnsureAdminScope.php
```

## 15. How To Add A New Page

Basic Laravel steps:

1. Add route in `routes/web.php`.
2. Add controller method in the correct controller.
3. Add Blade view in `resources/views`.
4. Add sidebar link in `resources/views/layouts/app.blade.php` if needed.
5. Test in browser.

Example flow:

```text
Route -> Controller -> Database -> View
```

## 16. How To Add A New Database Field

Example: add `guardian_phone` to students.

1. Create migration:

```bash
php artisan make:migration add_guardian_phone_to_students_table
```

2. Edit migration file inside `database/migrations`.
3. Run migration:

```bash
php artisan migrate
```

4. Add validation in controller.
5. Add input field in Blade form.
6. Save the field during create/update.
7. Display the field on list or detail page if needed.

## 17. How To Explain The Code During Presentation

Use this simple explanation:

> This system uses Laravel MVC. The route receives the URL request, the controller processes the logic and database query, then the Blade view displays the result to the user. For import, the controller validates the uploaded file, reads CSV/XLSX rows, filters only Politeknik Besut data, then saves students and scholarship records into MySQL.

For database:

> The main table is students. Other modules connect to students using `student_id`, for example scholarships, offenses, movements, and vehicle sticker applications. This makes one student record reusable across many modules.

For security:

> Admin pages are protected by session authentication and role-based middleware. Each admin role only sees pages allowed for that role.

## 18. Presentation Role Suggestion For 3 People

Person 1: System overview and problem statement

- Explain manual Google Sheet problem.
- Explain why StudentEdge was built.
- Explain main modules.
- Show login and dashboard.

Person 2: Scholarship and student management demo

- Show Student Management.
- Show search by matric number.
- Show import CSV/XLSX.
- Show B40 TVET page.
- Upload file and explain only Politeknik Besut rows are imported.
- Export CSV and open in Excel.

Person 3: Code and database explanation

- Explain Laravel MVC.
- Show `routes/web.php`.
- Show `StudentController.php`.
- Show `ScholarshipController.php`.
- Explain `students` and `scholarships` tables.
- Explain `student_id` relationship.
- Explain role access middleware.

## 19. Common Panel Questions And Answers

Question: How does the system know which data is Politeknik Besut?

Answer: The import function reads the institution column and checks whether the text contains `POLITEKNIK BESUT`. Only matching rows are imported.

Question: What happens if the same student already exists?

Answer: The system checks existing student data using IC number and matric number where available. If the student exists, the system updates the record instead of creating unnecessary duplicate data.

Question: What happens if the Excel file has no matric number?

Answer: The system leaves matric number blank. It does not generate random matric number anymore.

Question: Why use IC number?

Answer: IC number is more reliable because every student should have a unique IC number, while the source file may not always contain matric number.

Question: Can the system handle 2000 to 3000 rows?

Answer: Yes. 2000 to 3000 rows is a normal size for CSV/XLSX import. The system reads the file, filters the matching rows, and saves them into MySQL.

Question: Why export CSV?

Answer: CSV is simple, lightweight, and can be opened directly in Microsoft Excel.

Question: How is the database connected?

Answer: Laravel connects to MySQL using the settings inside `.env`. Code uses Laravel database queries such as `DB::table('students')` and `DB::table('scholarships')`.

Question: How is access controlled?

Answer: Admin access is controlled using session login and middleware. The middleware checks the admin role before allowing access to a page.

Question: Where is the B40 TVET code?

Answer: The main code is in `app/Http/Controllers/Admin/ScholarshipController.php` and the page design is in `resources/views/admin/scholarships/b40_tvet.blade.php`.

Question: Where is the student CRUD code?

Answer: The main code is in `app/Http/Controllers/Admin/StudentController.php` and the page is in `resources/views/admin/students/index.blade.php`.

## 20. Troubleshooting

Problem: Page keeps loading or blank page.

Try:

```bash
php artisan view:clear
php artisan route:clear
php artisan config:clear
```

Then check:

```text
storage/logs/laravel.log
```

Problem: Database table missing.

Try:

```bash
php artisan migrate
```

If using imported SQL, make sure the database name in `.env` matches the database in MySQL.

Problem: CSS or JavaScript not updating.

Try:

```bash
npm run build
```

or during development:

```bash
npm run dev
```

Problem: Session table missing.

Try:

```bash
php artisan migrate
```

or check `SESSION_DRIVER` inside `.env`.

Problem: Import does not find Politeknik Besut rows.

Check:

- File is CSV or XLSX.
- Header row contains institution column.
- Institution cell contains text similar to `POLITEKNIK BESUT`.
- File is not password protected.

## 21. Files To Open During Presentation

Open these files if the panel asks about code:

```text
routes/web.php
app/Http/Controllers/Admin/StudentController.php
app/Http/Controllers/Admin/ScholarshipController.php
app/Http/Middleware/EnsureAdminScope.php
resources/views/admin/students/index.blade.php
resources/views/admin/scholarships/b40_tvet.blade.php
database/migrations
StudentEdge.sql
```

## 22. Final Presentation Checklist

Before presentation:

- Run `php artisan migrate`.
- Run `npm run build`.
- Run `php artisan view:clear`.
- Login using admin account.
- Prepare one sample B40 TVET CSV/XLSX file.
- Prepare one sample student CSV/XLSX file.
- Test B40 TVET import.
- Test B40 TVET export.
- Test student search by matric number.
- Test student create, edit, and delete.
- Open phpMyAdmin and show `students` and `scholarships` tables.

## 23. Short Summary To Memorize

StudentEdge is a Laravel and MySQL system for student affairs. It uses MVC architecture where routes call controllers, controllers process database logic, and Blade views display pages. The B40 TVET feature reads uploaded CSV/XLSX files, filters only Politeknik Besut students by institution name, stores student data and scholarship records in the database, and exports the result to CSV for Excel. Student matric number is optional and is only saved if it exists in the uploaded file.
