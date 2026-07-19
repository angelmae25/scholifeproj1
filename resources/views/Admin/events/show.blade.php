@extends('layouts.admin')
@section('title','Event Details')
@section('content')

    <div style="max-width:1120px;margin:0 auto">

        {{-- Back button --}}
        <a href="{{ route('admin.events') }}"
           style="display:inline-flex;align-items:center;gap:6px;color:#8b1c2c;font-size:.82rem;font-weight:600;text-decoration:none;margin-bottom:20px">
            ← Back to Events
        </a>

        <div style="display:grid;grid-template-columns:minmax(0,1fr) 320px;gap:18px;align-items:start">
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
                    <div style="font-size:.68rem;color:#999;font-weight:700;text-transform:uppercase;letter-spacing:.6px;margin-bottom:4px">Audience</div>
                    <div style="font-size:.85rem;font-weight:700;color:#333">{{ $event->audience ?? 'All' }}</div>
                </div>
                <div style="background:#fdf8f3;border-radius:8px;padding:12px 14px">
                    <div style="font-size:.68rem;color:#999;font-weight:700;text-transform:uppercase;letter-spacing:.6px;margin-bottom:4px">Location</div>
                    <div style="font-size:.85rem;font-weight:700;color:#333">{{ $event->location ?? '—' }}</div>
                </div>
            </div>

            {{-- Stats row --}}
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(120px,1fr));gap:14px;margin-bottom:24px">
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
                    <div style="font-size:1.5rem;font-weight:800;color:#8b1c2c">{{ number_format($event->reminders_sent) }}</div>
                    <div style="font-size:.7rem;color:#999;margin-top:2px">Reminders</div>
                </div>
            </div>

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
                      data-confirm-message="Delete this event? This will remove it from both web and mobile."
                      data-confirm-action="Delete"
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

        <div class="panel" style="position:sticky;top:86px">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px">
                <div>
                    <div style="font-size:.72rem;font-weight:800;color:#8b1c2c;text-transform:uppercase;letter-spacing:.8px">Student Attendance</div>
                    <div style="font-size:.75rem;color:#888;margin-top:2px">Confirm students who really attended.</div>
                </div>
                <span class="badge badge-yellow">{{ $attendances->where('status', 'pending')->count() }} Pending</span>
            </div>

            @forelse($attendances as $attendance)
                <div style="display:flex;align-items:center;gap:10px;padding:10px 0;border-top:1px solid #f0e8e8">
                    <div style="width:36px;height:36px;border-radius:50%;background:#9b1c31;color:#fff;display:flex;align-items:center;justify-content:center;font-size:.72rem;font-weight:800;flex-shrink:0">
                        {{ strtoupper(substr($attendance->user->name ?? 'U', 0, 2)) }}
                    </div>
                    <div style="min-width:0;flex:1">
                        <div style="font-size:.83rem;font-weight:800;color:#171717;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">{{ $attendance->user->name ?? 'Unknown student' }}</div>
                        <div style="font-size:.7rem;color:#888">{{ $attendance->user->department ?? 'No department' }}</div>
                        <div style="font-size:.68rem;color:#aaa">Requested {{ optional($attendance->requested_at)->format('M d, Y h:i A') }}</div>
                    </div>
                </div>
                @if($attendance->status === 'confirmed')
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
                        <span class="badge badge-green">Attended</span>
                        <span style="font-size:.72rem;font-weight:700;color:#38a169">+{{ $attendance->points_awarded }} pts</span>
                    </div>
                @elseif($attendance->status === 'rejected')
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
                        <span class="badge badge-red">Not attended</span>
                        <span style="font-size:.72rem;font-weight:700;color:#e53e3e">0 pts</span>
                    </div>
                @else
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:8px">
                        <form method="POST" action="{{ route('admin.events.attendances.confirm', [$event, $attendance]) }}">
                            @csrf
                            <input type="hidden" name="decision" value="attended">
                            <button type="submit" title="Attended - award 20 points" style="width:100%;height:36px;border:none;border-radius:8px;background:#38a169;color:#fff;font-size:1rem;font-weight:900;cursor:pointer">
                                &#10003;
                            </button>
                        </form>
                        <form method="POST" action="{{ route('admin.events.attendances.confirm', [$event, $attendance]) }}">
                            @csrf
                            <input type="hidden" name="decision" value="not_attended">
                            <button type="submit" title="Not attended - no points" style="width:100%;height:36px;border:none;border-radius:8px;background:#e53e3e;color:#fff;font-size:1rem;font-weight:900;cursor:pointer">
                                X
                            </button>
                        </form>
                    </div>
                @endif
            @empty
                <div style="padding:18px 0;border-top:1px solid #f0e8e8;color:#999;font-size:.82rem;text-align:center">
                    No attendance requests yet.
                </div>
            @endforelse
        </div>
        </div>
    </div>

@endsection
