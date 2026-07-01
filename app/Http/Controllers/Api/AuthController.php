<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\MobileLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|max:255|unique:users,email',
            'password'   => 'required|string|min:8|confirmed',
            'student_id' => 'nullable|string|max:50',
            'department' => 'nullable|string|max:255',
        ]);

        $user = User::create([
            'name'        => $request->name,
            'email'       => $request->email,
            'password'    => Hash::make($request->password),
            'student_id'  => $request->student_id,
            'department'  => $request->department,
            'role'        => 'student',
            'status'      => 'active',
            'last_active_at' => now(),
        ]);

        $token = $user->createToken('mobile-app')->plainTextToken;

        // Log to admin activity
        DB::table('mobile_logs')->insert([
            'user_id'     => $user->id,
            'action'      => 'REGISTER',
            'module'      => 'Auth',
            'description' => 'New user registered: ' . $user->name . ' (student)',
            'ip_address'  => $request->ip(),
            'device_info' => $request->header('User-Agent'),
            'created_at'  => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Registration successful',
            'token'   => $token,
            'user'    => $user,
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid credentials',
            ], 401);
        }

        if ($user->status === 'deactivated') {
            return response()->json([
                'success' => false,
                'message' => 'Your account has been deactivated.',
            ], 403);
        }

        $user->update(['last_active_at' => now()]);
        $token = $user->createToken('mobile-app')->plainTextToken;

        // Log login to admin
        DB::table('mobile_logs')->insert([
            'user_id'     => $user->id,
            'action'      => 'LOGIN',
            'module'      => 'Auth',
            'description' => $user->name . ' logged in from mobile app',
            'ip_address'  => $request->ip(),
            'device_info' => $request->header('User-Agent'),
            'created_at'  => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'token'   => $token,
            'user'    => $user,
        ]);
    }

    public function logout(Request $request)
    {
        DB::table('mobile_logs')->insert([
            'user_id'     => $request->user()->id,
            'action'      => 'LOGOUT',
            'module'      => 'Auth',
            'description' => $request->user()->name . ' logged out from mobile app',
            'ip_address'  => $request->ip(),
            'created_at'  => now(),
        ]);

        $request->user()->currentAccessToken()->delete();

        return response()->json(['success' => true, 'message' => 'Logged out']);
    }

    public function me(Request $request)
    {
        return response()->json(['success' => true, 'user' => $request->user()]);
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'name'       => 'sometimes|required|string|max:255',
            'department' => 'nullable|string|max:255',
            'student_id' => 'nullable|string|max:50',
        ]);

        $user = $request->user();
        $user->update($request->only('name','department','student_id'));
        return response()->json(['success' => true, 'user' => $user]);
    }

    public function saveDeviceToken(Request $request)
    {
        $request->validate([
            'token'    => 'required|string|max:500',
            'platform' => 'nullable|in:android,ios,web',
        ]);

        DB::table('device_tokens')->updateOrInsert(
            ['user_id' => $request->user()->id],
            [
                'token'      => $request->token,
                'platform'   => $request->platform ?? 'android',
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return response()->json(['success' => true]);
    }
}
