<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LostFoundClaim extends Model
{
    protected $fillable = [
        'lost_found_item_id',
        'user_id',
        'status',
    ];

    public function item()
    {
        return $this->belongsTo(LostFoundItem::class, 'lost_found_item_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
