<?php
namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventAttendance;
use App\Models\PointRule;
use App\Models\PointTransaction;
use App\Helpers\LogActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class EventController extends Controller
{
    public function index()
    {
        $stats = [
            'total_events'     => Event::count(),
            'total_attendance' => EventAttendance::where('status', 'confirmed')->count(),
            'pending_requests' => EventAttendance::where('status', 'pending')->count(),
        ];

        $events = Event::withCount([
                'attendances as confirmed_attendance_count' => fn ($query) => $query->where('status', 'confirmed'),
                'attendances as pending_attendance_count' => fn ($query) => $query->where('status', 'pending'),
            ])
            ->latest()
            ->paginate(20)
            ->through(function ($event) {
                $event->attendance_count = $event->confirmed_attendance_count;
                return $event;
            });

        return view('Admin.events.index', compact('stats', 'events'));
    }

    public function attendanceNotifications()
    {
        $pending = EventAttendance::with('user:id,name,email,department,student_id', 'event:id,title,location,event_date')
            ->where('status', 'pending')
            ->latest('requested_at')
            ->take(8)
            ->get()
            ->map(function ($attendance) {
                return [
                    'id' => $attendance->id,
                    'student_name' => $attendance->user?->name ?? 'Unknown student',
                    'student_id' => $attendance->user?->student_id,
                    'event_id' => $attendance->event_id,
                    'event_title' => $attendance->event?->title ?? 'Unknown event',
                    'event_location' => $attendance->event?->location,
                    'requested_at' => optional($attendance->requested_at)->diffForHumans(),
                    'manage_url' => route('admin.events.show', $attendance->event_id),
                ];
            });

        return response()->json([
            'success' => true,
            'count' => EventAttendance::where('status', 'pending')->count(),
            'attendances' => $pending,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'          => ['required', 'string', 'max:255'],
            'organizer'      => ['required', 'string', 'max:255'],
            'event_date'     => ['required', 'date'],
            'event_time'     => ['nullable', 'date_format:H:i'],
            'end_date'       => ['nullable', 'date', 'after_or_equal:event_date'],
            'end_time'       => ['nullable', 'date_format:H:i'],
            'location'       => ['nullable', 'string', 'max:255'],
            'description'    => ['nullable', 'string', 'max:5000'],
            'type'           => ['required', Rule::in(['on_campus', 'academic', 'organization', 'online'])],
            'audience'       => ['nullable', Rule::in(['All', 'BASD', 'MAAD', 'CAAD', 'EAAD'])],
            'points_awarded' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'image'          => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('events', 'public');
        }

        Event::create([
            'title'            => $validated['title'],
            'description'      => $validated['description'] ?? null,
            'organizer'        => $validated['organizer'],
            'event_date'       => $validated['event_date'],
            'event_time'       => $validated['event_time'] ?? null,
            'end_date'         => $validated['end_date'] ?? null,
            'end_time'         => $validated['end_time'] ?? null,
            'location'         => $validated['location'] ?? null,
            'type'             => $validated['type'],
            'audience'         => $this->normalizeAudience($request->input('audience')),
            'status'           => 'upcoming',
            'attendance_count' => 0,
            'reminders_sent'   => 0,
            'points_awarded'   => $validated['points_awarded'] ?? 0,
            'remind_1day'      => $request->has('remind_1day')  ? 1 : 0,
            'remind_1hour'     => $request->has('remind_1hour') ? 1 : 0,
            'image'            => $imagePath,
            'admin_id'         => Auth::guard('admin')->id(),
            'organization_id'  => null,
            'user_id'          => null,
            'created_by_type'  => 'admin',
        ]);

        LogActivity::log(
            'CREATE',
            'Events',
            'Created event: ' . $validated['title']
        );

        return redirect()->route('admin.events')
            ->with('success', 'Event created successfully!');
    }

    public function update(Request $request, Event $event)
    {
        $validated = $request->validate([
            'title'          => ['required', 'string', 'max:255'],
            'organizer'      => ['required', 'string', 'max:255'],
            'event_date'     => ['required', 'date'],
            'event_time'     => ['nullable', 'date_format:H:i'],
            'end_date'       => ['nullable', 'date', 'after_or_equal:event_date'],
            'end_time'       => ['nullable', 'date_format:H:i'],
            'location'       => ['nullable', 'string', 'max:255'],
            'description'    => ['nullable', 'string', 'max:5000'],
            'type'           => ['required', Rule::in(['on_campus', 'academic', 'organization', 'online'])],
            'audience'       => ['nullable', Rule::in(['All', 'BASD', 'MAAD', 'CAAD', 'EAAD'])],
            'status'         => ['nullable', Rule::in(['upcoming', 'ongoing', 'completed', 'cancelled'])],
            'points_awarded' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'image'          => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        if ($request->hasFile('image')) {
            if ($event->image) {
                Storage::disk('public')->delete($event->image);
            }
            $validated['image'] = $request->file('image')->store('events', 'public');
        }

        $event->update([
            'title'          => $validated['title'],
            'description'    => $validated['description'] ?? null,
            'organizer'      => $validated['organizer'],
            'event_date'     => $validated['event_date'],
            'event_time'     => $validated['event_time'] ?? null,
            'end_date'       => $validated['end_date'] ?? null,
            'end_time'       => $validated['end_time'] ?? null,
            'location'       => $validated['location'] ?? null,
            'type'           => $validated['type'],
            'audience'       => $this->normalizeAudience($request->input('audience')),
            'status'         => $validated['status'] ?? $event->status,
            'points_awarded' => $validated['points_awarded'] ?? 0,
            'remind_1day'    => $request->has('remind_1day') ? 1 : 0,
            'remind_1hour'   => $request->has('remind_1hour') ? 1 : 0,
            'image'          => $validated['image'] ?? $event->image,
        ]);

        LogActivity::log('UPDATE', 'Events', 'Updated event: ' . $validated['title']);

        return redirect()->route('admin.events')
            ->with('success', 'Event "' . $validated['title'] . '" updated successfully!');
    }

    public function show(Event $event)
    {
        $this->syncAttendanceCount($event);
        $event->refresh();

        $attendances = EventAttendance::with('user:id,name,email,department,student_id')
            ->where('event_id', $event->id)
            ->latest('requested_at')
            ->get();

        return view('Admin.events.show', compact('event', 'attendances'));
    }

    public function confirmAttendance(Request $request, Event $event, EventAttendance $attendance)
    {
        if ($attendance->event_id !== $event->id) {
            abort(404);
        }

        if (in_array($attendance->status, ['confirmed', 'rejected'], true)) {
            return back()->with('success', 'Attendance already checked.');
        }

        $decision = $request->input('decision', 'attended');

        if ($decision === 'not_attended') {
            $attendance->update([
                'status' => 'rejected',
                'confirmed_at' => now(),
                'confirmed_by' => Auth::guard('admin')->id(),
                'points_awarded' => 0,
            ]);

            LogActivity::log('UPDATE', 'Events', 'Marked not attended for event: ' . $event->title);

            return back()->with('success', 'Student marked as not attended. No points awarded.');
        }

        DB::transaction(function () use ($event, $attendance) {
            $rule = PointRule::where('trigger', 'event_attendance')
                ->where('is_active', true)
                ->first();
            $points = 20;

            $attendance->update([
                'status' => 'confirmed',
                'confirmed_at' => now(),
                'confirmed_by' => Auth::guard('admin')->id(),
                'points_awarded' => $points,
            ]);

            $attendance->user()->increment('points', $points);
            $event->increment('attendance_count');

            PointTransaction::create([
                'user_id' => $attendance->user_id,
                'point_rule_id' => $rule?->id,
                'points' => $points,
                'reason' => 'Confirmed attendance: ' . $event->title,
                'is_reward_claim' => false,
            ]);
        });

        LogActivity::log('UPDATE', 'Events', 'Confirmed attendance for event: ' . $event->title);

        return back()->with('success', 'Attendance confirmed and 20 points awarded.');
    }
    public function destroy(Event $event)
    {
        $title = $event->title;
        if ($event->image) {
            Storage::disk('public')->delete($event->image);
        }
        $event->delete();

        LogActivity::log('DELETE', 'Events', 'Deleted event: ' . $title);

        return redirect()->route('admin.events')
            ->with('success', 'Event deleted successfully!');
    }

    private function normalizeAudience(?string $audience): ?string
    {
        $value = trim((string) $audience);

        if ($value === '' || strcasecmp($value, 'All') === 0 || strcasecmp($value, 'All Users') === 0) {
            return null;
        }

        $allowed = ['BASD', 'MAAD', 'CAAD', 'EAAD'];
        foreach ($allowed as $option) {
            if (strcasecmp($value, $option) === 0) {
                return $option;
            }
        }

        return null;
    }
    private function syncAttendanceCount(Event $event): void
    {
        $confirmedCount = EventAttendance::where('event_id', $event->id)
            ->where('status', 'confirmed')
            ->count();

        if ((int) $event->attendance_count !== $confirmedCount) {
            $event->forceFill(['attendance_count' => $confirmedCount])->save();
        }
    }
}
