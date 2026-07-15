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
        $this->ensureSuperAdmin($request);

        [$from, $to] = $this->dateRange($request);

        $baseQuery = UserActivity::query()
            ->whereBetween('started_at', [
                $from,
                $to,
            ]);

        if ($request->filled('user_id')) {
            $baseQuery->where(
                'user_id',
                $request->integer('user_id')
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Overall summary
        |--------------------------------------------------------------------------
        */

        $summary = [
            'total_seconds' => (clone $baseQuery)
                ->sum('duration_seconds'),

            'page_views' => (clone $baseQuery)
                ->count(),

            'active_users' => (clone $baseQuery)
                ->distinct()
                ->count('user_id'),

            'average_seconds' => (int) round(
                (clone $baseQuery)->avg('duration_seconds') ?: 0
            ),
        ];

        /*
        |--------------------------------------------------------------------------
        | User filter list
        |--------------------------------------------------------------------------
        */

        $users = User::query()
            ->whereDoesntHave('roles', function ($query) {
                $query->where('name', 'super_admin');
            })
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'email',
            ]);

        /*
        |--------------------------------------------------------------------------
        | User-wise software usage
        |--------------------------------------------------------------------------
        */

        $userUsage = UserActivity::query()
            ->select('user_id')

            ->selectRaw(
                'SUM(duration_seconds) as total_seconds'
            )

            ->selectRaw(
                'COUNT(*) as page_views'
            )

            ->selectRaw(
                'COUNT(DISTINCT DATE(started_at)) as active_days'
            )

            ->selectRaw(
                'MAX(last_seen_at) as last_seen_at'
            )

            ->whereBetween('started_at', [
                $from,
                $to,
            ])

            ->when(
                $request->filled('user_id'),
                function ($query) use ($request) {
                    $query->where(
                        'user_id',
                        $request->integer('user_id')
                    );
                }
            )

            ->with([
                'user:id,name,email',
            ])

            ->groupBy('user_id')
            ->orderByDesc('total_seconds')
            ->paginate(20)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Most used pages
        |--------------------------------------------------------------------------
        */

        $topPages = UserActivity::query()
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
                'COUNT(DISTINCT user_id) as unique_users'
            )

            ->whereBetween('started_at', [
                $from,
                $to,
            ])

            ->when(
                $request->filled('user_id'),
                function ($query) use ($request) {
                    $query->where(
                        'user_id',
                        $request->integer('user_id')
                    );
                }
            )

            ->groupBy([
                'route_name',
                'path',
            ])

            ->orderByDesc('total_seconds')
            ->limit(15)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Daily usage chart
        |--------------------------------------------------------------------------
        */

        $dailyUsage = UserActivity::query()
            ->selectRaw(
                'DATE(started_at) as activity_date'
            )

            ->selectRaw(
                'SUM(duration_seconds) as total_seconds'
            )

            ->selectRaw(
                'COUNT(*) as page_views'
            )

            ->whereBetween('started_at', [
                $from,
                $to,
            ])

            ->when(
                $request->filled('user_id'),
                function ($query) use ($request) {
                    $query->where(
                        'user_id',
                        $request->integer('user_id')
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
        | Device summary
        |--------------------------------------------------------------------------
        */

        $deviceUsage = UserActivity::query()
            ->select('device_type')

            ->selectRaw(
                'SUM(duration_seconds) as total_seconds'
            )

            ->selectRaw(
                'COUNT(*) as page_views'
            )

            ->whereBetween('started_at', [
                $from,
                $to,
            ])

            ->when(
                $request->filled('user_id'),
                function ($query) use ($request) {
                    $query->where(
                        'user_id',
                        $request->integer('user_id')
                    );
                }
            )

            ->groupBy('device_type')
            ->orderByDesc('total_seconds')
            ->get();

        return view(
            'super_admin.user_activity.index',
            compact(
                'summary',
                'users',
                'userUsage',
                'topPages',
                'dailyUsage',
                'deviceUsage',
                'from',
                'to'
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
}