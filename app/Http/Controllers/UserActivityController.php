<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserActivity;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UserActivityController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Browser heartbeat
    |--------------------------------------------------------------------------
    |
    | Browser har 15 seconds ke active usage ko is endpoint par bhejega.
    |
    */

    public function heartbeat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'activity_id' => [
                'required',
                'integer',
            ],

            'seconds' => [
                'required',
                'integer',
                'min:1',
                'max:60',
            ],

            'page_title' => [
                'nullable',
                'string',
                'max:255',
            ],
        ]);

        $activity = UserActivity::query()
            ->withoutGlobalScopes()
            ->where('id', $validated['activity_id'])
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$activity) {
            return response()->json([
                'success' => false,
                'message' => 'Activity record not found.',
            ], 404);
        }

        $seconds = min(
            max((int) $validated['seconds'], 1),
            60
        );

        $activity->duration_seconds =
            (int) $activity->duration_seconds + $seconds;

        $activity->heartbeat_count =
            (int) $activity->heartbeat_count + 1;

        $activity->last_seen_at = now();

        if (!empty($validated['page_title'])) {
            $activity->page_title =
                $validated['page_title'];
        }

        $activity->save();

        return response()->json([
            'success' => true,
            'duration_seconds' =>
                $activity->duration_seconds,

            'heartbeat_count' =>
                $activity->heartbeat_count,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Page close/end
    |--------------------------------------------------------------------------
    |
    | User page change, reload ya close karega to pending seconds save honge.
    |
    */

public function end(Request $request): JsonResponse
{
    $validated = $request->validate([
        'activity_id' => [
            'required',
            'integer',
        ],

        'seconds' => [
            'nullable',
            'integer',
            'min:0',
            'max:60',
        ],

        'page_title' => [
            'nullable',
            'string',
            'max:255',
        ],
    ]);

    $activity = UserActivity::query()
        ->withoutGlobalScopes()
        ->where('id', $validated['activity_id'])
        ->where('user_id', $request->user()->id)
        ->first();

    if (!$activity) {
        return response()->json([
            'success' => false,
            'message' => 'Activity record not found.',
        ], 404);
    }

    $seconds = min(
        max((int) ($validated['seconds'] ?? 0), 0),
        60
    );

    if ($seconds > 0) {
        $activity->duration_seconds =
            (int) $activity->duration_seconds + $seconds;
    }

    $activity->last_seen_at = now();
    $activity->ended_at = now();

    if (!empty($validated['page_title'])) {
        $activity->page_title =
            $validated['page_title'];
    }

    $activity->save();

    return response()->json([
        'success' => true,
        'duration_seconds' =>
            $activity->duration_seconds,
    ]);
}

    /*
    |--------------------------------------------------------------------------
    | Main Super Admin analytics page
    |--------------------------------------------------------------------------
    */

public function index(Request $request): View
{
    /*
    |--------------------------------------------------------------------------
    | Super Admin Access Check
    |--------------------------------------------------------------------------
    */

    $this->ensureSuperAdmin($request);

    /*
    |--------------------------------------------------------------------------
    | Date Range
    |--------------------------------------------------------------------------
    */

    [$from, $to] = $this->dateRange($request);

    $selectedUserId = $request->filled('user_id')
        ? $request->integer('user_id')
        : null;

    /*
    |--------------------------------------------------------------------------
    | Base Analytics Query
    |--------------------------------------------------------------------------
    |
    | UserActivity model par koi business global scope ho to report incomplete
    | na ho, isliye withoutGlobalScopes() use kiya gaya hai.
    |
    */

    $baseQuery = UserActivity::query()
        ->withoutGlobalScopes()
        ->whereBetween('started_at', [
            $from,
            $to,
        ]);

    if ($selectedUserId) {
        $baseQuery->where(
            'user_id',
            $selectedUserId
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Super Admin IDs
    |--------------------------------------------------------------------------
    |
    | Super Admin ko user usage list aur analytics totals se exclude kiya gaya
    | hai. Dono possible role names support kiye hain.
    |
    */

    $superAdminIds = User::query()
        ->whereHas('roles', function ($query) {
            $query->whereIn('name', [
                'super_admin',
                'superadmin',
            ]);
        })
        ->pluck('users.id');

    /*
    |--------------------------------------------------------------------------
    | Selected user Super Admin nahi hai to exclude karein
    |--------------------------------------------------------------------------
    */

    if ($superAdminIds->isNotEmpty()) {
        $baseQuery->whereNotIn(
            'user_id',
            $superAdminIds
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Overall Summary
    |--------------------------------------------------------------------------
    */

    $summary = [
        'total_seconds' => (int) (
            (clone $baseQuery)
                ->sum('duration_seconds')
        ),

        'page_views' => (int) (
            (clone $baseQuery)
                ->count()
        ),

        'active_users' => (int) (
            (clone $baseQuery)
                ->distinct()
                ->count('user_id')
        ),

        'average_seconds' => (int) round(
            (float) (
                (clone $baseQuery)
                    ->avg('duration_seconds')
                ?: 0
            )
        ),
    ];

    /*
    |--------------------------------------------------------------------------
    | User Filter List
    |--------------------------------------------------------------------------
    */

    $users = User::query()
        ->whereDoesntHave('roles', function ($query) {
            $query->whereIn('name', [
                'super_admin',
                'superadmin',
            ]);
        })
        ->orderBy('name')
        ->get([
            'id',
            'name',
            'email',
        ]);

    /*
    |--------------------------------------------------------------------------
    | User-wise Software Usage
    |--------------------------------------------------------------------------
    |
    | Ordering:
    | 1. Highest total usage first
    | 2. Same usage ho to latest active user first
    | 3. Same last seen ho to highest views first
    |
    */

    $userUsage = UserActivity::query()
        ->withoutGlobalScopes()

        ->select('user_id')

        ->selectRaw(
            'COALESCE(SUM(duration_seconds), 0) AS total_seconds'
        )

        ->selectRaw(
            'COUNT(*) AS page_views'
        )

        ->selectRaw(
            'COUNT(DISTINCT DATE(started_at)) AS active_days'
        )

        ->selectRaw(
            'MAX(last_seen_at) AS last_seen_at'
        )

        ->selectRaw(
            'MIN(started_at) AS first_activity_at'
        )->selectRaw(
            'COALESCE(SUM(error_count), 0) AS total_errors'
        )

        ->whereBetween('started_at', [
            $from,
            $to,
        ])

        ->when(
            $selectedUserId,
            function ($query) use ($selectedUserId) {
                $query->where(
                    'user_id',
                    $selectedUserId
                );
            }
        )

        ->when(
            $superAdminIds->isNotEmpty(),
            function ($query) use ($superAdminIds) {
                $query->whereNotIn(
                    'user_id',
                    $superAdminIds
                );
            }
        )

        ->whereHas('user')

        ->with([
            'user:id,name,email',
        ])

        ->groupBy('user_id')

        /*
        | Highest active usage first.
        */

        ->orderByRaw(
            'COALESCE(SUM(duration_seconds), 0) DESC'
        )

        /*
        | Same usage mein recently active user first.
        */

        ->orderByRaw(
            'MAX(last_seen_at) DESC'
        )

        /*
        | Same duration aur last seen mein more views first.
        */

        ->orderByRaw(
            'COUNT(*) DESC'
        )

        ->paginate(20)
        ->withQueryString();

    /*
    |--------------------------------------------------------------------------
    | Most Used Pages
    |--------------------------------------------------------------------------
    */

    $topPages = UserActivity::query()
        ->withoutGlobalScopes()

        ->select([
            'route_name',
            'path',
        ])

        ->selectRaw(
            'COALESCE(SUM(duration_seconds), 0) AS total_seconds'
        )

        ->selectRaw(
            'COUNT(*) AS page_views'
        )

        ->selectRaw(
            'COUNT(DISTINCT user_id) AS unique_users'
        )

        ->selectRaw(
            'MAX(last_seen_at) AS last_seen_at'
        )

        ->whereBetween('started_at', [
            $from,
            $to,
        ])

        ->when(
            $selectedUserId,
            function ($query) use ($selectedUserId) {
                $query->where(
                    'user_id',
                    $selectedUserId
                );
            }
        )

        ->when(
            $superAdminIds->isNotEmpty(),
            function ($query) use ($superAdminIds) {
                $query->whereNotIn(
                    'user_id',
                    $superAdminIds
                );
            }
        )

        ->groupBy([
            'route_name',
            'path',
        ])

        ->orderByRaw(
            'COALESCE(SUM(duration_seconds), 0) DESC'
        )

        ->orderByRaw(
            'COUNT(*) DESC'
        )

        ->limit(15)
        ->get();

    /*
    |--------------------------------------------------------------------------
    | Daily Usage Chart
    |--------------------------------------------------------------------------
    */

    $dailyUsage = UserActivity::query()
        ->withoutGlobalScopes()

        ->selectRaw(
            'DATE(started_at) AS activity_date'
        )

        ->selectRaw(
            'COALESCE(SUM(duration_seconds), 0) AS total_seconds'
        )

        ->selectRaw(
            'COUNT(*) AS page_views'
        )

        ->selectRaw(
            'COUNT(DISTINCT user_id) AS active_users'
        )

        ->whereBetween('started_at', [
            $from,
            $to,
        ])

        ->when(
            $selectedUserId,
            function ($query) use ($selectedUserId) {
                $query->where(
                    'user_id',
                    $selectedUserId
                );
            }
        )

        ->when(
            $superAdminIds->isNotEmpty(),
            function ($query) use ($superAdminIds) {
                $query->whereNotIn(
                    'user_id',
                    $superAdminIds
                );
            }
        )

        ->groupBy(
            DB::raw('DATE(started_at)')
        )

        ->orderBy('activity_date')
        ->get();

    /*
    |--------------------------------------------------------------------------
    | Device Usage Summary
    |--------------------------------------------------------------------------
    */

    $deviceUsage = UserActivity::query()
        ->withoutGlobalScopes()

        ->select('device_type')

        ->selectRaw(
            'COALESCE(SUM(duration_seconds), 0) AS total_seconds'
        )

        ->selectRaw(
            'COUNT(*) AS page_views'
        )

        ->selectRaw(
            'COUNT(DISTINCT user_id) AS unique_users'
        )

        ->whereBetween('started_at', [
            $from,
            $to,
        ])

        ->when(
            $selectedUserId,
            function ($query) use ($selectedUserId) {
                $query->where(
                    'user_id',
                    $selectedUserId
                );
            }
        )

        ->when(
            $superAdminIds->isNotEmpty(),
            function ($query) use ($superAdminIds) {
                $query->whereNotIn(
                    'user_id',
                    $superAdminIds
                );
            }
        )

        ->groupBy('device_type')

        ->orderByRaw(
            'COALESCE(SUM(duration_seconds), 0) DESC'
        )

        ->get();

    /*
    |--------------------------------------------------------------------------
    | Browser Usage Summary
    |--------------------------------------------------------------------------
    */

    $browserUsage = UserActivity::query()
        ->withoutGlobalScopes()

        ->select('browser')

        ->selectRaw(
            'COALESCE(SUM(duration_seconds), 0) AS total_seconds'
        )

        ->selectRaw(
            'COUNT(*) AS page_views'
        )

        ->whereBetween('started_at', [
            $from,
            $to,
        ])

        ->when(
            $selectedUserId,
            function ($query) use ($selectedUserId) {
                $query->where(
                    'user_id',
                    $selectedUserId
                );
            }
        )

        ->when(
            $superAdminIds->isNotEmpty(),
            function ($query) use ($superAdminIds) {
                $query->whereNotIn(
                    'user_id',
                    $superAdminIds
                );
            }
        )

        ->groupBy('browser')

        ->orderByRaw(
            'COALESCE(SUM(duration_seconds), 0) DESC'
        )

        ->limit(10)
        ->get();

    /*
    |--------------------------------------------------------------------------
    | Return View
    |--------------------------------------------------------------------------
    */

    return view(
        'super_admin.user_activity.index',
        compact(
            'summary',
            'users',
            'userUsage',
            'topPages',
            'dailyUsage',
            'deviceUsage',
            'browserUsage',
            'from',
            'to',
            'selectedUserId'
        )
    );
}
    /*
    |--------------------------------------------------------------------------
    | Particular user detail page
    |--------------------------------------------------------------------------
    */

    public function show(
        Request $request,
        User $user
    ): View {
        $this->ensureSuperAdmin($request);

        [$from, $to] = $this->dateRange($request);

        /*
        |--------------------------------------------------------------------------
        | User activities
        |--------------------------------------------------------------------------
        */

        $activities = UserActivity::query()
            ->where('user_id', $user->id)

            ->whereBetween('started_at', [
                $from,
                $to,
            ])

            ->latest('started_at')
            ->paginate(30)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Page-wise stats
        |--------------------------------------------------------------------------
        */

        $pageStats = UserActivity::query()
            ->select([
                'route_name',
                'path',
            ])

            ->selectRaw(
                'SUM(duration_seconds) as total_seconds'
            )

            ->selectRaw(
                'COUNT(*) as page_views'
            )

            ->selectRaw(
                'AVG(duration_seconds) as average_seconds'
            )

            ->where('user_id', $user->id)

            ->whereBetween('started_at', [
                $from,
                $to,
            ])

            ->groupBy([
                'route_name',
                'path',
            ])

            ->orderByDesc('total_seconds')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Device stats
        |--------------------------------------------------------------------------
        */

        $deviceStats = UserActivity::query()
            ->select('device_type')

            ->selectRaw(
                'SUM(duration_seconds) as total_seconds'
            )

            ->selectRaw(
                'COUNT(*) as page_views'
            )

            ->where('user_id', $user->id)

            ->whereBetween('started_at', [
                $from,
                $to,
            ])

            ->groupBy('device_type')
            ->orderByDesc('total_seconds')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Browser stats
        |--------------------------------------------------------------------------
        */

        $browserStats = UserActivity::query()
            ->select('browser')

            ->selectRaw(
                'SUM(duration_seconds) as total_seconds'
            )

            ->selectRaw(
                'COUNT(*) as page_views'
            )

            ->where('user_id', $user->id)

            ->whereBetween('started_at', [
                $from,
                $to,
            ])

            ->groupBy('browser')
            ->orderByDesc('total_seconds')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Totals
        |--------------------------------------------------------------------------
        */

        $totalQuery = UserActivity::query()
            ->where('user_id', $user->id)
            ->whereBetween('started_at', [
                $from,
                $to,
            ]);

        $totals = [
            'total_seconds' => (clone $totalQuery)
                ->sum('duration_seconds'),

            'page_views' => (clone $totalQuery)
                ->count(),

            'active_days' => (clone $totalQuery)
                ->distinct()
                ->count(DB::raw('DATE(started_at)')),

            'last_seen' => (clone $totalQuery)
                ->max('last_seen_at'),
        ];

        return view(
            'super_admin.user_activity.show',
            compact(
                'user',
                'activities',
                'pageStats',
                'deviceStats',
                'browserStats',
                'totals',
                'from',
                'to'
            )
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Date range handling
    |--------------------------------------------------------------------------
    */

    private function dateRange(Request $request): array
    {
        try {
            $from = $request->filled('from')
                ? Carbon::parse(
                    $request->input('from')
                )->startOfDay()
                : now()->subDays(29)->startOfDay();

            $to = $request->filled('to')
                ? Carbon::parse(
                    $request->input('to')
                )->endOfDay()
                : now()->endOfDay();
        } catch (\Throwable $exception) {
            $from = now()->subDays(29)->startOfDay();
            $to = now()->endOfDay();
        }

        /*
        |--------------------------------------------------------------------------
        | From date ko To date se bada nahi hone dena
        |--------------------------------------------------------------------------
        */

        if ($from->greaterThan($to)) {
            [$from, $to] = [
                $to->copy()->startOfDay(),
                $from->copy()->endOfDay(),
            ];
        }

        return [
            $from,
            $to,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Super Admin protection
    |--------------------------------------------------------------------------
    */

    private function ensureSuperAdmin(
        Request $request
    ): void {
        abort_unless(
            $request->user()
            && $request->user()->hasRole('super admin'),
            403,
            'Only Super Admin can access user activity analytics.'
        );
    }







    public function storeError(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'activity_id' => [
                'required',
                'integer',
            ],

            'error_type' => [
                'required',
                'string',
                'max:50',
            ],

            'message' => [
                'required',
                'string',
                'max:5000',
            ],

            'source_file' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'source_line' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'source_column' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'stack_trace' => [
                'nullable',
                'string',
                'max:20000',
            ],

            'request_url' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'request_method' => [
                'nullable',
                'string',
                'max:10',
            ],

            'http_status' => [
                'nullable',
                'integer',
                'min:100',
                'max:599',
            ],
        ]);

        $activity = UserActivity::query()
            ->withoutGlobalScopes()
            ->whereKey($validated['activity_id'])
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$activity) {
            return response()->json([
                'success' => false,
                'message' => 'Activity not found.',
            ], 404);
        }

        $errors = $activity->errors ?? [];

        /*
        |--------------------------------------------------------------------------
        | Same error 30 seconds ke andar duplicate save na ho
        |--------------------------------------------------------------------------
        */

        $fingerprint = hash('sha256', implode('|', [
            $validated['error_type'],
            $validated['message'],
            $validated['source_file'] ?? '',
            $validated['source_line'] ?? '',
            $validated['http_status'] ?? '',
            $validated['request_url'] ?? '',
        ]));

        $lastSameErrorIndex = null;

        foreach ($errors as $index => $error) {
            if (
                ($error['fingerprint'] ?? null) === $fingerprint
                && !empty($error['last_seen_at'])
                && \Carbon\Carbon::parse($error['last_seen_at'])
                    ->greaterThanOrEqualTo(now()->subSeconds(30))
            ) {
                $lastSameErrorIndex = $index;
                break;
            }
        }

        if ($lastSameErrorIndex !== null) {
            $errors[$lastSameErrorIndex]['count'] =
                (int) ($errors[$lastSameErrorIndex]['count'] ?? 1) + 1;

            $errors[$lastSameErrorIndex]['last_seen_at'] =
                now()->toDateTimeString();
        } else {
            $errors[] = [
                'fingerprint' => $fingerprint,

                'error_type' =>
                    $validated['error_type'],

                'message' =>
                    $validated['message'],

                'source_file' =>
                    $validated['source_file'] ?? null,

                'source_line' =>
                    $validated['source_line'] ?? null,

                'source_column' =>
                    $validated['source_column'] ?? null,

                'stack_trace' =>
                    $validated['stack_trace'] ?? null,

                'request_url' =>
                    $validated['request_url'] ?? null,

                'request_method' =>
                    $validated['request_method'] ?? null,

                'http_status' =>
                    $validated['http_status'] ?? null,

                'count' => 1,

                'first_seen_at' =>
                    now()->toDateTimeString(),

                'last_seen_at' =>
                    now()->toDateTimeString(),
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Array ko bahut bada hone se bachao
        |--------------------------------------------------------------------------
        */

        if (count($errors) > 50) {
            $errors = array_slice($errors, -50);
        }

        $activity->forceFill([
            'errors' => $errors,
            'error_count' => collect($errors)
                ->sum(fn ($error) => (int) ($error['count'] ?? 1)),
            'last_error_at' => now(),
        ])->save();

        return response()->json([
            'success' => true,
            'error_count' => $activity->error_count,
        ]);
    }
}