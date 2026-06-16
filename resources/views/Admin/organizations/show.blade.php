@extends('layouts.admin')
@section('title','Organization')
@section('content')

    @if(session('success'))
        <div style="background:#d4edda;color:#155724;border:1px solid #c3e6cb;border-radius:8px;padding:12px 16px;margin-bottom:16px;font-size:.85rem;font-weight:600">
            ✅ {{ session('success') }}
        </div>
    @endif

    {{-- Back --}}
    <a href="{{ route('admin.organizations') }}"
       style="display:inline-flex;align-items:center;gap:6px;color:#8b1c2c;font-size:.82rem;font-weight:600;text-decoration:none;margin-bottom:20px">
        ← Back to Organizations
    </a>

    {{-- Org Info Card --}}
    <div class="panel" style="margin-bottom:20px">
        <div style="display:flex;align-items:flex-start;gap:16px">
            {{-- Logo --}}
            <div style="width:72px;height:72px;border-radius:50%;background:#f5eaea;display:flex;align-items:center;justify-content:center;flex-shrink:0;overflow:hidden;border:2px solid #d9b8bc">
                @if($organization->logo)
                    <img src="{{ asset('storage/'.$organization->logo) }}" style="width:100%;height:100%;object-fit:cover">
                @else
                    <span style="font-size:2rem">🏛</span>
                @endif
            </div>

            {{-- Info --}}
            <div style="flex:1">
                <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:4px">
                    <h1 style="font-size:1.2rem;font-weight:800;color:#1a1a1a">{{ $organization->name }}</h1>
                    <span class="badge {{ $organization->status === 'active' ? 'badge-green' : ($organization->status === 'pending' ? 'badge-yellow' : 'badge-gray') }}">
                    {{ strtoupper($organization->status) }}
                </span>
                </div>
                <div style="font-size:.75rem;color:#999;margin-bottom:6px">
                    {{ $organization->acronym }} · {{ ucfirst($organization->type) }}
                </div>
                @if($organization->description)
                    <p style="font-size:.82rem;color:#555;line-height:1.5;margin-bottom:10px">{{ $organization->description }}</p>
                @endif

                {{-- Meta --}}
                <div style="display:flex;gap:20px;flex-wrap:wrap;font-size:.78rem;color:#777">
                    @if($organization->department)
                        <span>🏫 {{ $organization->department }}</span>
                    @endif
                    @if($organization->year_founded)
                        <span>📅 Founded {{ $organization->year_founded }}</span>
                    @endif
                    @if($organization->adviser)
                        <span>👤 Adviser: <strong>{{ $organization->adviser }}</strong></span>
                    @endif
                    @if($organization->co_adviser)
                        <span>👤 Co-Adviser: <strong>{{ $organization->co_adviser }}</strong></span>
                    @endif
                    <span>👥 {{ $organization->member_count }} Members</span>
                </div>
            </div>

            {{-- Status toggle --}}
            <div style="display:flex;flex-direction:column;gap:8px;flex-shrink:0">
                <form method="POST" action="{{ route('admin.organizations.status', $organization) }}">
                    @csrf
                    @method('PATCH')
                    <select name="status" onchange="this.form.submit()"
                            style="border:1.5px solid #c9999f;border-radius:8px;padding:7px 12px;font-size:.78rem;outline:none;background:#fff;cursor:pointer">
                        <option value="active"   {{ $organization->status==='active'   ? 'selected':'' }}>Active</option>
                        <option value="inactive" {{ $organization->status==='inactive' ? 'selected':'' }}>Inactive</option>
                        <option value="pending"  {{ $organization->status==='pending'  ? 'selected':'' }}>Pending</option>
                    </select>
                </form>
            </div>
        </div>
    </div>

    {{-- Assign Student Role --}}
    <div class="panel">
        <div style="font-size:1.05rem;font-weight:800;color:#8b1c2c;margin-bottom:16px">Assign Student Role</div>

        {{-- Role buttons --}}
        <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:20px" id="roleTabs">
            @foreach(['President','Vice President','Secretary','Treasurer','Auditor'] as $role)
                <button onclick="selectRole('{{ $role }}')"
                        id="role-{{ Str::slug($role) }}"
                        style="padding:7px 16px;border-radius:20px;font-size:.78rem;font-weight:700;cursor:pointer;border:none;background:{{ $role==='President'?'#8b1c2c':'#e8ddd5' }};color:{{ $role==='President'?'#fff':'#555' }};transition:all .15s">
                    {{ $role }}
                </button>
            @endforeach
        </div>

        <div class="two-col" style="align-items:flex-start">

            {{-- Left: Select Student --}}
            <div>
                <label style="font-size:.7rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;display:block;margin-bottom:8px">
                    Select Student
                </label>
                <form method="POST" action="{{ route('admin.organizations.assign', $organization) }}">
                    @csrf
                    <input type="hidden" name="role" id="selectedRole" value="President">
                    <select name="user_id"
                            style="width:100%;border:1.5px solid #c9999f;border-radius:8px;padding:10px 14px;font-size:.85rem;outline:none;margin-bottom:12px;background:#fff">
                        <option value="">-- Select a student --</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->department }})</option>
                        @endforeach
                    </select>
                    <button type="submit"
                            style="width:100%;padding:10px;background:#8b1c2c;color:#fff;border:none;border-radius:8px;font-size:.82rem;font-weight:700;cursor:pointer">
                        Assign Role
                    </button>
                </form>
            </div>

            {{-- Right: Assigned Officials --}}
            <div>
                <label style="font-size:.7rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;display:block;margin-bottom:8px">
                    Assigned Officials
                </label>
                @forelse($officials as $official)
                    <div style="display:flex;align-items:center;gap:10px;padding:10px 0;border-bottom:1px solid #f5eaea">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($official->user->name ?? 'U') }}&size=36&background=8b1c2c&color=fff"
                             style="width:36px;height:36px;border-radius:50%">
                        <div style="flex:1">
                            <div style="font-size:.85rem;font-weight:700">{{ $official->user->name ?? '—' }}</div>
                            <div style="font-size:.7rem;color:#999">{{ $official->user->department ?? '' }}</div>
                        </div>
                        <span style="padding:4px 12px;background:#8b1c2c;color:#fff;border-radius:20px;font-size:.68rem;font-weight:700">
                    {{ $official->position }}
                </span>
                        <form method="POST" action="{{ route('admin.organizations.unassign', [$organization, $official]) }}" style="margin:0">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="background:none;border:none;color:#e53e3e;cursor:pointer;font-size:.85rem" title="Remove">✕</button>
                        </form>
                    </div>
                @empty
                    <p style="color:#999;font-size:.82rem;padding:12px 0">No officials assigned yet.</p>
                @endforelse
            </div>

        </div>
    </div>

    <script>
        function selectRole(role) {
            document.getElementById('selectedRole').value = role;
            document.querySelectorAll('#roleTabs button').forEach(btn => {
                btn.style.background = '#e8ddd5';
                btn.style.color = '#555';
            });
            const slug = role.toLowerCase().replace(/ /g,'-');
            const btn = document.getElementById('role-' + slug);
            if (btn) { btn.style.background = '#8b1c2c'; btn.style.color = '#fff'; }
        }
    </script>

@endsection
