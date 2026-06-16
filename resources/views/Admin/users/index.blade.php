@extends('layouts.admin')
@section('title', 'Users')
@section('content')

    <div class="stat-grid">
        <div class="stat-card"><div class="stat-label">Total Accounts</div><div class="stat-value">{{ number_format($stats['total']) }}</div></div>
        <div class="stat-card"><div class="stat-label">Active</div><div class="stat-value">{{ number_format($stats['active']) }}</div></div>
        <div class="stat-card"><div class="stat-label">Deactivated</div><div class="stat-value">{{ number_format($stats['deactivated']) }}</div></div>
        <div class="stat-card"><div class="stat-label">New This Week</div><div class="stat-value">{{ $stats['new_this_week'] }}</div></div>
    </div>

    <div class="panel">
        <div class="panel-header">
            <span class="panel-title">User Account</span>
            <div style="display:flex;gap:8px;">
                <select style="padding:6px 10px;border:1.5px solid #d9b8bc;border-radius:6px;font-size:0.78rem;">
                    <option>All Roles</option>
                    <option>Student</option>
                    <option>Professor</option>
                    <option>Office</option>
                    <option>Org Officer</option>
                </select>
                <a href="#" class="btn btn-primary">+ Add User</a>
            </div>
        </div>
        <table>
            <thead>
            <tr>
                <th>Name</th>
                <th>Role</th>
                <th>Department</th>
                <th>Status</th>
                <th>Last Active</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            @forelse($users as $user)
                <tr>
                    <td>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&size=32&background=c9999f&color=fff" style="width:32px;height:32px;border-radius:50%;">
                            <div>
                                <div style="font-weight:700;">{{ $user->name }}</div>
                                <div style="font-size:0.7rem;color:#999;">{{ $user->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td><span class="badge badge-yellow">{{ ucfirst($user->role) }}</span></td>
                    <td>{{ $user->department }}</td>
                    <td><span class="badge {{ $user->status === 'active' ? 'badge-green' : 'badge-red' }}">{{ ucfirst($user->status) }}</span></td>
                    <td>{{ $user->last_active_at ? $user->last_active_at->diffForHumans() : '—' }}</td>
                    <td><a href="#" style="color:#8b1c2c;">👁</a></td>
                </tr>
            @empty
                <tr><td colspan="6" style="text-align:center;color:#999;padding:24px;">No users found.</td></tr>
            @endforelse
            </tbody>
        </table>
        <div style="margin-top:16px;">{{ $users->links() }}</div>
    </div>

@endsection
