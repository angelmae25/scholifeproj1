@extends('layouts.admin')
@section('title','Organization')
@section('content')

    @if(session('success'))
        <div style="background:#d4edda;color:#155724;border:1px solid #c3e6cb;border-radius:8px;padding:12px 16px;margin-bottom:16px;font-size:.85rem;font-weight:600">
            <x-icon name="check-circle" /> {{ session('success') }}
        </div>
    @endif

    <div class="stat-grid">
        <div class="stat-card"><div class="stat-label">Total Organization</div><div class="stat-value">{{ $stats['total'] }}</div></div>
        <div class="stat-card"><div class="stat-label">Active This Month</div><div class="stat-value">{{ $stats['active_this_month'] }}</div></div>
        <div class="stat-card"><div class="stat-label">Pending Approval</div><div class="stat-value">{{ $stats['pending'] }}</div></div>
    </div>

    <div class="panel">
        <div class="panel-header">
            <span class="panel-title">Organization</span>
            <button onclick="openModal()" class="btn btn-outline">+ New Organization</button>
        </div>

        {{-- Organization Cards --}}
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;margin-top:8px">
            @forelse($organizations as $org)
                <div style="border:1.5px solid #d9b8bc;border-radius:10px;padding:16px;background:#fff">
                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px">
                        {{-- Logo --}}
                        <div style="width:48px;height:48px;border-radius:50%;background:#f5eaea;display:flex;align-items:center;justify-content:center;flex-shrink:0;overflow:hidden">
                            @if($org->logo)
                                <img src="{{ asset('storage/'.$org->logo) }}" style="width:100%;height:100%;object-fit:cover">
                            @else
                                <span style="font-size:1.3rem"><x-icon name="building" /></span>
                            @endif
                        </div>
                        <div style="flex:1;min-width:0">
                            <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap">
                                <span style="font-weight:800;font-size:.9rem;color:#1a1a1a">{{ $org->name }}</span>
                                <span class="badge {{ $org->status === 'active' ? 'badge-green' : ($org->status === 'pending' ? 'badge-yellow' : 'badge-gray') }}" style="font-size:.6rem">
                            {{ strtoupper($org->status) }}
                        </span>
                            </div>
                            <div style="font-size:.7rem;color:#999;margin-top:2px">{{ $org->acronym }} · {{ $org->type }}</div>
                        </div>
                    </div>

                    @if($org->description)
                        <p style="font-size:.78rem;color:#555;line-height:1.5;margin-bottom:12px">
                            {{ Str::limit($org->description, 120) }}
                        </p>
                    @endif

                    @if($org->adviser)
                        <div style="font-size:.72rem;color:#777;margin-bottom:10px">
                            <x-icon name="user" /> Adviser: <strong>{{ $org->adviser }}</strong>
                        </div>
                    @endif

                    <div style="display:flex;gap:8px">
                        <a href="{{ route('admin.organizations.show', $org) }}"
                           style="flex:1;text-align:center;padding:8px;background:#8b1c2c;color:#fff;border-radius:6px;font-size:.75rem;font-weight:700;text-decoration:none">
                            MANAGE
                        </a>
                        <button type="button"
                                onclick="openEditModalFromButton(this)"
                                data-update-url="{{ route('admin.organizations.update', $org) }}"
                                data-name="{{ e($org->name) }}"
                                data-acronym="{{ e($org->acronym) }}"
                                data-description="{{ e($org->description) }}"
                                data-type="{{ e($org->type) }}"
                                data-status="{{ e($org->status) }}"
                                data-department="{{ e($org->department) }}"
                                data-year-founded="{{ e($org->year_founded) }}"
                                data-adviser="{{ e($org->adviser) }}"
                                data-co-adviser="{{ e($org->co_adviser) }}"
                                data-logo-url="{{ $org->logo ? asset('storage/'.$org->logo) : '' }}"
                                style="padding:8px 12px;background:#fff3cd;color:#8b1c2c;border:none;border-radius:6px;font-size:.75rem;font-weight:700;cursor:pointer">
                            <x-icon name="pencil" />
                        </button>
                        <form method="POST" action="{{ route('admin.organizations.destroy', $org) }}"
                              onsubmit="return confirm('Delete this organization?')" style="margin:0">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    style="padding:8px 12px;background:#fee2e2;color:#e53e3e;border:none;border-radius:6px;font-size:.75rem;font-weight:700;cursor:pointer">
                                <x-icon name="trash" />
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div style="grid-column:1/-1;text-align:center;color:#999;padding:40px">
                    <div style="font-size:2rem;margin-bottom:8px"><x-icon name="building" /></div>
                    <div>No organizations yet. Click <strong>+ New Organization</strong> to add one.</div>
                </div>
            @endforelse
        </div>

        <div style="margin-top:16px">{{ $organizations->links() }}</div>
    </div>

    {{-- CREATE ORGANIZATION MODAL --}}
    <div id="orgModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:999;align-items:center;justify-content:center">
        <div style="background:#fff;border-radius:14px;width:560px;max-width:95vw;max-height:90vh;overflow-y:auto;padding:28px 32px;box-shadow:0 8px 40px rgba(0,0,0,.2)">

            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
                <h2 style="font-size:1.2rem;font-weight:800;color:#8b1c2c">Create Organization</h2>
                <button onclick="closeModal()" style="background:none;border:none;font-size:1.4rem;cursor:pointer;color:#999">→</button>
            </div>

            <form method="POST" action="{{ route('admin.organizations.store') }}" enctype="multipart/form-data">
                @csrf

                {{-- Logo upload --}}
                <div style="display:flex;flex-direction:column;align-items:center;margin-bottom:18px">
                    <label id="logoPreviewWrap" style="width:80px;height:80px;border-radius:50%;background:#f0e8e8;display:flex;align-items:center;justify-content:center;cursor:pointer;overflow:hidden;border:2px dashed #c9999f">
                        <img id="logoPreview" src="" style="display:none;width:100%;height:100%;object-fit:cover">
                        <span id="logoIcon" style="font-size:2rem"><x-icon name="user" /></span>
                        <input type="file" name="logo" accept="image/*" style="display:none" onchange="previewLogo(this)">
                    </label>
                    <div style="font-size:.72rem;color:#8b1c2c;margin-top:6px;font-weight:600"><x-icon name="camera" /> Organization Logo</div>
                </div>

                {{-- Name --}}
                <label style="font-size:.7rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;display:block;margin-bottom:4px">Organization Name</label>
                <input type="text" name="name" placeholder="e.g. Intramural Sports Fest 2026"
                       style="width:100%;border:1.5px solid #c9999f;border-radius:8px;padding:10px 14px;font-size:.88rem;margin-bottom:14px;outline:none">

                {{-- Acronym --}}
                <label style="font-size:.7rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;display:block;margin-bottom:4px">Short name/acronym</label>
                <input type="text" name="acronym" placeholder="e.g. Intramural Sports Fest 2026"
                       style="width:100%;border:1.5px solid #c9999f;border-radius:8px;padding:10px 14px;font-size:.88rem;margin-bottom:14px;outline:none">

                {{-- Description --}}
                <label style="font-size:.7rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;display:block;margin-bottom:4px">Description</label>
                <textarea name="description" rows="3" placeholder="e.g. Intramural Sports Fest 2026"
                          style="width:100%;border:1.5px solid #c9999f;border-radius:8px;padding:10px 14px;font-size:.85rem;outline:none;resize:vertical;font-family:inherit;margin-bottom:14px"></textarea>

                {{-- Organization Type --}}
                <label style="font-size:.7rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;display:block;margin-bottom:8px">Organization Type</label>
                <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-bottom:14px">
                    @foreach([['academic','graduation-cap','Academic'],['civic','heart','Civic'],['cultural','theater','Cultural'],['sports','activity','Sports'],['governance','building','Governance'],['other','more-horizontal','Other']] as $t)
                        <label style="display:flex;flex-direction:column;align-items:center;gap:4px;padding:10px;border:1.5px solid #c9999f;border-radius:8px;cursor:pointer;font-size:.75rem;transition:all .15s"
                               onmouseover="this.style.background='#fdf0f1'" onmouseout="this.style.background='#fff'">
                            <input type="radio" name="type" value="{{ $t[0] }}" style="display:none" onclick="selectType(this)">
                            <x-icon name="{{ $t[1] }}" size="22" />
                            <span style="font-weight:600;color:#555">{{ $t[2] }}</span>
                        </label>
                    @endforeach
                </div>

                {{-- Department & Year Founded --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px">
                    <div>
                        <label style="font-size:.7rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;display:block;margin-bottom:4px">Department</label>
                        <input type="text" name="department" placeholder="e.g. Intramural Sports"
                               style="width:100%;border:1.5px solid #c9999f;border-radius:8px;padding:9px 12px;font-size:.85rem;outline:none">
                    </div>
                    <div>
                        <label style="font-size:.7rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;display:block;margin-bottom:4px">Year Founded</label>
                        <input type="number" name="year_founded" placeholder="{{ date('Y') }}" min="1900" max="{{ date('Y') }}"
                               style="width:100%;border:1.5px solid #c9999f;border-radius:8px;padding:9px 12px;font-size:.85rem;outline:none">
                    </div>
                </div>

                {{-- Adviser & Co-Adviser --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:20px">
                    <div>
                        <label style="font-size:.7rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;display:block;margin-bottom:4px">Assign Adviser</label>
                        <input type="text" name="adviser" placeholder="e.g. Intramural Sports"
                               style="width:100%;border:1.5px solid #c9999f;border-radius:8px;padding:9px 12px;font-size:.85rem;outline:none">
                    </div>
                    <div>
                        <label style="font-size:.7rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;display:block;margin-bottom:4px">Assign Co-Adviser</label>
                        <input type="text" name="co_adviser" placeholder="e.g. Intramural Sports"
                               style="width:100%;border:1.5px solid #c9999f;border-radius:8px;padding:9px 12px;font-size:.85rem;outline:none">
                    </div>
                </div>

                {{-- Buttons --}}
                <div style="display:flex;align-items:center;justify-content:flex-end;gap:10px">
                    <button type="button" onclick="closeModal()"
                            style="padding:9px 18px;border:1.5px solid #ccc;background:#fff;color:#666;border-radius:8px;font-size:.82rem;font-weight:600;cursor:pointer">
                        Cancel
                    </button>
                    <button type="submit"
                            style="padding:9px 20px;background:#8b1c2c;color:#fff;border:none;border-radius:8px;font-size:.82rem;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:6px">
                        <x-icon name="building" /> Create
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- EDIT ORGANIZATION MODAL --}}
    <div id="editOrgModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:999;align-items:center;justify-content:center">
        <div style="background:#fff;border-radius:14px;width:560px;max-width:95vw;max-height:90vh;overflow-y:auto;padding:28px 32px;box-shadow:0 8px 40px rgba(0,0,0,.2)">

            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
                <h2 style="font-size:1.2rem;font-weight:800;color:#8b1c2c">Edit Organization</h2>
                <button onclick="closeEditModal()" style="background:none;border:none;font-size:1.4rem;cursor:pointer;color:#999">â†’</button>
            </div>

            <form id="editOrgForm" method="POST" action="" enctype="multipart/form-data">
                @csrf
                @method('PATCH')

                <div style="display:flex;flex-direction:column;align-items:center;margin-bottom:18px">
                    <label id="editLogoPreviewWrap" style="width:80px;height:80px;border-radius:50%;background:#f0e8e8;display:flex;align-items:center;justify-content:center;cursor:pointer;overflow:hidden;border:2px dashed #c9999f">
                        <img id="editLogoPreview" src="" style="display:none;width:100%;height:100%;object-fit:cover">
                        <span id="editLogoIcon" style="font-size:2rem"><x-icon name="building" /></span>
                        <input type="file" name="logo" accept="image/*" style="display:none" onchange="previewEditLogo(this)">
                    </label>
                    <div style="font-size:.72rem;color:#8b1c2c;margin-top:6px;font-weight:600"><x-icon name="camera" /> Change Logo</div>
                </div>

                <label style="font-size:.7rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;display:block;margin-bottom:4px">Organization Name</label>
                <input id="editName" type="text" name="name"
                       style="width:100%;border:1.5px solid #c9999f;border-radius:8px;padding:10px 14px;font-size:.88rem;margin-bottom:14px;outline:none">

                <label style="font-size:.7rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;display:block;margin-bottom:4px">Short name/acronym</label>
                <input id="editAcronym" type="text" name="acronym"
                       style="width:100%;border:1.5px solid #c9999f;border-radius:8px;padding:10px 14px;font-size:.88rem;margin-bottom:14px;outline:none">

                <label style="font-size:.7rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;display:block;margin-bottom:4px">Description</label>
                <textarea id="editDescription" name="description" rows="3"
                          style="width:100%;border:1.5px solid #c9999f;border-radius:8px;padding:10px 14px;font-size:.85rem;outline:none;resize:vertical;font-family:inherit;margin-bottom:14px"></textarea>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px">
                    <div>
                        <label style="font-size:.7rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;display:block;margin-bottom:4px">Organization Type</label>
                        <select id="editType" name="type"
                                style="width:100%;border:1.5px solid #c9999f;border-radius:8px;padding:9px 12px;font-size:.85rem;outline:none;background:#fff">
                            <option value="academic">Academic</option>
                            <option value="civic">Civic</option>
                            <option value="cultural">Cultural</option>
                            <option value="sports">Sports</option>
                            <option value="governance">Governance</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div>
                        <label style="font-size:.7rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;display:block;margin-bottom:4px">Status</label>
                        <select id="editStatus" name="status"
                                style="width:100%;border:1.5px solid #c9999f;border-radius:8px;padding:9px 12px;font-size:.85rem;outline:none;background:#fff">
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                            <option value="pending">Pending</option>
                        </select>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px">
                    <div>
                        <label style="font-size:.7rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;display:block;margin-bottom:4px">Department</label>
                        <input id="editDepartment" type="text" name="department"
                               style="width:100%;border:1.5px solid #c9999f;border-radius:8px;padding:9px 12px;font-size:.85rem;outline:none">
                    </div>
                    <div>
                        <label style="font-size:.7rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;display:block;margin-bottom:4px">Year Founded</label>
                        <input id="editYearFounded" type="number" name="year_founded" min="1900" max="{{ date('Y') }}"
                               style="width:100%;border:1.5px solid #c9999f;border-radius:8px;padding:9px 12px;font-size:.85rem;outline:none">
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:20px">
                    <div>
                        <label style="font-size:.7rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;display:block;margin-bottom:4px">Adviser</label>
                        <input id="editAdviser" type="text" name="adviser"
                               style="width:100%;border:1.5px solid #c9999f;border-radius:8px;padding:9px 12px;font-size:.85rem;outline:none">
                    </div>
                    <div>
                        <label style="font-size:.7rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;display:block;margin-bottom:4px">Co-Adviser</label>
                        <input id="editCoAdviser" type="text" name="co_adviser"
                               style="width:100%;border:1.5px solid #c9999f;border-radius:8px;padding:9px 12px;font-size:.85rem;outline:none">
                    </div>
                </div>

                <div style="display:flex;align-items:center;justify-content:flex-end;gap:10px">
                    <button type="button" onclick="closeEditModal()"
                            style="padding:9px 18px;border:1.5px solid #ccc;background:#fff;color:#666;border-radius:8px;font-size:.82rem;font-weight:600;cursor:pointer">
                        Cancel
                    </button>
                    <button type="submit"
                            style="padding:9px 20px;background:#8b1c2c;color:#fff;border:none;border-radius:8px;font-size:.82rem;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:6px">
                        <x-icon name="pencil" /> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById('orgModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
        function closeModal() {
            document.getElementById('orgModal').style.display = 'none';
            document.body.style.overflow = '';
        }
        document.getElementById('orgModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });
        document.getElementById('editOrgModal').addEventListener('click', function(e) {
            if (e.target === this) closeEditModal();
        });
        function previewLogo(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = e => {
                    document.getElementById('logoPreview').src = e.target.result;
                    document.getElementById('logoPreview').style.display = 'block';
                    document.getElementById('logoIcon').style.display = 'none';
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
        function selectType(radio) {
            document.querySelectorAll('[name="type"]').forEach(r => {
                r.closest('label').style.background = '#fff';
                r.closest('label').style.borderColor = '#c9999f';
            });
            radio.closest('label').style.background = '#fdf0f1';
            radio.closest('label').style.borderColor = '#8b1c2c';
        }
        function openEditModalFromButton(button) {
            openEditModal({
                update_url: button.dataset.updateUrl || '',
                name: button.dataset.name || '',
                acronym: button.dataset.acronym || '',
                description: button.dataset.description || '',
                type: button.dataset.type || 'academic',
                status: button.dataset.status || 'pending',
                department: button.dataset.department || '',
                year_founded: button.dataset.yearFounded || '',
                adviser: button.dataset.adviser || '',
                co_adviser: button.dataset.coAdviser || '',
                logo_url: button.dataset.logoUrl || ''
            });
        }
        function openEditModal(org) {
            document.getElementById('editOrgForm').action = org.update_url;
            document.getElementById('editName').value = org.name || '';
            document.getElementById('editAcronym').value = org.acronym || '';
            document.getElementById('editDescription').value = org.description || '';
            document.getElementById('editType').value = org.type || 'academic';
            document.getElementById('editStatus').value = org.status || 'pending';
            document.getElementById('editDepartment').value = org.department || '';
            document.getElementById('editYearFounded').value = org.year_founded || '';
            document.getElementById('editAdviser').value = org.adviser || '';
            document.getElementById('editCoAdviser').value = org.co_adviser || '';

            if (org.logo_url) {
                document.getElementById('editLogoPreview').src = org.logo_url;
                document.getElementById('editLogoPreview').style.display = 'block';
                document.getElementById('editLogoIcon').style.display = 'none';
            } else {
                document.getElementById('editLogoPreview').src = '';
                document.getElementById('editLogoPreview').style.display = 'none';
                document.getElementById('editLogoIcon').style.display = 'block';
            }

            document.getElementById('editOrgModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
        function closeEditModal() {
            document.getElementById('editOrgModal').style.display = 'none';
            document.body.style.overflow = '';
        }
        function previewEditLogo(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = e => {
                    document.getElementById('editLogoPreview').src = e.target.result;
                    document.getElementById('editLogoPreview').style.display = 'block';
                    document.getElementById('editLogoIcon').style.display = 'none';
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>

@endsection
