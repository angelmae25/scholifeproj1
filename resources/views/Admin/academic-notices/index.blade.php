@extends('layouts.admin')
@section('title','Academic Notices')
@section('content')

    <div class="stat-grid">
        <div class="stat-card"><div class="stat-label">Notices Posted</div><div class="stat-value">{{ $stats['posted'] }}</div></div>
        <div class="stat-card"><div class="stat-label">Departments Active</div><div class="stat-value">{{ $stats['dept_active'] }}</div></div>
        <div class="stat-card"><div class="stat-label">Pending Approval</div><div class="stat-value">{{ $stats['pending_approval'] }}</div></div>
    </div>

    <div class="panel">
        <div class="panel-header">
            <span class="panel-title">Academic Notices &amp; Office Posts</span>
            <button onclick="openModal()" class="btn btn-outline">+ Post Notices</button>
        </div>
        <table>
            <thead>
            <tr>
                <th>Title</th>
                <th>Posted By</th>
                <th>Department</th>
                <th>Type</th>
                <th>Status</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            @forelse($notices as $notice)
                <tr>
                    <td>
                        <div style="font-weight:700">{{ $notice->title }}</div>
                        <div style="font-size:.7rem;color:#999">{{ $notice->published_at?->format('M d, Y') }}</div>
                    </td>
                    <td>{{ $notice->posted_by }}</td>
                    <td>{{ $notice->department }}</td>
                    <td><span class="badge badge-red">{{ strtoupper($notice->type) }}</span></td>
                    <td>
                    <span class="badge {{
                        $notice->status === 'published' ? 'badge-green' :
                        ($notice->status === 'pending'  ? 'badge-yellow' : 'badge-gray')
                    }}">{{ strtoupper($notice->status) }}</span>
                    </td>
                    <td>
                        <a href="{{ route('admin.academic-notices.show', $notice) }}"
                           style="color:#8b1c2c;font-size:1.1rem"><x-icon name="eye" /></a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" style="text-align:center;color:#999;padding:24px">No notices found.</td></tr>
            @endforelse
            </tbody>
        </table>
        <div style="margin-top:16px">{{ $notices->links() }}</div>
    </div>

    {{-- POST NOTICE MODAL --}}
    <div id="noticeModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:999;align-items:center;justify-content:center">
        <div style="background:#fff;border-radius:14px;width:540px;max-width:95vw;max-height:90vh;overflow-y:auto;box-shadow:0 8px 40px rgba(0,0,0,.2);position:relative">

            {{-- Sticky header --}}
            <div style="position:sticky;top:0;background:#fff;padding:22px 28px 16px;border-bottom:1.5px solid #f0e8e8;z-index:10">
                <div style="display:flex;align-items:center;justify-content:space-between">
                    <div>
                        <h2 style="font-size:1.15rem;font-weight:800;color:#8b1c2c;margin-bottom:2px">Post academic notice</h2>
                        <p style="font-size:.75rem;color:#888">Submitted notices require admin approval before publishing</p>
                    </div>
                    <button type="button" onclick="closeModal()" style="background:none;border:none;font-size:1.3rem;cursor:pointer;color:#999">→</button>
                </div>

                {{-- Step indicator --}}
                <div style="display:flex;gap:8px;margin-top:12px">
                    <div id="step1-tab" onclick="goToStep(1)"
                         style="flex:1;text-align:center;padding:6px;border-radius:6px;font-size:.75rem;font-weight:700;cursor:pointer;background:#8b1c2c;color:#fff">
                        1. Notice Type
                    </div>
                    <div id="step2-tab" onclick="goToStep(2)"
                         style="flex:1;text-align:center;padding:6px;border-radius:6px;font-size:.75rem;font-weight:700;cursor:pointer;background:#f5eaea;color:#8b1c2c">
                        2. Details
                    </div>
                    <div id="step3-tab" onclick="goToStep(3)"
                         style="flex:1;text-align:center;padding:6px;border-radius:6px;font-size:.75rem;font-weight:700;cursor:pointer;background:#f5eaea;color:#8b1c2c">
                        3. Content & Publish
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.academic-notices.store') }}" enctype="multipart/form-data">
                @csrf

                {{-- ── STEP 1: Notice Type ── --}}
                <div id="step1" style="padding:22px 28px">

                    <div style="font-size:.7rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;margin-bottom:12px">
                        Notice Type
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;margin-bottom:20px">
                        <label id="type-academic" onclick="selectType('academic')"
                               style="display:flex;flex-direction:column;align-items:center;gap:6px;padding:14px 10px;border:2px solid #8b1c2c;border-radius:10px;cursor:pointer;background:#fff8f8;text-align:center">
                            <input type="radio" name="type" value="academic" checked style="display:none">
                            <span style="width:36px;height:36px;border-radius:50%;background:#8b1c2c;color:#fff;display:flex;align-items:center;justify-content:center;font-size:1.1rem"><x-icon name="graduation-cap" /></span>
                            <span style="font-size:.8rem;font-weight:700;color:#8b1c2c">Academic</span>
                            <span style="font-size:.65rem;color:#999">Class, exam, grade notices</span>
                        </label>

                        <label id="type-office" onclick="selectType('office')"
                               style="display:flex;flex-direction:column;align-items:center;gap:6px;padding:14px 10px;border:2px solid #e8ddd5;border-radius:10px;cursor:pointer;background:#fff;text-align:center">
                            <input type="radio" name="type" value="office" style="display:none">
                            <span style="width:36px;height:36px;border-radius:50%;background:#e8ddd5;color:#8b1c2c;display:flex;align-items:center;justify-content:center;font-size:1.1rem"><x-icon name="building" /></span>
                            <span style="font-size:.8rem;font-weight:700;color:#555">Office notice</span>
                            <span style="font-size:.65rem;color:#999">Admin & office communication</span>
                        </label>

                        <label id="type-memo" onclick="selectType('memo')"
                               style="display:flex;flex-direction:column;align-items:center;gap:6px;padding:14px 10px;border:2px solid #e8ddd5;border-radius:10px;cursor:pointer;background:#fff;text-align:center">
                            <input type="radio" name="type" value="memo" style="display:none">
                            <span style="width:36px;height:36px;border-radius:50%;background:#e8ddd5;color:#8b1c2c;display:flex;align-items:center;justify-content:center;font-size:1.1rem"><x-icon name="clipboard" /></span>
                            <span style="font-size:.8rem;font-weight:700;color:#555">Memo / circular</span>
                            <span style="font-size:.65rem;color:#999">Formal school memos</span>
                        </label>
                    </div>

                    <div style="font-size:.7rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;margin-bottom:12px">
                        ② Notice Details
                    </div>

                    {{-- Title --}}
                    <label style="font-size:.72rem;font-weight:700;color:#333;display:block;margin-bottom:4px">
                        Notice title <span style="color:#e53e3e">*</span>
                    </label>
                    <input type="text" name="title" placeholder="e.g. Thesis Defense Schedule - May 2026" required
                           style="width:100%;border:1.5px solid #c9999f;border-radius:8px;padding:10px 14px;font-size:.88rem;outline:none;margin-bottom:14px">

                    {{-- Department & Audience --}}
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px">
                        <div>
                            <label style="font-size:.72rem;font-weight:700;color:#333;display:block;margin-bottom:4px">Department</label>
                            <select name="department" style="width:100%;border:1.5px solid #c9999f;border-radius:8px;padding:9px 12px;font-size:.85rem;outline:none">
                                <option>Engineering</option>
                                <option>Computer Science</option>
                                <option>Business</option>
                                <option>Architecture</option>
                                <option>All Departments</option>
                            </select>
                        </div>
                        <div>
                            <label style="font-size:.72rem;font-weight:700;color:#333;display:block;margin-bottom:4px">Target Audience</label>
                            <select name="audience" style="width:100%;border:1.5px solid #c9999f;border-radius:8px;padding:9px 12px;font-size:.85rem;outline:none">
                                <option>All students</option>
                                <option>Students only</option>
                                <option>Faculty only</option>
                                <option>All Users</option>
                            </select>
                        </div>
                    </div>

                    {{-- Posted by --}}
                    <label style="font-size:.72rem;font-weight:700;color:#333;display:block;margin-bottom:4px">
                        Posted by <span style="color:#e53e3e">*</span>
                    </label>
                    <div style="display:flex;align-items:center;gap:10px;background:#f0faf4;border:1.5px solid #38a169;border-radius:8px;padding:10px 14px;margin-bottom:14px">
                        <div style="width:28px;height:28px;border-radius:50%;background:#8b1c2c;display:flex;align-items:center;justify-content:center;color:#fff;font-size:.7rem;font-weight:800;flex-shrink:0">
                            {{ strtoupper(substr(Auth::guard('admin')->user()->name, 0, 2)) }}
                        </div>
                        <span style="font-size:.85rem;font-weight:700;color:#155724">
                        {{ Auth::guard('admin')->user()->name }}
                    </span>
                    </div>
                    <p style="font-size:.7rem;color:#888;margin-bottom:16px">Auto-filled from your account. You can add co-authors.</p>

                    {{-- Tags --}}
                    <label style="font-size:.72rem;font-weight:700;color:#333;display:block;margin-bottom:8px">Tags (optional)</label>
                    <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:6px" id="tagContainer">
                        @foreach(['Thesis','Defense','Enrollment','Exam','Deadline','Schedule','Requirements'] as $tag)
                            <label style="display:flex;align-items:center;gap:4px;padding:5px 12px;border-radius:20px;font-size:.75rem;cursor:pointer;border:1.5px solid #d9b8bc;background:#fff">
                                <input type="checkbox" name="tags[]" value="{{ $tag }}" style="display:none" onchange="updateTag(this)">
                                <span>{{ $tag }}</span>
                            </label>
                        @endforeach
                    </div>

                    <div style="display:flex;justify-content:flex-end;margin-top:20px">
                        <button type="button" onclick="goToStep(2)"
                                style="padding:9px 24px;background:#8b1c2c;color:#fff;border:none;border-radius:8px;font-size:.82rem;font-weight:700;cursor:pointer">
                            Next →
                        </button>
                    </div>
                </div>

                {{-- ── STEP 2 & 3: Content & Publish ── --}}
                <div id="step2" style="display:none;padding:22px 28px">

                    {{-- Content --}}
                    <label style="font-size:.72rem;font-weight:700;color:#333;display:block;margin-bottom:6px">Content</label>
                    <div style="border:1.5px solid #38a169;border-radius:8px;overflow:hidden;margin-bottom:16px">
                        <div style="background:#f0faf4;padding:6px 10px;display:flex;gap:4px;border-bottom:1px solid #c6e9d4;flex-wrap:wrap">
                            @foreach([['B','Bold'],['I','Italic'],['U','Underline'],['align-left','Align'],['link','Link'],['paperclip','Attachment'],['image','Image'],['list','List'],['arrow-left','Outdent'],['arrow-up','Move up'],['arrow-right','Indent'],['undo','Undo'],['redo','Redo']] as $t)
                                <button type="button"
                                        class="toolbar-btn" title="{{ $t[1] }}" aria-label="{{ $t[1] }}">@if(in_array($t[0], ['B','I','U'])){{ $t[0] }}@else<x-icon name="{{ $t[0] }}" />@endif</button>
                            @endforeach
                        </div>
                        <textarea name="content" id="contentArea" rows="6"
                                  placeholder="Write the full content of your academic notice here. Be clear and concise - students and faculty will act on this information."
                                  style="width:100%;border:none;outline:none;padding:12px 14px;font-size:.85rem;resize:vertical;font-family:inherit;min-height:130px"></textarea>
                        <div style="text-align:right;padding:4px 10px;font-size:.68rem;color:#aaa;border-top:1px solid #f0faf4">
                            0/5000 characters
                        </div>
                    </div>

                    {{-- Attachments --}}
                    <div style="font-size:.7rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;margin-bottom:8px">
                        Attachments (optional)
                    </div>
                    <label style="display:flex;flex-direction:column;align-items:center;justify-content:center;border:2px dashed #38a169;border-radius:8px;padding:20px;cursor:pointer;background:#f0faf4;gap:6px;margin-bottom:8px">
                        <span style="font-size:1.4rem"><x-icon name="upload" /></span>
                        <span style="font-size:.8rem;font-weight:600;color:#38a169">Drag &amp; drop or click to upload</span>
                        <span style="font-size:.7rem;color:#888">PDF, DOCX, XLSX, JPG, PNG · MAX 10MB per file</span>
                        <input type="file" name="attachment" id="attachmentInput" multiple accept=".pdf,.docx,.xlsx,.jpg,.jpeg,.png" style="display:none" onchange="showFiles(this)">
                    </label>
                    <div id="fileList" style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:16px"></div>

                    {{-- Visibility & Scheduling --}}
                    <div style="font-size:.7rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;margin-bottom:10px">
                        Visibility &amp; Scheduling
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px">
                        <div>
                            <label style="font-size:.72rem;font-weight:700;color:#333;display:block;margin-bottom:4px">Publish preference</label>
                            <select name="publish_preference" onchange="toggleSchedule(this)"
                                    style="width:100%;border:1.5px solid #c9999f;border-radius:8px;padding:9px 12px;font-size:.85rem;outline:none">
                                <option value="after_approval">Publish after approval</option>
                                <option value="scheduled">Schedule for later</option>
                                <option value="draft">Save as draft</option>
                            </select>
                        </div>
                        <div id="scheduleField">
                            <label style="font-size:.72rem;font-weight:700;color:#333;display:block;margin-bottom:4px">Scheduled date &amp; time</label>
                            <input type="datetime-local" name="scheduled_at"
                                   style="width:100%;border:1.5px solid #c9999f;border-radius:8px;padding:8px 12px;font-size:.82rem;outline:none">
                        </div>
                    </div>

                    {{-- Expiry --}}
                    <div style="margin-bottom:20px">
                        <label style="font-size:.72rem;font-weight:700;color:#333;display:block;margin-bottom:4px">Notice expiry (optional)</label>
                        <input type="datetime-local" name="expires_at"
                               style="width:100%;border:1.5px solid #c9999f;border-radius:8px;padding:9px 12px;font-size:.82rem;outline:none;margin-bottom:4px">
                        <p style="font-size:.68rem;color:#888">Notice will be automatically archived after this date</p>
                    </div>

                    {{-- Action buttons --}}
                    <div style="display:flex;align-items:center;gap:10px;padding-top:4px">
                        <button type="submit" name="action" value="draft"
                                style="padding:9px 18px;border:1.5px solid #8b1c2c;background:#fff;color:#8b1c2c;border-radius:8px;font-size:.82rem;font-weight:600;cursor:pointer">
                            Save as draft
                        </button>
                        <button type="button" onclick="closeModal()"
                                style="padding:9px 18px;border:1.5px solid #ccc;background:#fff;color:#666;border-radius:8px;font-size:.82rem;font-weight:600;cursor:pointer">
                            Cancel
                        </button>
                        <button type="submit" name="action" value="submit"
                                style="margin-left:auto;padding:9px 20px;background:#8b1c2c;color:#fff;border:none;border-radius:8px;font-size:.82rem;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:6px">
                            <x-icon name="megaphone" /> Submit for approval
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </div>

    <script>
        // Modal
        function openModal() {
            document.getElementById('noticeModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
            goToStep(1);
        }
        function closeModal() {
            document.getElementById('noticeModal').style.display = 'none';
            document.body.style.overflow = '';
        }
        document.getElementById('noticeModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });

        // Steps
        function goToStep(n) {
            document.getElementById('step1').style.display = n === 1 ? 'block' : 'none';
            document.getElementById('step2').style.display = n === 2 ? 'block' : 'none';

            ['step1-tab','step2-tab','step3-tab'].forEach((id, i) => {
                const el = document.getElementById(id);
                if (el) {
                    el.style.background = (i + 1) === n ? '#8b1c2c' : '#f5eaea';
                    el.style.color      = (i + 1) === n ? '#fff'    : '#8b1c2c';
                }
            });
        }

        // Step 3 tab goes to step 2 (content section)
        document.getElementById('step3-tab').onclick = () => goToStep(2);

        // Type selector
        function selectType(type) {
            ['academic','office','memo'].forEach(t => {
                const el = document.getElementById('type-' + t);
                if (t === type) {
                    el.style.border = '2px solid #8b1c2c';
                    el.style.background = '#fff8f8';
                    el.querySelector('span:nth-child(2)').style.background = '#8b1c2c';
                    el.querySelector('input').checked = true;
                } else {
                    el.style.border = '2px solid #e8ddd5';
                    el.style.background = '#fff';
                    el.querySelector('span:nth-child(2)').style.background = '#e8ddd5';
                }
            });
        }

        // Tags
        function updateTag(cb) {
            const label = cb.closest('label');
            if (cb.checked) {
                label.style.background = '#fdf0f1';
                label.style.borderColor = '#8b1c2c';
                label.style.color = '#8b1c2c';
                label.insertAdjacentHTML('afterbegin','<span class="tag-check" style="color:#38a169;display:inline-flex"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="m8 12 3 3 5-6"/></svg></span>');
            } else {
                label.style.background = '#fff';
                label.style.borderColor = '#d9b8bc';
                label.style.color = '#333';
                const check = label.querySelector('.tag-check');
                if (check) check.remove();
            }
        }

        // Character counter
        document.getElementById('contentArea').addEventListener('input', function() {
            this.nextElementSibling && (this.nextElementSibling.textContent = this.value.length + '/5000 characters');
        });

        // File list
        function showFiles(input) {
            const list = document.getElementById('fileList');
            list.innerHTML = '';
            Array.from(input.files).forEach(f => {
                list.innerHTML += `<span style="background:#f0faf4;border:1px solid #38a169;border-radius:20px;padding:4px 10px;font-size:.72rem;color:#155724"><x-icon name="file" /> ${f.name} <x-icon name="x" /></span>`;
            });
        }

        // Schedule toggle
        function toggleSchedule(sel) {
            document.getElementById('scheduleField').style.opacity = sel.value === 'scheduled' ? '1' : '0.4';
        }
    </script>

@endsection
