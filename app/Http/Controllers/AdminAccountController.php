<?php
namespace App\Http\Controllers;

use App\Models\Admin;
use App\Helpers\LogActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdminAccountController extends Controller
{
    public function index()
    {
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
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:admins,email',
            'password' => 'required|string|min:6|confirmed',
            'role'     => 'required|string',
        ]);

        $permissions = $request->permissions ?? [];

        if ($request->role === 'super_admin') {
            $permissions = [
                'dashboard','analytics','users','announcements',
                'events','organizations','admin-accounts',
                'reports','academic-notices','points','logs'
            ];
        }

        DB::table('admins')->insert([
            'name'        => $request->name,
            'email'       => $request->email,
            'password'    => Hash::make($request->password),
            'role'        => $request->role,
            'student_id'  => $request->student_id,
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
        return view('Admin.admin-accounts.show', compact('admin'));
    }

    public function toggle(Admin $admin)
    {
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
}
