<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Event;
use App\Models\EventAttendance;
use App\Models\Organization;
use App\Models\OrganizationMember;
use Carbon\Carbon;
use App\Models\PointTransaction;
use App\Models\PointRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MobileEventController extends Controller
{
    private const VIEW_POINTS = 5;
    private const ATTENDANCE_POINTS = 20;
    private const MOBILE_ACCESS_ROLES = [
        'president',
        'vice_president',
        'vice_president_internal',
        'vice_president_external',
    ];

    public function index()
    {
        $events = Event::withCount([
                'attendances as confirmed_attendance_count' => fn ($query) => $query->where('status', 'confirmed'),
            ])
            ->whereIn('status', ['upcoming', 'ongoing'])
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
                return $this->formatEvent($event);
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
            'audience' => 'nullable|string|max:50',
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
        $audience = $this->normalizeAudience($request->input('audience'), $organization->department);

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
            'audience' => $audience,
            'status' => 'upcoming',
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

    public function attend(Request $request, Event $event)
    {
        $attendance = EventAttendance::firstOrCreate(
            [
                'event_id' => $event->id,
                'user_id' => $request->user()->id,
            ],
            [
                'status' => 'pending',
                'requested_at' => now(),
            ]
        );

        if ($attendance->status === 'confirmed') {
            return response()->json([
                'success' => true,
                'message' => 'Your attendance was already confirmed.',
                'status' => 'confirmed',
            ]);
        }

        if (! $attendance->wasRecentlyCreated && $attendance->status !== 'pending') {
            $attendance->update([
                'status' => 'pending',
                'requested_at' => now(),
            ]);
        }

        if ($attendance->wasRecentlyCreated || $attendance->wasChanged('status')) {
            ActivityLog::create([
                'admin_id' => null,
                'action' => 'ATTENDANCE_REQUEST',
                'module' => 'Mobile Events',
                'description' => $request->user()->name . ' requested attendance for event: ' . $event->title,
                'ip_address' => $request->ip(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Attendance request sent. Please wait for admin confirmation.',
            'status' => 'pending',
        ]);
    }
    public function attendances(Request $request, Event $event)
    {
        if (! $this->canManageEventAttendance($request, $event)) {
            return response()->json([
                'success' => false,
                'message' => 'Only advisers, co-advisers, presidents, and vice presidents can check attendance.',
            ], 403);
        }

        $attendances = EventAttendance::with('user:id,name,email,department,student_id')
            ->where('event_id', $event->id)
            ->latest('requested_at')
            ->get()
            ->map(fn ($attendance) => $this->formatAttendance($attendance));

        $this->syncAttendanceCount($event);

        return response()->json([
            'success' => true,
            'attendances' => $attendances,
            'event' => $this->formatEvent($event->fresh()),
            'counts' => [
                'pending' => $attendances->where('status', 'pending')->count(),
                'confirmed' => $attendances->where('status', 'confirmed')->count(),
                'rejected' => $attendances->where('status', 'rejected')->count(),
            ],
        ]);
    }

    public function confirmAttendance(Request $request, Event $event, EventAttendance $attendance)
    {
        if ($attendance->event_id !== $event->id) {
            abort(404);
        }

        if (! $this->canManageEventAttendance($request, $event)) {
            return response()->json([
                'success' => false,
                'message' => 'Only advisers, co-advisers, presidents, and vice presidents can check attendance.',
            ], 403);
        }

        if (in_array($attendance->status, ['confirmed', 'rejected'], true)) {
            return response()->json([
                'success' => true,
                'message' => 'Attendance already checked.',
                'status' => $attendance->status,
            ]);
        }

        $decision = $request->input('decision', 'attended');

        if ($decision === 'not_attended') {
            $attendance->update([
                'status' => 'rejected',
                'confirmed_at' => now(),
                'points_awarded' => 0,
            ]);

            $this->syncAttendanceCount($event);
            $attendance->refresh()->load('user:id,name,email,department,student_id');

            return response()->json([
                'success' => true,
                'message' => 'Marked not attended. No points awarded.',
                'status' => 'rejected',
                'attendance' => $this->formatAttendance($attendance),
                'event' => $this->formatEvent($event->fresh()),
            ]);
        }

        DB::transaction(function () use ($event, $attendance) {
            $rule = PointRule::where('trigger', 'event_attendance')
                ->where('is_active', true)
                ->first();

            $attendance->update([
                'status' => 'confirmed',
                'confirmed_at' => now(),
                'points_awarded' => self::ATTENDANCE_POINTS,
            ]);

            $attendance->user()->increment('points', self::ATTENDANCE_POINTS);
            $event->increment('attendance_count');

            PointTransaction::create([
                'user_id' => $attendance->user_id,
                'point_rule_id' => $rule?->id,
                'points' => self::ATTENDANCE_POINTS,
                'reason' => 'Confirmed attendance: ' . $event->title,
                'is_reward_claim' => false,
            ]);
        });

        $this->syncAttendanceCount($event);
        $attendance->refresh()->load('user:id,name,email,department,student_id');

        return response()->json([
            'success' => true,
            'message' => 'Attendance confirmed and 20 points awarded.',
            'status' => 'confirmed',
            'points_awarded' => self::ATTENDANCE_POINTS,
            'attendance' => $this->formatAttendance($attendance),
            'event' => $this->formatEvent($event->fresh()),
        ]);
    }

    private function formatEvent(Event $event): Event
    {
        $confirmedCount = $event->confirmed_attendance_count
            ?? EventAttendance::where('event_id', $event->id)->where('status', 'confirmed')->count();

        $event->attendance_count = $confirmedCount;
        $event->image_url = $event->image ? asset('storage/' . $event->image) : null;
        unset($event->confirmed_attendance_count);

        return $event;
    }

    private function formatAttendance(EventAttendance $attendance): array
    {
        return [
            'id' => $attendance->id,
            'status' => $attendance->status,
            'requested_at' => optional($attendance->requested_at)->format('M d, Y h:i A'),
            'confirmed_at' => optional($attendance->confirmed_at)->format('M d, Y h:i A'),
            'points_awarded' => $attendance->points_awarded,
            'student' => [
                'id' => $attendance->user?->id,
                'name' => $attendance->user?->name ?? 'Unknown student',
                'email' => $attendance->user?->email,
                'department' => $attendance->user?->department,
                'student_id' => $attendance->user?->student_id,
            ],
        ];
    }

    private function syncAttendanceCount(Event $event): void
    {
        $confirmedCount = EventAttendance::where('event_id', $event->id)
            ->where('status', 'confirmed')
            ->count();

        if ((int) $event->attendance_count !== $confirmedCount) {
            $event->forceFill(['attendance_count' => $confirmedCount])->save();
        }
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

    private function normalizeAudience(?string $audience, ?string $fallbackDepartment): ?string
    {
        if ($audience === null) {
            return $fallbackDepartment;
        }

        $value = trim($audience);
        if ($value === '' || in_array(strtolower($value), ['all', 'all users', 'students', 'students only'], true)) {
            return null;
        }

        $allowed = ['BASD', 'MAAD', 'CAAD', 'EAAD'];
        foreach ($allowed as $option) {
            if (strcasecmp($value, $option) === 0) {
                return $option;
            }
        }

        return $fallbackDepartment;
    }

    private function canManageEventAttendance(Request $request, Event $event): bool
    {
        $user = $request->user();
        $eventDepartment = $event->audience ?: Organization::find($event->organization_id)?->department;

        if (! $eventDepartment || $user->department !== $eventDepartment) {
            return false;
        }

        if ($event->organization_id === null) {
            return $this->activeOrganizationFor($request) !== null;
        }

        $memberHasAccess = OrganizationMember::where('organization_id', $event->organization_id)
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->whereIn(DB::raw('LOWER(position)'), self::MOBILE_ACCESS_ROLES)
            ->exists();

        if ($memberHasAccess) {
            return true;
        }

        if ($user->role !== 'professor') {
            return false;
        }

        return Organization::where('id', $event->organization_id)
            ->where('department', $user->department)
            ->where(function ($query) use ($user) {
                $query->whereRaw('LOWER(TRIM(adviser)) = ?', [strtolower(trim($user->name))])
                    ->orWhereRaw('LOWER(TRIM(co_adviser)) = ?', [strtolower(trim($user->name))]);
            })
            ->exists();
    }
    private function activeOrganizationFor(Request $request): ?Organization
    {
        $user = $request->user();
        $member = OrganizationMember::with('organization:id,name,acronym,department')
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
            ->where('department', $user->department)
            ->where(function ($query) use ($user) {
                $query->whereRaw('LOWER(TRIM(adviser)) = ?', [strtolower(trim($user->name))])
                    ->orWhereRaw('LOWER(TRIM(co_adviser)) = ?', [strtolower(trim($user->name))]);
            })
            ->first(['id', 'name', 'acronym', 'department']);
    }
}



