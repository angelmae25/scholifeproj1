<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class MobileAuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'department' => ['required', Rule::in(['BASD', 'CAAD', 'EAAD', 'MAAD'])],
            'student_id' => 'nullable|string|max:255',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|string|max:50',
            'phone_number' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:255',
            'year_level' => 'nullable|string|max:50',
            'role' => ['required', Rule::in(['student', 'professor', 'teacher'])],
        ]);

        $role = $request->role === 'teacher' ? 'professor' : $request->role;

        if ($role === 'professor' && ! preg_match('/^[A-Za-z]{3}-\d{4}-\d+$/', (string) $request->student_id)) {
            return response()->json([
                'success' => false,
                'message' => 'Teacher ID must look like INC-2026-123.',
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'role' => $role,
            'department' => $request->department,
            'student_id' => $request->student_id,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'phone_number' => $request->phone_number,
            'address' => $request->address,
            'year_level' => $request->year_level,
            'points' => 0,
            'status' => 'active',
            'last_active_at' => now(),
        ]);

        ActivityLog::create([
            'admin_id' => null,
            'action' => 'REGISTER',
            'module' => 'Mobile App',
            'description' => $user->name . ' registered using the mobile app',
            'ip_address' => $request->ip(),
        ]);

        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Registration successful',
            'token' => $token,
            'user' => $user,
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password',
            ], 401);
        }

        $user->update([
            'last_active_at' => now(),
        ]);

        ActivityLog::create([
            'admin_id' => null,
            'action' => 'LOGIN',
            'module' => 'Mobile App',
            'description' => $user->name . ' logged in using the mobile app',
            'ip_address' => $request->ip(),
        ]);

        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'token' => $token,
            'user' => $user,
        ]);
    }
}

