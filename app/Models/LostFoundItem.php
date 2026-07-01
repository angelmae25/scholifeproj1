<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LostFoundItem extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'location',
        'description',
        'image',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function claims()
    {
        return $this->hasMany(LostFoundClaim::class);
    }
}
