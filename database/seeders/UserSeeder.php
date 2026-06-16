<?php
namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder {
    public function run(): void {
        $users = [
            ['name'=>'Juan Reyes',       'email'=>'juan.reyes@scholife.edu',      'role'=>'student',    'department'=>'Engineering',       'points'=>1840, 'student_id'=>'2021-00001'],
            ['name'=>'Tung Sahur',       'email'=>'tung.sahur@scholife.edu',      'role'=>'student',    'department'=>'Engineering',       'points'=>1720, 'student_id'=>'2021-00002'],
            ['name'=>'Maria Santos',     'email'=>'maria.santos@scholife.edu',    'role'=>'professor',  'department'=>'Computer Science',  'points'=>520,  'student_id'=>null],
            ['name'=>'Ana Cruz',         'email'=>'ana.cruz@scholife.edu',        'role'=>'student',    'department'=>'Computer Science',  'points'=>1500, 'student_id'=>'2022-00010'],
            ['name'=>'Ben Lim',          'email'=>'ben.lim@scholife.edu',         'role'=>'student',    'department'=>'Business',          'points'=>980,  'student_id'=>'2022-00011'],
            ['name'=>'Carla Reyes',      'email'=>'carla.reyes@scholife.edu',     'role'=>'student',    'department'=>'Engineering',       'points'=>870,  'student_id'=>'2022-00012'],
            ['name'=>'Dennis Garcia',    'email'=>'dennis.garcia@scholife.edu',   'role'=>'org_officer','department'=>'Engineering',       'points'=>760,  'student_id'=>'2021-00050'],
            ['name'=>'Elena Tan',        'email'=>'elena.tan@scholife.edu',       'role'=>'professor',  'department'=>'Business',          'points'=>640,  'student_id'=>null],
            ['name'=>'Francis Bautista', 'email'=>'francis.bautista@scholife.edu','role'=>'student',    'department'=>'Architecture',      'points'=>550,  'student_id'=>'2023-00020'],
            ['name'=>'Grace Mendoza',    'email'=>'grace.mendoza@scholife.edu',   'role'=>'office',     'department'=>'Registrar Office',  'points'=>0,    'student_id'=>null],
        ];

        foreach ($users as $u) {
            User::create([
                'name'           => $u['name'],
                'email'          => $u['email'],
                'password'       => Hash::make('password'),
                'role'           => $u['role'],
                'department'     => $u['department'],
                'student_id'     => $u['student_id'],
                'points'         => $u['points'],
                'status'         => 'active',
                'last_active_at' => now()->subMinutes(rand(1, 1440)),
            ]);
        }
    }
}
