<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckAdminPermission
{
    public function handle(Request $request, Closure $next, string $module)
    {
        $admin = Auth::guard('admin')->user();

        if (!$admin || !$admin->hasPermission($module)) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'You do not have permission to access ' . $module . '.');
        }

        return $next($request);
    }
}
