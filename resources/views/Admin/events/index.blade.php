@extends('layouts.admin')
@section('title','Events')
@section('content')

    {{-- Success message --}}
    @if(session('success'))
        <div style="background:#d4edda;color:#155724;border:1px solid #c3e6cb;border-radius:8px;padding:12px 16px;margin-bottom:16px;font-size:.85rem;font-weight:600">
            <x-icon name="check-circle" /> {{ session('success') }}
        </div>
    @endif

    <div class="stat-grid">
        <div class="stat-card"><div class="stat-label">Total Events</div><div class="stat-value">{{ $stats['total_events'] }}</div></div>
        <div class="stat-card"><div class="stat-label">Total RSVPs</div><div class="stat-value">{{ number_format($stats['total_rsvps']) }}</div></div>
        <div class="stat-card"><div class="stat-label">Avg Attendance</div><div class="stat-value">{{ $stats['avg_attendance'] }}</div></div>
        <div class="stat-card"><div class="stat-label">Reminder Sent</div><div class="stat-value">{{ number_format($stats['reminders_sent']) }}</div></div>
    </div>

    <div class="panel">
        <div class="panel-header">
            <span class="panel-title">Event Management</span>
            <button onclick="openModal()" class="btn btn-outline">+ Create Event</button>
        </div>
        <table>
            <thead>
            <tr>
                <th>Event</th>
                <th>Date</th>
                <th>Organizer</th>
                <th>RSVPs</th>
                <th>Attendance</th>
                <th>Status</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            @forelse($events as $event)
                <tr>
                    <td>
                        <div style="font-weight:700">{{ $event->title }}</div>
                        <div style="font-size:.7rem;color:#999">{{ $event->location }}</div>
                    </td>
                    <td>{{ $event->event_date->format('M d, Y') }}</td>
                    <td>{{ $event->organizer }}</td>
                    <td>{{ number_format($event->rsvp_count) }}</td>
                    <td>{{ $event->attendance_pct }}</td>
                    <td>
                    <span class="badge {{
                        $event->status === 'completed' ? 'badge-green'  :
                        ($event->status === 'upcoming'  ? 'badge-blue'   :
                        ($event->status === 'ongoing'   ? 'badge-yellow' : 'badge-red'))
                    }}">{{ ucfirst($event->status) }}</span>
                    </td>
                    <td>
                        <a href="{{ route('admin.events.show', $event) }}" class="icon-button" title="View" aria-label="View"><x-icon name="eye" /></a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" style="text-align:center;color:#999;padding:24px">No events found.</td></tr>
            @endforelse
            </tbody>
        </table>
        <div style="margin-top:16px">{{ $events->links() }}</div>
    </div>

    {{-- CREATE EVENT MODAL --}}
    <div id="eventModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:999;align-items:center;justify-content:center">
        <div style="background:#fff;border-radius:14px;width:560px;max-width:95vw;max-height:90vh;overflow-y:auto;padding:28px 32px;position:relative;box-shadow:0 8px 40px rgba(0,0,0,.2)">

            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
                <h2 style="font-size:1.2rem;font-weight:800;color:#8b1c2c">Create Event</h2>
                <button onclick="closeModal()" style="background:none;border:none;font-size:1.4rem;cursor:pointer;color:#999">→</button>
            </div>

            <form method="POST" action="{{ route('admin.events.store') }}" enctype="multipart/form-data">
                @csrf

                {{-- Event Name --}}
                <label style="font-size:.7rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;display:block;margin-bottom:4px">Event Name</label>
                <input type="text" name="title" placeholder="e.g. Intramural Sports Fest 2026"
                       style="width:100%;border:1.5px solid #c9999f;border-radius:8px;padding:10px 14px;font-size:.88rem;margin-bottom:14px;outline:none">

                {{-- Event Type & Organizer --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px">
                    <div>
                        <label style="font-size:.7rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;display:block;margin-bottom:4px">Event type</label>
                        <select name="type" style="width:100%;border:1.5px solid #c9999f;border-radius:8px;padding:9px 12px;font-size:.85rem;outline:none">
                            <option value="on_campus">On-campus</option>
                            <option value="academic">Academic</option>
                            <option value="organization">Organization</option>
                            <option value="online">Online</option>
                        </select>
                    </div>
                    <div>
                        <label style="font-size:.7rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;display:block;margin-bottom:4px">Organizer</label>
                        <input type="text" name="organizer" placeholder="e.g. Admin, CS Department"
                               style="width:100%;border:1.5px solid #c9999f;border-radius:8px;padding:9px 12px;font-size:.85rem;outline:none">
                    </div>
                </div>

                {{-- Start & End date/time --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px">
                    <div>
                        <label style="font-size:.7rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;display:block;margin-bottom:4px">Start date &amp; time</label>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px">
                            <input type="date" name="event_date" style="border:1.5px solid #c9999f;border-radius:8px;padding:8px 10px;font-size:.82rem;outline:none">
                            <input type="time" name="event_time" style="border:1.5px solid #c9999f;border-radius:8px;padding:8px 10px;font-size:.82rem;outline:none">
                        </div>
                    </div>
                    <div>
                        <label style="font-size:.7rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;display:block;margin-bottom:4px">End date &amp; time</label>
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:6px">
                            <input type="date" name="end_date" style="border:1.5px solid #c9999f;border-radius:8px;padding:8px 10px;font-size:.82rem;outline:none">
                            <input type="time" name="end_time" style="border:1.5px solid #c9999f;border-radius:8px;padding:8px 10px;font-size:.82rem;outline:none">
                        </div>
                    </div>
                </div>

                {{-- Venue --}}
                <label style="font-size:.7rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;display:block;margin-bottom:4px">Venue / location</label>
                <input type="text" name="location" placeholder="e.g. Main Gymnasium, Building A Room 301, Online (Zoom)"
                       style="width:100%;border:1.5px solid #c9999f;border-radius:8px;padding:10px 14px;font-size:.88rem;margin-bottom:14px;outline:none">

                {{-- Description --}}
                <label style="font-size:.7rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;display:block;margin-bottom:4px">Description</label>
                <textarea name="description" rows="4" placeholder="Describe the event, activities, and what participants can expect..."
                          style="width:100%;border:1.5px solid #c9999f;border-radius:8px;padding:10px 14px;font-size:.85rem;outline:none;resize:vertical;font-family:inherit;margin-bottom:14px"></textarea>

                {{-- Event Image --}}
                <label style="font-size:.7rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;display:block;margin-bottom:8px">Event Photo</label>
                <label style="display:flex;align-items:center;justify-content:center;min-height:150px;border:2px dashed #38a169;border-radius:10px;background:#f0faf4;cursor:pointer;margin-bottom:14px;overflow:hidden;position:relative">
                    <img id="eventImagePreview" src="" style="display:none;width:100%;height:180px;object-fit:cover">
                    <span id="eventImagePlaceholder" style="display:flex;flex-direction:column;align-items:center;gap:8px;color:#2f855a;font-size:.82rem;font-weight:700">
                        <x-icon name="image" size="28" />
                        Click to upload event photo
                    </span>
                    <input type="file" name="image" accept="image/*" style="display:none" onchange="previewEventImage(this)">
                </label>

                {{-- Max attendees, RSVP deadline, Points --}}
                <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:14px">
                    <div>
                        <label style="font-size:.7rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;display:block;margin-bottom:4px">Max attendees</label>
                        <input type="number" name="max_attendees" placeholder="e.g. 500"
                               style="width:100%;border:1.5px solid #c9999f;border-radius:8px;padding:9px 12px;font-size:.85rem;outline:none">
                    </div>
                    <div>
                        <label style="font-size:.7rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;display:block;margin-bottom:4px">RSVP deadline</label>
                        <input type="date" name="rsvp_deadline"
                               style="width:100%;border:1.5px solid #c9999f;border-radius:8px;padding:9px 12px;font-size:.82rem;outline:none">
                    </div>
                    <div>
                        <label style="font-size:.7rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;display:block;margin-bottom:4px">Points awarded</label>
                        <input type="number" name="points_awarded" placeholder="e.g 50"
                               style="width:100%;border:1.5px solid #c9999f;border-radius:8px;padding:9px 12px;font-size:.85rem;outline:none">
                    </div>
                </div>

                {{-- Reminders --}}
                <label style="font-size:.7rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;display:block;margin-bottom:8px">Reminders</label>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:20px">
                    <label style="display:flex;align-items:center;gap:8px;background:#fff8f8;border:1.5px solid #f0d0d4;border-radius:8px;padding:10px 14px;cursor:pointer;font-size:.82rem;color:#555">
                        <input type="checkbox" name="remind_1day" style="accent-color:#8b1c2c;width:14px;height:14px">
                        Remind 1 day before
                    </label>
                    <label style="display:flex;align-items:center;gap:8px;background:#fff8f8;border:1.5px solid #f0d0d4;border-radius:8px;padding:10px 14px;cursor:pointer;font-size:.82rem;color:#555">
                        <input type="checkbox" name="remind_1hour" style="accent-color:#8b1c2c;width:14px;height:14px">
                        Remind 1 hour before
                    </label>
                </div>

                {{-- Buttons --}}
                <div style="display:flex;align-items:center;justify-content:flex-end;gap:10px">
                    <button type="button" onclick="closeModal()"
                            style="padding:9px 18px;border:1.5px solid #ccc;background:#fff;color:#666;border-radius:8px;font-size:.82rem;font-weight:600;cursor:pointer">
                        Cancel
                    </button>
                    <button type="submit"
                            style="padding:9px 20px;background:#8b1c2c;color:#fff;border:none;border-radius:8px;font-size:.82rem;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:6px">
                        <x-icon name="calendar" /> Create event
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal() {
            document.getElementById('eventModal').style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }
        function closeModal() {
            document.getElementById('eventModal').style.display = 'none';
            document.body.style.overflow = '';
        }
        document.getElementById('eventModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });
        function previewEventImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = e => {
                    document.getElementById('eventImagePreview').src = e.target.result;
                    document.getElementById('eventImagePreview').style.display = 'block';
                    document.getElementById('eventImagePlaceholder').style.display = 'none';
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>

@endsection
