<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PointTransaction extends Model {
    protected $fillable = ['user_id','point_rule_id','points','reason','is_reward_claim'];
    protected $casts = ['is_reward_claim' => 'boolean'];

    public function user() { return $this->belongsTo(User::class); }
    public function rule() { return $this->belongsTo(PointRule::class, 'point_rule_id'); }
}
