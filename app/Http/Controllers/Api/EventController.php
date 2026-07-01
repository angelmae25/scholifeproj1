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
        $event = Event::whereIn('status', ['upcoming', 'ongoing'])->findOrFail($id);
        return response()->json(['success' => true, 'data' => $event]);
    }

    public function rsvp(Request $request, $id)
    {
        $userId = $request->user()->id;

        $result = DB::transaction(function () use ($id, $userId) {
            $event = Event::whereIn('status', ['upcoming', 'ongoing'])
                ->whereKey($id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($event->rsvp_deadline && now()->greaterThan($event->rsvp_deadline)) {
                return ['success' => false, 'message' => 'RSVP deadline has passed', 'status' => 422];
            }

            if ($event->max_attendees && $event->rsvp_count >= $event->max_attendees) {
                return ['success' => false, 'message' => 'Event is full', 'status' => 422];
            }

            $exists = DB::table('event_attendees')
                ->where('event_id', $id)
                ->where('user_id', $userId)
                ->exists();

            if ($exists) {
                return ['success' => false, 'message' => 'Already RSVPed', 'status' => 409];
            }

            DB::table('event_attendees')->insert([
                'event_id'   => $id,
                'user_id'    => $userId,
                'status'     => 'rsvp',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $event->increment('rsvp_count');

            return ['success' => true, 'message' => 'RSVP successful', 'status' => 200];
        });

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
        ], $result['status']);
    }
}
