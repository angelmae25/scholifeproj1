<?php
namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Admin;
use Illuminate\Database\Seeder;

class AnnouncementSeeder extends Seeder {
    public function run(): void {
        $admin = Admin::first();

        $announcements = [
            [
                'title'        => 'Enrollment Period Open — 2nd Sem',
                'content'      => 'Enrollment for 2nd Semester AY 2025-2026 is now open. Please log in to the student portal to enroll. Deadline is June 15, 2026.',
                'author'       => 'Registrar Office',
                'audience'     => 'All Users',
                'status'       => 'live',
                'views'        => 3201,
                'published_at' => now()->subDays(3),
            ],
            [
                'title'        => 'University Foundation Day Holiday',
                'content'      => 'Classes are suspended on June 20, 2026 in celebration of the University Foundation Day.',
                'author'       => 'Office of the President',
                'audience'     => 'All Users',
                'status'       => 'live',
                'views'        => 2850,
                'published_at' => now()->subDays(7),
            ],
            [
                'title'        => 'Library System Maintenance',
                'content'      => 'The online library system will be undergoing maintenance on June 14, 2026 from 10PM to 2AM.',
                'author'       => 'IT Department',
                'audience'     => 'All Users',
                'status'       => 'scheduled',
                'views'        => 0,
                'scheduled_at' => now()->addDays(2),
            ],
            [
                'title'        => 'Scholarship Application Now Open',
                'content'      => 'Students who wish to apply for university scholarships for AY 2026-2027 may now submit their applications at the Scholarship Office.',
                'author'       => 'Scholarship Office',
                'audience'     => 'Students',
                'status'       => 'live',
                'views'        => 1540,
                'published_at' => now()->subDays(5),
            ],
            [
                'title'        => 'Faculty Development Seminar',
                'content'      => 'All faculty members are required to attend the Faculty Development Seminar on June 18, 2026.',
                'author'       => 'HR Department',
                'audience'     => 'Professors',
                'status'       => 'live',
                'views'        => 672,
                'published_at' => now()->subDays(2),
            ],
            [
                'title'        => 'Campus Wi-Fi Upgrade Notice',
                'content'      => 'Campus Wi-Fi will be upgraded on June 16. Expect intermittent connectivity from 8AM to 12NN.',
                'author'       => 'IT Department',
                'audience'     => 'All Users',
                'status'       => 'scheduled',
                'views'        => 0,
                'scheduled_at' => now()->addDays(4),
            ],
            [
                'title'        => 'Lost & Found Policy Update',
                'content'      => 'The university has updated its Lost & Found policy. Items not claimed within 30 days will be donated.',
                'author'       => 'Security Office',
                'audience'     => 'All Users',
                'status'       => 'draft',
                'views'        => 0,
            ],
        ];

        foreach ($announcements as $a) {
            Announcement::create(array_merge($a, ['admin_id' => $admin->id]));
        }
    }
}
