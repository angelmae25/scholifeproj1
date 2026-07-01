<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $table = 'announcements';

    protected $fillable = [
        'title',
        'content',
        'author',
        'audience',
        'category',
        'status',
        'views',
        'attachment',
        'published_at',
        'scheduled_at',
        'admin_id',
        'organization_id',
        'user_id',
        'created_by_type',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'scheduled_at' => 'datetime',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
