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
                <div style="font-size:2rem;margin-bottom:8px"><x-icon name="clipboard" /></div>
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
            @if($logs->hasPages())
                <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;margin-top:16px;flex-wrap:wrap">
                    <div style="font-size:.75rem;color:#777">
                        Showing {{ $logs->firstItem() }} to {{ $logs->lastItem() }} of {{ $logs->total() }} logs
                    </div>

                    <div style="display:flex;align-items:center;gap:6px">
                        @if($logs->onFirstPage())
                            <span class="icon-button" style="opacity:.35;pointer-events:none" aria-disabled="true">
                                <x-icon name="arrow-left" size="16" />
                            </span>
                        @else
                            <a href="{{ $logs->previousPageUrl() }}" class="icon-button" title="Previous page" aria-label="Previous page">
                                <x-icon name="arrow-left" size="16" />
                            </a>
                        @endif

                        @foreach($logs->getUrlRange(max(1, $logs->currentPage() - 2), min($logs->lastPage(), $logs->currentPage() + 2)) as $page => $url)
                            @if($page === $logs->currentPage())
                                <span style="display:inline-flex;align-items:center;justify-content:center;min-width:32px;height:32px;border-radius:6px;background:#8b1c2c;color:#fff;font-size:.78rem;font-weight:800">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $url }}" style="display:inline-flex;align-items:center;justify-content:center;min-width:32px;height:32px;border-radius:6px;border:1px solid #f0d0d4;color:#8b1c2c;text-decoration:none;font-size:.78rem;font-weight:700">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach

                        @if($logs->hasMorePages())
                            <a href="{{ $logs->nextPageUrl() }}" class="icon-button" title="Next page" aria-label="Next page">
                                <x-icon name="arrow-right" size="16" />
                            </a>
                        @else
                            <span class="icon-button" style="opacity:.35;pointer-events:none" aria-disabled="true">
                                <x-icon name="arrow-right" size="16" />
                            </span>
                        @endif
                    </div>
                </div>
            @endif
        @endif
    </div>

@endsection
