<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class NotificationFeedController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $user = session('auth_user');
        abort_unless(is_array($user) && isset($user['id'], $user['role']), 401);

        [$items, $actionableCount] = $user['role'] === 'student'
            ? $this->studentFeed((int) $user['id'])
            : $this->adminFeed((string) ($user['admin_role'] ?? ''));

        usort($items, fn (array $left, array $right) => strcmp($right['timestamp'], $left['timestamp']));

        return response()->json([
            'items' => array_slice($items, 0, 10),
            'count' => $actionableCount,
            'generated_at' => now()->toIso8601String(),
        ])->header('Cache-Control', 'no-store, private');
    }

    private function studentFeed(int $studentId): array
    {
        $items = [];
        $actionableCount = 0;

        if (Schema::hasTable('offenses')) {
            $unpaid = DB::table('offenses')
                ->where('student_id', $studentId)
                ->where('status', 'unpaid')
                ->count();

            if ($unpaid > 0) {
                $actionableCount += $unpaid;
                $items[] = $this->item(
                    'unpaid-fines',
                    __('Unpaid fines'),
                    trans_choice('{1} You have :count unpaid fine.|[2,*] You have :count unpaid fines.', $unpaid, ['count' => $unpaid]),
                    route('student.offenses.index'),
                    now(),
                    'danger',
                );
            }
        }

        if (Schema::hasTable('fine_payment_applications')) {
            $application = DB::table('fine_payment_applications')
                ->where('student_id', $studentId)
                ->latest('updated_at')
                ->first();

            if ($application) {
                $isPending = $application->status === 'pending';
                $actionableCount += $isPending ? 1 : 0;
                $items[] = $this->item(
                    'payment-' . $application->id,
                    __('Payment review'),
                    __('Your latest payment receipt is :status.', ['status' => __($application->status)]),
                    route('student.offenses.index'),
                    $application->updated_at,
                    $isPending ? 'warning' : ($application->status === 'approved' ? 'success' : 'danger'),
                );
            }
        }

        if (Schema::hasTable('vehicle_sticker_applications')) {
            $sticker = DB::table('vehicle_sticker_applications')
                ->where('student_id', $studentId)
                ->latest('updated_at')
                ->first();

            if ($sticker) {
                $items[] = $this->item(
                    'sticker-' . $sticker->id,
                    __('Vehicle sticker'),
                    __('Your :vehicle sticker application is :status.', [
                        'vehicle' => $sticker->vehicle_no,
                        'status' => __($sticker->status),
                    ]),
                    route('student.vehicle-stickers.index'),
                    $sticker->updated_at,
                    $sticker->status === 'approved' ? 'success' : ($sticker->status === 'rejected' ? 'danger' : 'warning'),
                );
            }
        }

        $items = array_merge(
            $items,
            $this->announcementItems('discipline_announcements', route('student.discipline-announcements.index'), 'discipline'),
            $this->announcementItems('scholarship_announcements', route('student.scholarships.announcements'), 'scholarship'),
        );

        $recentAnnouncements = collect($items)
            ->whereIn('type', ['discipline', 'scholarship'])
            ->filter(fn (array $item) => Carbon::parse($item['timestamp'])->greaterThanOrEqualTo(now()->subDays(7)))
            ->count();

        return [$items, $actionableCount + $recentAnnouncements];
    }

    private function adminFeed(string $scope): array
    {
        $items = [];
        $actionableCount = 0;

        if ($scope === 'system_admin' && Schema::hasTable('bug_reports')) {
            $openBugs = DB::table('bug_reports')->whereIn('status', ['new', 'in_progress'])->count();
            if ($openBugs > 0) {
                $actionableCount += $openBugs;
                $items[] = $this->item(
                    'open-bugs',
                    __('Bug reports'),
                    trans_choice('{1} :count report needs attention.|[2,*] :count reports need attention.', $openBugs, ['count' => $openBugs]),
                    route('admin.bug-reports.index'),
                    DB::table('bug_reports')->whereIn('status', ['new', 'in_progress'])->max('updated_at') ?: now(),
                    'danger',
                );
            }
        }

        if (in_array($scope, ['discipline_admin', 'student_affairs_head', 'system_admin'], true)) {
            if (Schema::hasTable('fine_payment_applications')) {
                $pendingPayments = DB::table('fine_payment_applications')->where('status', 'pending')->count();
                if ($pendingPayments > 0) {
                    $actionableCount += $pendingPayments;
                    $items[] = $this->item(
                        'pending-payments',
                        __('Payment receipts'),
                        trans_choice('{1} :count receipt is waiting for review.|[2,*] :count receipts are waiting for review.', $pendingPayments, ['count' => $pendingPayments]),
                        route('admin.offenses.index', ['status' => 'applied']),
                        DB::table('fine_payment_applications')->where('status', 'pending')->max('updated_at') ?: now(),
                        'warning',
                    );
                }
            }

            if (Schema::hasTable('vehicle_sticker_applications')) {
                $pendingStickers = DB::table('vehicle_sticker_applications')->where('status', 'pending')->count();
                if ($pendingStickers > 0) {
                    $actionableCount += $pendingStickers;
                    $items[] = $this->item(
                        'pending-stickers',
                        __('Vehicle stickers'),
                        trans_choice('{1} :count application is waiting for review.|[2,*] :count applications are waiting for review.', $pendingStickers, ['count' => $pendingStickers]),
                        route('admin.vehicle-stickers.index', ['status' => 'pending']),
                        DB::table('vehicle_sticker_applications')->where('status', 'pending')->max('updated_at') ?: now(),
                        'warning',
                    );
                }
            }
        }

        if (in_array($scope, ['guard', 'discipline_admin', 'student_affairs_head', 'system_admin'], true) && Schema::hasTable('student_movements')) {
            $lateMovements = DB::table('student_movements')
                ->where('rule_status', 'late')
                ->where('updated_at', '>=', now()->subDays(7))
                ->count();
            if ($lateMovements > 0) {
                $actionableCount += $lateMovements;
                $items[] = $this->item(
                    'late-movements',
                    __('Movement violations'),
                    trans_choice('{1} :count late return was detected this week.|[2,*] :count late returns were detected this week.', $lateMovements, ['count' => $lateMovements]),
                    route('admin.movements.violations'),
                    DB::table('student_movements')->where('rule_status', 'late')->max('updated_at') ?: now(),
                    'danger',
                );
            }
        }

        if ($items === []) {
            $items[] = $this->item(
                'all-clear',
                __('All caught up'),
                __('There are no urgent items waiting for review.'),
                route('admin.dashboard'),
                now(),
                'success',
            );
        }

        return [$items, $actionableCount];
    }

    private function announcementItems(string $table, string $url, string $type): array
    {
        if (!Schema::hasTable($table)) {
            return [];
        }

        return DB::table($table)
            ->latest('created_at')
            ->limit(2)
            ->get()
            ->map(fn (object $announcement) => $this->item(
                $type . '-' . $announcement->id,
                $announcement->title,
                Str::limit(strip_tags((string) $announcement->body), 95),
                $url,
                $announcement->created_at,
                'info',
                $type,
            ))
            ->all();
    }

    private function item(
        string $id,
        string $title,
        string $body,
        string $url,
        mixed $timestamp,
        string $tone,
        string $type = 'system',
    ): array {
        $date = Carbon::parse($timestamp);

        return [
            'id' => $id,
            'title' => $title,
            'body' => $body,
            'url' => $url,
            'tone' => $tone,
            'type' => $type,
            'time' => $date->diffForHumans(),
            'timestamp' => $date->toIso8601String(),
        ];
    }
}
