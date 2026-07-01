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
                <option>Feedback</option>
            </select>
        </div>

        @forelse($reports as $report)
            <div style="display:flex;align-items:flex-start;gap:14px;padding:14px 0;border-bottom:1px solid #f5eaea;">
                <div style="width:40px;height:40px;border-radius:50%;background:{{ $report->priority === 'high' ? '#fed7d7' : '#fefcbf' }};display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0;">
                    @if(str_contains(strtolower($report->type), 'market')) <x-icon name="shopping-bag" /> @else <x-icon name="map-pin" /> @endif
                </div>

                <div style="flex:1;min-width:0;">
                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                        <div style="font-weight:700;font-size:0.9rem;color:#111;">{{ $report->title }}</div>
                        <span class="badge {{ $report->priority === 'high' ? 'badge-red' : 'badge-yellow' }}">{{ ucfirst($report->priority) }} priority</span>
                        <span class="badge badge-yellow">{{ ucfirst($report->status) }}</span>
                    </div>
                    <div style="font-size:0.72rem;color:#999;margin-top:4px;">
                        {{ $report->type }} · Submitted by {{ optional($report->user)->name ?? 'Mobile User' }} · {{ $report->created_at->format('M d, Y h:i A') }}
                    </div>
                    <div style="font-size:0.78rem;color:#555;margin-top:7px;line-height:1.4;">
                        {{ \Illuminate\Support\Str::limit($report->description, 180) }}
                    </div>
                </div>

                <div style="display:flex;gap:8px;align-items:center;justify-content:flex-end;">
                    <a href="{{ route('admin.reports.show', $report) }}" title="View report" style="color:#8b1c2c;font-size:1.1rem;display:inline-flex;align-items:center;text-decoration:none;"><x-icon name="eye" /></a>
                    <a href="{{ route('admin.reports.download', $report) }}" style="border:1px solid #8b1c2c;color:#8b1c2c;border-radius:6px;padding:7px 10px;font-size:0.74rem;font-weight:700;text-decoration:none;">Download Word</a>
                </div>
            </div>
        @empty
            <p style="text-align:center;color:#999;padding:24px;">No reports found.</p>
        @endforelse

        <div style="margin-top:16px;">{{ $reports->links() }}</div>
    </div>

@endsection
