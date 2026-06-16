<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Event;
use App\Models\Report;
use App\Models\ActivityLog;

class DashboardController extends Controller {
    public function index() {
        $stats = [
            'total_users'     => User::count(),
            'active_today'    => User::where('status','active')->count(),
            'pending_reports' => Report::where('status','open')->count(),
            'events_month'    => Event::whereMonth('event_date', now()->month)->count(),
        ];

        // Pull real logs from DB, fallback to empty collection
        $recentActivity = ActivityLog::with('admin')
            ->latest()
            ->limit(6)
            ->get()
            ->map(function($log) {
                $color = match($log->action) {
                    'LOGIN','CREATE','RESOLVE' => 'green',
                    'DELETE','DISABLE'         => 'red',
                    default                    => 'orange',
                };
                return [
                    'color' => $color,
                    'text'  => '<strong>[' . $log->module . ']</strong> ' . $log->description
                        . ' — <em>' . ($log->admin->name ?? 'System') . '</em>',
                    'time'  => $log->created_at->diffForHumans(),
                ];
            });

        $upcomingEvents = Event::where('status','upcoming')
            ->orderBy('event_date')
            ->limit(4)
            ->get();

        $topReports = Report::where('status','open')
            ->orderByRaw("FIELD(priority,'high','medium','low')")
            ->limit(2)
            ->get();

        $usersByRole = [
            'Students'    => User::where('role','student')->count(),
            'Professors'  => User::where('role','professor')->count(),
            'Offices'     => User::where('role','office')->count(),
            'Org Officers'=> User::where('role','org_officer')->count(),
        ];

        return view('admin.dashboard', compact(
            'stats','recentActivity','upcomingEvents','topReports','usersByRole'
        ));
    }
}
