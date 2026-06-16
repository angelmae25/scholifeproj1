<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'title',
        'description',
        'organizer',
        'event_date',
        'event_time',
        'end_date',
        'end_time',
        'location',
        'type',
        'status',
        'rsvp_count',
        'attendance_count',
        'reminders_sent',
        'max_attendees',
        'rsvp_deadline',
        'points_awarded',
        'remind_1day',
        'remind_1hour',
        'qr_code',
        'admin_id',
    ];

    protected $casts = [
        'event_date'   => 'date',
        'end_date'     => 'date',
        'rsvp_deadline'=> 'date',
        'remind_1day'  => 'boolean',
        'remind_1hour' => 'boolean',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function getAttendancePctAttribute(): string
    {
        if (!$this->rsvp_count) return '0%';
        return round(($this->attendance_count / $this->rsvp_count) * 100) . '%';
    }
}
