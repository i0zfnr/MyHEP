<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BackupSetting extends Model
{
    protected $table = 'backup_settings';

    protected $fillable = [
        'google_drive_client_id',
        'google_drive_client_secret',
        'google_drive_refresh_token',
        'google_drive_folder_id',
        'archive_password',
        'notification_email',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'google_drive_client_id' => 'encrypted',
            'google_drive_client_secret' => 'encrypted',
            'google_drive_refresh_token' => 'encrypted',
            'google_drive_folder_id' => 'encrypted',
            'archive_password' => 'encrypted',
        ];
    }
}
