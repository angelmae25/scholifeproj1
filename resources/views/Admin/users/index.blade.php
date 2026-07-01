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
                    <td>
                        <button type="button"
                                onclick="openUserModal(this)"
                                data-name="{{ e($user->name) }}"
                                data-nickname="{{ e($user->nickname) }}"
                                data-email="{{ e($user->email) }}"
                                data-role="{{ e($user->role) }}"
                                data-department="{{ e($user->department) }}"
                                data-student-id="{{ e($user->student_id) }}"
                                data-phone="{{ e($user->phone_number) }}"
                                data-date-of-birth="{{ $user->date_of_birth ? $user->date_of_birth->format('F d, Y') : '' }}"
                                data-points="{{ e($user->points) }}"
                                data-status="{{ e($user->status) }}"
                                data-last-active="{{ $user->last_active_at ? $user->last_active_at->format('F d, Y h:i A') : 'Never' }}"
                                data-created-at="{{ $user->created_at ? $user->created_at->format('F d, Y h:i A') : '' }}"
                                data-avatar="{{ $user->avatar ? asset('storage/'.$user->avatar) : '' }}"
                                style="background:none;border:none;color:#8b1c2c;cursor:pointer;padding:4px">
                            <x-icon name="eye" />
                        </button>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" style="text-align:center;color:#999;padding:24px;">No users found.</td></tr>
            @endforelse
            </tbody>
        </table>
        <div style="margin-top:16px;">{{ $users->links() }}</div>
    </div>

    <div id="userViewModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:999;align-items:center;justify-content:center">
        <div style="width:520px;max-width:94vw;background:#fff;border-radius:14px;box-shadow:0 12px 40px rgba(0,0,0,.22);overflow:hidden">
            <div style="background:#8b1c2c;color:#fff;padding:18px 22px;display:flex;align-items:center;justify-content:space-between">
                <div style="font-size:1.05rem;font-weight:800;letter-spacing:.08em;text-transform:uppercase">Student Information</div>
                <button type="button" onclick="closeUserModal()" style="background:none;border:none;color:#fff;font-size:1.35rem;cursor:pointer;line-height:1">
                    <x-icon name="x" />
                </button>
            </div>

            <div style="padding:24px">
                <div style="display:flex;align-items:center;gap:16px;margin-bottom:18px">
                    <img id="modalUserAvatar" src="" style="width:76px;height:76px;border-radius:50%;object-fit:cover;background:#c9999f">
                    <div>
                        <div id="modalUserName" style="font-size:1.25rem;font-weight:800;color:#1a1a1a"></div>
                        <div id="modalUserEmail" style="font-size:.82rem;color:#777;margin-top:3px"></div>
                        <div style="display:flex;gap:8px;margin-top:8px;flex-wrap:wrap">
                            <span id="modalUserRole" class="badge badge-yellow"></span>
                            <span id="modalUserStatus" class="badge"></span>
                        </div>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                    <div style="background:#faf7f7;border:1px solid #f0dddd;border-radius:10px;padding:12px">
                        <div style="font-size:.68rem;color:#8b1c2c;font-weight:800;text-transform:uppercase;letter-spacing:.06em">Nickname</div>
                        <div id="modalUserNickname" style="font-size:.9rem;margin-top:4px;color:#333"></div>
                    </div>
                    <div style="background:#faf7f7;border:1px solid #f0dddd;border-radius:10px;padding:12px">
                        <div style="font-size:.68rem;color:#8b1c2c;font-weight:800;text-transform:uppercase;letter-spacing:.06em">Student ID</div>
                        <div id="modalUserStudentId" style="font-size:.9rem;margin-top:4px;color:#333"></div>
                    </div>
                    <div style="background:#faf7f7;border:1px solid #f0dddd;border-radius:10px;padding:12px">
                        <div style="font-size:.68rem;color:#8b1c2c;font-weight:800;text-transform:uppercase;letter-spacing:.06em">Department</div>
                        <div id="modalUserDepartment" style="font-size:.9rem;margin-top:4px;color:#333"></div>
                    </div>
                    <div style="background:#faf7f7;border:1px solid #f0dddd;border-radius:10px;padding:12px">
                        <div style="font-size:.68rem;color:#8b1c2c;font-weight:800;text-transform:uppercase;letter-spacing:.06em">Phone</div>
                        <div id="modalUserPhone" style="font-size:.9rem;margin-top:4px;color:#333"></div>
                    </div>
                    <div style="background:#faf7f7;border:1px solid #f0dddd;border-radius:10px;padding:12px">
                        <div style="font-size:.68rem;color:#8b1c2c;font-weight:800;text-transform:uppercase;letter-spacing:.06em">Date of Birth</div>
                        <div id="modalUserBirth" style="font-size:.9rem;margin-top:4px;color:#333"></div>
                    </div>
                    <div style="background:#faf7f7;border:1px solid #f0dddd;border-radius:10px;padding:12px">
                        <div style="font-size:.68rem;color:#8b1c2c;font-weight:800;text-transform:uppercase;letter-spacing:.06em">Points</div>
                        <div id="modalUserPoints" style="font-size:.9rem;margin-top:4px;color:#333"></div>
                    </div>
                    <div style="background:#faf7f7;border:1px solid #f0dddd;border-radius:10px;padding:12px">
                        <div style="font-size:.68rem;color:#8b1c2c;font-weight:800;text-transform:uppercase;letter-spacing:.06em">Last Active</div>
                        <div id="modalUserLastActive" style="font-size:.9rem;margin-top:4px;color:#333"></div>
                    </div>
                    <div style="background:#faf7f7;border:1px solid #f0dddd;border-radius:10px;padding:12px">
                        <div style="font-size:.68rem;color:#8b1c2c;font-weight:800;text-transform:uppercase;letter-spacing:.06em">Created At</div>
                        <div id="modalUserCreatedAt" style="font-size:.9rem;margin-top:4px;color:#333"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function valueOrDash(value) {
            return value && value.trim() !== '' ? value : '—';
        }

        function titleCase(value) {
            if (!value) return '—';
            return value.replace(/_/g, ' ').replace(/\b\w/g, letter => letter.toUpperCase());
        }

        function openUserModal(button) {
            const data = button.dataset;
            const avatar = data.avatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(data.name || 'User')}&size=96&background=c9999f&color=fff`;

            document.getElementById('modalUserAvatar').src = avatar;
            document.getElementById('modalUserName').textContent = valueOrDash(data.name);
            document.getElementById('modalUserEmail').textContent = valueOrDash(data.email);
            document.getElementById('modalUserNickname').textContent = valueOrDash(data.nickname);
            document.getElementById('modalUserStudentId').textContent = valueOrDash(data.studentId);
            document.getElementById('modalUserDepartment').textContent = valueOrDash(data.department);
            document.getElementById('modalUserPhone').textContent = valueOrDash(data.phone);
            document.getElementById('modalUserBirth').textContent = valueOrDash(data.dateOfBirth);
            document.getElementById('modalUserPoints').textContent = valueOrDash(data.points);
            document.getElementById('modalUserLastActive').textContent = valueOrDash(data.lastActive);
            document.getElementById('modalUserCreatedAt').textContent = valueOrDash(data.createdAt);

            const role = document.getElementById('modalUserRole');
            role.textContent = titleCase(data.role);

            const status = document.getElementById('modalUserStatus');
            status.textContent = titleCase(data.status);
            status.className = 'badge ' + (data.status === 'active' ? 'badge-green' : 'badge-red');

            document.getElementById('userViewModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function closeUserModal() {
            document.getElementById('userViewModal').style.display = 'none';
            document.body.style.overflow = '';
        }

        document.getElementById('userViewModal').addEventListener('click', function (event) {
            if (event.target === this) closeUserModal();
        });
    </script>

@endsection
