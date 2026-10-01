# MyHEP Backup and Recovery

## What the system backs up

MyHEP queues encrypted ZIP archives to the configured Google Drive folder. Database archives are created hourly; full archives are created daily and include the MySQL/MariaDB dump plus persistent files under `storage/app/private`, `storage/app/public`, and `storage/app/certificate-templates` when those folders exist. Temporary backup output, QA folders, `vendor`, `node_modules`, build output, logs, `.env`, and Git metadata are not included. The backup does not contain application source code; keep the production release and its matching source revision in Git/deployment storage.

Google Drive contains `Database/` and `Full/` folders. The retention policy keeps hourly database archives for 72 hours, daily points for 30 days, weekly points for 8 weeks, then monthly points for up to 12 months. Expired Drive archives are permanently deleted to reclaim quota. Cleanup does not run for a folder with fewer than two archives, and Spatie's default strategy also preserves the newest archive. Cleanup activity is recorded in the Laravel log.

## Environment configuration

Set these values in the production `.env` through Ryaze's secure environment editor or SSH. Keep the file outside Git and never copy the values into frontend/Vite variables. After deploying the code, apply the new additive run-history migration with `php artisan migrate --force`, then refresh Laravel's configuration cache using the site's normal deployment process.

| Variable | Purpose |
| --- | --- |
| `GOOGLE_DRIVE_CLIENT_ID` | OAuth client ID for the dedicated backup integration |
| `GOOGLE_DRIVE_CLIENT_SECRET` | OAuth client secret |
| `GOOGLE_DRIVE_REFRESH_TOKEN` | Server-side refresh token with Drive access |
| `GOOGLE_DRIVE_FOLDER_ID` | ID of the dedicated parent folder in Google Drive |
| `BACKUP_ARCHIVE_PASSWORD` | Strong password of at least 32 characters used for AES-256 ZIP encryption |
| `DB_DUMP_BINARY_PATH` | Directory containing the production `mysqldump` executable; leave empty if it is on `PATH` |
| `DB_DUMP_TIMEOUT` | Maximum database dump duration in seconds; default `3600` |
| `BACKUP_QUEUE_RETRY_AFTER` | Queue lease in seconds; default `18000` |
| `BACKUP_QUEUE_CRON_FALLBACK` | `true` to start a short-lived queue worker each minute; set `false` if using a persistent worker |
| `BACKUP_MIN_FREE_DISK_MB` | Free-space reserve maintained before a backup starts; default `512` |

Configure Google Drive before entering the values:

1. In Google Cloud Console, create a project and enable the Google Drive API.
2. Configure the OAuth consent screen and create an OAuth client for a server-side web application. This integration requests `https://www.googleapis.com/auth/drive` because it must find the configured folder, create/list files, verify uploaded files, and delete expired archives. Google classifies that as a restricted scope; public use can require OAuth verification and a security assessment. Because this grants broad access to the OAuth account's Drive, use a dedicated backup Google account with no unrelated files if possible. For a private MyHEP integration, use an internal Workspace app if the institution has a Workspace organization, or use the personal-use flow and understand its unverified-app consent screen. [Google Drive scope guidance](https://developers.google.com/workspace/drive/api/guides/api-specific-auth)
3. Create a dedicated `MyHEP Backups` folder in the Drive account used for OAuth. Copy its folder ID into `GOOGLE_DRIVE_FOLDER_ID`; the integration creates `Database/` and `Full/` below it.
4. Authorize the OAuth client for the Drive account that owns or can manage that folder. The authorization must request offline access so Google issues a refresh token. Google says refresh tokens issued while an external OAuth app is in Testing expire after seven days; move the production integration to an appropriate published/internal state before relying on automatic backups. [Google offline OAuth guidance](https://developers.google.com/identity/protocols/oauth2/web-server), [Google publishing status and token expiry](https://support.google.com/cloud/answer/15549945?hl=en)
5. Put the client ID, client secret, refresh token, and folder ID into Ryaze's server-side environment, then open the admin backup page to check connection status.

Keep the refresh token and archive password in a password manager or an organization secret vault as well as on the host. The recovery copy must be accessible if Ryaze is lost.

Also preserve the production `APP_KEY`, database connection details, and any Firebase, mail, payment, AI, and Web Push credentials needed to restore the application. Do not rotate `APP_KEY` during a restore unless you have a planned data re-encryption migration.

## Scheduler and queue on Ryaze

Laravel must run `schedule:run` once per minute. This project schedules the hourly database backup, daily full backup, daily retention cleanup, and—when enabled—the queue fallback. The scheduler uses `Asia/Kuala_Lumpur` from the current project configuration.

If Ryaze exposes cPanel Cron Jobs, create one entry with this command shape and an every-minute schedule:

```cron
* * * * * cd /home/CPANEL_USER/ABSOLUTE_MYHEP_PATH && /usr/local/bin/ea-php84 artisan schedule:run >> /dev/null 2>&1
```

Replace `CPANEL_USER`, `ABSOLUTE_MYHEP_PATH`, and the PHP executable with the values shown by the Ryaze account. cPanel documents PHP CLI commands using `/usr/local/bin/php` or a versioned `/usr/local/bin/ea-phpXX`; the correct Ryaze path and whether Cron Jobs are enabled must be confirmed in that account. This repository cannot see the production account's home directory, PHP CLI binary, or existing cron entries. [cPanel cron command guidance](https://support.cpanel.net/hc/en-us/articles/360053606793-How-to-create-or-edit-cron-jobs-in-the-cpanel-user-interface)

The most reliable queue option is a persistent worker managed by the host's process monitor:

```sh
cd /home/CPANEL_USER/ABSOLUTE_MYHEP_PATH
/usr/local/bin/ea-php84 artisan queue:work backups --queue=backups --sleep=3 --tries=1 --timeout=14400
```

Set `BACKUP_QUEUE_CRON_FALLBACK=false` when that worker is confirmed to stay alive. If Ryaze does not allow persistent workers, keep the default fallback enabled; the scheduler starts `queue:work` with `--stop-when-empty` once per minute. That fallback is suitable only if Ryaze permits a scheduled PHP CLI process to run for the duration of a backup. Confirm PHP CLI has `zip`, `openssl`, PDO MySQL, and `mysqldump`, and that the account allows enough memory, execution time, and temporary disk space for the largest archive.

## Health checks and manual backup

Open **System Administration → Backup & Recovery** as a System Admin to view Drive reachability, recent run metadata, last successful backup, the next scheduled time, and health. The status check and recent history use Drive metadata; they do not download archives. A queued or running status is recorded in `backup_runs`. Health becomes overdue when the latest database backup is over two hours old or the full backup is over 27 hours old.

To queue a manual full backup from the server:

```sh
php artisan myhep:backup-dispatch full
```

For a database-only backup:

```sh
php artisan myhep:backup-dispatch database
```

Those commands enqueue work. A persistent worker must be running, or the next scheduler fallback must run. In the dashboard, use **Backup Now** to queue a database or full archive. The operation lock prevents overlapping backup and cleanup operations. The job creates a unique name, checks free disk space, lets Spatie verify the ZIP, uploads to Drive, and checks that the remote file exists and has a nonzero size before recording success.

For a real acceptance check, configure production credentials, create one manual database archive during a low-usage period, wait for a **Successful** dashboard row, and confirm the timestamped file is visible in the configured Google Drive folder. Then create and verify one full archive. Do not treat the feature as production-ready until both upload and remote verification have succeeded on Ryaze.

## Controlled recovery procedure

There is deliberately no web restore action. Restore only from server administrator access into a new or isolated recovery environment.

1. Provision a replacement server with a supported PHP version and the required extensions, `mysqldump`/`mysql` or MariaDB client tools, Composer, and the required Node runtime if rebuilding frontend assets.
2. Deploy the MyHEP source revision that matches the database snapshot as closely as possible. Configure a private document root, writable Laravel storage/cache directories, and a new MySQL/MariaDB database.
3. Restore the production `.env` securely, including the original `APP_KEY`, database values, and required integrations. Configure the backup OAuth values only if recovery should continue to write to Google Drive.
4. From the dedicated Drive folder, download the latest known-good `Database/` or `Full/` archive. Use a trusted ZIP utility and enter the recovery password interactively. Never put the archive password in a command argument, shell history, or a public ticket.
5. Extract into a protected directory outside the public web root. Inspect the SQL dump and files before restoring. Use a new, empty recovery database first; import the SQL dump with the MySQL/MariaDB client using a protected client option file or another secure credential prompt.
6. For a full archive, copy the extracted `storage/app/private`, `storage/app/public`, and `storage/app/certificate-templates` contents back to those same paths. Preserve ownership and private permissions; do not make private uploads web-accessible.
7. Run `composer install --no-dev --prefer-dist --optimize-autoloader`. Build frontend assets if the deployment process requires it, then run the project's documented cache/storage-link setup. Inspect `php artisan migrate:status` before running any migration; apply only migrations appropriate for the restored application version.
8. Configure and verify session/cache/queue storage, scheduler, and worker. Check Laravel logs, sign in with a System Admin account, and smoke-test student login and critical read-only pages before switching production traffic.
9. Keep the original encrypted archive and the isolated restore environment until the recovery has been reviewed. A successful remote upload is not proof that the full disaster-recovery process has been rehearsed.

For a first recovery rehearsal, use a separate test server and test database. Never test restore against production.

## Package notes and limitations

This implementation uses Spatie Laravel Backup for database dumps, ZIP creation, encryption, verification, and retention, plus the Masbug Flysystem Google Drive adapter. Current Spatie documentation describes ZIP/OpenSSL and local free-space requirements and requires the database dump executable; the adapter supports Flysystem v2/v3 and a shared Google Drive folder ID. [Spatie requirements](https://github.com/spatie/laravel-backup/blob/main/docs/requirements.md) · [Google Drive adapter](https://github.com/masbug/flysystem-google-drive-ext)

Actual Ryaze limits, PHP CLI path, cron availability, worker process support, Drive OAuth access, and upload size/quota were not verifiable from this checkout. Large archives may exceed shared-hosting runtime or disk limits even when the free-space guard passes; confirm those limits with Ryaze and run the live acceptance check before relying on these backups.
