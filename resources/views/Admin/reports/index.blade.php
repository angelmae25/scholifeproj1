@extends('layouts.admin')
@section('title', 'Report')
@section('content')

    <div class="stat-grid">
        <div class="stat-card"><div class="stat-label">Open Reports</div><div class="stat-value">{{ $stats['open'] }}</div></div>
        <div class="stat-card"><div class="stat-label">Resolved This Week</div><div class="stat-value">{{ $stats['resolved_this_week'] }}</div></div>
        <div class="stat-card"><div class="stat-label">Avg Resolution</div><div class="stat-value">{{ $stats['avg_resolution'] }}</div></div>
        <div class="stat-card"><div class="stat-label">Violation Issue</div><div class="stat-value">{{ $stats['violation_issues'] }}</div></div>
    </div>

    <div class="panel">
        <div class="panel-header">
            <span class="panel-title">Reported Content</span>
            <select style="padding:6px 10px;border:1.5px solid #d9b8bc;border-radius:6px;font-size:0.78rem;">
                <option>All Types</option>
                <option>Marketplace</option>
                <option>Lost &amp; Found</option>
            </select>
        </div>
        @forelse($reports as $report)
            <div style="display:flex;align-items:center;gap:14px;padding:12px 0;border-bottom:1px solid #f5eaea;">
                <div style="width:40px;height:40px;border-radius:50%;background:{{ $report->priority === 'high' ? '#fed7d7' : '#fefcbf' }};display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0;">
                    {{ $report->type === 'Marketplace' ? '🛒' : '📍' }}
                </div>
                <div style="flex:1;">
                    <div style="font-weight:700;font-size:0.85rem;">{{ $report->title }}</div>
                    <span class="badge {{ $report->priority === 'high' ? 'badge-red' : 'badge-yellow' }}" style="margin-top:3px;">{{ ucfirst($report->priority) }} priority</span>
                    <div style="font-size:0.7rem;color:#999;margin-top:3px;">Reported by {{ $report->reporter_count }} user{{ $report->reporter_count > 1 ? 's' : '' }}</div>
                </div>
                <a href="#" style="color:#8b1c2c;font-size:1.1rem;">👁</a>
            </div>
        @empty
            <p style="text-align:center;color:#999;padding:24px;">No reports found.</p>
        @endforelse
        <div style="margin-top:16px;">{{ $reports->links() }}</div>
    </div>

@endsection
