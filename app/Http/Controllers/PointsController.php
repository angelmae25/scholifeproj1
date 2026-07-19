<?php
namespace App\Http\Controllers;

use App\Models\PointTransaction;
use App\Models\PointRule;
use App\Models\User;
use App\Helpers\LogActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PointsController extends Controller
{
    public function index()
    {
        $stats = [
            'points_awarded'  => PointTransaction::where('is_reward_claim', false)->sum('points'),
            'rewards_claimed' => PointTransaction::where('is_reward_claim', true)->count(),
            'active_rules'    => PointRule::where('is_active', true)->count(),
        ];

        $leaderboard = User::orderByDesc('points')->limit(10)->get();
        $rules       = PointRule::where('is_active', true)->latest()->get();

        return view('Admin.points.index', compact('stats', 'leaderboard', 'rules'));
    }

    public function leaderboard()
    {
        $stats = [
            'points_awarded'  => PointTransaction::where('is_reward_claim', false)->sum('points'),
            'rewards_claimed' => PointTransaction::where('is_reward_claim', true)->count(),
            'active_rules'    => PointRule::where('is_active', true)->count(),
        ];

        $users = User::orderByDesc('points')->paginate(20);

        return view('Admin.points.leaderboard', compact('stats', 'users'));
    }

    public function storeRule(Request $request)
    {
        $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'points'      => ['required', 'integer', 'min:1', 'max:100000'],
        ]);

        DB::table('point_rules')->insert([
            'name'        => $request->name,
            'description' => $request->description ?? null,
            'points'      => (int) $request->points,
            'is_active'   => 1,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        LogActivity::log('CREATE', 'Points', 'Added rule: ' . $request->name . ' (+' . $request->points . ' pts)');

        return redirect()->route('admin.points')
            ->with('success', 'Point rule added!');
    }

    public function updateRule(Request $request, PointRule $pointRule)
    {
        $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'points'      => ['required', 'integer', 'min:1', 'max:100000'],
        ]);

        $pointRule->update([
            'name'        => $request->name,
            'description' => $request->description ?? null,
            'points'      => (int) $request->points,
        ]);

        LogActivity::log('UPDATE', 'Points', 'Updated rule: ' . $request->name);

        return redirect()->route('admin.points')
            ->with('success', 'Point rule updated!');
    }

    public function destroyRule(PointRule $pointRule)
    {
        LogActivity::log('DELETE', 'Points', 'Deleted rule: ' . $pointRule->name);

        $pointRule->delete();

        return redirect()->route('admin.points')
            ->with('success', 'Point rule deleted!');
    }
}
