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

    <div class="two-col">

        {{-- Leaderboard --}}
        <div class="panel">
            <div class="panel-header">
                <span class="panel-title">Leaderboard</span>
                <a href="{{ route('admin.points.leaderboard') }}" class="btn btn-outline" style="font-size:.72rem">View all →</a>
            </div>
            @forelse($leaderboard as $i => $user)
                <div style="display:flex;align-items:center;gap:12px;padding:10px 0;border-bottom:1px solid #f5eaea">
                    {{-- Rank --}}
                    <div style="width:28px;text-align:center;flex-shrink:0">
                        @if($i === 0)
                            <span style="font-size:1.1rem"><x-icon name="medal" class="rank-gold" /></span>
                        @elseif($i === 1)
                            <span style="font-size:1.1rem"><x-icon name="medal" class="rank-silver" /></span>
                        @elseif($i === 2)
                            <span style="font-size:1.1rem"><x-icon name="medal" class="rank-bronze" /></span>
                        @else
                            <span style="font-size:.85rem;font-weight:700;color:#777">{{ $i + 1 }}</span>
                        @endif
                    </div>

                    {{-- Avatar --}}
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&size=40&background=c9999f&color=fff"
                         style="width:40px;height:40px;border-radius:50%;flex-shrink:0">

                    {{-- Info --}}
                    <div style="flex:1;min-width:0">
                        <div style="font-weight:800;font-size:.9rem;color:#1a1a1a">{{ $user->name }}</div>
                        <div style="font-size:.72rem;color:#999">{{ $user->department }}</div>
                    </div>

                    {{-- Points --}}
                    <div style="text-align:right;flex-shrink:0">
                        <div style="font-weight:800;color:#8b1c2c;font-size:.9rem">{{ number_format($user->points) }}</div>
                        <div style="font-size:.65rem;color:#aaa">pts</div>
                    </div>
                </div>
            @empty
                <p style="color:#999;font-size:.82rem;padding:12px 0">No data yet.</p>
            @endforelse
        </div>

        {{-- Point Rules --}}
        <div class="panel">
            <div class="panel-header">
                <span class="panel-title">Point Rules</span>
                <button onclick="openRuleModal()" class="btn btn-outline" style="font-size:.72rem">+ Add Rule</button>
            </div>
            @forelse($rules as $rule)
                <div style="display:flex;align-items:center;gap:10px;padding:12px 0;border-bottom:1px solid #f5eaea">
                    <div style="flex:1;min-width:0">
                        <div style="font-weight:700;font-size:.88rem">{{ $rule->name }}</div>
                        <div style="font-size:.72rem;color:#777;margin-top:2px">{{ $rule->description }}</div>
                    </div>
                    <span style="display:flex;gap:4px;flex-shrink:0">
                        <button type="button"
                                data-id="{{ $rule->id }}"
                                data-name="{{ $rule->name }}"
                                data-description="{{ $rule->description ?? '' }}"
                                data-points="{{ $rule->points }}"
                                class="edit-rule-btn"
                                style="background:none;border:none;cursor:pointer;color:#8b1c2c;padding:4px;border-radius:4px;display:inline-flex"
                                title="Edit rule">
                            <x-icon name="pencil" size="16" />
                        </button>
                        <form method="POST" action="{{ route('admin.points.rules.destroy', $rule) }}"
                              style="display:inline"
                              data-confirm-message="Delete rule &quot;{{ e($rule->name) }}&quot;?"
                              data-confirm-action="Delete">
                            @csrf @method('DELETE')
                            <button type="submit" style="background:none;border:none;cursor:pointer;color:#b33;padding:4px;border-radius:4px;display:inline-flex" title="Delete rule">
                                <x-icon name="trash" size="16" />
                            </button>
                        </form>
                    </span>
                    <span style="background:#f5eaea;color:#8b1c2c;font-weight:800;font-size:.78rem;padding:4px 12px;border-radius:20px;flex-shrink:0">
                +{{ $rule->points }} pts
            </span>
                </div>
            @empty
                <p style="color:#999;font-size:.82rem;padding:12px 0">No rules yet.</p>
            @endforelse
        </div>

    </div>

    {{-- Add Rule Modal --}}
    <div id="ruleModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:999;align-items:center;justify-content:center">
        <div style="background:#fff;border-radius:14px;width:420px;max-width:95vw;padding:28px 32px;box-shadow:0 8px 40px rgba(0,0,0,.2)">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
                <h2 style="font-size:1.1rem;font-weight:800;color:#8b1c2c">Add Point Rule</h2>
                <button type="button" onclick="closeRuleModal()" style="background:none;border:none;font-size:1.3rem;cursor:pointer;color:#999"><x-icon name="x" /></button>
            </div>
            <form method="POST" action="{{ route('admin.points.rules.store') }}">
                @csrf
                <label style="font-size:.7rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;display:block;margin-bottom:4px">Rule Name</label>
                <input type="text" name="name" placeholder="e.g. Event Attendance" required
                       style="width:100%;border:1.5px solid #c9999f;border-radius:8px;padding:9px 12px;font-size:.85rem;outline:none;margin-bottom:12px">

                <label style="font-size:.7rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;display:block;margin-bottom:4px">Description</label>
                <input type="text" name="description" placeholder="e.g. Scan QR at event"
                       style="width:100%;border:1.5px solid #c9999f;border-radius:8px;padding:9px 12px;font-size:.85rem;outline:none;margin-bottom:12px">

                <label style="font-size:.7rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;display:block;margin-bottom:4px">Points</label>
                <input type="number" name="points" placeholder="e.g. 50" required min="1"
                       style="width:100%;border:1.5px solid #c9999f;border-radius:8px;padding:9px 12px;font-size:.85rem;outline:none;margin-bottom:20px">

                <div style="display:flex;gap:10px;justify-content:flex-end">
                    <button type="button" onclick="closeRuleModal()"
                            style="padding:9px 16px;border:1.5px solid #ccc;background:#fff;color:#666;border-radius:8px;font-size:.82rem;font-weight:600;cursor:pointer">
                        Cancel
                    </button>
                    <button type="submit"
                            style="padding:9px 20px;background:#8b1c2c;color:#fff;border:none;border-radius:8px;font-size:.82rem;font-weight:700;cursor:pointer">
                        <x-icon name="check-circle" /> Add Rule
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Edit Rule Modal --}}
    <div id="editRuleModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:999;align-items:center;justify-content:center">
        <div style="background:#fff;border-radius:14px;width:420px;max-width:95vw;padding:28px 32px;box-shadow:0 8px 40px rgba(0,0,0,.2)">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
                <h2 style="font-size:1.1rem;font-weight:800;color:#8b1c2c">Edit Point Rule</h2>
                <button type="button" onclick="closeEditRuleModal()" style="background:none;border:none;font-size:1.3rem;cursor:pointer;color:#999"><x-icon name="x" /></button>
            </div>
            <form method="POST" id="editRuleForm">
                @csrf @method('PATCH')
                <label style="font-size:.7rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;display:block;margin-bottom:4px">Rule Name</label>
                <input type="text" name="name" id="editRuleName" required
                       style="width:100%;border:1.5px solid #c9999f;border-radius:8px;padding:9px 12px;font-size:.85rem;outline:none;margin-bottom:12px">

                <label style="font-size:.7rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;display:block;margin-bottom:4px">Description</label>
                <input type="text" name="description" id="editRuleDescription"
                       style="width:100%;border:1.5px solid #c9999f;border-radius:8px;padding:9px 12px;font-size:.85rem;outline:none;margin-bottom:12px">

                <label style="font-size:.7rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;display:block;margin-bottom:4px">Points</label>
                <input type="number" name="points" id="editRulePoints" required min="1"
                       style="width:100%;border:1.5px solid #c9999f;border-radius:8px;padding:9px 12px;font-size:.85rem;outline:none;margin-bottom:20px">

                <div style="display:flex;gap:10px;justify-content:flex-end">
                    <button type="button" onclick="closeEditRuleModal()"
                            style="padding:9px 16px;border:1.5px solid #ccc;background:#fff;color:#666;border-radius:8px;font-size:.82rem;font-weight:600;cursor:pointer">
                        Cancel
                    </button>
                    <button type="submit"
                            style="padding:9px 20px;background:#8b1c2c;color:#fff;border:none;border-radius:8px;font-size:.82rem;font-weight:700;cursor:pointer">
                        <x-icon name="check-circle" /> Update Rule
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openRuleModal() {
            document.getElementById('ruleModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
        function closeRuleModal() {
            document.getElementById('ruleModal').style.display = 'none';
            document.body.style.overflow = '';
        }
        document.getElementById('ruleModal').addEventListener('click', function(e) {
            if (e.target === this) closeRuleModal();
        });

        var EDIT_RULE_URL = '{{ route('admin.points.rules.update', '_ID_') }}';

        document.addEventListener('click', function(e) {
            var btn = e.target.closest('.edit-rule-btn');
            if (!btn) return;
            var id = btn.getAttribute('data-id');
            document.getElementById('editRuleForm').action = EDIT_RULE_URL.replace('_ID_', id);
            document.getElementById('editRuleName').value = btn.getAttribute('data-name');
            document.getElementById('editRuleDescription').value = btn.getAttribute('data-description');
            document.getElementById('editRulePoints').value = btn.getAttribute('data-points');
            document.getElementById('editRuleModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        });

        function closeEditRuleModal() {
            document.getElementById('editRuleModal').style.display = 'none';
            document.body.style.overflow = '';
        }
        document.getElementById('editRuleModal').addEventListener('click', function(e) {
            if (e.target === this) closeEditRuleModal();
        });
    </script>

@endsection
