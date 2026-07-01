<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AdminLoginController extends Controller {
    public function showLoginForm() {
        if (Auth::guard('admin')->check()) return redirect()->route($this->firstAllowedRoute());
        return view('auth.admin-login');
    }

    public function login(Request $request) {
        $request->validate(['email' => 'required|email', 'password' => 'required']);

        if (Auth::guard('admin')->attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            if (Auth::guard('admin')->user()->status !== 'active') {
                Auth::guard('admin')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                throw ValidationException::withMessages([
                    'email' => 'This admin account is inactive.',
                ]);
            }

            $request->session()->regenerate();
            Auth::guard('admin')->user()->update(['last_login_at' => now()]);
            return redirect()->route($this->firstAllowedRoute());
        }

        throw ValidationException::withMessages(['email' => 'These credentials do not match our records.']);
    }

    public function logout(Request $request) {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }

    private function firstAllowedRoute(): string
    {
        $admin = Auth::guard('admin')->user();

        foreach ([
            'dashboard' => 'admin.dashboard',
            'analytics' => 'admin.analytics',
            'users' => 'admin.users',
            'announcements' => 'admin.announcements',
            'events' => 'admin.events',
            'organizations' => 'admin.organizations',
            'admin-accounts' => 'admin.admin-accounts',
            'reports' => 'admin.reports',
            'academic-notices' => 'admin.academic-notices',
            'points' => 'admin.points',
            'logs' => 'admin.logs',
        ] as $permission => $route) {
            if ($admin->hasPermission($permission)) {
                return $route;
            }
        }

        Auth::guard('admin')->logout();

        throw ValidationException::withMessages([
            'email' => 'This admin account does not have any module permissions.',
        ]);
    }
}
