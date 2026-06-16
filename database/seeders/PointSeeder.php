<?php
namespace Database\Seeders;

use App\Models\PointRule;
use App\Models\PointTransaction;
use App\Models\User;
use Illuminate\Database\Seeder;

class PointSeeder extends Seeder {
    public function run(): void {

        // Rules
        $rules = [
            ['name'=>'Event Attendance',    'description'=>'Scan QR at event',         'points'=>50,  'trigger'=>'event_attendance',    'is_active'=>true],
            ['name'=>'Post Announcement',   'description'=>'Published & approved',      'points'=>20,  'trigger'=>'post_announcement',   'is_active'=>true],
            ['name'=>'Join Organization',   'description'=>'Become a member',           'points'=>30,  'trigger'=>'join_organization',   'is_active'=>true],
            ['name'=>'Profile Completed',   'description'=>'Complete your profile',     'points'=>10,  'trigger'=>'profile_completed',   'is_active'=>true],
            ['name'=>'Report Resolved',     'description'=>'Helped resolve a report',   'points'=>15,  'trigger'=>'report_resolved',     'is_active'=>true],
            ['name'=>'Academic Notice Post','description'=>'Posted & approved notice',  'points'=>25,  'trigger'=>'academic_notice_post','is_active'=>true],
            ['name'=>'Referral Bonus',      'description'=>'Referred a new student',    'points'=>40,  'trigger'=>'referral',            'is_active'=>false],
        ];

        foreach ($rules as $r) {
            PointRule::create($r);
        }

        // Transactions
        $rule1 = PointRule::where('trigger','event_attendance')->first();
        $rule2 = PointRule::where('trigger','post_announcement')->first();
        $rule3 = PointRule::where('trigger','join_organization')->first();

        $users = User::all();

        foreach ($users as $user) {
            // Event attendance points
            PointTransaction::create([
                'user_id'       => $user->id,
                'point_rule_id' => $rule1->id,
                'points'        => 50,
                'reason'        => 'Attended CHED Regional Summit',
                'is_reward_claim'=> false,
            ]);

            // Org join points
            if (in_array($user->role, ['student', 'org_officer'])) {
                PointTransaction::create([
                    'user_id'        => $user->id,
                    'point_rule_id'  => $rule3->id,
                    'points'         => 30,
                    'reason'         => 'Joined an organization',
                    'is_reward_claim'=> false,
                ]);
            }
        }

        // A few reward claims
        PointTransaction::create([
            'user_id'        => $users->first()->id,
            'point_rule_id'  => null,
            'points'         => -200,
            'reason'         => 'Redeemed: Free Cafeteria Meal',
            'is_reward_claim'=> true,
        ]);

        PointTransaction::create([
            'user_id'        => $users->skip(1)->first()->id,
            'point_rule_id'  => null,
            'points'         => -100,
            'reason'         => 'Redeemed: University Merch Discount',
            'is_reward_claim'=> true,
        ]);
    }
}
