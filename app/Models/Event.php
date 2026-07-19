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
        'audience',
        'attendance_count',
        'reminders_sent',
        'points_awarded',
        'remind_1day',
        'remind_1hour',
        'qr_code',
        'image',
        'admin_id',
        'organization_id',
        'user_id',
        'created_by_type',
    ];

    protected $casts = [
        'event_date'   => 'date',
        'end_date'     => 'date',
        'remind_1day'  => 'boolean',
        'remind_1hour' => 'boolean',
    ];

    public function setAudienceAttribute($value): void
    {
        $audience = trim((string) $value);

        if ($audience === '' || in_array(strtolower($audience), ['all', 'all users', 'students', 'students only', 'others', 'null'], true)) {
            $this->attributes['audience'] = null;
            return;
        }

        $allowed = ['BASD', 'MAAD', 'CAAD', 'EAAD'];
        foreach ($allowed as $option) {
            if (strcasecmp($audience, $option) === 0) {
                $this->attributes['audience'] = $option;
                return;
            }
        }

        $this->attributes['audience'] = null;
    }
    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function attendances()
    {
        return $this->hasMany(EventAttendance::class);
    }

    public function getAttendancePctAttribute(): string
    {
        return '0%';
    }
}
