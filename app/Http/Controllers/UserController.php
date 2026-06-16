<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller {
    public function index(Request $request) {
        $stats = [
            'total'         => User::count(),
            'active'        => User::where('status','active')->count(),
            'deactivated'   => User::where('status','deactivated')->count(),
            'new_this_week' => User::whereBetween('created_at',[now()->startOfWeek(),now()])->count(),
        ];
        $query = User::query();
        if ($request->role && $request->role !== 'all') $query->where('role', $request->role);
        $users = $query->latest()->paginate(20);
        return view('admin.users.index', compact('stats','users'));
    }
}
