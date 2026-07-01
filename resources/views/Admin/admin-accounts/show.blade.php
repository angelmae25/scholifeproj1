@extends('layouts.admin')
@section('title','Admin Account Details')
@section('content')

@php
    $modulePermissions = [
        ['dashboard', 'home','Dashboard'],
        ['analytics', 'chart','Analytics'],
        ['users', 'users','Users'],
        ['announcements', 'megaphone','Announcements'],
        ['events', 'calendar','Events'],
        ['organizations', 'building','Organizations'],
        ['admin-accounts', 'shield','Admin Accounts'],
        ['reports', 'flag','Reports'],
        ['academic-notices', 'clipboard','Academic Notices'],
        ['points', 'trophy','Points System'],
        ['logs', 'pencil','Activity Logs'],
    ];
    $currentAdmin = Auth::guard('admin')->user();
    $canManageAdmins = $currentAdmin && $currentAdmin->role === 'super_admin';
    $isOwnProfile = $currentAdmin && $currentAdmin->id === $admin->id;
    $adminPermissions = $admin->role === 'super_admin' ? collect($modulePermissions)->pluck(0)->all() : ($admin->permissions ?? []);
    $avatarUrl = $admin->avatar ? asset('storage/'.$admin->avatar) : 'https://ui-avatars.com/api/?name='.urlencode($admin->name).'&size=90&background=8b1c2c&color=fff';
    $backUrl = $canManageAdmins ? route('admin.admin-accounts') : route('admin.dashboard');
@endphp

<div style="max-width:700px;margin:0 auto">
    <a href="{{ $backUrl }}" style="display:inline-flex;align-items:center;gap:6px;color:#8b1c2c;font-size:.82rem;font-weight:600;text-decoration:none;margin-bottom:20px">&larr; Back</a>

    @if(session('success'))
        <div style="background:#d4edda;color:#155724;border:1px solid #c3e6cb;border-radius:8px;padding:12px 16px;margin-bottom:16px;font-size:.85rem;font-weight:600">
            <x-icon name="check-circle" /> {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div style="background:#f8d7da;color:#721c24;border:1px solid #f5c6cb;border-radius:8px;padding:12px 16px;margin-bottom:16px;font-size:.82rem">
            <strong>Please fix the following:</strong>
            <ul style="margin:6px 0 0;padding-left:18px">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="panel">
        <div style="display:flex;align-items:center;gap:20px;padding-bottom:20px;border-bottom:1.5px solid #f0e8e8;margin-bottom:20px;position:relative">
            @if($canManageAdmins || $isOwnProfile)
                <button type="button" onclick="openEditModal()" title="Edit admin profile" style="position:absolute;top:0;right:0;width:38px;height:38px;border:none;border-radius:10px;background:#f5eaea;color:#8b1c2c;display:flex;align-items:center;justify-content:center;cursor:pointer">
                    <x-icon name="pencil" />
                </button>
            @endif

            <div style="position:relative;flex-shrink:0">
                <img src="{{ $avatarUrl }}" style="width:90px;height:90px;border-radius:50%;object-fit:cover;border:4px solid #d9b8bc;box-shadow:0 4px 16px rgba(139,28,44,.2)">
                <span style="position:absolute;bottom:4px;right:4px;width:16px;height:16px;border-radius:50%;background:{{ $admin->status==='active'?'#38a169':'#e53e3e' }};border:2px solid #fff"></span>
            </div>

            <div style="flex:1;padding-right:46px">
                <h1 style="font-size:1.3rem;font-weight:800;color:#1a1a1a;margin-bottom:4px">{{ $admin->name }}</h1>
                <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                    <span class="badge badge-red">{{ strtoupper(str_replace('_',' ',$admin->role)) }}</span>
                    <span class="badge {{ $admin->status==='active'?'badge-green':'badge-red' }}">{{ ucfirst($admin->status) }}</span>
                </div>
                <div style="font-size:.8rem;color:#777;margin-top:6px">{{ $admin->email }}</div>
            </div>

            @if($canManageAdmins && !$isOwnProfile)
                <form method="POST" action="{{ route('admin.admin-accounts.toggle', $admin) }}" style="flex-shrink:0">
                    @csrf
                    <button type="submit" style="padding:8px 18px;border:none;border-radius:8px;font-size:.78rem;font-weight:700;cursor:pointer;background:{{ $admin->status==='active'?'#fee2e2':'#d4edda' }};color:{{ $admin->status==='active'?'#e53e3e':'#155724' }}">
                        @if($admin->status === 'active')
                            <x-icon name="x-circle" /> Disable Account
                        @else
                            <x-icon name="check-circle" /> Enable Account
                        @endif
                    </button>
                </form>
            @endif
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:14px;margin-bottom:24px">
            <div style="background:#fdf8f3;border-radius:8px;padding:12px 14px"><div style="font-size:.68rem;color:#999;font-weight:700;text-transform:uppercase;letter-spacing:.6px;margin-bottom:4px">Student/Staff ID</div><div style="font-size:.88rem;font-weight:700;color:#333">{{ $admin->student_id ?? '—' }}</div></div>
            <div style="background:#fdf8f3;border-radius:8px;padding:12px 14px"><div style="font-size:.68rem;color:#999;font-weight:700;text-transform:uppercase;letter-spacing:.6px;margin-bottom:4px">Role</div><div style="font-size:.88rem;font-weight:700;color:#333">{{ ucfirst(str_replace('_',' ',$admin->role)) }}</div></div>
            <div style="background:#fdf8f3;border-radius:8px;padding:12px 14px"><div style="font-size:.68rem;color:#999;font-weight:700;text-transform:uppercase;letter-spacing:.6px;margin-bottom:4px">Status</div><div style="font-size:.88rem;font-weight:700;color:{{ $admin->status==='active'?'#38a169':'#e53e3e' }}">{{ ucfirst($admin->status) }}</div></div>
            <div style="background:#fdf8f3;border-radius:8px;padding:12px 14px"><div style="font-size:.68rem;color:#999;font-weight:700;text-transform:uppercase;letter-spacing:.6px;margin-bottom:4px">Last Login</div><div style="font-size:.82rem;font-weight:600;color:#333">{{ $admin->last_login_at ? $admin->last_login_at->format('M d, Y h:i A') : '—' }}</div></div>
            <div style="background:#fdf8f3;border-radius:8px;padding:12px 14px"><div style="font-size:.68rem;color:#999;font-weight:700;text-transform:uppercase;letter-spacing:.6px;margin-bottom:4px">Created</div><div style="font-size:.82rem;font-weight:600;color:#333">{{ $admin->created_at->format('M d, Y') }}</div></div>
        </div>

        <div style="margin-bottom:24px">
            <div style="font-size:.72rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;margin-bottom:12px">Module Permissions</div>
            @if($admin->role === 'super_admin')
                <div style="background:#d4edda;border:1px solid #c3e6cb;border-radius:8px;padding:12px 16px;color:#155724;font-size:.85rem;font-weight:600"><x-icon name="check-circle" /> Super Admin — Full access to all modules</div>
            @else
                <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:8px">
                    @foreach($modulePermissions as $perm)
                        @php $hasPermission = in_array($perm[0], $adminPermissions); @endphp
                        <div style="display:flex;align-items:center;gap:8px;padding:8px 12px;border-radius:8px;border:1.5px solid {{ $hasPermission?'#c3e6cb':'#f0d0d4' }};background:{{ $hasPermission?'#f0fff4':'#fff8f8' }}">
                            <x-icon name="{{ $perm[1] }}" />
                            <span style="font-size:.78rem;font-weight:600;color:{{ $hasPermission?'#155724':'#999' }}">{{ $perm[2] }}</span>
                            <span style="margin-left:auto;font-size:.8rem;color:{{ $hasPermission?'#38a169':'#e53e3e' }}"><x-icon name="{{ $hasPermission ? 'check-circle' : 'x-circle' }}" /></span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div style="display:flex;gap:10px;padding-top:16px;border-top:1.5px solid #f0e8e8"><a href="{{ $backUrl }}" class="btn btn-outline">&larr; Back</a></div>
    </div>
</div>

@if($canManageAdmins || $isOwnProfile)
<div id="editAdminModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:999;align-items:center;justify-content:center">
    <div style="background:#fff;border-radius:14px;width:620px;max-width:95vw;max-height:92vh;overflow-y:auto;padding:28px 32px;box-shadow:0 8px 40px rgba(0,0,0,.2)">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
            <h2 style="font-size:1.2rem;font-weight:800;color:#8b1c2c">Edit Admin Profile</h2>
            <button type="button" onclick="closeEditModal()" style="background:none;border:none;font-size:1.4rem;cursor:pointer;color:#999"><x-icon name="x" /></button>
        </div>

        <form method="POST" action="{{ route('admin.admin-accounts.update', $admin) }}" enctype="multipart/form-data">
            @csrf
            @method('PATCH')

            <div style="display:flex;align-items:center;gap:14px;margin-bottom:16px">
                <img src="{{ $avatarUrl }}" style="width:70px;height:70px;border-radius:50%;object-fit:cover;border:3px solid #d9b8bc">
                <div style="flex:1"><label style="font-size:.7rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;display:block;margin-bottom:5px">Profile Picture</label><input type="file" name="avatar" accept="image/*" style="width:100%;font-size:.82rem"></div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px">
                <div><label style="font-size:.7rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;display:block;margin-bottom:4px">Full Name</label><input type="text" name="name" value="{{ old('name', $admin->name) }}" required style="width:100%;border:1.5px solid #c9999f;border-radius:8px;padding:9px 12px;font-size:.85rem;outline:none"></div>
                <div><label style="font-size:.7rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;display:block;margin-bottom:4px">Email</label><input type="email" name="email" value="{{ old('email', $admin->email) }}" required style="width:100%;border:1.5px solid #c9999f;border-radius:8px;padding:9px 12px;font-size:.85rem;outline:none"></div>
                <div><label style="font-size:.7rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;display:block;margin-bottom:4px">Student/Staff ID</label><input type="text" name="student_id" value="{{ old('student_id', $admin->student_id) }}" style="width:100%;border:1.5px solid #c9999f;border-radius:8px;padding:9px 12px;font-size:.85rem;outline:none"></div>
                @if($canManageAdmins)
                    <div><label style="font-size:.7rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;display:block;margin-bottom:4px">Role</label><select name="role" id="editRoleSelect" onchange="toggleEditPermissions()" style="width:100%;border:1.5px solid #c9999f;border-radius:8px;padding:9px 12px;font-size:.85rem;outline:none"><option value="admin" {{ old('role', $admin->role)==='admin'?'selected':'' }}>Admin</option><option value="moderator" {{ old('role', $admin->role)==='moderator'?'selected':'' }}>Moderator</option><option value="super_admin" {{ old('role', $admin->role)==='super_admin'?'selected':'' }}>Super Admin</option></select></div>
                @else
                    <div><label style="font-size:.7rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;display:block;margin-bottom:4px">Role</label><div style="width:100%;border:1.5px solid #ead2d5;border-radius:8px;padding:9px 12px;font-size:.85rem;background:#fdf8f3;color:#666">{{ ucfirst(str_replace('_', ' ', $admin->role)) }}</div></div>
                @endif
                <div><label style="font-size:.7rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;display:block;margin-bottom:4px">New Password</label><input type="password" name="password" placeholder="Leave blank to keep current" style="width:100%;border:1.5px solid #c9999f;border-radius:8px;padding:9px 12px;font-size:.85rem;outline:none"></div>
                <div><label style="font-size:.7rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;display:block;margin-bottom:4px">Confirm Password</label><input type="password" name="password_confirmation" placeholder="Repeat new password" style="width:100%;border:1.5px solid #c9999f;border-radius:8px;padding:9px 12px;font-size:.85rem;outline:none"></div>
            </div>

            @if($canManageAdmins)
                <div id="editPermissionsSection" style="margin-bottom:20px">
                    <label style="font-size:.7rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;display:block;margin-bottom:10px">Module Permissions</label>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
                        @foreach($modulePermissions as $perm)
                            <label style="display:flex;align-items:center;gap:8px;padding:8px 12px;border:1.5px solid #f0d0d4;border-radius:8px;cursor:pointer;font-size:.8rem;color:#333;background:#fff8f8">
                                <input type="checkbox" name="permissions[]" value="{{ $perm[0] }}" style="accent-color:#8b1c2c;width:14px;height:14px" {{ in_array($perm[0], old('permissions', $adminPermissions)) ? 'checked' : '' }}>
                                <span style="display:inline-flex;align-items:center;gap:6px"><x-icon name="{{ $perm[1] }}" /> {{ $perm[2] }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif

            <div style="display:flex;gap:10px;justify-content:flex-end;padding-top:4px"><button type="button" onclick="closeEditModal()" style="padding:9px 18px;border:1.5px solid #ccc;background:#fff;color:#666;border-radius:8px;font-size:.82rem;font-weight:600;cursor:pointer">Cancel</button><button type="submit" style="padding:9px 20px;background:#8b1c2c;color:#fff;border:none;border-radius:8px;font-size:.82rem;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:6px"><x-icon name="check-circle" /> Save Changes</button></div>
        </form>
    </div>
</div>
@endif

<script>
    function openEditModal() {
        const modal = document.getElementById('editAdminModal');
        if (!modal) return;
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        toggleEditPermissions();
    }
    function closeEditModal() {
        const modal = document.getElementById('editAdminModal');
        if (!modal) return;
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
    function toggleEditPermissions() {
        const roleInput = document.getElementById('editRoleSelect');
        const section = document.getElementById('editPermissionsSection');
        if (!roleInput || !section) return;
        const isSuperAdmin = roleInput.value === 'super_admin';
        const boxes = section.querySelectorAll('[name="permissions[]"]');
        section.style.opacity = isSuperAdmin ? '0.45' : '1';
        section.style.pointerEvents = isSuperAdmin ? 'none' : 'auto';
        if (isSuperAdmin) boxes.forEach(cb => cb.checked = true);
    }
    const modal = document.getElementById('editAdminModal');
    if (modal) modal.addEventListener('click', function(e) { if (e.target === this) closeEditModal(); });
    @if($errors->any()) document.addEventListener('DOMContentLoaded', openEditModal); @endif
</script>

@endsection
