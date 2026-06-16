@extends('layouts.admin')
@section('title', 'Analytics')
@section('content')

    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-label">Daily Active Users</div>
            <div class="stat-value">{{ number_format($stats['daily_active']) }}</div>
            <div class="stat-sub" style="color:#38a169">+12% vs yesterday</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Weekly Sessions</div>
            <div class="stat-value">{{ number_format($stats['weekly_sessions']) }}</div>
            <div class="stat-sub" style="color:#38a169">+7% this week</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Avg Session Time</div>
            <div class="stat-value">{{ $stats['avg_session'] }}</div>
            <div class="stat-sub" style="color:#38a169">+1.2m</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">System Uptime</div>
            <div class="stat-value">{{ $stats['uptime'] }}</div>
            <div class="stat-sub">30-day avg</div>
        </div>
    </div>

    <div class="two-col">
        <!-- Engagement by Feature -->
        <div class="panel">
            <div class="panel-header"><span class="panel-title">Engagement by Feature</span></div>
            @php $maxEng = max(array_values($engagement)) ?: 1; @endphp
            @foreach($engagement as $feature => $count)
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:14px;">
                    <span style="width:110px;font-size:0.82rem;">{{ $feature }}</span>
                    <div style="flex:1;background:#f5eaea;border-radius:20px;height:14px;">
                        <div style="background:#8b1c2c;height:14px;border-radius:20px;width:{{ ($count/$maxEng)*100 }}%;"></div>
                    </div>
                    <span style="font-size:0.82rem;font-weight:700;color:#333;width:40px;text-align:right;">{{ number_format($count) }}</span>
                </div>
            @endforeach
        </div>

        <!-- Recent Activity -->
        <div class="panel" style="border:2px solid #3182ce;">
            <div class="panel-header">
                <span class="panel-title" style="color:#3182ce;">Recent Activity</span>
                <a href="#" class="btn btn-outline">View logs</a>
            </div>
            @foreach($recentActivity as $item)
                <div style="display:flex;gap:10px;align-items:flex-start;padding:10px 0;border-bottom:1px solid #ebf4ff;">
                    <span style="width:8px;height:8px;border-radius:50%;background:{{ $item['color'] === 'green' ? '#38a169' : '#dd6b20' }};margin-top:5px;flex-shrink:0;"></span>
                    <div>
                        <div style="font-size:0.82rem;">{{ $item['text'] }}</div>
                        <div style="font-size:0.7rem;color:#999;margin-top:2px;">{{ $item['time'] }}</div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Bar chart (pure CSS) -->
    <div class="panel" style="margin-top:20px;">
        <div style="font-size:0.95rem;font-weight:800;color:#333;margin-bottom:4px;">👥 New user registration</div>
        <div style="font-size:0.75rem;color:#777;margin-bottom:16px;">By week, May 2026</div>
        <div style="display:flex;gap:8px;margin-bottom:12px;font-size:0.72rem;">
            <span><span style="display:inline-block;width:12px;height:12px;background:#8b1c2c;border-radius:2px;margin-right:4px;"></span>Students</span>
            <span><span style="display:inline-block;width:12px;height:12px;background:#38a169;border-radius:2px;margin-right:4px;"></span>Professors</span>
            <span><span style="display:inline-block;width:12px;height:12px;background:#f9c6cb;border-radius:2px;margin-right:4px;"></span>Offices</span>
            <span><span style="display:inline-block;width:12px;height:12px;background:#4299e1;border-radius:2px;margin-right:4px;"></span>Organization</span>
        </div>
        <div style="display:flex;align-items:flex-end;gap:24px;height:120px;border-bottom:1px solid #eee;padding-bottom:4px;">
            @foreach([['s'=>18,'p'=>8,'o'=>4,'or'=>2],['s'=>11,'p'=>5,'o'=>2,'or'=>1],['s'=>14,'p'=>7,'o'=>3,'or'=>2],['s'=>10,'p'=>6,'o'=>3,'or'=>1]] as $i => $week)
                @php $total = $week['s']+$week['p']+$week['o']+$week['or']; $max=20; @endphp
                <div style="display:flex;flex-direction:column;align-items:center;flex:1;">
                    <div style="display:flex;flex-direction:column-reverse;width:60%;height:100px;">
                        <div style="background:#8b1c2c;height:{{ ($week['s']/$max)*100 }}%;"></div>
                        <div style="background:#38a169;height:{{ ($week['p']/$max)*100 }}%;"></div>
                        <div style="background:#f9c6cb;height:{{ ($week['o']/$max)*100 }}%;"></div>
                        <div style="background:#4299e1;height:{{ ($week['or']/$max)*100 }}%;"></div>
                    </div>
                </div>
            @endforeach
        </div>
        <div style="display:flex;gap:24px;margin-top:6px;">
            @foreach(['Week 1','Week 2','Week 3','Week 4'] as $w)
                <div style="flex:1;text-align:center;font-size:0.72rem;color:#777;">{{ $w }}</div>
            @endforeach
        </div>
    </div>

@endsection
