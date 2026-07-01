<?php
namespace App\Http\Controllers;

use App\Models\Event;
use App\Helpers\LogActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class EventController extends Controller
{
    public function index()
    {
        $stats = [
            'total_events'   => Event::count(),
            'total_rsvps'    => Event::sum('rsvp_count'),
            'avg_attendance' => Event::sum('rsvp_count') > 0
                ? round((Event::sum('attendance_count') / Event::sum('rsvp_count')) * 100) . '%'
                : '0%',
            'reminders_sent' => Event::sum('reminders_sent'),
        ];

        $events = Event::latest()->paginate(20);

        return view('Admin.events.index', compact('stats', 'events'));
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
            'max_attendees'  => ['nullable', 'integer', 'min:1', 'max:100000'],
            'rsvp_deadline'  => ['nullable', 'date', 'before_or_equal:event_date'],
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
            'status'           => 'upcoming',
            'rsvp_count'       => 0,
            'attendance_count' => 0,
            'reminders_sent'   => 0,
            'max_attendees'    => $validated['max_attendees'] ?? null,
            'rsvp_deadline'    => $validated['rsvp_deadline'] ?? null,
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

    public function show(Event $event)
    {
        return view('Admin.events.show', compact('event'));
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
}
