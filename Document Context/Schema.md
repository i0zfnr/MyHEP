# StudentEdge Data Schema

Last updated: 2026-08-09

## Sources of Truth

- `StudentEdge.sql` contains the original tables and seed structure.
- `database/migrations` contains later tables and column changes.
- The live database and migration status must be checked before deployment; the migration set alone may not recreate the complete original schema.

## Core Relationship Map

```text
students
  |-- scholarships
  |-- student_scholarship_status_forms
  |-- student_documents
  |-- offenses --< offense_items >-- offense_types
  |       |-- offense_evidence_photos
  |       `-- fine_payment_applications
  |-- vehicle_sticker_applications
  `-- student_movements

admins
  |-- scholarship_announcements
  |-- discipline_announcements
  |-- rules
  |-- offenses
  |-- jhep_laptop_loans --< jhep_laptops
  `-- administrative decisions and audit records

movement_checkpoints --< student_movements >-- movement_types
rule_categories --< rules
```

## Domain Tables

| Table | Purpose | Important relationships |
| --- | --- | --- |
| `students` | Student identity, authentication, program, contact, residence, guardian, and profile data | Parent of most student workflows |
| `admins` | Administrator/staff identity, password, role, profile, staff category, and active state | Referenced by administrative records, laptop loans, and decisions |
| `scholarships` | Scholarship, sponsorship, welfare, or no-scholarship records | Belongs to `students` |
| `student_scholarship_status_forms` | Student-submitted scholarship status declaration | Belongs to `students` |
| `scholarship_announcements` | Scholarship notices and links | Optionally associated with an admin |
| `offense_types` | Offense classification lookup | Many-to-many with offenses through `offense_items` |
| `offenses` | Student summons, status, amount, evidence, and issuer data | Belongs to student and admin |
| `offense_items` | Offense/type junction records | Belongs to offense and offense type |
| `offense_evidence_photos` | Additional offense evidence files | Belongs to offense |
| `fine_payment_applications` | Student receipt submission and admin decision | Belongs to offense and student |
| `vehicle_sticker_applications` | Vehicle, license, permission, plate, status, and decision data | Belongs to student; decision may reference admin |
| `rule_categories` | Rule grouping lookup | Parent of rules |
| `rules` | Campus rule content | Belongs to category; may reference updating admin |
| `discipline_announcements` | Discipline notices | Optionally associated with an admin |
| `movement_checkpoints` | QR checkpoint configuration and token state | Referenced by movement records |
| `movement_types` | Checkout/return movement types | Referenced by movement records |
| `movement_settings` | Curfew, GPS, and movement configuration | Operational singleton/configuration data |
| `student_movements` | Checkout, return, GPS, residence, plate, late status, and explanation | Belongs to student, checkpoint, and movement type |
| `student_documents` | Private document metadata, source, review state, expiry, and storage path | Belongs to student; review may reference admin |
| `jhep_laptops` | JHEP laptop asset, QR token, availability, and active state | Parent of laptop loan history |
| `jhep_laptop_loans` | Staff borrowing and return timestamps | Belongs to a laptop and an admin/staff account |

## Platform Tables

| Table | Purpose |
| --- | --- |
| `password_reset_codes` | Reset identity, hashed/controlled code state, attempts, expiry, verification, and consumption |
| `push_subscriptions` | Browser endpoint and Web Push subscription keys |
| `push_notification_markers` | Idempotency markers for event-driven push delivery |
| `system_settings` | System-level operational settings such as session lifetime |
| `bug_reports` | User problem reports and optional screenshots |
| `audit_logs` | Actor, action, target, metadata, IP, and timestamp trace |
| `sessions` | Database-backed Laravel sessions |
| `account_sessions` | Public device handle, owner, active role/account, user agent, IP, and activity timestamps |
| `system_features` | Feature key, enabled state, and last updating admin |
| `cache`, `cache_locks` | Laravel cache and locks |
| `jobs`, `job_batches`, `failed_jobs` | Queue state and failures |

## Identity and Access Notes

- Student identity is anchored by the student row and commonly addressed by `matric_no`; migrations permit a nullable matric number for incomplete imports.
- Student IC numbers are sensitive and currently participate in temporary default-password fallback behavior.
- Admin role values include `lecturer`, `scholarship_admin`, `discipline_admin`, `student_affairs_head`, `guard`, and `system_admin`; lecturer accounts additionally use a controlled staff category and active state.
- A privileged linked-role feature may associate a student session with an admin identity; authorization must be resolved from trusted database/session state.

## Integrity Expectations

- Foreign-key columns should be indexed.
- Matric number, IC number, email, and other identifiers need explicit uniqueness rules based on institutional data policy.
- Monetary values use fixed-precision decimal columns, never floating point.
- Status fields must use a documented controlled vocabulary and validated transitions.
- `student_documents` status is `pending`, `approved`, or `rejected`; category is `letters`, `receipts`, `scholarship`, `official_notices`, or `other`.
- Private documents must remain on the `student_documents` disk; replacing or deleting metadata must also handle the file.
- `system_features.feature_key` is unique and must match the application registry.
- One-time reset codes and QR tokens need atomic consumption.
- Laptop QR processing locks the asset and active loan rows so two staff members cannot borrow the same laptop concurrently.
- A student should not gain duplicate active movement rows through concurrent requests.

## Scale Guidance

For approximately 50,000 students:

- Index the identifiers used by login, search, joins, filters, and sorting.
- Use database pagination and bounded result sets.
- Import in chunks and upsert using stable institutional identifiers.
- Track import batches so test data can be removed safely as one batch.
- Run large imports in a queue for production and report progress outside the request timeout.

## Migration Workflow

1. Create a migration for every schema change.
2. Back up the target database.
3. Run `php artisan migrate:status`.
4. Test the migration and rollback against a non-production copy.
5. Update this file and the expanded system documentation.
6. Deploy with `php artisan migrate --force` only after backup and verification.
