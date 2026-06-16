<?php
namespace App\Http\Controllers;

use App\Models\AcademicNotice;
use App\Helpers\LogActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AcademicNoticeController extends Controller
{
    public function index()
    {
        $stats = [
            'posted'           => AcademicNotice::where('status','published')->count(),
            'dept_active'      => AcademicNotice::distinct('department')->count('department'),
            'pending_approval' => AcademicNotice::where('status','pending')->count(),
        ];
        $notices = AcademicNotice::latest()->paginate(20);
        return view('Admin.academic-notices.index', compact('stats','notices'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title'      => 'required|string|max:255',
            'type'       => 'required|string',
            'department' => 'required|string',
            'content'    => 'required|string',
        ]);

        $status = $request->action === 'draft' ? 'draft' : 'pending';

        // Handle attachment
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')
                ->store('academic-notices', 'public');
        }

        AcademicNotice::create([
            'title'        => $request->title,
            'content'      => $request->content,
            'posted_by'    => Auth::guard('admin')->user()->name,
            'department'   => $request->department,
            'type'         => $request->type,
            'status'       => $status,
            'attachment'   => $attachmentPath,
            'published_at' => $status === 'published' ? now() : null,
            'admin_id'     => Auth::guard('admin')->id(),
        ]);

        LogActivity::log('CREATE', 'Academic Notices', 'Posted notice: ' . $request->title);

        return redirect()->route('admin.academic-notices')
            ->with('success', 'Notice ' . $status . ' successfully!');
    }

    public function show(AcademicNotice $academicNotice)
    {
        return view('Admin.academic-notices.show', compact('academicNotice'));
    }

    public function destroy(AcademicNotice $academicNotice)
    {
        $title = $academicNotice->title;
        $academicNotice->delete();
        LogActivity::log('DELETE', 'Academic Notices', 'Deleted notice: ' . $title);
        return redirect()->route('admin.academic-notices')
            ->with('success', 'Notice deleted successfully!');
    }
}
