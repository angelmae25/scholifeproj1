<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;

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
}
