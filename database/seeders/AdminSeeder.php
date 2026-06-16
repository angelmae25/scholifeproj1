<?php
namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder {
    public function run(): void {
        Admin::create([
            'name'          => 'Jemuel Oyat',
            'email'         => 'admin@scholife.com',
            'password'      => Hash::make('Admin@1234!'),
            'role'          => 'super_admin',
            'status'        => 'active',
            'last_login_at' => now(),
        ]);

        Admin::create([
            'name'          => 'Maria Santos',
            'email'         => 'maria.santos@scholife.com',
            'password'      => Hash::make('Admin@1234!'),
            'role'          => 'admin',
            'student_id'    => '240630',
            'status'        => 'active',
            'last_login_at' => now()->subDays(3),
        ]);
    }
}
