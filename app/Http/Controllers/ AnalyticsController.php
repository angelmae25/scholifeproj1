<?php
namespace App\Http\Controllers;

use App\Models\User;

class AnalyticsController extends Controller {
    public function index() {
        $stats = [
            'daily_active'   => User::where('status', 'active')->count(),
            'weekly_sessions'=> 8341,
            'avg_session'    => '9.4m',
            'uptime'         => '99.8%',
        ];

        $engagement = [
            'Announcement' => 3754,
            'Events'       => 672,
            'Marketplace'  => 244,
            'Organization' => 142,
        ];

        $recentActivity = [
            ['color' => 'green',  'text' => 'Backup completed successfully',         'time' => 'Today, 3:00 AM'],
            ['color' => 'green',  'text' => '38 new accounts created this week',     'time' => 'May 19, 2026'],
            ['color' => 'orange', 'text' => 'Storage at 72% capacity — review files','time' => 'May 18, 2026'],
            ['color' => 'orange', 'text' => 'System update v4.2.1 applied',          'time' => 'May 17, 2026'],
        ];

        return view('admin.analytics', compact('stats', 'engagement', 'recentActivity'));
    }
}
