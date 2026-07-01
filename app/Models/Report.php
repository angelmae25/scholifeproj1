<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model {
    protected $fillable = ['user_id','title','description','attachment','type','priority','status','reporter_count','admin_id','resolution_notes','reported_by','resolved_by','resolved_at'];
    protected $casts = ['resolved_at' => 'datetime'];

    public function user() {
        return $this->belongsTo(User::class);
    }
}
