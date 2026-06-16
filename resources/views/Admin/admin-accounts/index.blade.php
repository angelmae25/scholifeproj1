@extends('layouts.admin')
@section('title','Admin Account')
@section('content')

    <div class="stat-grid">
        <div class="stat-card"><div class="stat-label">Admin Account</div><div class="stat-value">{{ $stats['total'] }}</div></div>
        <div class="stat-card"><div class="stat-label">Active</div><div class="stat-value">{{ $stats['active'] }}</div></div>
        <div class="stat-card"><div class="stat-label">Deactivated</div><div class="stat-value">{{ $stats['deactivated'] }}</div></div>
    </div>

    <div class="panel">
        <div class="panel-header">
            <span class="panel-title">Admin Account</span>
            <button onclick="openModal()" class="btn btn-outline">+ New Admin Account</button>
        </div>
        <table>
            <thead>
            <tr>
                <th>Admin</th>
                <th>Role</th>
                <th>ID</th>
                <th>Permissions</th>
                <th>Status</th>
                <th>Last Login</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            @forelse($admins as $admin)
                <tr>
                    <td>
                        <a href="{{ route('admin.admin-accounts.show', $admin->id) }}"
                           style="display:flex;align-items:center;gap:8px;text-decoration:none;color:inherit">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($admin->name) }}&size=36&background=8b1c2c&color=fff"
                                 style="width:36px;height:36px;border-radius:50%;flex-shrink:0;border:2px solid #d9b8bc;transition:border-color .2s"
                                 onmouseover="this.style.borderColor='#8b1c2c'" onmouseout="this.style.borderColor='#d9b8bc'">
                            <div>
                                <div style="font-weight:700;color:#1a1a1a">{{ $admin->name }}</div>
                                <div style="font-size:.7rem;color:#999">{{ $admin->email }}</div>
                            </div>
                        </a>
                    </td>
                    <td><span class="badge badge-red">{{ strtoupper(str_replace('_',' ',$admin->role)) }}</span></td>
                    <td style="font-size:.8rem">{{ $admin->student_id ?? '—' }}</td>
                    <td>
                        @if($admin->role === 'super_admin')
                            <span class="badge badge-blue">All Access</span>
                        @else
                            <div style="display:flex;flex-wrap:wrap;gap:3px">
                                @foreach($admin->permissions ?? [] as $perm)
                                    <span style="background:#f5eaea;color:#8b1c2c;padding:2px 6px;border-radius:10px;font-size:.6rem;font-weight:700">
                                {{ ucfirst(str_replace('-',' ',$perm)) }}
                            </span>
                                @endforeach
                            </div>
                        @endif
                    </td>
                    <td>
                    <span class="badge {{ $admin->status === 'active' ? 'badge-green' : 'badge-red' }}">
                        {{ ucfirst($admin->status) }}
                    </span>
                    </td>
                    <td style="font-size:.78rem;color:#777">
                        {{ $admin->last_login_at ? $admin->last_login_at->format('M d, Y h:i A') : '—' }}
                    </td>
                    <td>
                        <form method="POST" action="{{ route('admin.admin-accounts.toggle', $admin->id) }}">
                            @csrf
                            <button type="submit"
                                    style="padding:5px 12px;border:none;border-radius:6px;font-size:.72rem;font-weight:600;cursor:pointer;background:{{ $admin->status === 'active' ? '#fee2e2' : '#d4edda' }};color:{{ $admin->status === 'active' ? '#e53e3e' : '#155724' }}">
                                {{ $admin->status === 'active' ? 'Disable' : 'Enable' }}
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" style="text-align:center;color:#999;padding:24px">No admin accounts found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- CREATE ADMIN MODAL --}}
    <div id="adminModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:999;align-items:center;justify-content:center">
        <div style="background:#fff;border-radius:14px;width:580px;max-width:95vw;max-height:90vh;overflow-y:auto;padding:28px 32px;box-shadow:0 8px 40px rgba(0,0,0,.2)">

            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
                <h2 style="font-size:1.2rem;font-weight:800;color:#8b1c2c">Create Admin Account</h2>
                <button type="button" onclick="closeModal()" style="background:none;border:none;font-size:1.4rem;cursor:pointer;color:#999">✕</button>
            </div>

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

            <form method="POST" action="{{ route('admin.admin-accounts.store') }}">
                @csrf

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px">
                    <div>
                        <label style="font-size:.7rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;display:block;margin-bottom:4px">Full Name</label>
                        <input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Maria Santos" required
                               style="width:100%;border:1.5px solid #c9999f;border-radius:8px;padding:9px 12px;font-size:.85rem;outline:none">
                    </div>
                    <div>
                        <label style="font-size:.7rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;display:block;margin-bottom:4px">Email</label>
                        <input type="email" name="email" value="{{ old('email') }}" placeholder="e.g. maria@scholife.com" required
                               style="width:100%;border:1.5px solid #c9999f;border-radius:8px;padding:9px 12px;font-size:.85rem;outline:none">
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px">
                    <div>
                        <label style="font-size:.7rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;display:block;margin-bottom:4px">Student/Staff ID</label>
                        <input type="text" name="student_id" value="{{ old('student_id') }}" placeholder="e.g. 2024-00001"
                               style="width:100%;border:1.5px solid #c9999f;border-radius:8px;padding:9px 12px;font-size:.85rem;outline:none">
                    </div>
                    <div>
                        <label style="font-size:.7rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;display:block;margin-bottom:4px">Role</label>
                        <select name="role" id="roleSelect" onchange="togglePermissions()"
                                style="width:100%;border:1.5px solid #c9999f;border-radius:8px;padding:9px 12px;font-size:.85rem;outline:none">
                            <option value="admin"       {{ old('role')==='admin'       ?'selected':'' }}>Admin</option>
                            <option value="moderator"   {{ old('role')==='moderator'   ?'selected':'' }}>Moderator</option>
                            <option value="super_admin" {{ old('role')==='super_admin' ?'selected':'' }}>Super Admin</option>
                        </select>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px">
                    <div>
                        <label style="font-size:.7rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;display:block;margin-bottom:4px">Password</label>
                        <input type="password" name="password" placeholder="Min. 6 characters" required
                               style="width:100%;border:1.5px solid #c9999f;border-radius:8px;padding:9px 12px;font-size:.85rem;outline:none">
                    </div>
                    <div>
                        <label style="font-size:.7rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;display:block;margin-bottom:4px">Confirm Password</label>
                        <input type="password" name="password_confirmation" placeholder="Repeat password" required
                               style="width:100%;border:1.5px solid #c9999f;border-radius:8px;padding:9px 12px;font-size:.85rem;outline:none">
                    </div>
                </div>

                <div id="permissionsSection" style="margin-bottom:20px">
                    <label style="font-size:.7rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;display:block;margin-bottom:10px">
                        Module Permissions
                        <span style="font-weight:400;color:#999;text-transform:none;letter-spacing:0;font-size:.68rem;margin-left:6px">
                        (Super Admin gets all automatically)
                    </span>
                    </label>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px">
                        @foreach([
                            ['dashboard',        '🏠', 'Dashboard'],
                            ['analytics',        '📊', 'Analytics'],
                            ['users',            '👥', 'Users'],
                            ['announcements',    '📢', 'Announcements'],
                            ['events',           '📅', 'Events'],
                            ['organizations',    '🏛', 'Organizations'],
                            ['admin-accounts',   '🛡', 'Admin Accounts'],
                            ['reports',          '🚩', 'Reports'],
                            ['academic-notices', '📋', 'Academic Notices'],
                            ['points',           '🏆', 'Points System'],
                            ['logs',             '📝', 'Activity Logs'],
                        ] as $perm)
                            <label style="display:flex;align-items:center;gap:8px;padding:8px 12px;border:1.5px solid #f0d0d4;border-radius:8px;cursor:pointer;font-size:.8rem;color:#333;background:#fff8f8;transition:background .15s"
                                   onmouseover="this.style.background='#fdf0f1'" onmouseout="this.style.background='#fff8f8'">
                                <input type="checkbox" name="permissions[]" value="{{ $perm[0] }}"
                                       style="accent-color:#8b1c2c;width:14px;height:14px"
                                    {{ is_array(old('permissions')) && in_array($perm[0], old('permissions')) ? 'checked' : '' }}>
                                <span>{{ $perm[1] }} {{ $perm[2] }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div style="display:flex;gap:10px;justify-content:flex-end;padding-top:4px">
                    <button type="button" onclick="closeModal()"
                            style="padding:9px 18px;border:1.5px solid #ccc;background:#fff;color:#666;border-radius:8px;font-size:.82rem;font-weight:600;cursor:pointer">
                        Cancel
                    </button>
                    <button type="submit"
                            style="padding:9px 20px;background:#8b1c2c;color:#fff;border:none;border-radius:8px;font-size:.82rem;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:6px">
                        ✅ Create Admin
                    </button>
                </div>
            </form>
        </div>
    </div>

    @if($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                document.getElementById('adminModal').style.display = 'flex';
                document.body.style.overflow = 'hidden';
            });
        </script>
    @endif

    <script>
        function openModal() {
            document.getElementById('adminModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
        function closeModal() {
            document.getElementById('adminModal').style.display = 'none';
            document.body.style.overflow = '';
        }
        document.getElementById('adminModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });
        function togglePermissions() {
            const role    = document.getElementById('roleSelect').value;
            const section = document.getElementById('permissionsSection');
            const boxes   = document.querySelectorAll('[name="permissions[]"]');
            if (role === 'super_admin') {
                section.style.opacity = '0.4';
                section.style.pointerEvents = 'none';
                boxes.forEach(cb => cb.checked = true);
            } else {
                section.style.opacity = '1';
                section.style.pointerEvents = 'auto';
                boxes.forEach(cb => cb.checked = false);
            }
        }
    </script>

@endsection
