<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Event;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = $request->user();

        $announcements = Announcement::where('status','live')
            ->latest()->limit(5)->get();

        $events = Event::where('status','upcoming')
            ->orderBy('event_date')->limit(5)->get();

        return response()->json([
            'success'       => true,
            'user'          => $user,
            'announcements' => $announcements,
            'events'        => $events,
        ]);
    }
}
