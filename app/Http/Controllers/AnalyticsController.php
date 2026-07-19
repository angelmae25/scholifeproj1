<?php
namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Report;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AnalyticsController extends Controller
{
    public function index()
    {
        $today = now()->startOfDay();
        $yesterday = now()->subDay()->startOfDay();
        $weekStart = now()->subDays(6)->startOfDay();
        $previousWeekStart = now()->subDays(13)->startOfDay();
        $previousWeekEnd = now()->subDays(7)->endOfDay();

        $activeToday = $this->activeUserIdsBetween($today, now())->count();
        $activeYesterday = $this->activeUserIdsBetween($yesterday, $yesterday->copy()->endOfDay())->count();

        $adminActivityWeek = $this->countBetween('activity_logs', 'created_at', $weekStart, now());
        $mobileActivityWeek = $this->mobileActivityCountBetween($weekStart, now());
        $weeklyActivity = $adminActivityWeek + $mobileActivityWeek;
        $previousWeeklyActivity = $this->countBetween('activity_logs', 'created_at', $previousWeekStart, $previousWeekEnd)
            + $this->mobileActivityCountBetween($previousWeekStart, $previousWeekEnd);

        $openReports = $this->safeCount('reports', fn () => Report::where('status', 'open')->count());
        $highPriorityReports = $this->safeCount('reports', fn () => Report::where('status', 'open')->where('priority', 'high')->count());
        $totalUsers = $this->safeCount('users', fn () => User::count());
        $newUsersThisWeek = $this->countBetween('users', 'created_at', $weekStart, now());

        $stats = [
            'daily_active' => $activeToday,
            'daily_active_sub' => $this->formatChange($activeToday, $activeYesterday) . ' vs yesterday',
            'weekly_activity' => $weeklyActivity,
            'weekly_activity_sub' => $this->formatChange($weeklyActivity, $previousWeeklyActivity) . ' vs previous week',
            'open_reports' => $openReports,
            'open_reports_sub' => $highPriorityReports . ' high priority',
            'total_users' => $totalUsers,
            'total_users_sub' => '+' . number_format($newUsersThisWeek) . ' this week',
        ];

        $engagement = [
            'News / Announcement' => $this->tableCount('announcements'),
            'Events' => $this->tableCount('events'),
            'Marketplace' => $this->mobileOwnedCount('pre_loved_items'),
            'Lost and Found' => $this->mobileOwnedCount('lost_found_items'),
            'Organization' => $this->tableCount('organizations'),
        ];

        $recentActivity = $this->recentActivity();
        $registrationWeeks = $this->registrationWeeks();

        return view('admin.analytics', compact('stats', 'engagement', 'recentActivity', 'registrationWeeks'));
    }

    private function activeUserIdsBetween($start, $end)
    {
        $ids = collect();

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'last_active_at')) {
            $ids = $ids->merge(User::whereBetween('last_active_at', [$start, $end])->pluck('id'));
        }

        if (Schema::hasTable('sessions') && Schema::hasColumn('sessions', 'user_id') && Schema::hasColumn('sessions', 'last_activity')) {
            $ids = $ids->merge(DB::table('sessions')
                ->whereNotNull('user_id')
                ->whereBetween('last_activity', [$start->timestamp, $end->timestamp])
                ->pluck('user_id'));
        }

        if (Schema::hasTable('personal_access_tokens') && Schema::hasColumn('personal_access_tokens', 'tokenable_id') && Schema::hasColumn('personal_access_tokens', 'last_used_at')) {
            $ids = $ids->merge(DB::table('personal_access_tokens')
                ->where('tokenable_type', User::class)
                ->whereBetween('last_used_at', [$start, $end])
                ->pluck('tokenable_id'));
        }

        if (Schema::hasTable('mobile_content_views') && Schema::hasColumn('mobile_content_views', 'user_id')) {
            $ids = $ids->merge(DB::table('mobile_content_views')
                ->whereBetween('created_at', [$start, $end])
                ->pluck('user_id'));
        }

        return $ids->filter()->unique()->values();
    }

    private function mobileActivityCountBetween($start, $end): int
    {
        return $this->countBetween('mobile_content_views', 'created_at', $start, $end)
            + $this->countBetween('pre_loved_messages', 'created_at', $start, $end)
            + $this->countBetween('lost_found_claims', 'created_at', $start, $end)
            + $this->countBetween('reports', 'created_at', $start, $end)
            + $this->countBetween('organization_evaluations', 'created_at', $start, $end)
            + $this->countBetween('pre_loved_items', 'created_at', $start, $end)
            + $this->countBetween('lost_found_items', 'created_at', $start, $end);
    }

    private function recentActivity(): array
    {
        if (! Schema::hasTable('activity_logs')) {
            return [];
        }

        return ActivityLog::latest()
            ->take(5)
            ->get()
            ->map(function ($log) {
                $action = strtoupper((string) $log->action);
                $isWarning = str_contains($action, 'DELETE') || str_contains($action, 'FAIL') || str_contains($action, 'DISMISS');

                return [
                    'color' => $isWarning ? 'orange' : 'green',
                    'text' => $log->description ?: trim($log->action . ' ' . $log->module),
                    'time' => optional($log->created_at)->format('M d, Y h:i A') ?: '',
                ];
            })
            ->all();
    }

    private function registrationWeeks(): array
    {
        $weeks = [];

        for ($i = 3; $i >= 0; $i--) {
            $start = now()->startOfWeek()->subWeeks($i);
            $end = $start->copy()->endOfWeek();

            $weeks[] = [
                'label' => $start->format('M d') . ' - ' . $end->format('M d'),
                'students' => $this->roleCountBetween(['student', 'org_officer'], $start, $end),
                'professors' => $this->roleCountBetween(['professor'], $start, $end),
                'offices' => $this->roleCountBetween(['office'], $start, $end),
                'organizations' => $this->countBetween('organizations', 'created_at', $start, $end),
            ];
        }

        return $weeks;
    }

    private function roleCountBetween(array $roles, $start, $end): int
    {
        if (! Schema::hasTable('users')) {
            return 0;
        }

        return User::whereIn('role', $roles)
            ->whereBetween('created_at', [$start, $end])
            ->count();
    }

    private function mobileOwnedCount(string $table): int
    {
        if (! Schema::hasTable($table)) {
            return 0;
        }

        if (Schema::hasColumn($table, 'user_id')) {
            return DB::table($table)->whereNotNull('user_id')->count();
        }

        return DB::table($table)->count();
    }

    private function countBetween(string $table, string $column, $start, $end): int
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return 0;
        }

        return DB::table($table)->whereBetween($column, [$start, $end])->count();
    }

    private function tableCount(string $table): int
    {
        if (! Schema::hasTable($table)) {
            return 0;
        }

        return DB::table($table)->count();
    }

    private function safeCount(string $table, callable $callback): int
    {
        if (! Schema::hasTable($table)) {
            return 0;
        }

        return (int) $callback();
    }

    private function formatChange(int $current, int $previous): string
    {
        if ($previous === 0) {
            return $current > 0 ? '+100%' : '0%';
        }

        $change = (($current - $previous) / $previous) * 100;
        return ($change >= 0 ? '+' : '') . round($change) . '%';
    }
}
