<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AdminLoginController extends Controller {
    public function showLoginForm() {
        if (Auth::guard('admin')->check()) return redirect()->route('admin.dashboard');
        return view('auth.admin-login');
    }

    public function login(Request $request) {
        $request->validate(['email' => 'required|email', 'password' => 'required']);

        if (Auth::guard('admin')->attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            $request->session()->regenerate();
            Auth::guard('admin')->user()->update(['last_login_at' => now()]);
            return redirect()->route('admin.dashboard');
        }

        throw ValidationException::withMessages(['email' => 'These credentials do not match our records.']);
    }

    public function logout(Request $request) {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login');
    }
}
