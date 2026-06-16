<?php
namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        // Save using raw DB to bypass any model issues
        DB::table('events')->insert([
            'title'            => $request->title ?? 'Untitled',
            'description'      => $request->description,
            'organizer'        => $request->organizer ?? 'Admin',
            'event_date'       => $request->event_date ?? now()->format('Y-m-d'),
            'event_time'       => $request->event_time,
            'end_date'         => $request->end_date,
            'end_time'         => $request->end_time,
            'location'         => $request->location,
            'type'             => $request->type ?? 'on_campus',
            'status'           => 'upcoming',
            'rsvp_count'       => 0,
            'attendance_count' => 0,
            'reminders_sent'   => 0,
            'max_attendees'    => $request->max_attendees,
            'rsvp_deadline'    => $request->rsvp_deadline,
            'points_awarded'   => $request->points_awarded ?? 0,
            'remind_1day'      => $request->has('remind_1day')  ? 1 : 0,
            'remind_1hour'     => $request->has('remind_1hour') ? 1 : 0,
            'admin_id'         => Auth::guard('admin')->id(),
            'created_at'       => now(),
            'updated_at'       => now(),
        ]);

        return redirect()->route('admin.events')
            ->with('success', 'Event created successfully!');
    }

    public function show(Event $event)
    {
        return view('Admin.events.show', compact('event'));
    }

    public function destroy(Event $event)
    {
        $event->delete();
        return redirect()->route('admin.events')
            ->with('success', 'Event deleted successfully!');
    }
}
