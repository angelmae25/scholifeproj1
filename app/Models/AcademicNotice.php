<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicNotice extends Model
{
    protected $fillable = [
        'title',
        'content',
        'posted_by',
        'department',
        'type',
        'status',
        'attachment',
        'audience',
        'tags',
        'published_at',
        'scheduled_at',
        'expires_at',
        'admin_id',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'scheduled_at' => 'datetime',
        'expires_at'   => 'datetime',
        'tags'         => 'array',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
