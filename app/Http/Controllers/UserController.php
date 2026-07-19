<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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

    public function store(Request $request) {
        $validated = $request->validate([
            'role' => ['required', Rule::in(['student', 'professor'])],
            'name' => ['required', 'string', 'max:255'],
            'department' => ['required', Rule::in(['BASD', 'MAAD', 'EAAD', 'CAAD'])],
            'student_id' => ['required', 'string', 'max:255', 'unique:users,student_id'],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', 'string', 'max:50'],
            'phone_number' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:255'],
            'year_level' => ['nullable', 'required_if:role,student', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        if ($validated['role'] === 'professor') {
            if (! preg_match('/^[A-Za-z]{3}-\d{4}-\d+$/', $validated['student_id'])) {
                return back()
                    ->withErrors(['student_id' => 'Teacher ID must look like INC-2026-123.'])
                    ->withInput();
            }

            $validated['year_level'] = null;
        }

        $validated['status'] = 'active';
        $validated['points'] = 0;

        User::create($validated);

        return redirect()->route('admin.users')->with('success', 'User account created successfully.');
    }

    public function destroy(User $user) {
        $user->delete();

        return redirect()->route('admin.users')->with('success', 'User account removed successfully.');
    }
}
