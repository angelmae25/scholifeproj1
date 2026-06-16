<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EventController extends Controller
{
    public function index()
    {
        $events = Event::whereIn('status',['upcoming','ongoing'])
            ->orderBy('event_date')
            ->paginate(15);

        return response()->json(['success' => true, 'data' => $events]);
    }

    public function show($id)
    {
        $event = Event::findOrFail($id);
        return response()->json(['success' => true, 'data' => $event]);
    }

    public function rsvp(Request $request, $id)
    {
        $event = Event::findOrFail($id);
        $userId = $request->user()->id;

        $exists = DB::table('event_attendees')
            ->where('event_id', $id)
            ->where('user_id', $userId)
            ->exists();

        if ($exists) {
            return response()->json(['success' => false, 'message' => 'Already RSVPed']);
        }

        DB::table('event_attendees')->insert([
            'event_id'   => $id,
            'user_id'    => $userId,
            'status'     => 'rsvp',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $event->increment('rsvp_count');

        return response()->json(['success' => true, 'message' => 'RSVP successful']);
    }
}
