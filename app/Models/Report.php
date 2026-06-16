<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model {
    protected $fillable = ['title','description','type','priority','status','reporter_count','resolution_notes','reported_by','resolved_by','resolved_at'];
    protected $casts = ['resolved_at' => 'datetime'];
}
