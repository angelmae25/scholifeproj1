@extends('layouts.admin')
@section('title','Activity Logs')
@section('content')

    {{-- Stats --}}
    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-label">Total Logs</div>
            <div class="stat-value">{{ number_format($stats['total']) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Today</div>
            <div class="stat-value">{{ $stats['today'] }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Logins</div>
            <div class="stat-value">{{ $stats['logins'] }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Actions Taken</div>
            <div class="stat-value">{{ $stats['actions'] }}</div>
        </div>
    </div>

    {{-- Log Table --}}
    <div class="panel">
        <div class="panel-header">
            <span class="panel-title">All Activity Logs</span>
            <span style="font-size:.78rem;color:#999">Showing all admin and system activity</span>
        </div>

        @if($logs->isEmpty())
            <div style="text-align:center;padding:40px;color:#999">
                <div style="font-size:2rem;margin-bottom:8px">📋</div>
                <div>No activity logs yet.</div>
                <div style="font-size:.78rem;margin-top:4px">Logs will appear here as admins use the system.</div>
            </div>
        @else
            <table>
                <thead>
                <tr>
                    <th style="width:160px">Time</th>
                    <th style="width:140px">Admin</th>
                    <th style="width:90px">Action</th>
                    <th style="width:110px">Module</th>
                    <th>Description</th>
                    <th style="width:110px">IP Address</th>
                </tr>
                </thead>
                <tbody>
                @foreach($logs as $log)
                    <tr>
                        {{-- Time --}}
                        <td>
                            <div style="font-size:.78rem;font-weight:600">
                                {{ $log->created_at->format('M d, Y') }}
                            </div>
                            <div style="font-size:.7rem;color:#aaa">
                                {{ $log->created_at->format('h:i A') }}
                                · {{ $log->created_at->diffForHumans() }}
                            </div>
                        </td>

                        {{-- Admin --}}
                        <td>
                            <div style="display:flex;align-items:center;gap:7px">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($log->admin->name ?? 'System') }}&size=28&background=8b1c2c&color=fff"
                                     style="width:28px;height:28px;border-radius:50%;flex-shrink:0">
                                <div>
                                    <div style="font-size:.78rem;font-weight:700">
                                        {{ $log->admin->name ?? 'System' }}
                                    </div>
                                    <div style="font-size:.65rem;color:#999">
                                        {{ $log->admin->role ?? '' }}
                                    </div>
                                </div>
                            </div>
                        </td>

                        {{-- Action badge --}}
                        <td>
                    <span class="badge {{
                        $log->action === 'LOGIN'   ? 'badge-green'  :
                        ($log->action === 'LOGOUT'  ? 'badge-gray'   :
                        ($log->action === 'DELETE'  ? 'badge-red'    :
                        ($log->action === 'CREATE'  ? 'badge-blue'   :
                        ($log->action === 'RESOLVE' ? 'badge-green'  :
                        ($log->action === 'DISABLE' ? 'badge-red'    :
                        ($log->action === 'VIEW'    ? 'badge-gray'   :
                        'badge-yellow'))))))
                    }}">
                        {{ $log->action }}
                    </span>
                        </td>

                        {{-- Module --}}
                        <td>
                    <span style="font-size:.78rem;color:#555;font-weight:600">
                        {{ $log->module }}
                    </span>
                        </td>

                        {{-- Description --}}
                        <td style="font-size:.8rem;color:#333">
                            {{ $log->description }}
                        </td>

                        {{-- IP --}}
                        <td style="font-size:.75rem;color:#999;font-family:monospace">
                            {{ $log->ip_address ?? '—' }}
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>

            {{-- Pagination --}}
            <div style="margin-top:16px">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

@endsection
