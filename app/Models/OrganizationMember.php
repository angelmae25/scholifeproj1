<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrganizationMember extends Model
{
    protected $fillable = [
        'organization_id',
        'user_id',
        'position',
        'status',
        'assigned_by_admin_id',
        'assigned_at',
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function organization() { return $this->belongsTo(Organization::class); }
}
