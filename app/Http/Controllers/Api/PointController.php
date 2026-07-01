<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class PointController extends Controller
{
    public function leaderboard()
    {
        $users = User::select('id','name','department','points','avatar')
            ->orderByDesc('points')
            ->limit(50)
            ->get();

        return response()->json(['success' => true, 'data' => $users]);
    }

    public function myPoints(Request $request)
    {
        $user = $request->user();
        $rank = User::where('points','>',$user->points)->count() + 1;

        return response()->json([
            'success' => true,
            'points'  => $user->points,
            'rank'    => $rank,
        ]);
    }
}
