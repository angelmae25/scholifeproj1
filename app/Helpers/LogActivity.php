<?php
namespace App\Helpers;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class LogActivity {
    public static function log(string $action, string $module, string $description): void {
        try {
            ActivityLog::create([
                'admin_id'    => Auth::guard('admin')->id(),
                'action'      => $action,
                'module'      => $module,
                'description' => $description,
                'ip_address'  => request()->ip(),
            ]);
        } catch (\Exception $e) {
            // Fail silently so it doesn't break the app
        }
    }
}
