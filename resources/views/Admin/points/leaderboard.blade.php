@extends('layouts.admin')
@section('title','Points System')
@section('content')

    <div class="stat-grid">
        <div class="stat-card">
            <div class="stat-label">Points Awarded</div>
            <div class="stat-value">{{ number_format($stats['points_awarded']) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Reward Claimed</div>
            <div class="stat-value">{{ $stats['rewards_claimed'] }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Active Rules</div>
            <div class="stat-value">{{ $stats['active_rules'] }}</div>
        </div>
    </div>

    <div class="panel">
        <div class="panel-header">
            <span class="panel-title">Leaderboard</span>
            <a href="{{ route('admin.points') }}" class="btn btn-outline" style="font-size:.72rem">← Back to Points</a>
        </div>

        {{-- Top 3 podium --}}
        @if($users->count() >= 3)
            <div style="display:flex;align-items:flex-end;justify-content:center;gap:16px;padding:20px 0 30px;border-bottom:1.5px solid #f0e8e8;margin-bottom:20px">

                {{-- 2nd place --}}
                <div style="text-align:center;flex:1">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($users[1]->name) }}&size=52&background=c9999f&color=fff"
                         style="width:52px;height:52px;border-radius:50%;border:3px solid #c0c0c0;margin-bottom:6px">
                    <div style="font-size:.78rem;font-weight:800;color:#1a1a1a">{{ $users[1]->name }}</div>
                    <div style="font-size:.68rem;color:#999;margin-bottom:4px">{{ $users[1]->department }}</div>
                    <div style="background:#c0c0c0;color:#fff;border-radius:8px;padding:6px;font-size:.8rem;font-weight:800">
                        🥈 {{ number_format($users[1]->points) }} pts
                    </div>
                </div>

                {{-- 1st place --}}
                <div style="text-align:center;flex:1;transform:translateY(-12px)">
                    <div style="font-size:1.3rem;margin-bottom:4px">👑</div>
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($users[0]->name) }}&size=64&background=8b1c2c&color=fff"
                         style="width:64px;height:64px;border-radius:50%;border:3px solid #f0a500;margin-bottom:6px">
                    <div style="font-size:.85rem;font-weight:800;color:#1a1a1a">{{ $users[0]->name }}</div>
                    <div style="font-size:.7rem;color:#999;margin-bottom:4px">{{ $users[0]->department }}</div>
                    <div style="background:#8b1c2c;color:#fff;border-radius:8px;padding:8px;font-size:.85rem;font-weight:800">
                        🥇 {{ number_format($users[0]->points) }} pts
                    </div>
                </div>

                {{-- 3rd place --}}
                <div style="text-align:center;flex:1">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($users[2]->name) }}&size=52&background=c9999f&color=fff"
                         style="width:52px;height:52px;border-radius:50%;border:3px solid #cd7f32;margin-bottom:6px">
                    <div style="font-size:.78rem;font-weight:800;color:#1a1a1a">{{ $users[2]->name }}</div>
                    <div style="font-size:.68rem;color:#999;margin-bottom:4px">{{ $users[2]->department }}</div>
                    <div style="background:#cd7f32;color:#fff;border-radius:8px;padding:6px;font-size:.8rem;font-weight:800">
                        🥉 {{ number_format($users[2]->points) }} pts
                    </div>
                </div>

            </div>
        @endif

        {{-- Full ranking table --}}
        <table>
            <thead>
            <tr>
                <th style="width:50px">Rank</th>
                <th>Student</th>
                <th>Department</th>
                <th>Role</th>
                <th style="text-align:right">Points</th>
            </tr>
            </thead>
            <tbody>
            @forelse($users as $i => $user)
                <tr style="{{ $i < 3 ? 'background:#fffbf0' : '' }}">
                    <td style="text-align:center;font-weight:800;font-size:.9rem">
                        @if($i === 0) 🥇
                        @elseif($i === 1) 🥈
                        @elseif($i === 2) 🥉
                        @else <span style="color:#777">{{ $users->firstItem() + $i }}</span>
                        @endif
                    </td>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&size=36&background={{ $i===0?'8b1c2c':($i===1?'888':'c9999f') }}&color=fff"
                                 style="width:36px;height:36px;border-radius:50%;flex-shrink:0">
                            <div>
                                <div style="font-weight:700;font-size:.85rem">{{ $user->name }}</div>
                                <div style="font-size:.7rem;color:#999">{{ $user->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td style="font-size:.82rem">{{ $user->department ?? '—' }}</td>
                    <td>
                        <span class="badge badge-yellow">{{ ucfirst($user->role) }}</span>
                    </td>
                    <td style="text-align:right">
                        <span style="font-weight:800;color:#8b1c2c;font-size:.9rem">{{ number_format($user->points) }}</span>
                        <span style="font-size:.7rem;color:#aaa"> pts</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align:center;color:#999;padding:32px">
                        No users in the leaderboard yet.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>

        <div style="margin-top:16px">{{ $users->links() }}</div>
    </div>

@endsection
