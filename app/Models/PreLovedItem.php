<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PreLovedItem extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'price',
        'location',
        'description',
        'image',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function messages()
    {
        return $this->hasMany(PreLovedMessage::class, 'pre_loved_item_id');
    }
}
