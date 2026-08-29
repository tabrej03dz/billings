<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Plan;
use App\Models\User;
use App\Models\UserPlan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SuperAdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            abort(401);
        }

        abort_unless(
            $user->hasAnyRole(['super admin', 'super_admin']),
            403,
            'Only Super Admin can access this dashboard.'
        );

        $timezone = config('app.timezone', 'Asia/Kolkata');
        $today = Carbon::now($timezone)->startOfDay();
        $next7Days = $today->copy()->addDays(7)->endOfDay();
        $next30Days = $today->copy()->addDays(30)->endOfDay();

        /*
        |--------------------------------------------------------------------------
        | Overall Master Counts
        |--------------------------------------------------------------------------
        */
        $totalBusinesses = Business::query()->count();
        $totalUsers = User::query()->count();
        $totalPlans = Plan::query()->count();
        $activePlanMasters = Plan::query()->where('status', 1)->count();

        /*
        |--------------------------------------------------------------------------
        | Active Plan Subscription Query
        |--------------------------------------------------------------------------
        */
        $activePlanSubscriptionQuery = UserPlan::query()
            ->where('status', 1)
            ->where(function ($query) use ($today) {
                $query->whereNull('start_date')
                    ->orWhereDate('start_date', '<=', $today->toDateString());
            })
            ->where(function ($query) use ($today) {
                $query->whereNull('expiry_date')
                    ->orWhereDate('expiry_date', '>=', $today->toDateString());
            });

        $totalAssignedPlans = UserPlan::query()->count();
        $activePlans = (clone $activePlanSubscriptionQuery)->count();

        $expiredPlans = UserPlan::query()
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<', $today->toDateString())
            ->count();

        $expiringIn7Days = UserPlan::query()
            ->where('status', 1)
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '>=', $today->toDateString())
            ->whereDate('expiry_date', '<=', $next7Days->toDateString())
            ->count();

        $expiringIn30Days = UserPlan::query()
            ->where('status', 1)
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '>=', $today->toDateString())
            ->whereDate('expiry_date', '<=', $next30Days->toDateString())
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Businesses With / Without Active Plan
        |--------------------------------------------------------------------------
        */
        $businessesWithActivePlan = (clone $activePlanSubscriptionQuery)
            ->whereNotNull('business_id')
            ->distinct()
            ->count('business_id');

        $businessesWithoutActivePlan = max(0, $totalBusinesses - $businessesWithActivePlan);

        /*
        |--------------------------------------------------------------------------
        | Current Month Growth
        |--------------------------------------------------------------------------
        */
        $newBusinessesThisMonth = Business::query()
            ->whereYear('created_at', $today->year)
            ->whereMonth('created_at', $today->month)
            ->count();

        $newUsersThisMonth = User::query()
            ->whereYear('created_at', $today->year)
            ->whereMonth('created_at', $today->month)
            ->count();

        $previousMonth = $today->copy()->subMonth();

        $newBusinessesPreviousMonth = Business::query()
            ->whereYear('created_at', $previousMonth->year)
            ->whereMonth('created_at', $previousMonth->month)
            ->count();

        $newUsersPreviousMonth = User::query()
            ->whereYear('created_at', $previousMonth->year)
            ->whereMonth('created_at', $previousMonth->month)
            ->count();

        $businessGrowthPercentage = $this->growthPercentage(
            $newBusinessesThisMonth,
            $newBusinessesPreviousMonth
        );

        $userGrowthPercentage = $this->growthPercentage(
            $newUsersThisMonth,
            $newUsersPreviousMonth
        );

        /*
        |--------------------------------------------------------------------------
        | Last 12 Months Summary
        |--------------------------------------------------------------------------
        */
        $growthStart = $today->copy()->subMonths(11)->startOfMonth();
        $growthEnd = $today->copy()->endOfMonth();

        $businessGrowthRows = Business::query()
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as total')
            ->whereBetween('created_at', [$growthStart, $growthEnd])
            ->groupByRaw('YEAR(created_at), MONTH(created_at)')
            ->get()
            ->keyBy(function ($row) {
                return sprintf('%04d-%02d', $row->year, $row->month);
            });

        $userGrowthRows = User::query()
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as total')
            ->whereBetween('created_at', [$growthStart, $growthEnd])
            ->groupByRaw('YEAR(created_at), MONTH(created_at)')
            ->get()
            ->keyBy(function ($row) {
                return sprintf('%04d-%02d', $row->year, $row->month);
            });

        $monthlyGrowth = collect();

        for ($i = 0; $i < 12; $i++) {
            $month = $growthStart->copy()->addMonths($i);
            $key = $month->format('Y-m');

            $monthlyGrowth->push([
                'key' => $key,
                'label' => $month->format('M Y'),
                'short_label' => $month->format('M'),
                'businesses' => (int) optional($businessGrowthRows->get($key))->total,
                'users' => (int) optional($userGrowthRows->get($key))->total,
            ]);
        }

        $maxMonthlyGrowth = max(
            1,
            (int) $monthlyGrowth->max(function ($row) {
                return max($row['businesses'], $row['users']);
            })
        );

        $businessesLast12Months = (int) $monthlyGrowth->sum('businesses');
        $usersLast12Months = (int) $monthlyGrowth->sum('users');

        /*
        |--------------------------------------------------------------------------
        | Daily Trend Graph (Last 30 Days) - Screenshot style line chart
        |--------------------------------------------------------------------------
        */
        $trendDays = 30;
        $trendStart = $today->copy()->subDays($trendDays - 1)->startOfDay();
        $trendEnd = $today->copy()->endOfDay();

        $dailyBusinessRows = Business::query()
            ->selectRaw('DATE(created_at) as graph_date, COUNT(*) as total')
            ->whereBetween('created_at', [$trendStart, $trendEnd])
            ->groupBy('graph_date')
            ->orderBy('graph_date')
            ->get()
            ->keyBy('graph_date');

        $dailyUserRows = User::query()
            ->selectRaw('DATE(created_at) as graph_date, COUNT(*) as total')
            ->whereBetween('created_at', [$trendStart, $trendEnd])
            ->groupBy('graph_date')
            ->orderBy('graph_date')
            ->get()
            ->keyBy('graph_date');

        $trendLabels = [];
        $businessTrendData = [];
        $userTrendData = [];

        for ($i = 0; $i < $trendDays; $i++) {
            $date = $trendStart->copy()->addDays($i);
            $key = $date->toDateString();

            $trendLabels[] = $date->format('d M');
            $businessTrendData[] = (int) optional($dailyBusinessRows->get($key))->total;
            $userTrendData[] = (int) optional($dailyUserRows->get($key))->total;
        }

        /*
        |--------------------------------------------------------------------------
        | Expiring Plans - Next 30 Days
        |--------------------------------------------------------------------------
        */
        $expiringPlans = UserPlan::query()
            ->with([
                'business:id,name,email,mobile',
                'user:id,name,email',
                'plan:id,name,price,duration_days',
            ])
            ->where('status', 1)
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '>=', $today->toDateString())
            ->whereDate('expiry_date', '<=', $next30Days->toDateString())
            ->orderBy('expiry_date')
            ->limit(15)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Recently Expired Plans
        |--------------------------------------------------------------------------
        */
        $recentlyExpiredPlans = UserPlan::query()
            ->with([
                'business:id,name,email,mobile',
                'user:id,name,email',
                'plan:id,name,price,duration_days',
            ])
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<', $today->toDateString())
            ->orderByDesc('expiry_date')
            ->limit(10)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Latest Businesses
        |--------------------------------------------------------------------------
        */
        $latestBusinesses = Business::query()
            ->withCount('users')
            ->latest('id')
            ->limit(10)
            ->get();

        $latestBusinessIds = $latestBusinesses->pluck('id');

        $latestBusinessPlans = UserPlan::query()
            ->with('plan:id,name,price')
            ->whereIn('business_id', $latestBusinessIds)
            ->orderByDesc('id')
            ->get()
            ->unique('business_id')
            ->keyBy('business_id');

        /*
        |--------------------------------------------------------------------------
        | Plan Distribution
        |--------------------------------------------------------------------------
        */
        $planDistribution = DB::table('user_plans')
            ->join('plans', 'plans.id', '=', 'user_plans.plan_id')
            ->select(
                'plans.id',
                'plans.name',
                DB::raw('COUNT(user_plans.id) as assigned_count')
            )
            ->groupBy('plans.id', 'plans.name')
            ->orderByDesc('assigned_count')
            ->limit(10)
            ->get();

        return view('super-admin-dashboard', compact(
            'today',
            'totalBusinesses',
            'totalUsers',
            'totalPlans',
            'activePlanMasters',
            'totalAssignedPlans',
            'activePlans',
            'expiredPlans',
            'expiringIn7Days',
            'expiringIn30Days',
            'businessesWithActivePlan',
            'businessesWithoutActivePlan',
            'newBusinessesThisMonth',
            'newUsersThisMonth',
            'newBusinessesPreviousMonth',
            'newUsersPreviousMonth',
            'businessGrowthPercentage',
            'userGrowthPercentage',
            'monthlyGrowth',
            'maxMonthlyGrowth',
            'businessesLast12Months',
            'usersLast12Months',
            'trendDays',
            'trendLabels',
            'businessTrendData',
            'userTrendData',
            'expiringPlans',
            'recentlyExpiredPlans',
            'latestBusinesses',
            'latestBusinessPlans',
            'planDistribution'
        ));
    }

    /**
     * Calculate month-over-month growth percentage.
     *
     * null means comparison is not meaningful because previous month was zero.
     */
    private function growthPercentage(int $current, int $previous): ?float
    {
        if ($previous <= 0) {
            return $current > 0 ? null : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 1);
    }
}