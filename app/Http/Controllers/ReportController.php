<?php
namespace App\Http\Controllers;

use App\Models\Report;

class ReportController extends Controller {
    public function index() {
        $stats = [
            'open'               => Report::where('status','open')->count(),
            'resolved_this_week' => Report::where('status','resolved')
                ->whereBetween('resolved_at',[now()->startOfWeek(),now()])->count(),
            'avg_resolution'     => '3.2h',
            'violation_issues'   => Report::where('priority','high')->where('status','open')->count(),
        ];
        $reports = Report::orderByRaw("FIELD(priority,'high','medium','low')")->paginate(20);
        return view('admin.reports.index', compact('stats','reports'));
    }
}
