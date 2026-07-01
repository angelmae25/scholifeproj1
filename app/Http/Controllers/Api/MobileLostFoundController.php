<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\LostFoundClaim;
use App\Models\LostFoundItem;
use App\Models\PointTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MobileLostFoundController extends Controller
{
    private const POST_POINTS = 10;

    public function index()
    {
        return response()->json([
            'success' => true,
            'items' => LostFoundItem::latest()->get()->map(function ($item) {
                return [
                    'id' => $item->id,
                    'user_id' => $item->user_id,
                    'title' => $item->title,
                    'location' => $item->location,
                    'description' => $item->description,
                    'status' => $item->status,
                    'image' => $item->image,
                    'image_url' => $item->image ? asset('storage/' . $item->image) : null,
                ];
            }),
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image_base64' => 'nullable|string',
            'image_extension' => 'nullable|string|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $imagePath = null;

        if ($request->filled('image_base64')) {
            Storage::disk('public')->makeDirectory('lost-found-items');

            $base64 = $request->input('image_base64');
            if (str_contains($base64, ',')) {
                $base64 = explode(',', $base64, 2)[1];
            }

            $imageBytes = base64_decode($base64, true);

            if ($imageBytes === false) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid image data.',
                ], 422);
            }

            $extension = strtolower($request->input('image_extension', 'jpg'));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'heic', 'heif'];

            if (! in_array($extension, $allowedExtensions, true)) {
                $extension = 'jpg';
            }

            $imagePath = 'lost-found-items/' . Str::uuid() . '.' . $extension;
            Storage::disk('public')->put($imagePath, $imageBytes);
        }

        $item = LostFoundItem::create([
            'user_id' => $request->user()->id,
            'title' => $request->title,
            'location' => $request->location,
            'description' => $request->description,
            'image' => $imagePath,
            'status' => 'available',
        ]);

        ActivityLog::create([
            'admin_id' => null,
            'action' => 'POST',
            'module' => 'Lost and Found',
            'description' => $request->user()->name . ' posted lost and found item: ' . $item->title,
            'ip_address' => $request->ip(),
        ]);

        $request->user()->increment('points', self::POST_POINTS);

        PointTransaction::create([
            'user_id' => $request->user()->id,
            'point_rule_id' => null,
            'points' => self::POST_POINTS,
            'reason' => 'Posted lost and found item: ' . $item->title,
            'is_reward_claim' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lost and found item posted successfully. You earned ' . self::POST_POINTS . ' points.',
            'points_awarded' => self::POST_POINTS,
            'total_points' => $request->user()->fresh()->points,
            'item' => [
                'id' => $item->id,
                'user_id' => $item->user_id,
                'title' => $item->title,
                'location' => $item->location,
                'description' => $item->description,
                'status' => $item->status,
                'image' => $item->image,
                'image_url' => $item->image ? asset('storage/' . $item->image) : null,
            ],
        ], 201);
    }

    public function claim(Request $request, LostFoundItem $item)
    {
        if ($item->user_id && (int) $item->user_id === (int) $request->user()->id) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot claim your own post.',
            ], 422);
        }

        $claim = LostFoundClaim::create([
            'lost_found_item_id' => $item->id,
            'user_id' => $request->user()->id,
            'status' => 'pending',
        ]);

        ActivityLog::create([
            'admin_id' => null,
            'action' => 'CLAIM',
            'module' => 'Lost and Found',
            'description' => $request->user()->name . ' claimed lost item: ' . $item->title,
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'success' => true,
            'message' => $item->user_id
                ? 'Claim request sent. The person who posted this item will see it in Messages.'
                : 'Claim request sent.',
            'claim' => $claim,
        ], 201);
    }
}
