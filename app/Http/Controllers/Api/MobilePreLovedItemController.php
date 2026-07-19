<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PreLovedItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MobilePreLovedItemController extends Controller
{
    public function index()
    {
        return response()->json([
            'success' => true,
            'items' => PreLovedItem::with('user:id,name')->latest()->get()->map(function ($item) {
                return [
                    'id' => $item->id,
                    'user_id' => $item->user_id,
                    'name' => $item->name,
                    'price' => $item->price,
                    'location' => $item->location,
                    'description' => $item->description,
                    'image' => $item->image,
                    'image_url' => $item->image ? asset('storage/' . $item->image) : null,
                    'status' => $item->status,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                    'user' => $item->user,
                    'seller_name' => optional($item->user)->name,
                    'can_message' => $item->user_id !== optional(request()->user())->id,
                ];
            }),
        ]);
    }

    public function store(Request $request)
    {
        Log::info('Mobile pre-loved store request received', [
            'user_id' => optional($request->user())->id,
            'content_type' => $request->header('Content-Type'),
            'name' => $request->input('name'),
            'has_image_base64' => $request->filled('image_base64'),
            'image_base64_length' => $request->filled('image_base64') ? strlen($request->input('image_base64')) : 0,
        ]);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
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

        try {
            $imagePath = null;

            if ($request->filled('image_base64')) {
                Storage::disk('public')->makeDirectory('pre-loved-items');

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

                $imagePath = 'pre-loved-items/' . Str::uuid() . '.' . $extension;
                Storage::disk('public')->put($imagePath, $imageBytes);
            }

            $item = PreLovedItem::create([
                'user_id' => $request->user()->id,
                'name' => $request->name,
                'price' => $request->price,
                'location' => $request->location,
                'description' => $request->description,
                'image' => $imagePath,
                'status' => 'available',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Item posted successfully',
                'item' => [
                    'id' => $item->id,
                    'user_id' => $item->user_id,
                    'name' => $item->name,
                    'price' => $item->price,
                    'location' => $item->location,
                    'description' => $item->description,
                    'image' => $item->image,
                    'image_url' => $item->image ? asset('storage/' . $item->image) : null,
                    'status' => $item->status,
                    'created_at' => $item->created_at,
                    'updated_at' => $item->updated_at,
                ],
            ], 201);
        } catch (\Throwable $e) {
            Log::error('Mobile pre-loved post failed', [
                'message' => $e->getMessage(),
                'user_id' => optional($request->user())->id,
                'has_image_base64' => $request->filled('image_base64'),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage(),
            ], 500);
        }
    }
    public function update(Request $request, PreLovedItem $item)
    {
        if ((int) $item->user_id !== (int) $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'You can only edit your own post.'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors(),
            ], 422);
        }

        $item->update([
            'name' => $request->name,
            'price' => $request->price,
            'location' => $request->location,
            'description' => $request->description,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Item updated successfully.',
            'item' => $item->fresh(),
        ]);
    }

    public function destroy(Request $request, PreLovedItem $item)
    {
        if ((int) $item->user_id !== (int) $request->user()->id) {
            return response()->json(['success' => false, 'message' => 'You can only delete your own post.'], 403);
        }

        if ($item->image) {
            Storage::disk('public')->delete($item->image);
        }

        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item deleted successfully.',
        ]);
    }
}
