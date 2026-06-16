<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;

class AnnouncementController extends Controller
{
    public function index()
    {
        $announcements = Announcement::where('status','live')
            ->latest()
            ->paginate(15);

        return response()->json(['success' => true, 'data' => $announcements]);
    }

    public function show($id)
    {
        $announcement = Announcement::where('status','live')->findOrFail($id);
        $announcement->increment('views');
        return response()->json(['success' => true, 'data' => $announcement]);
    }
}
