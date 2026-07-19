<?php
namespace Database\Seeders;

use App\Models\Event;
use App\Models\Admin;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder {
    public function run(): void {
        $admin = Admin::first();

        $events = [
            [
                'title'            => 'CHED Regional Summit',
                'description'      => 'Annual CHED Regional Summit for academic affairs.',
                'organizer'        => 'Academic Affair',
                'event_date'       => '2026-05-07',
                'event_time'       => '08:00:00',
                'location'         => 'Main Auditorium',
                'type'             => 'on_campus',
                'status'           => 'completed',
                'attendance_count' => 301,
                'reminders_sent'   => 320,
            ],
            [
                'title'            => 'CS Capstone Defense Day',
                'description'      => 'Final capstone project defense for CS students.',
                'organizer'        => 'CS Department',
                'event_date'       => '2026-05-24',
                'event_time'       => '09:00:00',
                'location'         => 'CS Building Room 301',
                'type'             => 'academic',
                'status'           => 'upcoming',
                'attendance_count' => 0,
                'reminders_sent'   => 80,
            ],
            [
                'title'            => 'Cultural Night Org Fair',
                'description'      => 'Annual cultural night and organization fair.',
                'organizer'        => 'Student Affairs',
                'event_date'       => '2026-05-10',
                'event_time'       => '17:00:00',
                'location'         => 'University Grounds',
                'type'             => 'organization',
                'status'           => 'upcoming',
                'attendance_count' => 0,
                'reminders_sent'   => 200,
            ],
            [
                'title'            => 'Intramural Sports Fest',
                'description'      => 'University-wide intramural sports competition.',
                'organizer'        => 'Sports Office',
                'event_date'       => '2026-06-01',
                'event_time'       => '07:00:00',
                'location'         => 'University Gymnasium',
                'type'             => 'on_campus',
                'status'           => 'upcoming',
                'attendance_count' => 0,
                'reminders_sent'   => 340,
            ],
            [
                'title'            => 'Business Summit 2026',
                'description'      => 'Annual business and entrepreneurship summit.',
                'organizer'        => 'Business Department',
                'event_date'       => '2026-04-15',
                'event_time'       => '08:00:00',
                'location'         => 'Conference Hall',
                'type'             => 'academic',
                'status'           => 'completed',
                'attendance_count' => 138,
                'reminders_sent'   => 150,
            ],
        ];

        foreach ($events as $event) {
            Event::create(array_merge($event, ['admin_id' => $admin->id]));
        }
    }
}
