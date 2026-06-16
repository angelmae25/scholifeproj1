<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Organization extends Model
{
    protected $fillable = [
        'name',
        'acronym',
        'description',
        'logo',
        'type',
        'status',
        'adviser',
        'co_adviser',
        'department',
        'year_founded',
        'member_count',
        'president',
    ];
}
