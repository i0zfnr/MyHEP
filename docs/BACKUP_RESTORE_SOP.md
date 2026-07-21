# Backup & Restore SOP

## Backup (MySQL)
1. `mysqldump -u root -p StudentEdge > StudentEdge_backup_YYYYMMDD.sql`
2. Store backup in secure location (off-server copy).
3. Verify SQL file is not empty and contains tables.

## Restore
1. Create database if needed: `CREATE DATABASE StudentEdge ...`.
2. Import: `mysql -u root -p StudentEdge < StudentEdge_backup_YYYYMMDD.sql`
3. Run smoke checks:
- admin login
- student login
- dashboard loads
- one list page per module loads

## Backup Frequency
- Daily before working hours.
- Mandatory before each deployment.
