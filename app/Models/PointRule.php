<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PointRule extends Model {
    protected $fillable = ['name','description','points','trigger','is_active'];
    protected $casts = ['is_active' => 'boolean'];
}
