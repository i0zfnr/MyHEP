# StudentEdge Backup and Restore SOP

Last updated: 2026-08-02

## Backup scope

Back up these as one consistent recovery set:

- MySQL/MariaDB database
- Public uploads under `storage/app/public`
- Private documents under `storage/app/private/student_documents`
- Release commit/tag and a secure inventory of required environment variables

Never place real backups in Git. Encrypt them, restrict access, keep an off-server copy, and apply the institution's personal-data retention policy.

## Database backup

```powershell
mysqldump -u root -p --single-transaction --routines --triggers StudentEdge > StudentEdge_backup_YYYYMMDD_HHMM.sql
```

Verify the command exit status, non-zero file size, expected table names, checksum, encryption, and off-server copy. Back up before migrations/deployments and on the approved daily schedule.

## Restore rehearsal

1. Create an isolated database and storage target; never overwrite production during a rehearsal.
2. Restore the SQL dump and both upload trees with their original relative paths.
3. Configure an isolated `.env`, run `php artisan optimize:clear`, then apply only migrations required by the restored release.
4. Verify row counts, migration state, public upload display, and private-file URL isolation.
5. Smoke-test student/admin login, active sessions, role boundaries, Document Centre ownership/review, scholarship offer letters, movement, exports, and audit logs.
6. Record checksum, restore duration, missing/orphaned files, tester, date, and approval.

## Recovery acceptance

- Database metadata and both file stores refer to the same backup point.
- A student cannot download another student's document.
- Private files are downloadable only through authenticated application routes.
- The queue, scheduler, mail, and storage permissions work after recovery.
- Recovery time and recovery point meet the institution's approved targets.
