@extends('layouts.admin')
@section('title','Admin Account Details')
@section('content')

    <div style="max-width:700px;margin:0 auto">

        {{-- Back --}}
        <a href="{{ route('admin.admin-accounts') }}"
           style="display:inline-flex;align-items:center;gap:6px;color:#8b1c2c;font-size:.82rem;font-weight:600;text-decoration:none;margin-bottom:20px">
            ← Back to Admin Accounts
        </a>

        <div class="panel">

            {{-- Profile header --}}
            <div style="display:flex;align-items:center;gap:20px;padding-bottom:20px;border-bottom:1.5px solid #f0e8e8;margin-bottom:20px">

                {{-- Avatar --}}
                <div style="position:relative;flex-shrink:0">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($admin->name) }}&size=90&background=8b1c2c&color=fff"
                         style="width:90px;height:90px;border-radius:50%;border:4px solid #d9b8bc;box-shadow:0 4px 16px rgba(139,28,44,.2)">
                    <span style="position:absolute;bottom:4px;right:4px;width:16px;height:16px;border-radius:50%;background:{{ $admin->status==='active'?'#38a169':'#e53e3e' }};border:2px solid #fff"></span>
                </div>

                {{-- Name & role --}}
                <div style="flex:1">
                    <h1 style="font-size:1.3rem;font-weight:800;color:#1a1a1a;margin-bottom:4px">
                        {{ $admin->name }}
                    </h1>
                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                        <span class="badge badge-red">{{ strtoupper(str_replace('_',' ',$admin->role)) }}</span>
                        <span class="badge {{ $admin->status==='active'?'badge-green':'badge-red' }}">
                        {{ ucfirst($admin->status) }}
                    </span>
                    </div>
                    <div style="font-size:.8rem;color:#777;margin-top:6px">{{ $admin->email }}</div>
                </div>

                {{-- Toggle status --}}
                <form method="POST" action="{{ route('admin.admin-accounts.toggle', $admin) }}" style="flex-shrink:0">
                    @csrf
                    <button type="submit"
                            style="padding:8px 18px;border:none;border-radius:8px;font-size:.78rem;font-weight:700;cursor:pointer;background:{{ $admin->status==='active'?'#fee2e2':'#d4edda' }};color:{{ $admin->status==='active'?'#e53e3e':'#155724' }}">
                        {{ $admin->status === 'active' ? '🔴 Disable Account' : '🟢 Enable Account' }}
                    </button>
                </form>
            </div>

            {{-- Info grid --}}
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:14px;margin-bottom:24px">
                <div style="background:#fdf8f3;border-radius:8px;padding:12px 14px">
                    <div style="font-size:.68rem;color:#999;font-weight:700;text-transform:uppercase;letter-spacing:.6px;margin-bottom:4px">Student/Staff ID</div>
                    <div style="font-size:.88rem;font-weight:700;color:#333">{{ $admin->student_id ?? '—' }}</div>
                </div>
                <div style="background:#fdf8f3;border-radius:8px;padding:12px 14px">
                    <div style="font-size:.68rem;color:#999;font-weight:700;text-transform:uppercase;letter-spacing:.6px;margin-bottom:4px">Role</div>
                    <div style="font-size:.88rem;font-weight:700;color:#333">{{ ucfirst(str_replace('_',' ',$admin->role)) }}</div>
                </div>
                <div style="background:#fdf8f3;border-radius:8px;padding:12px 14px">
                    <div style="font-size:.68rem;color:#999;font-weight:700;text-transform:uppercase;letter-spacing:.6px;margin-bottom:4px">Status</div>
                    <div style="font-size:.88rem;font-weight:700;color:{{ $admin->status==='active'?'#38a169':'#e53e3e' }}">
                        {{ ucfirst($admin->status) }}
                    </div>
                </div>
                <div style="background:#fdf8f3;border-radius:8px;padding:12px 14px">
                    <div style="font-size:.68rem;color:#999;font-weight:700;text-transform:uppercase;letter-spacing:.6px;margin-bottom:4px">Last Login</div>
                    <div style="font-size:.82rem;font-weight:600;color:#333">
                        {{ $admin->last_login_at ? $admin->last_login_at->format('M d, Y h:i A') : '—' }}
                    </div>
                </div>
                <div style="background:#fdf8f3;border-radius:8px;padding:12px 14px">
                    <div style="font-size:.68rem;color:#999;font-weight:700;text-transform:uppercase;letter-spacing:.6px;margin-bottom:4px">Created</div>
                    <div style="font-size:.82rem;font-weight:600;color:#333">
                        {{ $admin->created_at->format('M d, Y') }}
                    </div>
                </div>
            </div>

            {{-- Permissions --}}
            <div style="margin-bottom:24px">
                <div style="font-size:.72rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;margin-bottom:12px">
                    Module Permissions
                </div>

                @if($admin->role === 'super_admin')
                    <div style="background:#d4edda;border:1px solid #c3e6cb;border-radius:8px;padding:12px 16px;color:#155724;font-size:.85rem;font-weight:600">
                        ✅ Super Admin — Full access to all modules
                    </div>
                @else
                    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:8px">
                        @foreach([
                            ['dashboard',       '🏠','Dashboard'],
                            ['analytics',       '📊','Analytics'],
                            ['users',           '👥','Users'],
                            ['announcements',   '📢','Announcements'],
                            ['events',          '📅','Events'],
                            ['organizations',   '🏛','Organizations'],
                            ['admin-accounts',  '🛡','Admin Accounts'],
                            ['reports',         '🚩','Reports'],
                            ['academic-notices','📋','Academic Notices'],
                            ['points',          '🏆','Points System'],
                            ['logs',            '📝','Activity Logs'],
                        ] as $perm)
                            @php $hasPermission = is_array($admin->permissions) && in_array($perm[0], $admin->permissions); @endphp
                            <div style="display:flex;align-items:center;gap:8px;padding:8px 12px;border-radius:8px;border:1.5px solid {{ $hasPermission?'#c3e6cb':'#f0d0d4' }};background:{{ $hasPermission?'#f0fff4':'#fff8f8' }}">
                                <span>{{ $perm[1] }}</span>
                                <span style="font-size:.78rem;font-weight:600;color:{{ $hasPermission?'#155724':'#999' }}">
                        {{ $perm[2] }}
                    </span>
                                <span style="margin-left:auto;font-size:.8rem">
                        {{ $hasPermission ? '✅' : '❌' }}
                    </span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Footer --}}
            <div style="display:flex;gap:10px;padding-top:16px;border-top:1.5px solid #f0e8e8">
                <a href="{{ route('admin.admin-accounts') }}" class="btn btn-outline">← Back</a>
            </div>

        </div>
    </div>

@endsection
