<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Announcement;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\PointTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MobileNewsController extends Controller
{
    private const VIEW_POINTS = 5;
    private const MOBILE_ACCESS_ROLES = [
        'president',
        'vice_president',
        'vice_president_internal',
        'vice_president_external',
    ];

    public function index()
    {
        $news = Announcement::where('status', 'live')
            ->where(function ($query) {
                $query->whereNotNull('admin_id')
                    ->orWhereExists(function ($subQuery) {
                        $subQuery->select(DB::raw(1))
                            ->from('organization_members')
                            ->whereColumn('organization_members.user_id', 'announcements.user_id')
                            ->where('organization_members.status', 'active')
                            ->whereIn(DB::raw('LOWER(organization_members.position)'), self::MOBILE_ACCESS_ROLES);
                    })
                    ->orWhereExists(function ($subQuery) {
                        $subQuery->select(DB::raw(1))
                            ->from('organizations')
                            ->join('users', 'users.id', '=', 'announcements.user_id')
                            ->whereColumn('organizations.id', 'announcements.organization_id')
                            ->where('users.role', 'professor')
                            ->whereRaw('(LOWER(TRIM(organizations.adviser)) = LOWER(TRIM(users.name)) OR LOWER(TRIM(organizations.co_adviser)) = LOWER(TRIM(users.name)))');
                    });
            })
            ->latest('published_at')
            ->latest('created_at')
            ->get()
            ->map(function ($item) {
                $item->attachment_url = $item->attachment ? asset('storage/' . $item->attachment) : null;
                return $item;
            });

        return response()->json([
            'success' => true,
            'news' => $news,
        ]);
    }

    public function store(Request $request)
    {
        $organization = $this->activeOrganizationFor($request);

        if (!$organization) {
            return response()->json([
                'success' => false,
                'message' => 'Only advisers, co-advisers, presidents, and vice presidents can post news.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'nullable|string|max:255',
            'audience' => 'nullable|in:All Users,All,MAAD,EAAD,CAAD,BASD,Others',
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

        $attachmentPath = $this->saveBase64Image($request, 'announcements');

        $news = Announcement::create([
            'title' => $request->title,
            'content' => $request->content,
            'author' => $request->user()->name,
            'audience' => $request->audience === 'All' ? 'All Users' : ($request->audience ?: 'All Users'),
            'category' => $request->category ?: 'General',
            'status' => 'live',
            'views' => 0,
            'attachment' => $attachmentPath,
            'published_at' => now(),
            'admin_id' => null,
            'organization_id' => $organization->id,
            'user_id' => $request->user()->id,
            'created_by_type' => 'organization_officer',
        ]);

        ActivityLog::create([
            'admin_id' => null,
            'action' => 'CREATE_NEWS',
            'module' => 'Mobile News',
            'description' => $request->user()->name . ' posted news: ' . $news->title,
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'News posted successfully.',
            'news' => tap($news, function ($item) {
                $item->attachment_url = $item->attachment ? asset('storage/' . $item->attachment) : null;
            }),
        ], 201);
    }

    public function view(Request $request, Announcement $announcement)
    {
        $awarded = false;

        DB::transaction(function () use ($request, $announcement, &$awarded) {
            $alreadyViewed = DB::table('mobile_content_views')
                ->where('user_id', $request->user()->id)
                ->where('viewable_type', 'news')
                ->where('viewable_id', $announcement->id)
                ->exists();

            $announcement->increment('views');

            if ($alreadyViewed) {
                return;
            }

            DB::table('mobile_content_views')->insert([
                'user_id' => $request->user()->id,
                'viewable_type' => 'news',
                'viewable_id' => $announcement->id,
                'points_awarded' => self::VIEW_POINTS,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $request->user()->increment('points', self::VIEW_POINTS);

            PointTransaction::create([
                'user_id' => $request->user()->id,
                'point_rule_id' => null,
                'points' => self::VIEW_POINTS,
                'reason' => 'Viewed news: ' . $announcement->title,
                'is_reward_claim' => false,
            ]);

            $awarded = true;
        });

        return response()->json([
            'success' => true,
            'message' => $awarded ? 'You earned ' . self::VIEW_POINTS . ' points.' : 'Already viewed. No extra points added.',
            'points_awarded' => $awarded ? self::VIEW_POINTS : 0,
            'total_points' => $request->user()->fresh()->points,
            'views' => $announcement->fresh()->views,
        ]);
    }

    private function saveBase64Image(Request $request, string $folder): ?string
    {
        if (! $request->filled('image_base64')) {
            return null;
        }

        Storage::disk('public')->makeDirectory($folder);

        $base64 = $request->input('image_base64');
        if (str_contains($base64, ',')) {
            $base64 = explode(',', $base64, 2)[1];
        }

        $imageBytes = base64_decode($base64, true);
        if ($imageBytes === false) {
            abort(response()->json([
                'success' => false,
                'message' => 'Invalid image data.',
            ], 422));
        }

        $extension = strtolower($request->input('image_extension', 'jpg'));
        if (! in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'heic', 'heif'], true)) {
            $extension = 'jpg';
        }

        $path = $folder . '/' . Str::uuid() . '.' . $extension;
        Storage::disk('public')->put($path, $imageBytes);

        return $path;
    }

    private function activeOrganizationFor(Request $request): ?Organization
    {
        $user = $request->user();
        $member = OrganizationMember::with('organization:id,name,acronym')
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->whereIn(DB::raw('LOWER(position)'), self::MOBILE_ACCESS_ROLES)
            ->first();

        if ($member?->organization) {
            return $member->organization;
        }

        if ($user->role !== 'professor') {
            return null;
        }

        return Organization::query()
            ->whereRaw('LOWER(TRIM(adviser)) = ?', [strtolower(trim($user->name))])
            ->orWhereRaw('LOWER(TRIM(co_adviser)) = ?', [strtolower(trim($user->name))])
            ->first(['id', 'name', 'acronym']);
    }
}

