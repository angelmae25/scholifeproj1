@extends('layouts.admin')
@section('title','Announcement')
@section('content')

    <div class="stat-grid">
        <div class="stat-card"><div class="stat-label">Published</div><div class="stat-value">{{ $stats['published'] }}</div></div>
        <div class="stat-card"><div class="stat-label">Schedule</div><div class="stat-value">{{ $stats['scheduled'] }}</div></div>
        <div class="stat-card"><div class="stat-label">Total Views</div><div class="stat-value">{{ number_format($stats['total_views']) }}</div></div>
    </div>

    @if(session('success'))
        <div style="background:#d4edda;color:#155724;border:1px solid #c3e6cb;border-radius:8px;padding:12px 16px;margin-bottom:16px;font-size:.85rem;font-weight:600">
            <x-icon name="check-circle" /> {{ session('success') }}
        </div>
    @endif
    <div class="panel">
        <div class="panel-header">
            <span class="panel-title">Announcement</span>
            <button onclick="openModal()" class="btn btn-outline">+ New Announcement</button>
        </div>
        <table>
            <thead>
            <tr>
                <th>Title</th>
                <th>Author</th>
                <th>Audience</th>
                <th>Status</th>
                <th>Views</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            @forelse($announcements as $a)
                <tr>
                    <td>
                        <div style="font-weight:700">{{ $a->title }}</div>
                        <div style="font-size:.7rem;color:#999">Published {{ $a->published_at?->format('M d, Y') }}</div>
                    </td>
                    <td>{{ $a->author }}</td>
                    <td><span class="badge badge-gray">{{ $a->audience }}</span></td>
                    <td><span class="badge {{ $a->status==='live'?'badge-green':'badge-yellow' }}">{{ ucfirst($a->status) }}</span></td>
                    <td>{{ number_format($a->views) }}</td>
                    <td>
                        <a href="{{ route('admin.announcements.show', $a) }}" class="icon-button" title="View" aria-label="View"><x-icon name="eye" /></a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" style="text-align:center;color:#999;padding:24px">No announcements found.</td></tr>
            @endforelse
            </tbody>
        </table>
        <div style="margin-top:16px">{{ $announcements->links() }}</div>
    </div>

    {{-- ── MODAL ── --}}
    <div id="announcementModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:999;align-items:center;justify-content:center">
        <div style="background:#fff;border-radius:14px;width:520px;max-width:95vw;max-height:90vh;overflow-y:auto;padding:28px 32px;position:relative;box-shadow:0 8px 40px rgba(0,0,0,.2)">

            {{-- Header --}}
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
                <h2 style="font-size:1.2rem;font-weight:800;color:#8b1c2c">New Announcement</h2>
                <button onclick="closeModal()" style="background:none;border:none;font-size:1.4rem;cursor:pointer;color:#999;line-height:1">→</button>
            </div>

            <form method="POST" action="{{ route('admin.announcements.store') }}" enctype="multipart/form-data">
                @csrf

                {{-- Title --}}
                <input type="text" name="title" placeholder="e.g. Enrollment Period Open - 2nd Semester"
                       style="width:100%;border:1.5px solid #c9999f;border-radius:8px;padding:10px 14px;font-size:.88rem;margin-bottom:14px;outline:none">

                {{-- Audience & Category --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px">
                    <div>
                        <label style="font-size:.7rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;display:block;margin-bottom:4px">Audience</label>
                        <select name="audience" style="width:100%;border:1.5px solid #c9999f;border-radius:8px;padding:9px 12px;font-size:.85rem;outline:none">
                            <option>All Users</option>
                            <option>Students</option>
                            <option>Professors</option>
                            <option>Offices</option>
                            <option>Org Officers</option>
                        </select>
                    </div>
                    <div>
                        <label style="font-size:.7rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;display:block;margin-bottom:4px">Category</label>
                        <select name="category" style="width:100%;border:1.5px solid #c9999f;border-radius:8px;padding:9px 12px;font-size:.85rem;outline:none">
                            <option>General</option>
                            <option>Academic</option>
                            <option>Event</option>
                            <option>Emergency</option>
                            <option>Scholarship</option>
                        </select>
                    </div>
                </div>

                {{-- Content label + toolbar --}}
                <label style="font-size:.7rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;display:block;margin-bottom:6px">Content</label>
                <div style="border:1.5px solid #38a169;border-radius:8px;overflow:hidden;margin-bottom:14px">
                    {{-- Toolbar --}}
                    <div style="background:#f0faf4;padding:6px 10px;display:flex;gap:6px;border-bottom:1px solid #c6e9d4">
                        @foreach([['B','Bold'],['I','Italic'],['U','Underline'],['align-left','Align'],['link','Link'],['paperclip','Attachment'],['image','Image']] as $tool)
                            <button type="button" onclick="formatText('{{ $tool[0] }}')" class="toolbar-btn" title="{{ $tool[1] }}" aria-label="{{ $tool[1] }}">
                                @if(in_array($tool[0], ['B','I','U'])){{ $tool[0] }}@else<x-icon name="{{ $tool[0] }}" />@endif
                            </button>
                        @endforeach
                    </div>
                    <textarea name="content" id="contentArea" rows="5"
                              placeholder="Write your announcement here..."
                              style="width:100%;border:none;outline:none;padding:12px 14px;font-size:.85rem;resize:vertical;font-family:inherit;min-height:110px"></textarea>
                </div>

                {{-- Attachment & Publish type --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px">
                    <div>
                        <label style="font-size:.7rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;display:block;margin-bottom:6px">Attachment (Optional)</label>
                        <label style="display:flex;flex-direction:column;align-items:center;justify-content:center;border:2px dashed #38a169;border-radius:8px;padding:18px;cursor:pointer;background:#f0faf4;gap:6px">
                            <span style="font-size:1.4rem"><x-icon name="upload" /></span>
                            <span style="font-size:.75rem;color:#555">Click to upload file or image</span>
                            <input type="file" name="attachment" style="display:none">
                        </label>
                    </div>
                    <div>
                        <label style="font-size:.7rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;display:block;margin-bottom:6px">Publish type</label>
                        <select name="publish_type" id="publishType" onchange="toggleSchedule()"
                                style="width:100%;border:1.5px solid #c9999f;border-radius:8px;padding:9px 12px;font-size:.85rem;outline:none;margin-bottom:8px">
                            <option value="now">Publish now</option>
                            <option value="scheduled">Schedule</option>
                        </select>
                        <div id="scheduleFields" style="display:none">
                            <label style="font-size:.7rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;display:block;margin-bottom:4px">Scheduled date &amp; time</label>
                            <input type="datetime-local" name="scheduled_at"
                                   style="width:100%;border:1.5px solid #c9999f;border-radius:8px;padding:8px 12px;font-size:.82rem;outline:none">
                        </div>
                    </div>
                </div>

                {{-- Push notification checkbox --}}
                <label style="display:flex;align-items:center;gap:8px;font-size:.78rem;color:#555;background:#fff8f8;border:1.5px solid #f0d0d4;border-radius:8px;padding:10px 14px;cursor:pointer;margin-bottom:20px">
                    <input type="checkbox" name="send_push" style="accent-color:#8b1c2c;width:14px;height:14px">
                    Send push notification to audience when published
                </label>

                {{-- Action buttons --}}
                <div style="display:flex;align-items:center;gap:10px;justify-content:flex-start">
                    <button type="submit" name="action" value="draft"
                            style="padding:9px 18px;border:1.5px solid #8b1c2c;background:#fff;color:#8b1c2c;border-radius:8px;font-size:.82rem;font-weight:600;cursor:pointer">
                        Save as draft
                    </button>
                    <button type="button" onclick="closeModal()"
                            style="padding:9px 18px;border:1.5px solid #ccc;background:#fff;color:#666;border-radius:8px;font-size:.82rem;font-weight:600;cursor:pointer">
                        Cancel
                    </button>
                    <button type="submit" name="action" value="publish"
                            style="margin-left:auto;padding:9px 20px;background:#8b1c2c;color:#fff;border:none;border-radius:8px;font-size:.82rem;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:6px">
                        <x-icon name="megaphone" /> Publish announcement
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal() {
            const m = document.getElementById('announcementModal');
            m.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
        function closeModal() {
            const m = document.getElementById('announcementModal');
            m.style.display = 'none';
            document.body.style.overflow = '';
        }
        function toggleSchedule() {
            const val = document.getElementById('publishType').value;
            document.getElementById('scheduleFields').style.display = val === 'scheduled' ? 'block' : 'none';
        }
        function formatText(tool) {
            const ta = document.getElementById('contentArea');
            const start = ta.selectionStart;
            const end   = ta.selectionEnd;
            const sel   = ta.value.substring(start, end);
            const map   = {'B':'**'+sel+'**','I':'_'+sel+'_','U':'__'+sel+'__'};
            if (map[tool]) {
                ta.value = ta.value.substring(0,start) + map[tool] + ta.value.substring(end);
            }
        }
        // Close on backdrop click
        document.getElementById('announcementModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });
    </script>

@endsection
