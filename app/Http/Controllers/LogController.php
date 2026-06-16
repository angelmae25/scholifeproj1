<?php
namespace App\Http\Controllers;

use App\Models\ActivityLog;

class LogController extends Controller {
    public function index() {
        $logs = ActivityLog::with('admin')
            ->latest()
            ->paginate(50);

        $stats = [
            'total'   => ActivityLog::count(),
            'today'   => ActivityLog::whereDate('created_at', today())->count(),
            'logins'  => ActivityLog::where('action','LOGIN')->count(),
            'actions' => ActivityLog::whereNotIn('action',['LOGIN','LOGOUT','VIEW'])->count(),
        ];

        return view('admin.logs', compact('logs','stats'));
    }
}
