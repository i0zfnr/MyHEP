# StudentEdge Panel Coding Q&A Guide

This guide is for answering technical questions from panel members. It explains the coding side of StudentEdge in a simple but accurate way.

## 1. Is This System Using API?

Short answer:

> This system mainly uses Laravel web routes, not a separate REST API. The pages submit forms directly to Laravel routes, and the controllers return Blade views, redirects, CSV downloads, or JSON responses where needed.

Important detail:

- There is no `routes/api.php` file in this project.
- Most routes are inside `routes/web.php`.
- The system still has API-like behavior for some features, for example student search returns JSON using `response()->json(...)`.

Example JSON endpoint:

```text
GET /admin/students/search
```

This route is handled by:

```text
app/Http/Controllers/Admin/StudentController.php
```

Method:

```text
search(Request $request)
```

It returns:

```php
return response()->json(['data' => $students]);
```

Panel answer:

> We did not build a separated REST API because this project is a server-rendered Laravel system. But some parts behave like API endpoints, such as student search, because they return JSON data to the frontend.

## 2. What Architecture Is Used?

StudentEdge uses Laravel MVC.

MVC means:

- Model/data layer: MySQL database tables
- View: Blade templates inside `resources/views`
- Controller: PHP classes inside `app/Http/Controllers`

Simple flow:

```text
Browser request -> Route -> Middleware -> Controller -> Database -> Blade View/Response
```

Example:

```text
/admin/scholarships/b40-tvet
```

Flow:

1. Laravel checks route in `routes/web.php`.
2. Middleware checks whether the admin is logged in and allowed.
3. Controller gets B40 TVET data from database.
4. Blade view displays the data.

## 3. Why Use Laravel?

Panel answer:

> Laravel gives built-in routing, validation, session authentication, middleware, database query builder, Blade templates, and migration support. This makes the system faster to build and easier to maintain.

Laravel features used in this project:

- Routes
- Controllers
- Blade views
- Form validation
- Session authentication
- Middleware authorization
- Query builder using `DB::table(...)`
- Migrations
- File upload validation
- Redirect and flash messages
- CSV download response

## 4. Route Explanation

Routes are written in:

```text
routes/web.php
```

A route connects a URL to a controller function.

Example B40 TVET routes:

```php
Route::get('/admin/scholarships/b40-tvet', [ScholarshipController::class, 'b40Tvet'])
    ->middleware(['auth.session:admin', 'admin.scope:scholarship'])
    ->name('admin.scholarships.b40-tvet');

Route::post('/admin/scholarships/b40-tvet/import', [ScholarshipController::class, 'importB40Tvet'])
    ->middleware(['auth.session:admin', 'admin.scope:scholarship'])
    ->name('admin.scholarships.b40-tvet.import');

Route::get('/admin/scholarships/b40-tvet/export', [ScholarshipController::class, 'exportB40Tvet'])
    ->middleware(['auth.session:admin', 'admin.scope:scholarship'])
    ->name('admin.scholarships.b40-tvet.export');
```

How to explain:

> The GET route displays the page, the POST route receives the uploaded file, and the export route returns a CSV download.

## 5. Controller Explanation

Controllers are the main logic files.

Important controllers:

```text
app/Http/Controllers/Admin/StudentController.php
app/Http/Controllers/Admin/ScholarshipController.php
```

`StudentController.php` handles:

- Student list
- Student search
- Student create
- Student update
- Student delete
- Student import
- Student export

`ScholarshipController.php` handles:

- Scholarship list
- Scholarship CRUD
- B40 TVET page
- B40 TVET import
- B40 TVET export

Panel answer:

> The controller receives the request, validates input, performs database operations, then returns the correct response, either a page, redirect, JSON, or CSV download.

## 6. Validation Explanation

Laravel validation is used to protect the system from bad input.

Example file validation:

```php
$validated = $request->validate([
    'student_file' => ['required', 'file', 'max:10240', 'mimes:csv,txt,xlsx'],
]);
```

Meaning:

- File is required.
- Upload must be a file.
- Maximum file size is 10 MB.
- Only CSV, TXT, or XLSX is accepted.

Panel answer:

> We validate the uploaded file before processing it. This prevents unsupported file types and reduces import errors.

## 7. Authentication And Authorization

Authentication means checking whether user is logged in.

Authorization means checking whether the logged-in user is allowed to access the page.

This system uses:

```text
auth.session:admin
admin.scope:...
```

Role logic is inside:

```text
app/Http/Middleware/EnsureAdminScope.php
```

Example:

```php
$allowed = match ($scope) {
    'scholarship' => ['scholarship_admin', 'system_admin'],
    'discipline' => ['discipline_admin', 'system_admin'],
    'students' => ['scholarship_admin', 'discipline_admin', 'guard', 'system_admin'],
    'movement' => ['guard', 'discipline_admin', 'system_admin'],
    'backoffice' => ['scholarship_admin', 'discipline_admin', 'system_admin'],
    default => ['system_admin'],
};
```

Panel answer:

> Each admin has a role. Middleware checks the role before allowing access. For example, scholarship admin can access scholarship pages, while system admin has wider access.

## 8. Database Coding

This project mostly uses Laravel Query Builder:

```php
DB::table('students')->where('id', $id)->first();
```

Example insert:

```php
DB::table('students')->insert([
    'full_name' => $validated['full_name'],
    'matric_no' => filled($validated['matric_no'] ?? null) ? $validated['matric_no'] : null,
    'ic_no' => $validated['ic_no'],
    'program' => $validated['program'],
    'created_at' => now(),
    'updated_at' => now(),
]);
```

Example update:

```php
DB::table('students')
    ->where('id', $id)
    ->update([
        'full_name' => $validated['full_name'],
        'updated_at' => now(),
    ]);
```

Panel answer:

> We use Laravel Query Builder because it is simple and readable. It also helps avoid manual SQL string building for normal CRUD operations.

## 9. Student Search Coding

Student search is one of the API-like parts.

File:

```text
app/Http/Controllers/Admin/StudentController.php
```

Method:

```text
search(Request $request)
```

What it does:

1. Validate search text.
2. If empty, return empty JSON data.
3. Search by student name or matric number.
4. Return maximum 20 students.
5. Send result as JSON.

Panel answer:

> The search function receives a query, validates it, searches the students table using `LIKE`, and returns JSON data. This is useful for autocomplete or quick lookup.

## 10. Student CRUD Coding

CRUD means:

- Create
- Read
- Update
- Delete

Student CRUD is inside:

```text
app/Http/Controllers/Admin/StudentController.php
```

Methods:

```text
index()
create()
store()
show()
edit()
update()
destroy()
```

How to explain:

> The index method shows the student list. The create and edit methods show forms. The store method inserts new student data. The update method changes existing data. The destroy method deletes the selected student.

Important detail:

> Matric number is optional. The system no longer creates random matric numbers. If matric number exists, it is saved. If it is empty, the database stores null.

## 11. B40 TVET Import Coding

File:

```text
app/Http/Controllers/Admin/ScholarshipController.php
```

Important methods:

```text
importB40Tvet()
readImportRows()
readCsvRows()
readXlsxRows()
importB40Rows()
normalizeHeader()
normalizeSearchText()
```

Flow:

```text
Upload file -> Validate file -> Read CSV/XLSX -> Detect headers -> Filter Politeknik Besut -> Save student -> Save scholarship -> Return summary
```

Panel answer:

> The import function accepts CSV or XLSX. It reads the spreadsheet rows, normalizes the header names, detects columns such as name, IC, program, institution, and matric number, then filters only records where institution contains Politeknik Besut.

## 12. Why Normalize Header?

Different Excel files may use different column names.

Example:

- `NAMA PELAJAR`
- `Nama Pelajar`
- `nama pelajar`
- `NO. KAD PENGENALAN`
- `No IC`

The system normalizes header text so it can still understand the column.

Panel answer:

> Header normalization makes the importer more flexible. It reduces import failure when the source file uses slightly different header names.

## 13. Why Filter By Institution, Not Matric Number?

Reason:

- Source B40 TVET file may not contain real matric number.
- Some existing data had random generated matric numbers.
- Institution name is the correct way to detect Politeknik Besut students from the uploaded file.

Panel answer:

> We filter by institution because the client wants only Politeknik Besut students. Matric number is optional and not reliable in every source file, so the importer uses institution name as the main filter.

## 14. How Duplicate Data Is Handled

The system tries to avoid duplicate student records.

Common matching logic:

- Use IC number because it is unique and reliable.
- Use matric number if it exists.
- If matching student exists, update the existing student.
- If not, insert a new student.

Panel answer:

> The system checks existing student data before inserting. This prevents repeated imports from creating duplicate students.

## 15. B40 Scholarship Record Coding

B40 TVET scholarship records use:

```text
provider_name = SCHOLARSHIP B40 TVET
type = scholarship
status = confirmed
```

The student is connected using:

```text
scholarships.student_id -> students.id
```

Panel answer:

> After the student is saved, the system creates or updates a scholarship record connected to that student using `student_id`.

## 16. CSV Export Coding

CSV export uses the helper:

```text
app/Support/helpers.php
```

Function:

```text
downloadCsv()
```

Example usage:

```php
return downloadCsv(
    'students_' . now()->format('Ymd_His') . '.csv',
    ['ID', 'Nama', 'No Matrik', 'No IC', 'Program'],
    $rows
);
```

Panel answer:

> The export function queries data from MySQL, maps each record into CSV rows, then returns a download response. CSV is used because it can be opened in Excel.

## 17. Audit Log Coding

Some important actions are recorded using:

```text
auditLog()
```

Example:

```php
auditLog('students.import', 'students', null, json_encode($result));
```

Purpose:

- Track admin actions
- Help troubleshooting
- Keep basic system history

Panel answer:

> Import and important admin actions are logged into audit logs so we can know what action happened and when.

## 18. Pagination Coding

Student and scholarship lists use Laravel pagination:

```php
->paginate(15)
->withQueryString();
```

Meaning:

- Show 15 records per page.
- Keep search/filter values when moving to next page.

Panel answer:

> Pagination prevents the page from loading too much data at once. It also makes the interface more compact and easier to use.

## 19. Why Use CSV/XLSX Import Instead Of Manual Key In?

Panel answer:

> The client has around 2000 to 3000 student records. Manual key in is slow and easy to make mistakes. Import allows the system to process the data automatically and consistently.

## 20. Error Handling

The import uses validation exceptions when file cannot be read.

Example:

```php
throw ValidationException::withMessages([
    'student_file' => 'Fail tidak dapat dibaca. Sila upload CSV atau XLSX yang sah.',
]);
```

Panel answer:

> If the uploaded file is invalid, empty, or cannot be read, the system returns a validation message instead of crashing.

## 21. Security Points To Mention

Use these points if panel asks about security:

- Admin pages require login session.
- Middleware checks admin role.
- File upload validates file type and size.
- Form inputs are validated before database insert/update.
- Blade output is escaped by default using `{{ }}`.
- Passwords are handled separately from normal profile data.
- Important admin actions are recorded in audit logs.

Panel answer:

> Security is handled using login session, role middleware, input validation, file validation, and audit logs.

## 22. Performance Points To Mention

For 2000 to 3000 rows:

- This is a small to medium import size for Laravel/MySQL.
- Pagination prevents showing all rows at once.
- Search uses database filtering.
- CSV export streams a downloadable response.

Panel answer:

> The system can handle the expected 2000 to 3000 records because it reads the file server-side, saves to MySQL, and uses pagination when displaying data.

Possible future improvement:

> For very large files, we can improve it with queue jobs or chunked import.

Frontend performance answer:

> The shared layout separates lightweight scrolling content from rich transient overlays. Content cards avoid per-card pointer tracking and mobile backdrop filters, while notifications, dialogs, media previews, and filter sheets use the heavier glass treatment only while they are open. This keeps student pages responsive on mobile devices.

## 23. If Panel Asks Why No Full REST API

Answer:

> The current requirement is an internal admin system, so Laravel web routes are enough. A full REST API is useful when we need mobile apps or external systems to consume the data. This system can be extended later by adding `routes/api.php` and API controllers.

Possible future API examples:

```text
GET    /api/students
POST   /api/students/import
GET    /api/scholarships/b40-tvet
GET    /api/scholarships/b40-tvet/export
```

## 24. If Panel Asks How To Build API Later

Steps:

1. Create `routes/api.php`.
2. Create API controller, for example `app/Http/Controllers/Api/StudentApiController.php`.
3. Return JSON instead of Blade view.
4. Use token authentication if accessed by external systems.
5. Keep validation in controller or form request.
6. Reuse database logic where possible.

Example concept:

```php
public function index(Request $request)
{
    $students = DB::table('students')
        ->select('id', 'full_name', 'matric_no', 'ic_no', 'program')
        ->paginate(15);

    return response()->json($students);
}
```

Panel answer:

> To convert this into API, we would add API routes and return JSON responses. The database logic can mostly be reused, but authentication should use API token security.

## 25. Coding Files To Show If Asked

Open these files:

```text
routes/web.php
app/Http/Controllers/Admin/StudentController.php
app/Http/Controllers/Admin/ScholarshipController.php
app/Http/Middleware/EnsureAdminScope.php
app/Support/helpers.php
resources/views/admin/students/index.blade.php
resources/views/admin/scholarships/b40_tvet.blade.php
database/migrations/2026_07_20_120000_allow_nullable_student_matric_no.php
StudentEdge.sql
```

## 26. Fast Technical Answers To Memorize

Question: What framework is used?

Answer: Laravel, using MVC architecture.

Question: Where are routes stored?

Answer: `routes/web.php`.

Question: Where is the import logic?

Answer: `ScholarshipController.php` for B40 TVET and `StudentController.php` for student import.

Question: Does the system use API?

Answer: It mainly uses web routes. Some routes return JSON, like student search, but there is no separate REST API module yet.

Question: How does the system read Excel?

Answer: It reads CSV directly and reads XLSX by opening the XLSX file structure, then extracting sheet rows and shared strings.

Question: How does the system export to Excel?

Answer: It exports CSV, which can be opened in Excel.

Question: How is Politeknik Besut detected?

Answer: The importer checks the institution column and matches text containing `POLITEKNIK BESUT`.

Question: What if matric number is missing?

Answer: It stays blank/null. The system does not generate random matric number.

Question: What is the main student table?

Answer: `students`.

Question: How is scholarship connected to student?

Answer: `scholarships.student_id` references `students.id`.

Question: How is access controlled?

Answer: Admin session authentication and `EnsureAdminScope` middleware.

Question: How do you prevent invalid file upload?

Answer: Laravel validates file type, file existence, and file size before processing.

Question: How do you prevent duplicate students?

Answer: The import checks existing records using IC number and matric number where available before inserting.

## 27. Simple Code Explanation Script

Use this if asked to explain the code live:

> First, I open `routes/web.php` to show the route. The route points to a controller method. For example, B40 TVET import points to `importB40Tvet` in `ScholarshipController`. Inside that method, Laravel validates the uploaded file, reads the rows, and passes them to the import function. The import function checks the institution field. If it contains Politeknik Besut, it saves the student into the `students` table and creates a scholarship record in the `scholarships` table. The result is shown back on the page, and the export button uses `downloadCsv` to generate a CSV file.

## 28. One-Minute Technical Summary

StudentEdge is a Laravel MVC system. It uses web routes in `routes/web.php`, controllers for logic, Blade files for interface, and MySQL for storage. Admin access is controlled by session middleware and role scope middleware. Student and scholarship data are handled using Laravel Query Builder. The B40 TVET feature imports CSV/XLSX, detects headers, filters rows by `POLITEKNIK BESUT`, saves student records, creates scholarship records, and exports the result to CSV for Excel.
