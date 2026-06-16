<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use Notifiable;

    protected $guard = 'admin';

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'student_id',
        'status',
        'last_login_at',
        'permissions',
        'avatar',
    ];

    protected $hidden = ['password','remember_token'];

    protected $casts = [
        'password'      => 'hashed',
        'last_login_at' => 'datetime',
        'permissions'   => 'array',
    ];

    public function hasPermission(string $module): bool
    {
        // Super admin has all permissions
        if ($this->role === 'super_admin') return true;

        $permissions = $this->permissions ?? [];
        return in_array($module, $permissions);
    }
}
