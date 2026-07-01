@extends('layouts.admin')
@section('title','Event Details')
@section('content')

    <div style="max-width:750px;margin:0 auto">

        {{-- Back button --}}
        <a href="{{ route('admin.events') }}"
           style="display:inline-flex;align-items:center;gap:6px;color:#8b1c2c;font-size:.82rem;font-weight:600;text-decoration:none;margin-bottom:20px">
            ← Back to Events
        </a>

        <div class="panel">

            {{-- Header --}}
            <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:20px;padding-bottom:16px;border-bottom:1.5px solid #f0e8e8">
                <div style="flex:1">
                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;flex-wrap:wrap">
                    <span class="badge {{
                        $event->status === 'completed' ? 'badge-green'  :
                        ($event->status === 'upcoming'  ? 'badge-blue'   :
                        ($event->status === 'ongoing'   ? 'badge-yellow' : 'badge-red'))
                    }}">{{ strtoupper($event->status) }}</span>
                        <span class="badge badge-gray">{{ ucfirst(str_replace('_',' ',$event->type)) }}</span>
                    </div>
                    <h1 style="font-size:1.3rem;font-weight:800;color:#1a1a1a;line-height:1.3">
                        {{ $event->title }}
                    </h1>
                </div>
            </div>

            {{-- Event Image --}}
            @if($event->image)
                <div style="margin-bottom:24px">
                    <div style="font-size:.72rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;margin-bottom:10px">Event Photo</div>
                    <div style="background:#fff;border:1.5px solid #f0e8e8;border-radius:10px;padding:10px">
                        <img src="{{ asset('storage/' . $event->image) }}"
                             alt="Event photo"
                             style="width:100%;max-height:420px;object-fit:contain;border-radius:8px;background:#f8f1f1">
                    </div>
                    <a href="{{ asset('storage/' . $event->image) }}"
                       target="_blank"
                       style="display:inline-flex;align-items:center;gap:8px;margin-top:10px;color:#38a169;font-size:.82rem;font-weight:700;text-decoration:none">
                        <x-icon name="image" /> Open full image
                    </a>
                </div>
            @endif

            {{-- Meta grid --}}
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:14px;margin-bottom:24px">
                <div style="background:#fdf8f3;border-radius:8px;padding:12px 14px">
                    <div style="font-size:.68rem;color:#999;font-weight:700;text-transform:uppercase;letter-spacing:.6px;margin-bottom:4px">Organizer</div>
                    <div style="font-size:.85rem;font-weight:700;color:#333">{{ $event->organizer }}</div>
                </div>
                <div style="background:#fdf8f3;border-radius:8px;padding:12px 14px">
                    <div style="font-size:.68rem;color:#999;font-weight:700;text-transform:uppercase;letter-spacing:.6px;margin-bottom:4px">Event Date</div>
                    <div style="font-size:.85rem;font-weight:700;color:#333">
                        {{ $event->event_date->format('M d, Y') }}
                        @if($event->event_time)
                            <span style="color:#888;font-weight:400"> · {{ \Carbon\Carbon::parse($event->event_time)->format('g:i A') }}</span>
                        @endif
                    </div>
                </div>
                <div style="background:#fdf8f3;border-radius:8px;padding:12px 14px">
                    <div style="font-size:.68rem;color:#999;font-weight:700;text-transform:uppercase;letter-spacing:.6px;margin-bottom:4px">End Date</div>
                    <div style="font-size:.85rem;font-weight:700;color:#333">
                        {{ $event->end_date ? $event->end_date->format('M d, Y') : '—' }}
                        @if($event->end_time)
                            <span style="color:#888;font-weight:400"> · {{ \Carbon\Carbon::parse($event->end_time)->format('g:i A') }}</span>
                        @endif
                    </div>
                </div>
                <div style="background:#fdf8f3;border-radius:8px;padding:12px 14px">
                    <div style="font-size:.68rem;color:#999;font-weight:700;text-transform:uppercase;letter-spacing:.6px;margin-bottom:4px">Location</div>
                    <div style="font-size:.85rem;font-weight:700;color:#333">{{ $event->location ?? '—' }}</div>
                </div>
            </div>

            {{-- Stats row --}}
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(120px,1fr));gap:14px;margin-bottom:24px">
                <div style="background:#fff8f8;border:1.5px solid #f0d0d4;border-radius:8px;padding:12px 14px;text-align:center">
                    <div style="font-size:1.5rem;font-weight:800;color:#8b1c2c">{{ number_format($event->rsvp_count) }}</div>
                    <div style="font-size:.7rem;color:#999;margin-top:2px">RSVPs</div>
                </div>
                <div style="background:#fff8f8;border:1.5px solid #f0d0d4;border-radius:8px;padding:12px 14px;text-align:center">
                    <div style="font-size:1.5rem;font-weight:800;color:#8b1c2c">{{ number_format($event->attendance_count) }}</div>
                    <div style="font-size:.7rem;color:#999;margin-top:2px">Attended</div>
                </div>
                <div style="background:#fff8f8;border:1.5px solid #f0d0d4;border-radius:8px;padding:12px 14px;text-align:center">
                    <div style="font-size:1.5rem;font-weight:800;color:#8b1c2c">{{ $event->attendance_pct }}</div>
                    <div style="font-size:.7rem;color:#999;margin-top:2px">Attendance</div>
                </div>
                <div style="background:#fff8f8;border:1.5px solid #f0d0d4;border-radius:8px;padding:12px 14px;text-align:center">
                    <div style="font-size:1.5rem;font-weight:800;color:#8b1c2c">{{ $event->points_awarded ?? 0 }}</div>
                    <div style="font-size:.7rem;color:#999;margin-top:2px">Points</div>
                </div>
                <div style="background:#fff8f8;border:1.5px solid #f0d0d4;border-radius:8px;padding:12px 14px;text-align:center">
                    <div style="font-size:1.5rem;font-weight:800;color:#8b1c2c">{{ $event->max_attendees ?? '∞' }}</div>
                    <div style="font-size:.7rem;color:#999;margin-top:2px">Max Slots</div>
                </div>
                <div style="background:#fff8f8;border:1.5px solid #f0d0d4;border-radius:8px;padding:12px 14px;text-align:center">
                    <div style="font-size:1.5rem;font-weight:800;color:#8b1c2c">{{ number_format($event->reminders_sent) }}</div>
                    <div style="font-size:.7rem;color:#999;margin-top:2px">Reminders</div>
                </div>
            </div>

            {{-- RSVP Deadline --}}
            @if($event->rsvp_deadline)
                <div style="background:#fffbea;border:1.5px solid #f6e05e;border-radius:8px;padding:12px 16px;margin-bottom:20px;font-size:.85rem;color:#744210">
                    <x-icon name="calendar" /> <strong>RSVP Deadline:</strong> {{ $event->rsvp_deadline->format('M d, Y') }}
                </div>
            @endif

            {{-- Reminders --}}
            <div style="margin-bottom:20px">
                <div style="font-size:.72rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;margin-bottom:10px">Reminders</div>
                <div style="display:flex;gap:10px;flex-wrap:wrap">
                <span style="display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border-radius:20px;font-size:.78rem;font-weight:600;background:{{ $event->remind_1day ? '#d4edda' : '#f5f5f5' }};color:{{ $event->remind_1day ? '#155724' : '#999' }}">
                    <x-icon name="{{ $event->remind_1day ? 'check-circle' : 'x-circle' }}" /> 1 Day Before
                </span>
                    <span style="display:inline-flex;align-items:center;gap:6px;padding:6px 14px;border-radius:20px;font-size:.78rem;font-weight:600;background:{{ $event->remind_1hour ? '#d4edda' : '#f5f5f5' }};color:{{ $event->remind_1hour ? '#155724' : '#999' }}">
                    <x-icon name="{{ $event->remind_1hour ? 'check-circle' : 'x-circle' }}" /> 1 Hour Before
                </span>
                </div>
            </div>

            {{-- Description --}}
            @if($event->description)
                <div style="margin-bottom:24px">
                    <div style="font-size:.72rem;font-weight:700;color:#8b1c2c;text-transform:uppercase;letter-spacing:.6px;margin-bottom:10px">Description</div>
                    <div style="background:#fafafa;border:1.5px solid #f0e8e8;border-radius:8px;padding:18px 20px;font-size:.88rem;color:#333;line-height:1.7;white-space:pre-wrap">{{ $event->description }}</div>
                </div>
            @endif

            {{-- Footer actions --}}
            <div style="display:flex;gap:10px;padding-top:16px;border-top:1.5px solid #f0e8e8;align-items:center">
                <a href="{{ route('admin.events') }}" class="btn btn-outline">← Back</a>

                {{-- Delete --}}
                <form method="POST" action="{{ route('admin.events.destroy', $event) }}"
                      onsubmit="return confirm('Are you sure you want to delete this event?')"
                      style="margin-left:auto">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            style="padding:7px 16px;background:#e53e3e;color:#fff;border:none;border-radius:6px;font-size:.78rem;font-weight:600;cursor:pointer">
                        <x-icon name="trash" /> Delete Event
                    </button>
                </form>
            </div>

        </div>
    </div>

@endsection
