<?php

return [
    'lock_name' => 'myhep.backup-operation',
    'lock_ttl_seconds' => 18000,
    'job_timeout_seconds' => 14400,
    'queue_connection' => 'backups',
    'queue_name' => 'backups',
    'queue_cron_fallback' => (bool) env('BACKUP_QUEUE_CRON_FALLBACK', true),
    'minimum_free_disk_mb' => (int) env('BACKUP_MIN_FREE_DISK_MB', 512),
    'history_retention_months' => 13,
    'database_overdue_after_minutes' => 120,
    'full_overdue_after_hours' => 27,
    'queued_stale_after_minutes' => 30,
    'running_stale_after_hours' => 6,
    'database_schedule_minute' => 10,
    'full_schedule_time' => '00:20',
    'cleanup_schedule_time' => '02:30',
];
