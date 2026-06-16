<?php
namespace Database\Seeders;

use App\Models\Report;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReportSeeder extends Seeder {
    public function run(): void {
        $admin = Admin::first();
        $user  = User::first();

        $reports = [
            [
                'title'          => 'Marketplace: "Brand New iPhone 14 PHP 500"',
                'description'    => 'Suspicious listing in the marketplace. Price is unrealistically low. Possible scam.',
                'type'           => 'Marketplace',
                'priority'       => 'high',
                'status'         => 'open',
                'reporter_count' => 3,
            ],
            [
                'title'          => 'Lost & Found: Duplicate/spam post',
                'description'    => 'User is posting the same lost item multiple times creating spam.',
                'type'           => 'Lost & Found',
                'priority'       => 'medium',
                'status'         => 'open',
                'reporter_count' => 1,
            ],
            [
                'title'          => 'Marketplace: Misleading laptop listing',
                'description'    => 'Laptop described as brand new but photos show visible damage.',
                'type'           => 'Marketplace',
                'priority'       => 'medium',
                'status'         => 'resolved',
                'reporter_count' => 2,
                'resolved_at'    => now()->subDays(2),
                'resolution_notes' => 'Listing removed. User warned.',
            ],
            [
                'title'          => 'Announcement: Inappropriate content',
                'description'    => 'An announcement contains offensive language.',
                'type'           => 'Announcement',
                'priority'       => 'high',
                'status'         => 'resolved',
                'reporter_count' => 5,
                'resolved_at'    => now()->subDays(1),
                'resolution_notes' => 'Announcement taken down. Admin account suspended.',
            ],
            [
                'title'          => 'Event: Misleading event details',
                'description'    => 'Event venue and time information is incorrect.',
                'type'           => 'Event',
                'priority'       => 'low',
                'status'         => 'open',
                'reporter_count' => 1,
            ],
            [
                'title'          => 'Lost & Found: Misleading post',
                'description'    => 'User posted a found item but is asking for payment to return it.',
                'type'           => 'Lost & Found',
                'priority'       => 'medium',
                'status'         => 'open',
                'reporter_count' => 1,
            ],
        ];

        foreach ($reports as $r) {
            Report::create(array_merge($r, [
                'reported_by' => $user->id,
                'resolved_by' => isset($r['resolved_at']) ? $admin->id : null,
            ]));
        }
    }
}
