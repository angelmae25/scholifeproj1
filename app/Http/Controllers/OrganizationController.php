<?php
namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\User;
use App\Helpers\LogActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrganizationController extends Controller
{
    public function index()
    {
        $stats = [
            'total'             => Organization::count(),
            'active_this_month' => Organization::where('status','active')->count(),
            'pending'           => Organization::where('status','pending')->count(),
        ];
        $organizations = Organization::latest()->paginate(20);
        return view('Admin.organizations.index', compact('stats','organizations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string',
        ]);

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('organizations', 'public');
        }

        Organization::create([
            'name'         => $request->name,
            'acronym'      => $request->acronym,
            'description'  => $request->description,
            'logo'         => $logoPath,
            'type'         => $request->type,
            'status'       => 'pending',
            'adviser'      => $request->adviser,
            'co_adviser'   => $request->co_adviser,
            'department'   => $request->department,
            'year_founded' => $request->year_founded,
            'member_count' => 0,
        ]);

        LogActivity::log('CREATE', 'Organizations', 'Created organization: ' . $request->name);

        return redirect()->route('admin.organizations')
            ->with('success', 'Organization created successfully!');
    }

    public function show(Organization $organization)
    {
        $users     = User::whereIn('role',['student','org_officer'])->get();
        $officials = OrganizationMember::with('user')
            ->where('organization_id', $organization->id)
            ->get();

        return view('Admin.organizations.show', compact('organization','users','officials'));
    }

    public function destroy(Organization $organization)
    {
        $name = $organization->name;
        $organization->delete();
        LogActivity::log('DELETE', 'Organizations', 'Deleted organization: ' . $name);
        return redirect()->route('admin.organizations')
            ->with('success', 'Organization deleted successfully!');
    }

    public function updateStatus(Request $request, Organization $organization)
    {
        $organization->update(['status' => $request->status]);
        return back()->with('success', 'Status updated!');
    }

    public function assign(Request $request, Organization $organization)
    {
        $request->validate([
            'user_id' => 'required',
            'role'    => 'required',
        ]);

        OrganizationMember::updateOrCreate(
            [
                'organization_id' => $organization->id,
                'position'        => $request->role,
            ],
            [
                'user_id' => $request->user_id,
                'status'  => 'active',
            ]
        );

        $organization->increment('member_count');

        return back()->with('success', 'Official assigned successfully!');
    }

    public function unassign(Organization $organization, OrganizationMember $member)
    {
        $member->delete();
        $organization->decrement('member_count');
        return back()->with('success', 'Official removed!');
    }
}
