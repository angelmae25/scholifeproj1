<?php
namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Helpers\LogActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AnnouncementController extends Controller
{
    public function index()
    {
        $stats = [
            'published'   => Announcement::where('status', 'live')->count(),
            'total_views' => Announcement::sum('views'),
        ];

        $announcements = Announcement::latest()->paginate(20);

        return view('Admin.announcements.index', compact('stats', 'announcements'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'content'      => ['required', 'string', 'max:5000'],
            'audience'     => ['required', Rule::in(['All Users', 'Students', 'Professors', 'Offices', 'Org Officers', 'BASD', 'MAAD', 'CAAD', 'EAAD', 'Others'])],
            'category'     => ['nullable', Rule::in(['General', 'Academic', 'Event', 'Emergency', 'Scholarship'])],
            'action'       => ['nullable', Rule::in(['draft', 'publish'])],
            'attachment'   => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png', 'max:10240'],
        ]);

        $status = $request->action === 'draft' ? 'draft' : 'live';

        // Handle file upload
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('announcements', 'public');
        }

        // Save to database
        Announcement::create([
            'title'        => $request->title,
            'content'      => $request->content,
            'author'       => Auth::guard('admin')->user()->name,
            'audience'     => $request->audience,
            'status'       => $status,
            'views'        => 0,
            'attachment'   => $attachmentPath,
            'published_at' => $status === 'live' ? now() : null,
            'admin_id'     => Auth::guard('admin')->id(),
        ]);

        // Log the activity
        LogActivity::log(
            'CREATE',
            'Announcements',
            'Created announcement: ' . $request->title . ' [' . $status . ']'
        );

        return redirect()->route('admin.announcements')
            ->with('success', 'Announcement saved as ' . $status . '!');
    }
    public function show(Announcement $announcement)
    {
        return view('Admin.announcements.show', compact('announcement'));
    }
    public function destroy(Announcement $announcement)
    {
        $title = $announcement->title;
        $announcement->delete();

        LogActivity::log('DELETE', 'Announcements', 'Deleted announcement: ' . $title);

        return redirect()->route('admin.announcements')
            ->with('success', 'Announcement deleted successfully!');
    }
}
