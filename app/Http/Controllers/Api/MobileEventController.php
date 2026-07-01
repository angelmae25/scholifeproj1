<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Event;
use App\Models\Organization;
use App\Models\OrganizationMember;
use Carbon\Carbon;
use App\Models\PointTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MobileEventController extends Controller
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
        $events = Event::whereIn('status', ['upcoming', 'ongoing'])
            ->where(function ($query) {
                $query->whereNotNull('admin_id')
                    ->orWhereExists(function ($subQuery) {
                        $subQuery->select(DB::raw(1))
                            ->from('organization_members')
                            ->whereColumn('organization_members.user_id', 'events.user_id')
                            ->where('organization_members.status', 'active')
                            ->whereIn(DB::raw('LOWER(organization_members.position)'), self::MOBILE_ACCESS_ROLES);
                    })
                    ->orWhereExists(function ($subQuery) {
                        $subQuery->select(DB::raw(1))
                            ->from('organizations')
                            ->join('users', 'users.id', '=', 'events.user_id')
                            ->whereColumn('organizations.id', 'events.organization_id')
                            ->where('users.role', 'professor')
                            ->whereRaw('(LOWER(TRIM(organizations.adviser)) = LOWER(TRIM(users.name)) OR LOWER(TRIM(organizations.co_adviser)) = LOWER(TRIM(users.name)))');
                    });
            })
            ->latest('created_at')
            ->latest('event_date')
            ->get()
            ->map(function ($event) {
                $event->image_url = $event->image ? asset('storage/' . $event->image) : null;
                return $event;
            });

        return response()->json([
            'success' => true,
            'events' => $events,
        ]);
    }

    public function store(Request $request)
    {
        $organization = $this->activeOrganizationFor($request);

        if (!$organization) {
            return response()->json([
                'success' => false,
                'message' => 'Only advisers, co-advisers, presidents, and vice presidents can post events.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'event_date' => 'required|string|max:50',
            'event_time' => 'nullable',
            'location' => 'nullable|string|max:255',
            'type' => 'nullable|string|max:255',
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

        $imagePath = $this->saveBase64Image($request, 'events');
        $eventDate = $this->normalizeDate($request->event_date);
        $eventTime = $this->normalizeTime($request->event_time);

        if (! $eventDate) {
            return response()->json([
                'success' => false,
                'message' => 'Please use a valid date like 2026-06-20.',
            ], 422);
        }

        $event = Event::create([
            'title' => $request->title,
            'description' => $request->description,
            'organizer' => $organization->acronym ?: $organization->name ?: $request->user()->name,
            'event_date' => $eventDate,
            'event_time' => $eventTime,
            'location' => $request->location,
            'type' => $request->type ?: 'on_campus',
            'status' => 'upcoming',
            'rsvp_count' => 0,
            'attendance_count' => 0,
            'reminders_sent' => 0,
            'image' => $imagePath,
            'admin_id' => null,
            'organization_id' => $organization->id,
            'user_id' => $request->user()->id,
            'created_by_type' => 'organization_officer',
        ]);

        ActivityLog::create([
            'admin_id' => null,
            'action' => 'CREATE_EVENT',
            'module' => 'Mobile Events',
            'description' => $request->user()->name . ' posted event: ' . $event->title,
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Event posted successfully.',
            'event' => tap($event, function ($item) {
                $item->image_url = $item->image ? asset('storage/' . $item->image) : null;
            }),
        ], 201);
    }

    public function view(Request $request, Event $event)
    {
        $awarded = false;

        DB::transaction(function () use ($request, $event, &$awarded) {
            $alreadyViewed = DB::table('mobile_content_views')
                ->where('user_id', $request->user()->id)
                ->where('viewable_type', 'event')
                ->where('viewable_id', $event->id)
                ->exists();

            if ($alreadyViewed) {
                return;
            }

            DB::table('mobile_content_views')->insert([
                'user_id' => $request->user()->id,
                'viewable_type' => 'event',
                'viewable_id' => $event->id,
                'points_awarded' => self::VIEW_POINTS,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $request->user()->increment('points', self::VIEW_POINTS);

            PointTransaction::create([
                'user_id' => $request->user()->id,
                'point_rule_id' => null,
                'points' => self::VIEW_POINTS,
                'reason' => 'Viewed event: ' . $event->title,
                'is_reward_claim' => false,
            ]);

            $awarded = true;
        });

        return response()->json([
            'success' => true,
            'message' => $awarded ? 'You earned ' . self::VIEW_POINTS . ' points.' : 'Already viewed. No extra points added.',
            'points_awarded' => $awarded ? self::VIEW_POINTS : 0,
            'total_points' => $request->user()->fresh()->points,
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

    private function normalizeDate(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        $value = trim($value);
        $formats = ['Y-m-d', 'd/m/Y', 'm/d/Y', 'd-m-Y', 'm-d-Y'];

        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, $value)->format('Y-m-d');
            } catch (\Throwable $e) {
                // Try the next format.
            }
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function normalizeTime(?string $value): ?string
    {
        if (! $value) {
            return null;
        }

        $value = trim($value);
        $formats = ['H:i', 'H:i:s', 'h:i A', 'h:i a'];

        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, $value)->format('H:i:s');
            } catch (\Throwable $e) {
                // Try the next format.
            }
        }

        try {
            return Carbon::parse($value)->format('H:i:s');
        } catch (\Throwable $e) {
            return null;
        }
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
