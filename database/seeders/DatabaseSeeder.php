<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder {
    public function run(): void {
        $this->call([
            AdminSeeder::class,
            UserSeeder::class,
            OrganizationSeeder::class,
            EventSeeder::class,
            AnnouncementSeeder::class,
            ReportSeeder::class,
            AcademicNoticeSeeder::class,
            PointSeeder::class,
        ]);
    }
}
