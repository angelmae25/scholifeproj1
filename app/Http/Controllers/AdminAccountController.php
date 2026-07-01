<?php
namespace App\Http\Controllers;

use App\Models\Admin;
use App\Helpers\LogActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class AdminAccountController extends Controller
{
    private const MODULE_PERMISSIONS = [
        'dashboard', 'analytics', 'users', 'announcements',
        'events', 'organizations', 'admin-accounts',
        'reports', 'academic-notices', 'points', 'logs',
    ];

    public function index()
    {
        abort_unless($this->currentAdminCanManageAdmins(), 403);

        $stats = [
            'total'       => Admin::count(),
            'active'      => Admin::where('status','active')->count(),
            'deactivated' => Admin::where('status','inactive')->count(),
        ];
        $admins = Admin::latest()->paginate(20);
        return view('Admin.admin-accounts.index', compact('stats','admins'));
    }

    public function store(Request $request)
    {
        abort_unless($this->currentAdminCanManageAdmins(), 403);

        $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'email'         => ['required', 'email', 'max:255', 'unique:admins,email'],
            'password'      => ['required', 'string', 'min:8', 'confirmed'],
            'role'          => ['required', Rule::in(['admin', 'moderator', 'super_admin'])],
            'student_id'    => ['nullable', 'string', 'max:50'],
            'avatar'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'permissions'   => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in(self::MODULE_PERMISSIONS)],
        ]);

        $permissions = $this->permissionsForRole($request->role, $request->permissions ?? []);
        $avatarPath = $request->hasFile('avatar')
            ? $request->file('avatar')->store('admins', 'public')
            : null;

        DB::table('admins')->insert([
            'name'        => $request->name,
            'email'       => $request->email,
            'password'    => Hash::make($request->password),
            'role'        => $request->role,
            'student_id'  => $request->student_id,
            'avatar'      => $avatarPath,
            'status'      => 'active',
            'permissions' => json_encode($permissions),
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        LogActivity::log('CREATE', 'Admin Accounts', 'Created admin: ' . $request->name);

        return redirect()->route('admin.admin-accounts')
            ->with('success', 'Admin account created successfully!');
    }

    public function show(Admin $admin)
    {
        $this->authorizeProfileAccess($admin);
        return view('Admin.admin-accounts.show', compact('admin'));
    }

    public function update(Request $request, Admin $admin)
    {
        $this->authorizeProfileAccess($admin);
        $canManageAdmins = $this->currentAdminCanManageAdmins();

        $rules = [
            'name'          => ['required', 'string', 'max:255'],
            'email'         => ['required', 'email', 'max:255', Rule::unique('admins', 'email')->ignore($admin->id)],
            'password'      => ['nullable', 'string', 'min:8', 'confirmed'],
            'student_id'    => ['nullable', 'string', 'max:50'],
            'avatar'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];

        if ($canManageAdmins) {
            $rules['role'] = ['required', Rule::in(['admin', 'moderator', 'super_admin'])];
            $rules['permissions'] = ['nullable', 'array'];
            $rules['permissions.*'] = ['string', Rule::in(self::MODULE_PERMISSIONS)];
        }

        $request->validate($rules);

        $avatarPath = $admin->avatar;
        if ($request->hasFile('avatar')) {
            if ($avatarPath) {
                Storage::disk('public')->delete($avatarPath);
            }
            $avatarPath = $request->file('avatar')->store('admins', 'public');
        }

        $data = [
            'name'        => $request->name,
            'email'       => $request->email,
            'student_id'  => $request->student_id,
            'avatar'      => $avatarPath,
            'updated_at'  => now(),
        ];

        if ($canManageAdmins) {
            $role = $request->role;
            $data['role'] = $role;
            $data['permissions'] = json_encode($this->permissionsForRole($role, $request->permissions ?? []));
        }

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        DB::table('admins')->where('id', $admin->id)->update($data);

        LogActivity::log('UPDATE', 'Admin Accounts', 'Updated admin profile: ' . $request->name);

        return redirect()->route('admin.admin-accounts.show', $admin)
            ->with('success', 'Admin profile updated successfully!');
    }

    public function toggle(Admin $admin)
    {
        abort_unless($this->currentAdminCanManageAdmins(), 403);

        if ($admin->id === Auth::guard('admin')->id()) {
            return back()->with('error', 'You cannot disable your own account!');
        }

        $newStatus = $admin->status === 'active' ? 'inactive' : 'active';

        DB::table('admins')
            ->where('id', $admin->id)
            ->update(['status' => $newStatus, 'updated_at' => now()]);

        LogActivity::log(
            'UPDATE',
            'Admin Accounts',
            ($newStatus === 'active' ? 'Enabled' : 'Disabled') . ' admin: ' . $admin->name
        );

        return back()->with('success', 'Admin status updated!');
    }

    private function authorizeProfileAccess(Admin $admin): void
    {
        $currentAdmin = Auth::guard('admin')->user();
        abort_unless(
            $currentAdmin && ($currentAdmin->id === $admin->id || $currentAdmin->hasPermission('admin-accounts')),
            403,
            'You do not have permission to access this admin profile.'
        );
    }

    private function currentAdminCanManageAdmins(): bool
    {
        return optional(Auth::guard('admin')->user())->role === 'super_admin';
    }

    private function permissionsForRole(string $role, array $permissions): array
    {
        return $role === 'super_admin' ? self::MODULE_PERMISSIONS : $permissions;
    }
}



