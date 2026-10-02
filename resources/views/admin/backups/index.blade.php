@extends('layouts.app')

@section('title', __('backup.title'))
@section('header')<h2>{{ __('backup.title') }}</h2>@endsection

@section('content')
<div class="ui-shell" style="max-width:1120px;margin:0 auto;">
    @if(session('success'))<div class="se-feedback se-feedback--success" role="status">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="se-feedback se-feedback--error" role="alert">{{ session('error') }}</div>@endif

    <section class="ui-card">
        <div class="ui-card-head"><strong>{{ __('backup.health_title') }}</strong></div>
        <div class="ui-card-body">
            @php
                $healthTone = match($snapshot['health']['key']) {
                    'healthy' => '#15803d',
                    'running' => '#a16207',
                    default => '#b42318',
                };
            @endphp
            <div style="display:flex;align-items:flex-start;gap:12px;flex-wrap:wrap;">
                <span aria-hidden="true" style="color:{{ $healthTone }};font-size:1.2rem;line-height:1;">●</span>
                <div style="min-width:220px;flex:1;">
                    <strong style="color:{{ $healthTone }};">{{ $snapshot['health']['message'] }}</strong>
                    @if(!$snapshot['configured'])
                        <p style="margin:.35rem 0 0;color:var(--text-muted,#746b62);">{{ __('backup.missing_config') }} {{ implode(', ', $missingRequirements) }}</p>
                    @elseif($snapshot['health']['key'] === 'failed' && $snapshot['failed_run']?->error_message)
                        <p style="margin:.35rem 0 0;color:var(--text-muted,#746b62);">{{ $snapshot['failed_run']->error_message }}</p>
                    @elseif($snapshot['active_run'])
                        <p style="margin:.35rem 0 0;color:var(--text-muted,#746b62);">{{ __('backup.active_status', ['status' => __('backup.status_'.$snapshot['active_run']->status)]) }}</p>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,250px),1fr));gap:14px;margin-top:14px;">
        <section class="ui-card">
            <div class="ui-card-head"><strong>{{ __('backup.google_drive') }}</strong></div>
            <div class="ui-card-body">
                <div class="feature-row"><span>{{ __('backup.connection') }}</span><strong>{{ $connection['connected'] ? __('backup.connected') : __('backup.connection_error') }}</strong></div>
                <div class="feature-row"><span>{{ __('backup.automatic') }}</span><strong>{{ $snapshot['configured'] ? __('backup.enabled') : __('backup.not_configured') }}</strong></div>
                <div class="feature-row"><span>{{ __('backup.next_backup') }}</span><strong>{{ $snapshot['next_scheduled_at']->format('d M Y, H:i') }}</strong></div>
                <p style="margin:.65rem 0 0;color:var(--text-muted,#746b62);">{{ __('backup.automatic_help') }}</p>
            </div>
        </section>

        <section class="ui-card">
            <div class="ui-card-head"><strong>{{ __('backup.last_success') }}</strong></div>
            <div class="ui-card-body">
                @if($snapshot['last_successful'])
                    <div class="feature-row"><span>{{ __('backup.date_time') }}</span><strong>{{ $snapshot['last_successful']->finished_at->timezone(config('app.timezone'))->format('d M Y, H:i') }}</strong></div>
                    <div class="feature-row"><span>{{ __('backup.type') }}</span><strong>{{ $snapshot['last_successful']->type === 'database' ? __('backup.type_database') : __('backup.type_full') }}</strong></div>
                @else
                    <p style="margin:0;color:var(--text-muted,#746b62);">{{ __('backup.no_success_yet') }}</p>
                @endif
                <div class="feature-row"><span>{{ __('backup.database_schedule') }}</span><strong>{{ __('backup.every_hour') }}</strong></div>
                <div class="feature-row"><span>{{ __('backup.full_schedule') }}</span><strong>{{ __('backup.every_day') }}</strong></div>
            </div>
        </section>

        <section class="ui-card">
            <div class="ui-card-head"><strong>{{ __('backup.retention') }}</strong></div>
            <div class="ui-card-body">
                <div class="feature-row"><span>{{ __('backup.hourly') }}</span><strong>{{ $retention['hourly'] }}</strong></div>
                <div class="feature-row"><span>{{ __('backup.daily') }}</span><strong>{{ $retention['daily'] }}</strong></div>
                <div class="feature-row"><span>{{ __('backup.monthly') }}</span><strong>{{ $retention['monthly'] }}</strong></div>
            </div>
        </section>
    </div>

    <section class="ui-card" style="margin-top:14px;">
        <div class="ui-card-head"><strong>{{ __('backup.settings_title') }}</strong></div>
        <div class="ui-card-body">
            <p style="margin:0 0 14px;color:var(--text-muted,#746b62);">{{ __('backup.settings_help') }}</p>
            @if($errors->any())
                <div class="se-feedback se-feedback--error" role="alert" style="margin-bottom:14px;">
                    <ul style="margin:0;padding-left:20px;">
                        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                    </ul>
                </div>
            @endif
            <form method="POST" action="{{ route('admin.backups.settings.update') }}">
                @csrf
                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(min(100%,280px),1fr));gap:14px;">
                    <label for="backup_notification_email" style="display:grid;gap:6px;">
                        <span>{{ __('backup.notification_email') }}</span>
                        <input id="backup_notification_email" type="email" name="notification_email" maxlength="255" value="{{ old('notification_email', $backupSettings['notification_email']) }}" autocomplete="email">
                        <small style="color:var(--text-muted,#746b62);">{{ __('backup.notification_email_help') }}</small>
                    </label>
                    <label for="backup_google_client_id" style="display:grid;gap:6px;">
                        <span>{{ __('backup.google_client_id') }}</span>
                        <input id="backup_google_client_id" type="text" name="google_drive_client_id" maxlength="1024" value="{{ old('google_drive_client_id', $backupSettings['google_drive_client_id']) }}" autocomplete="off">
                    </label>
                    <label for="backup_google_folder_id" style="display:grid;gap:6px;">
                        <span>{{ __('backup.google_folder_id') }}</span>
                        <input id="backup_google_folder_id" type="text" name="google_drive_folder_id" maxlength="1024" value="{{ old('google_drive_folder_id', $backupSettings['google_drive_folder_id']) }}" autocomplete="off">
                    </label>
                    <label for="backup_google_client_secret" style="display:grid;gap:6px;">
                        <span>{{ __('backup.google_client_secret') }}</span>
                        <input id="backup_google_client_secret" type="password" name="google_drive_client_secret" maxlength="8192" autocomplete="new-password" placeholder="{{ $backupSettings['client_secret_configured'] ? __('backup.secret_saved') : __('backup.secret_not_set') }}">
                    </label>
                    <label for="backup_google_refresh_token" style="display:grid;gap:6px;">
                        <span>{{ __('backup.google_refresh_token') }}</span>
                        <input id="backup_google_refresh_token" type="password" name="google_drive_refresh_token" maxlength="8192" autocomplete="new-password" placeholder="{{ $backupSettings['refresh_token_configured'] ? __('backup.secret_saved') : __('backup.secret_not_set') }}">
                    </label>
                    <label for="backup_archive_password" style="display:grid;gap:6px;">
                        <span>{{ __('backup.archive_password') }}</span>
                        <input id="backup_archive_password" type="password" name="archive_password" minlength="32" maxlength="4096" autocomplete="new-password" placeholder="{{ $backupSettings['archive_password_configured'] ? __('backup.secret_saved') : __('backup.secret_not_set') }}">
                    </label>
                </div>
                <p style="margin:12px 0;color:var(--text-muted,#746b62);">{{ __('backup.secret_help') }}</p>
                <button class="ui-btn primary" type="submit">{{ __('backup.save_settings') }}</button>
            </form>
        </div>
    </section>

    <section class="ui-card" style="margin-top:14px;">
        <div class="ui-card-head"><strong>{{ __('backup.backup_now') }}</strong></div>
        <div class="ui-card-body">
            <form method="POST" action="{{ route('admin.backups.store') }}" style="display:flex;align-items:end;gap:12px;flex-wrap:wrap;">
                @csrf
                <label for="backup_type" style="display:grid;gap:6px;min-width:190px;">
                    <span>{{ __('backup.type') }}</span>
                    <select id="backup_type" name="type" required @disabled(!$snapshot['configured'] || !$connection['connected'] || $snapshot['active_run'])>
                        <option value="full" @selected(old('type', 'full') === 'full')>{{ __('backup.type_full') }}</option>
                        <option value="database" @selected(old('type') === 'database')>{{ __('backup.type_database') }}</option>
                    </select>
                </label>
                <button class="ui-btn primary" type="submit" @disabled(!$snapshot['configured'] || !$connection['connected'] || $snapshot['active_run'])>
                    {{ $snapshot['active_run'] ? __('backup.backup_queued') : __('backup.backup_now') }}
                </button>
            </form>
            <p style="margin:.75rem 0 0;color:var(--text-muted,#746b62);">{{ __('backup.restore_notice') }}</p>
        </div>
    </section>

    <section class="ui-card" style="margin-top:14px;">
        <div class="ui-card-head"><strong>{{ __('backup.recent_backups') }}</strong></div>
        <div class="ui-card-body" style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;min-width:620px;">
                <thead>
                    <tr>
                        <th scope="col" style="text-align:left;padding:10px 8px;">{{ __('backup.date_time') }}</th>
                        <th scope="col" style="text-align:left;padding:10px 8px;">{{ __('backup.type') }}</th>
                        <th scope="col" style="text-align:left;padding:10px 8px;">{{ __('backup.size') }}</th>
                        <th scope="col" style="text-align:left;padding:10px 8px;">{{ __('backup.status') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentBackups as $backup)
                        <tr>
                            <td style="padding:10px 8px;border-top:1px solid var(--border-subtle,#e7e2dc);">{{ $backup->created_at->timezone(config('app.timezone'))->format('d M Y, H:i') }}</td>
                            <td style="padding:10px 8px;border-top:1px solid var(--border-subtle,#e7e2dc);">{{ $backup->type === 'database' ? __('backup.type_database') : __('backup.type_full') }}</td>
                            <td style="padding:10px 8px;border-top:1px solid var(--border-subtle,#e7e2dc);">
                                @if($backup->size_bytes)
                                    @php $units = ['B', 'KB', 'MB', 'GB', 'TB']; $unitIndex = 0; $size = (float) $backup->size_bytes; while ($size >= 1024 && $unitIndex < count($units) - 1) { $size /= 1024; $unitIndex++; } @endphp
                                    {{ number_format($size, $unitIndex === 0 ? 0 : 1) }} {{ $units[$unitIndex] }}
                                @else
                                    —
                                @endif
                            </td>
                            <td style="padding:10px 8px;border-top:1px solid var(--border-subtle,#e7e2dc);">{{ __('backup.status_'.$backup->status) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" style="padding:14px 8px;color:var(--text-muted,#746b62);">{{ __('backup.no_runs') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection
