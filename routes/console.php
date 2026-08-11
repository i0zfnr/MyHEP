<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('notifications:send-reminders', function (): void {
    if (! myhepWebPushEnabled() || ! Schema::hasTable('push_notification_markers')) {
        $this->warn('Web Push is not enabled or notification markers are unavailable.');
        return;
    }

    $subscribedStudentIds = myhepPushSubscribedUserIds('student');
    if ($subscribedStudentIds === []) {
        $this->info('No subscribed students need reminders.');
        return;
    }

    if (Schema::hasTable('student_documents')) {
        DB::table('student_documents')
            ->whereIn('student_id', $subscribedStudentIds)
            ->whereNotNull('expiry_date')
            ->whereIn('expiry_date', [today()->toDateString(), today()->addDays(7)->toDateString(), today()->addDays(30)->toDateString()])
            ->orderBy('id')
            ->get(['id', 'student_id', 'title', 'expiry_date'])
            ->each(function (object $document): void {
                $days = today()->diffInDays(\Illuminate\Support\Carbon::parse($document->expiry_date), false);
                $label = $days === 0 ? 'expires today' : "expires in {$days} days";
                myhepSendPushOnce("document-expiry:{$document->id}:{$document->expiry_date}:{$days}", function () use ($document, $label): void {
                    myhepSendPushNotification('student', (int) $document->student_id, [
                        'category' => 'documents',
                        'title' => 'Document expiry reminder',
                        'body' => Str::limit("{$document->title} {$label}. Upload an updated document if required.", 180),
                        'url' => route('student.documents.index'),
                        'tag' => 'document-expiry-' . $document->id,
                        'requireInteraction' => true,
                    ]);
                });
            });
    }

    if (Schema::hasTable('student_movements')) {
        DB::table('student_movements')
            ->whereIn('student_id', $subscribedStudentIds)
            ->whereNull('return_at')
            ->where('movement_status', 'outside')
            ->where('expected_return_at', '>', now())
            ->where('expected_return_at', '<=', now()->addHour())
            ->orderBy('id')
            ->get(['id', 'student_id', 'expected_return_at'])
            ->each(function (object $movement): void {
                myhepSendPushOnce("movement-return-reminder:{$movement->id}", function () use ($movement): void {
                    $returnTime = \Illuminate\Support\Carbon::parse($movement->expected_return_at);
                    myhepSendPushNotification('student', (int) $movement->student_id, [
                        'category' => 'movement',
                        'title' => 'Return deadline approaching',
                        'body' => 'Your expected return time is ' . $returnTime->format('g:i A') . '. Return and scan before the deadline.',
                        'url' => route('student.movements.index'),
                        'tag' => 'movement-return-reminder-' . $movement->id,
                        'requireInteraction' => true,
                    ]);
                });
            });
    }

    $this->info('Notification reminders checked.');
})->purpose('Send document-expiry and movement return-deadline push reminders');

Schedule::command('notifications:send-reminders')->everyFiveMinutes()->withoutOverlapping();

Artisan::command('ai:prune-conversations', function (): void {
    $cutoff = now()->subDays((int) config('ai.conversation_retention_days', 30));
    $deleted = 0;

    foreach (['student_ai_conversations', 'admin_ai_conversations'] as $table) {
        if (Schema::hasTable($table)) {
            $deleted += DB::table($table)->where('updated_at', '<', $cutoff)->delete();
        }
    }

    $this->info("Deleted {$deleted} inactive AI conversation(s) older than {$cutoff->toDateString()}.");
})->purpose('Delete inactive student and admin AI conversations');

Schedule::command('ai:prune-conversations')->dailyAt('02:15')->withoutOverlapping();
