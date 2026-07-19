@extends('layouts.admin')
@section('title','Dashboard')
@section('content')

    <div class="stat-grid">
        <div class="stat-card" style="border-top:3px solid #f0a500">
            <div class="stat-label">Total User</div>
            <div class="stat-value">{{ number_format($stats['total_users']) }}</div>
            <div class="stat-sub" style="color:#38a169">↑ +38 this week</div>
        </div>
        <div class="stat-card" style="border-top:3px solid #38a169">
            <div class="stat-label">Active Today</div>
            <div class="stat-value">{{ number_format($stats['active_today']) }}</div>
            <div class="stat-sub" style="color:#38a169">↑ +12%</div>
        </div>
        <div class="stat-card" style="border-top:3px solid #e53e3e">
            <div class="stat-label">Pending Reports</div>
            <div class="stat-value">{{ $stats['pending_reports'] }}</div>
            <div class="stat-sub" style="color:#e53e3e">● {{ $stats['pending_reports'] }} high priority</div>
        </div>
        <div class="stat-card" style="border-top:3px solid #4299e1">
            <div class="stat-label">Event This Month</div>
            <div class="stat-value">{{ $stats['events_month'] }}</div>
            <div class="stat-sub">6 Upcoming</div>
        </div>
    </div>

    <div class="two-col">

        {{-- Recent Activity --}}
        <div class="panel">
            <div class="panel-header">
                <span class="panel-title">Recent Activity</span>
                <a href="{{ route('admin.logs') }}" class="btn btn-outline" style="font-size:.72rem">View logs →</a>
            </div>
            @forelse($recentActivity as $item)
                <div style="display:flex;gap:10px;align-items:flex-start;padding:9px 0;border-bottom:1px solid #f5eaea">
            <span style="width:8px;height:8px;border-radius:50%;flex-shrink:0;margin-top:5px;background:{{
                $item['color']==='red'    ? '#e53e3e' :
                ($item['color']==='green' ? '#38a169' : '#dd6b20')
            }}"></span>
                    <div>
                        <div style="font-size:.82rem">{!! $item['text'] !!}</div>
                        <div style="font-size:.7rem;color:#999;margin-top:2px">{{ $item['time'] }}</div>
                    </div>
                </div>
            @empty
                <p style="color:#999;font-size:.82rem;padding:12px 0">No activity yet.</p>
            @endforelse
        </div>

        {{-- Upcoming Events --}}
        <div class="panel">
            <div class="panel-header">
                <span class="panel-title">Upcoming Events</span>
                <a href="{{ route('admin.events') }}" class="btn btn-outline" style="font-size:.72rem">Manage →</a>
            </div>
            @forelse($upcomingEvents as $event)
                <div style="display:flex;align-items:center;gap:12px;padding:9px 0;border-bottom:1px solid #f5eaea">
                    <div style="background:#8b1c2c;color:#fff;border-radius:6px;padding:6px 10px;text-align:center;min-width:42px;flex-shrink:0">
                        <div style="font-size:.95rem;font-weight:800">{{ $event->event_date->format('j') }}</div>
                        <div style="font-size:.58rem;text-transform:uppercase">{{ $event->event_date->format('M') }}</div>
                    </div>
                    <div style="flex:1">
                        <div style="font-size:.85rem;font-weight:700">{{ $event->title }}</div>
                        <div style="font-size:.7rem;color:#888;margin-top:2px">
                            <x-icon name="map-pin" /> {{ $event->location ?? 'TBA' }}
                            @if($event->event_time)
                                <x-icon name="clock" /> {{ \Carbon\Carbon::parse($event->event_time)->format('g:i A') }}
                            @endif
                        </div>
                        <span class="badge badge-yellow" style="margin-top:3px;font-size:.62rem">
                    {{ ucfirst(str_replace('_',' ',$event->type)) }}
                </span>
                    </div>
                    <div style="text-align:right;font-size:.75rem">
                        <div style="font-weight:800;color:#8b1c2c">{{ $event->attendance_count }}</div>
                        <div style="color:#999">Attended</div>
                    </div>
                </div>
            @empty
                <p style="color:#999;font-size:.82rem;padding:12px 0">No upcoming events.</p>
            @endforelse
        </div>

    </div>

    <div class="two-col" style="margin-top:0">

        {{-- Users by Role --}}
        <div class="panel">
            <div class="panel-header">
                <span class="panel-title">Users by Role</span>
                <a href="{{ route('admin.users') }}" class="btn btn-outline" style="font-size:.72rem">View</a>
            </div>
            @php $maxRole = max(array_values($usersByRole)) ?: 1; @endphp
            @foreach($usersByRole as $role => $count)
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px">
                    <span style="width:88px;font-size:.8rem;flex-shrink:0">{{ $role }}</span>
                    <div style="flex:1;background:#f5eaea;border-radius:20px;height:10px">
                        <div style="background:#8b1c2c;height:10px;border-radius:20px;width:{{ ($count/$maxRole)*100 }}%"></div>
                    </div>
                    <span style="font-size:.8rem;font-weight:700;color:#8b1c2c;width:36px;text-align:right">
                {{ number_format($count) }}
            </span>
                </div>
            @endforeach
        </div>

        {{-- Top Reports --}}
        <div class="panel">
            <div class="panel-header">
                <span class="panel-title">Top Reports This Week</span>
                <a href="{{ route('admin.reports') }}" class="btn btn-outline" style="font-size:.72rem">View all</a>
            </div>
            @forelse($topReports as $report)
                <div style="display:flex;gap:12px;align-items:flex-start;padding:10px 0;border-bottom:1px solid #f5eaea">
                    <div style="width:40px;height:40px;border-radius:50%;background:{{ $report->priority==='high'?'#fed7d7':'#fefcbf' }};display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0">
                        @if($report->type==='Marketplace') <x-icon name="shopping-bag" /> @else <x-icon name="flag" /> @endif
                    </div>
                    <div style="flex:1">
                        <div style="font-size:.82rem;font-weight:700">{{ $report->title }}</div>
                        <div style="font-size:.7rem;color:#999;margin-top:2px">
                            Reported by {{ $report->reporter_count }} user{{ $report->reporter_count>1?'s':'' }}
                            · {{ $report->created_at->diffForHumans() }}
                        </div>
                        <div style="display:flex;gap:6px;margin-top:6px">
                            <a href="{{ route('admin.reports') }}" class="btn btn-outline" style="font-size:.7rem;padding:4px 10px">Review</a>
                            <a href="{{ route('admin.reports') }}" class="btn btn-outline" style="font-size:.7rem;padding:4px 10px">Dismiss</a>
                        </div>
                    </div>
                </div>
            @empty
                <p style="color:#999;font-size:.82rem;padding:12px 0">No open reports.</p>
            @endforelse
        </div>

    </div>

@endsection
