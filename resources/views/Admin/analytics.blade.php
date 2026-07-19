@extends('layouts.admin')
@section('title', 'Analytics')
@section('content')

    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-label">Active Users Today</div>
            <div class="stat-value">{{ number_format($stats['daily_active']) }}</div>
            <div class="stat-sub" style="color:#38a169">{{ $stats['daily_active_sub'] }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Weekly Activity</div>
            <div class="stat-value">{{ number_format($stats['weekly_activity']) }}</div>
            <div class="stat-sub" style="color:#38a169">{{ $stats['weekly_activity_sub'] }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Open Reports</div>
            <div class="stat-value">{{ number_format($stats['open_reports']) }}</div>
            <div class="stat-sub">{{ $stats['open_reports_sub'] }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Users</div>
            <div class="stat-value">{{ number_format($stats['total_users']) }}</div>
            <div class="stat-sub">{{ $stats['total_users_sub'] }}</div>
        </div>
    </div>

    <div class="two-col">
        <div class="panel">
            <div class="panel-header"><span class="panel-title">Records by Feature</span></div>
            @php $maxEng = max(array_values($engagement)) ?: 1; @endphp
            @foreach($engagement as $feature => $count)
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:14px;">
                    <span style="width:150px;font-size:0.82rem;">{{ $feature }}</span>
                    <div style="flex:1;background:#f5eaea;border-radius:20px;height:14px;">
                        <div style="background:#8b1c2c;height:14px;border-radius:20px;width:{{ max(4, ($count / $maxEng) * 100) }}%;"></div>
                    </div>
                    <span style="font-size:0.82rem;font-weight:700;color:#333;width:54px;text-align:right;">{{ number_format($count) }}</span>
                </div>
            @endforeach
        </div>

        <div class="panel" style="border:2px solid #3182ce;">
            <div class="panel-header">
                <span class="panel-title" style="color:#3182ce;">Recent Activity</span>
                <a href="{{ route('admin.logs') }}" class="btn btn-outline">View logs</a>
            </div>
            @forelse($recentActivity as $item)
                <div style="display:flex;gap:10px;align-items:flex-start;padding:10px 0;border-bottom:1px solid #ebf4ff;">
                    <span style="width:8px;height:8px;border-radius:50%;background:{{ $item['color'] === 'green' ? '#38a169' : '#dd6b20' }};margin-top:5px;flex-shrink:0;"></span>
                    <div>
                        <div style="font-size:0.82rem;">{{ $item['text'] }}</div>
                        <div style="font-size:0.7rem;color:#999;margin-top:2px;">{{ $item['time'] }}</div>
                    </div>
                </div>
            @empty
                <p style="color:#999;font-size:.82rem;padding:12px 0">No activity logs yet.</p>
            @endforelse
        </div>
    </div>

    <div class="panel" style="margin-top:20px;">
        <div style="font-size:0.95rem;font-weight:800;color:#333;margin-bottom:4px;"><x-icon name="users" /> New user registration</div>
        <div style="font-size:0.75rem;color:#777;margin-bottom:16px;">Last 4 weeks from users and organizations</div>
        <div style="display:flex;gap:8px;margin-bottom:12px;font-size:0.72rem;flex-wrap:wrap;">
            <span><span style="display:inline-block;width:12px;height:12px;background:#8b1c2c;border-radius:2px;margin-right:4px;"></span>Students</span>
            <span><span style="display:inline-block;width:12px;height:12px;background:#38a169;border-radius:2px;margin-right:4px;"></span>Professors</span>
            <span><span style="display:inline-block;width:12px;height:12px;background:#f9c6cb;border-radius:2px;margin-right:4px;"></span>Offices</span>
            <span><span style="display:inline-block;width:12px;height:12px;background:#4299e1;border-radius:2px;margin-right:4px;"></span>Organizations</span>
        </div>
        @php
            $maxWeek = collect($registrationWeeks)->map(fn($week) => $week['students'] + $week['professors'] + $week['offices'] + $week['organizations'])->max() ?: 1;
        @endphp
        <div style="display:flex;align-items:flex-end;gap:24px;height:130px;border-bottom:1px solid #eee;padding-bottom:4px;">
            @foreach($registrationWeeks as $week)
                @php
                    $studentsHeight = max(0, ($week['students'] / $maxWeek) * 100);
                    $professorsHeight = max(0, ($week['professors'] / $maxWeek) * 100);
                    $officesHeight = max(0, ($week['offices'] / $maxWeek) * 100);
                    $organizationsHeight = max(0, ($week['organizations'] / $maxWeek) * 100);
                @endphp
                <div style="display:flex;flex-direction:column;align-items:center;flex:1;min-width:70px;">
                    <div style="display:flex;flex-direction:column-reverse;width:60%;height:105px;background:#fafafa;justify-content:flex-start;">
                        <div title="Students: {{ $week['students'] }}" style="background:#8b1c2c;height:{{ $studentsHeight }}%;min-height:{{ $week['students'] > 0 ? 3 : 0 }}px;"></div>
                        <div title="Professors: {{ $week['professors'] }}" style="background:#38a169;height:{{ $professorsHeight }}%;min-height:{{ $week['professors'] > 0 ? 3 : 0 }}px;"></div>
                        <div title="Offices: {{ $week['offices'] }}" style="background:#f9c6cb;height:{{ $officesHeight }}%;min-height:{{ $week['offices'] > 0 ? 3 : 0 }}px;"></div>
                        <div title="Organizations: {{ $week['organizations'] }}" style="background:#4299e1;height:{{ $organizationsHeight }}%;min-height:{{ $week['organizations'] > 0 ? 3 : 0 }}px;"></div>
                    </div>
                </div>
            @endforeach
        </div>
        <div style="display:flex;gap:24px;margin-top:6px;">
            @foreach($registrationWeeks as $week)
                <div style="flex:1;text-align:center;font-size:0.72rem;color:#777;min-width:70px;">{{ $week['label'] }}</div>
            @endforeach
        </div>
    </div>

@endsection
