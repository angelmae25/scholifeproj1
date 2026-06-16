<?php
namespace Database\Seeders;

use App\Models\Organization;
use Illuminate\Database\Seeder;

class OrganizationSeeder extends Seeder {
    public function run(): void {
        $orgs = [
            [
                'name'        => 'Green Chemistry Society',
                'acronym'     => 'GREECS',
                'description' => 'The Green Chemistry Society is an academic, non-profit governing student organization in Technological University of the Philippines - Taguig Campus.',
                'type'        => 'Academic',
                'status'      => 'active',
                'president'   => 'Juan Reyes',
                'member_count'=> 45,
            ],
            [
                'name'        => 'University Student Government',
                'acronym'     => 'USG',
                'description' => 'The USG-Taguig shall be the student government and the highest policy making body of the student in the University.',
                'type'        => 'Academic',
                'status'      => 'active',
                'president'   => 'Ana Cruz',
                'member_count'=> 30,
            ],
            [
                'name'        => 'BCES Student Chapter',
                'acronym'     => 'BCES',
                'description' => 'Building and Construction Engineering Society student chapter.',
                'type'        => 'Academic',
                'status'      => 'active',
                'president'   => 'Ben Lim',
                'member_count'=> 60,
            ],
            [
                'name'        => 'Sports and Wellness Club',
                'acronym'     => 'SWC',
                'description' => 'Promoting health and wellness through sports activities.',
                'type'        => 'Sports',
                'status'      => 'active',
                'president'   => 'Carla Reyes',
                'member_count'=> 80,
            ],
            [
                'name'        => 'Cultural Arts Society',
                'acronym'     => 'CAS',
                'description' => 'Promoting Filipino culture and arts within the university.',
                'type'        => 'Cultural',
                'status'      => 'pending',
                'president'   => 'Dennis Garcia',
                'member_count'=> 25,
            ],
        ];

        foreach ($orgs as $org) {
            Organization::create($org);
        }
    }
}
