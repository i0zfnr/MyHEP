# Operational workflows

## Student onboarding gate

1. Student signs in.
2. The system checks required profile and guardian/household information.
3. Residence status, room number, and current residence during study are optional.
4. The student completes the scholarship-status form.
5. Once mandatory information is complete, normal student modules become available.

## Scholarship and welfare

Scholarship staff review student profiles and scholarship-status submissions, manage scholarship records and announcements, import/export B40 TVET data, and maintain welfare-only records. Welfare views support tracing categories such as OKU and B40 while retaining scoped access to sensitive data.

## Discipline and movement

Authorized staff register offenses and evidence, students review offenses and submit fine-related applications, and discipline officers record decisions and payments. Vehicle-sticker, rules, and announcement workflows are managed in the same scope. Movement uses checkpoints/QR, student check-out and return records, active/outside monitoring, configurable rules, and violation views.

## Laptop borrowing

JHEP creates laptop records and prints four branded QR labels per A4 page. Authorized staff scan a laptop QR to borrow or return it. Loan state and history remain available to authorized administrators.

## Program lifecycle

### Registration

The program director creates a program using structured entry or an approved PDF/DOCX. Attendance-only activities may be keyed in without externally approved paperwork. Coordinates are optional: when latitude and longitude are present, the configured radius in metres enables GPS/geofence validation; without coordinates, attendance works without GPS enforcement.

### Participation setup

The director chooses **Attendance Only** or **Attendance + Questionnaire**. In questionnaire mode, questions may be suggested by AI or written/edited manually, can use written-answer types, and can be marked required. Attendance can open only after the chosen participation setup is ready.

### Attendance and points

Internal Politeknik Besut students use their authenticated account. Public guests use the public check-in page. The roster records attendance validity and responses where applicable. Participation points are configured by the program director and awarded only to eligible internal Politeknik Besut students with valid attendance.

### Report generation

1. Director opens **Program Report Template** in AI Helper.
2. Selects one of their programs.
3. Adds approved paperwork where required and up to eight post-program images by picker or drag-and-drop.
4. Chooses DOCX, PDF, or both.
5. The server combines program, attendance, questionnaire, and uploaded-source data; AI drafts structured content; the exporter fills the official report template and embeds photos.
6. A persistent completion dialog confirms success and offers DOCX/PDF downloads plus links to the report workflow and program.
7. Director reviews names, dates, images, formatting, blank pages, content, and signatures. DOCX can be edited externally and uploaded back.
8. The final file is submitted through the configured organizational review stages and retained under KJ HEP after acceptance.

### Certificates

The director first decides whether the program provides certificates. Programs may award points without a certificate. When certificates are enabled, a design choice and preview precede bulk generation. Eligible internal students are linked by matric number, processing runs in the background, and students see Pending/Generating/Ready/Failed status before downloading a ready certificate. Official certificate artwork remains a separate template asset.

