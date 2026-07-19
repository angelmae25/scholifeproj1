@extends('layouts.admin')
@section('title','Organization')
@section('content')

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
                    <span style="font-size:2rem"><x-icon name="building" /></span>
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
                        <span><x-icon name="school" /> {{ $organization->department }}</span>
                    @endif
                    @if($organization->year_founded)
                        <span><x-icon name="calendar" /> Founded {{ $organization->year_founded }}</span>
                    @endif
                    @if($organization->adviser)
                        <span><x-icon name="user" /> Adviser: <strong>{{ $organization->adviser }}</strong></span>
                    @endif
                    @if($organization->co_adviser)
                        <span><x-icon name="user" /> Co-Adviser: <strong>{{ $organization->co_adviser }}</strong></span>
                    @endif
                    <span><x-icon name="users" /> {{ $organization->member_count }} Members</span>
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

        @php
            $roleLabels = [
                'president' => 'President',
                'vice_president_internal' => 'VP Internal',
                'vice_president_external' => 'VP External',
                'secretary' => 'Secretary',
                'treasurer' => 'Treasurer',
                'auditor' => 'Auditor',
                'pio' => 'PIO',
            ];
        @endphp

        {{-- Role buttons --}}
        <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:20px" id="roleTabs">
            @foreach($roleLabels as $role => $label)
                <button onclick="selectRole('{{ $role }}')"
                        id="role-{{ Str::slug($role) }}"
                        style="padding:7px 16px;border-radius:20px;font-size:.78rem;font-weight:700;cursor:pointer;border:none;background:{{ $role==='president'?'#8b1c2c':'#e8ddd5' }};color:{{ $role==='president'?'#fff':'#555' }};transition:all .15s">
                    {{ $label }}
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
                    <input type="hidden" name="role" id="selectedRole" value="president">
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
                    {{ $roleLabels[$official->position] ?? $official->position }}
                </span>
                        <form method="POST" action="{{ route('admin.organizations.unassign', [$organization, $official]) }}"
                              data-confirm-message="Remove this official from the organization?"
                              data-confirm-action="Remove"
                              style="margin:0">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="background:none;border:none;color:#e53e3e;cursor:pointer;font-size:.85rem" title="Remove"><x-icon name="x" /></button>
                        </form>
                    </div>
                @empty
                    <p style="color:#999;font-size:.82rem;padding:12px 0">No officials assigned yet.</p>
                @endforelse
            </div>

        </div>
    </div>
    {{-- Anonymous Organization Evaluations --}}
    <div class="panel" style="margin-top:20px">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:16px">
            <div>
                <div style="font-size:1.05rem;font-weight:800;color:#8b1c2c">Evaluation Results</div>
                <div style="font-size:.72rem;color:#888;margin-top:3px">Submitted from QR code, public link, and mobile app. Evaluator names are hidden.</div>
            </div>
            <div style="display:flex;gap:10px;flex-wrap:wrap">
                <div style="border:1px solid #f0dfe1;border-radius:8px;padding:8px 12px;min-width:92px;background:#fff">
                    <div style="font-size:.68rem;color:#777;font-weight:700;text-transform:uppercase">Total</div>
                    <div style="font-size:1rem;font-weight:800;color:#111">{{ $evaluationStats['total'] ?? 0 }}</div>
                </div>
                <div style="border:1px solid #f0dfe1;border-radius:8px;padding:8px 12px;min-width:92px;background:#fff">
                    <div style="font-size:.68rem;color:#777;font-weight:700;text-transform:uppercase">Avg Rating</div>
                    <div style="font-size:1rem;font-weight:800;color:#111">{{ $evaluationStats['average_rating'] ?? 0 }}/5</div>
                </div>
                <div style="border:1px solid #f0dfe1;border-radius:8px;padding:8px 12px;min-width:92px;background:#fff">
                    <div style="font-size:.68rem;color:#777;font-weight:700;text-transform:uppercase">Recommend</div>
                    <div style="font-size:1rem;font-weight:800;color:#111">{{ $evaluationStats['recommend_yes'] ?? 0 }}</div>
                </div>
            </div>
        </div>

        @forelse($evaluations as $evaluation)
            <div style="border:1px solid #f5eaea;border-radius:10px;padding:14px;margin-bottom:12px;background:#fff">
                <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:10px">
                    <div>
                        <div style="font-size:.86rem;font-weight:800;color:#111">Anonymous Evaluator #{{ $loop->iteration }}</div>
                        <div style="font-size:.7rem;color:#999;margin-top:2px">Submitted {{ $evaluation->created_at->format('M d, Y h:i A') }}</div>
                    </div>
                    <div style="display:flex;gap:7px;flex-wrap:wrap">
                        <span class="badge badge-yellow">Rating: {{ $evaluation->rating }}/5</span>
                        <span class="badge badge-green">{{ ucfirst($evaluation->satisfaction) }}</span>
                        <span class="badge badge-gray">Recommend: {{ ucfirst($evaluation->would_recommend) }}</span>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:10px">
                    @foreach($evaluation->answers as $answer)
                        @if(! blank($answer->answer))
                            <div style="background:#fbf8f8;border-radius:8px;padding:10px">
                                <div style="font-size:.68rem;color:#8b1c2c;font-weight:800;text-transform:uppercase;letter-spacing:.4px;margin-bottom:5px">{{ $answer->question }}</div>
                                <div style="font-size:.78rem;color:#333;line-height:1.45;white-space:pre-wrap">{{ $answer->answer }}</div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @empty
            <p style="color:#999;font-size:.82rem;padding:12px 0;text-align:center">No evaluations submitted yet.</p>
        @endforelse
    </div>
    <script>
        function selectRole(role) {
            document.getElementById('selectedRole').value = role;
            document.querySelectorAll('#roleTabs button').forEach(btn => {
                btn.style.background = '#e8ddd5';
                btn.style.color = '#555';
            });
            const slug = role.toLowerCase().replace(/_/g,'-').replace(/ /g,'-');
            const btn = document.getElementById('role-' + slug);
            if (btn) { btn.style.background = '#8b1c2c'; btn.style.color = '#fff'; }
        }
    </script>

@endsection


