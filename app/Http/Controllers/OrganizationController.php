<?php
namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\OrganizationEvaluation;
use App\Models\User;
use App\Helpers\LogActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class OrganizationController extends Controller
{
    private const OFFICER_ROLES = [
        'president',
        'vice_president_internal',
        'vice_president_external',
        'secretary',
        'treasurer',
        'auditor',
        'pio',
    ];

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
            'name'         => ['required', 'string', 'max:255'],
            'acronym'      => ['nullable', 'string', 'max:50'],
            'description'  => ['nullable', 'string', 'max:5000'],
            'logo'         => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'type'         => ['required', Rule::in(['academic', 'civic', 'cultural', 'sports', 'governance', 'other'])],
            'adviser'      => ['nullable', 'string', 'max:255'],
            'co_adviser'   => ['nullable', 'string', 'max:255'],
            'department'   => ['nullable', 'string', 'max:255'],
            'year_founded' => ['nullable', 'integer', 'min:1900', 'max:' . date('Y')],
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
        $users     = User::whereIn('role',['student','org_officer'])
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
        $officials = OrganizationMember::with('user')
            ->where('organization_id', $organization->id)
            ->where('status', 'active')
            ->get();

        $evaluations = OrganizationEvaluation::with('answers')
            ->where('organization_id', $organization->id)
            ->latest()
            ->get();

        $evaluationStats = [
            'total' => $evaluations->count(),
            'average_rating' => round((float) $evaluations->avg('rating'), 1),
            'recommend_yes' => $evaluations->filter(fn ($evaluation) => strtolower((string) $evaluation->would_recommend) === 'yes')->count(),
        ];

        return view('Admin.organizations.show', compact('organization','users','officials','evaluations','evaluationStats'));
    }

    public function update(Request $request, Organization $organization)
    {
        $request->validate([
            'name'         => ['required', 'string', 'max:255'],
            'acronym'      => ['nullable', 'string', 'max:50'],
            'description'  => ['nullable', 'string', 'max:5000'],
            'logo'         => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'type'         => ['required', Rule::in(['academic', 'civic', 'cultural', 'sports', 'governance', 'other'])],
            'adviser'      => ['nullable', 'string', 'max:255'],
            'co_adviser'   => ['nullable', 'string', 'max:255'],
            'department'   => ['nullable', 'string', 'max:255'],
            'year_founded' => ['nullable', 'integer', 'min:1900', 'max:' . date('Y')],
            'status'       => ['required', Rule::in(['active', 'inactive', 'pending'])],
        ]);

        $logoPath = $organization->logo;

        if ($request->hasFile('logo')) {
            if ($logoPath) {
                Storage::disk('public')->delete($logoPath);
            }

            $logoPath = $request->file('logo')->store('organizations', 'public');
        }

        $organization->update([
            'name'         => $request->name,
            'acronym'      => $request->acronym,
            'description'  => $request->description,
            'logo'         => $logoPath,
            'type'         => $request->type,
            'status'       => $request->status,
            'adviser'      => $request->adviser,
            'co_adviser'   => $request->co_adviser,
            'department'   => $request->department,
            'year_founded' => $request->year_founded,
        ]);

        LogActivity::log('UPDATE', 'Organizations', 'Updated organization: ' . $organization->name);

        return redirect()->route('admin.organizations')
            ->with('success', 'Organization updated successfully!');
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
        $request->validate([
            'status' => ['required', Rule::in(['active', 'inactive', 'pending'])],
        ]);

        $organization->update(['status' => $request->status]);
        LogActivity::log('UPDATE', 'Organizations', 'Updated organization status: ' . $organization->name);

        return back()->with('success', 'Status updated!');
    }

    public function assign(Request $request, Organization $organization)
    {
        $request->validate([
            'user_id' => ['required', 'integer', Rule::exists('users', 'id')->where(fn ($query) => $query->whereIn('role', ['student', 'org_officer']))],
            'role'    => ['required', Rule::in(self::OFFICER_ROLES)],
        ]);

        $member = DB::transaction(function () use ($request, $organization) {
            OrganizationMember::where('organization_id', $organization->id)
                ->where(function ($query) use ($request) {
                    $query->where('position', $request->role)
                        ->orWhere('user_id', $request->user_id);
                })
                ->delete();

            $member = OrganizationMember::create([
                'organization_id' => $organization->id,
                'user_id' => $request->user_id,
                'position' => $request->role,
                'status' => 'active',
            ]);

            $organization->member_count = OrganizationMember::where('organization_id', $organization->id)
                ->where('status', 'active')
                ->count();
            $organization->save();

            return $member;
        });

        LogActivity::log('UPDATE', 'Organizations', 'Assigned ' . $member->position . ' for: ' . $organization->name);

        return back()->with('success', 'Official assigned successfully!');
    }

    public function unassign(Organization $organization, OrganizationMember $member)
    {
        abort_unless($member->organization_id === $organization->id, 404);

        $member->delete();
        $organization->member_count = OrganizationMember::where('organization_id', $organization->id)
            ->where('status', 'active')
            ->count();
        $organization->save();

        LogActivity::log('UPDATE', 'Organizations', 'Removed official from: ' . $organization->name);

        return back()->with('success', 'Official removed!');
    }
}

