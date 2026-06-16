<?php
namespace Database\Seeders;

use App\Models\AcademicNotice;
use App\Models\Admin;
use Illuminate\Database\Seeder;

class AcademicNoticeSeeder extends Seeder {
    public function run(): void {
        $admin = Admin::first();

        $notices = [
            [
                'title'        => 'Revised Grading System Memo',
                'content'      => 'Effective 2nd Semester AY 2025-2026, the grading system will follow the new rubric attached herein.',
                'posted_by'    => 'Prof. Santos',
                'department'   => 'Computer Science',
                'type'         => 'memo',
                'status'       => 'published',
                'published_at' => now()->subDays(10),
            ],
            [
                'title'        => 'Thesis Defense Schedule — May 2026',
                'content'      => 'The thesis defense schedule for graduating students is now available. Please check your respective department for your slot.',
                'posted_by'    => 'Prof. Elena Tan',
                'department'   => 'Business',
                'type'         => 'academic',
                'status'       => 'published',
                'published_at' => now()->subDays(5),
            ],
            [
                'title'        => 'Office Hours Update — June 2026',
                'content'      => 'All office personnel are advised that office hours will be adjusted to 7AM–4PM starting June 1, 2026.',
                'posted_by'    => 'Admin Office',
                'department'   => 'Administration',
                'type'         => 'office',
                'status'       => 'published',
                'published_at' => now()->subDays(8),
            ],
            [
                'title'        => 'Laboratory Safety Compliance Memo',
                'content'      => 'All laboratory users must complete the safety compliance form before June 30, 2026.',
                'posted_by'    => 'Lab Safety Officer',
                'department'   => 'Engineering',
                'type'         => 'memo',
                'status'       => 'pending',
                'published_at' => null,
            ],
            [
                'title'        => 'Academic Calendar AY 2026-2027',
                'content'      => 'The academic calendar for AY 2026-2027 has been approved and is now available for download.',
                'posted_by'    => 'Registrar Office',
                'department'   => 'All Departments',
                'type'         => 'academic',
                'status'       => 'draft',
                'published_at' => null,
            ],
        ];

        foreach ($notices as $n) {
            AcademicNotice::create(array_merge($n, ['admin_id' => $admin->id]));
        }
    }
}
