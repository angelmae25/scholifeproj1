<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name',
        'nickname',
        'email',
        'password',
        'avatar',
        'role',
        'department',
        'student_id',
        'phone_number',
        'date_of_birth',
        'gender',
        'address',
        'year_level',
        'points',
        'status',
        'last_active_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
        'last_active_at' => 'datetime',
        'date_of_birth' => 'date',
    ];
}
