<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Invoice;
use App\Models\Item;
use App\Models\MetalRate;
use App\Models\Purchase;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            abort(401);
        }

        /*
        |--------------------------------------------------------------------------
        | Resolve user's valid active business
        |--------------------------------------------------------------------------
        |
        | business_user pivot ko direct query kar rahe hain, taaki Business model
        | ka koi global scope is resolution ko disturb na kare.
        |
        */

        $userBusinessIds = DB::table('business_user')
            ->join(
                'businesses',
                'businesses.id',
                '=',
                'business_user.business_id'
            )
            ->where('business_user.user_id', $user->id)
            ->orderByRaw("
                CASE
                    WHEN business_user.role = 'owner' THEN 1
                    WHEN business_user.role = 'staff' THEN 2
                    ELSE 3
                END
            ")
            ->orderBy('business_user.id')
            ->pluck('businesses.id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        /*
        |--------------------------------------------------------------------------
        | User ke saath koi business connected nahi hai
        |--------------------------------------------------------------------------
        */

        if ($userBusinessIds->isEmpty()) {
            session()->forget('active_business_id');

            return redirect()
                ->route('no-business.whatsapp')
                ->with(
                    'info',
                    'Please configure WhatsApp API and send PDFs directly.'
                );
        }

        $sessionBusinessId = session('active_business_id');
        $currentBusinessId = $user->current_business_id ?? null;

        $bid = null;

        /*
        |--------------------------------------------------------------------------
        | First priority: session active business
        |--------------------------------------------------------------------------
        */

        if (
            $sessionBusinessId
            && is_numeric($sessionBusinessId)
            && $userBusinessIds->contains((int) $sessionBusinessId)
        ) {
            $bid = (int) $sessionBusinessId;
        }

        /*
        |--------------------------------------------------------------------------
        | Second priority: users.current_business_id
        |--------------------------------------------------------------------------
        */

        if (
            !$bid
            && $currentBusinessId
            && is_numeric($currentBusinessId)
            && $userBusinessIds->contains((int) $currentBusinessId)
        ) {
            $bid = (int) $currentBusinessId;
        }

        /*
        |--------------------------------------------------------------------------
        | Final fallback: pivot ka first valid business
        |--------------------------------------------------------------------------
        */

        if (!$bid) {
            $bid = (int) $userBusinessIds->first();
        }

        /*
        |--------------------------------------------------------------------------
        | Active business sync
        |--------------------------------------------------------------------------
        */

        session([
            'active_business_id' => $bid,
        ]);

        /*
        |--------------------------------------------------------------------------
        | current_business_id sync
        |--------------------------------------------------------------------------
        |
        | Model save use nahi kar rahe, kyunki User model events/scopes se
        | unnecessary side effects aa sakte hain.
        |
        */

        if ((int) ($user->current_business_id ?? 0) !== $bid) {
            DB::table('users')
                ->where('id', $user->id)
                ->update([
                    'current_business_id' => $bid,
                    'updated_at' => now(),
                ]);

            $user->current_business_id = $bid;
        }

        /*
        |--------------------------------------------------------------------------
        | Load business without global scopes
        |--------------------------------------------------------------------------
        */

        $business = Business::query()
            ->withoutGlobalScopes()
            ->whereKey($bid)
            ->first();

        if (!$business) {
            session()->forget('active_business_id');

            return redirect()
                ->route('no-business.whatsapp')
                ->with(
                    'error',
                    'Selected business database mein available nahi hai.'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Timezone and date filters
        |--------------------------------------------------------------------------
        */

        $tz = config('app.timezone', 'Asia/Kolkata');

        $today = Carbon::now($tz)->startOfDay();

        $defaultFrom = Carbon::now($tz)
            ->startOfMonth()
            ->startOfDay();

        $defaultTo = Carbon::now($tz)
            ->endOfDay();

        try {
            $from = $request->filled('from')
                ? Carbon::parse(
                    $request->query('from'),
                    $tz
                )->startOfDay()
                : $defaultFrom;

            $to = $request->filled('to')
                ? Carbon::parse(
                    $request->query('to'),
                    $tz
                )->endOfDay()
                : $defaultTo;
        } catch (\Throwable $exception) {
            $from = $defaultFrom;
            $to = $defaultTo;
        }

        /*
        |--------------------------------------------------------------------------
        | Safety: from date greater than to date
        |--------------------------------------------------------------------------
        */

        if ($from->gt($to)) {
            $oldFrom = $from->copy();

            $from = $to->copy()->startOfDay();
            $to = $oldFrom->copy()->endOfDay();
        }

        /*
        |--------------------------------------------------------------------------
        | Preset filters
        |--------------------------------------------------------------------------
        */

        $preset = strtolower(
            trim((string) $request->query('preset', ''))
        );

        if ($preset === 'today') {
            $from = Carbon::now($tz)->startOfDay();
            $to = Carbon::now($tz)->endOfDay();
        } elseif ($preset === '7d') {
            $from = Carbon::now($tz)
                ->subDays(6)
                ->startOfDay();

            $to = Carbon::now($tz)->endOfDay();
        } elseif ($preset === 'month') {
            $from = Carbon::now($tz)
                ->startOfMonth()
                ->startOfDay();

            $to = Carbon::now($tz)->endOfDay();
        }

        /*
        |--------------------------------------------------------------------------
        | Base business queries
        |--------------------------------------------------------------------------
        |
        | withoutGlobalScope('business') ke baad manually correct business ID
        | lagayi gayi hai. Isse duplicate ya invalid global scope issue nahi hoga.
        |
        */

        $invoiceQ = Invoice::query()
            ->withoutGlobalScope('business')
            ->where('invoices.business_id', $bid);

        $purchaseQ = Purchase::query()
            ->withoutGlobalScope('business')
            ->where('purchases.business_id', $bid);

        $itemQ = Item::query()
            ->withoutGlobalScope('business')
            ->where('items.business_id', $bid);

        $rateQ = MetalRate::query()
            ->withoutGlobalScope('business')
            ->where('metal_rates.business_id', $bid);

        /*
        |--------------------------------------------------------------------------
        | Sales: tax invoices and old null invoice types
        |--------------------------------------------------------------------------
        */

        $salesQ = (clone $invoiceQ)
            ->where(function ($query) {
                $query
                    ->where('invoice_type', 'tax')
                    ->orWhereNull('invoice_type');
            });

        $todayDate = Carbon::now($tz)->toDateString();

        /*
        |--------------------------------------------------------------------------
        | Today's sales
        |--------------------------------------------------------------------------
        */

        $todaySalesAmount = (clone $salesQ)
            ->whereDate('invoice_date', $todayDate)
            ->sum('total');

        $todaySalesCount = (clone $salesQ)
            ->whereDate('invoice_date', $todayDate)
            ->count();


                /*
                |--------------------------------------------------------------------------
                | Delete abandoned registrations
                |--------------------------------------------------------------------------
                |
                | Aise users delete honge:
                | - Registration ko 10 din se zyada ho gaye hain
                | - Koi business create/attach nahi kiya
                | - Koi plan/subscription start nahi kiya
                |
                */

                $this->deleteAbandonedRegisteredUsers($user->id);

        /*
        |--------------------------------------------------------------------------
        | Today's item-wise gross profit
        |--------------------------------------------------------------------------
        |
        | Profit = Sale amount - Cost amount
        |
        */

        $todayProfitData = DB::table('invoice_items as ii')
            ->join(
                'invoices as inv',
                'inv.id',
                '=',
                'ii.invoice_id'
            )
            ->leftJoin('items as i', function ($join) use ($bid) {
                $join
                    ->on('i.id', '=', 'ii.item_id')
                    ->where('i.business_id', '=', $bid);
            })
            ->where('inv.business_id', $bid)
            ->whereDate('inv.invoice_date', $todayDate)
            ->where(function ($query) {
                $query
                    ->where('inv.invoice_type', 'tax')
                    ->orWhereNull('inv.invoice_type');
            })
            ->selectRaw('
                COALESCE(
                    SUM(COALESCE(ii.amount, 0)),
                    0
                ) AS sale_amount,

                COALESCE(
                    SUM(
                        COALESCE(ii.quantity, 0)
                        *
                        COALESCE(i.cost_price, 0)
                    ),
                    0
                ) AS cost_amount,

                COALESCE(
                    SUM(
                        COALESCE(ii.amount, 0)
                        -
                        (
                            COALESCE(ii.quantity, 0)
                            *
                            COALESCE(i.cost_price, 0)
                        )
                    ),
                    0
                ) AS profit_amount
            ')
            ->first();

        $todayItemSaleAmount = round(
            (float) ($todayProfitData->sale_amount ?? 0),
            2
        );

        $todayItemCostAmount = round(
            (float) ($todayProfitData->cost_amount ?? 0),
            2
        );

        $todayProfitAmount = round(
            (float) ($todayProfitData->profit_amount ?? 0),
            2
        );

        $todayProfitPercent = $todayItemSaleAmount > 0
            ? round(
                ($todayProfitAmount / $todayItemSaleAmount) * 100,
                2
            )
            : 0;

        /*
        |--------------------------------------------------------------------------
        | Sales totals
        |--------------------------------------------------------------------------
        */

        $monthSalesAmount = (clone $salesQ)
            ->whereBetween('invoice_date', [
                $from,
                $to,
            ])
            ->sum('total');

        $totalSalesAmount = (clone $salesQ)
            ->sum('total');

        /*
        |--------------------------------------------------------------------------
        | Purchase totals
        |--------------------------------------------------------------------------
        */

        $todayPurchasesAmount = (clone $purchaseQ)
            ->whereDate('invoice_date', $todayDate)
            ->sum('total_amount');

        $monthPurchasesAmount = (clone $purchaseQ)
            ->whereBetween('invoice_date', [
                $from,
                $to,
            ])
            ->sum('total_amount');

        $totalPurchasesAmount = (clone $purchaseQ)
            ->sum('total_amount');

        /*
        |--------------------------------------------------------------------------
        | Item and stock totals
        |--------------------------------------------------------------------------
        */

        $totalItems = (clone $itemQ)->count();

        $totalStockQty = (clone $itemQ)
            ->sum('stock_qty');

        $lowStockCount = (clone $itemQ)
            ->where('stock_qty', '<=', 2)
            ->count();

        /*
        |--------------------------------------------------------------------------
        | Today's metal rates
        |--------------------------------------------------------------------------
        */

        $todayMetalRates = (clone $rateQ)
            ->whereDate('rate_date', $todayDate)
            ->where('is_active', true)
            ->get();

        $baseGoldPurities = [
            '24K',
            '22K',
            '20K',
            '18K',
        ];

        $baseSilverPurities = [
            '999',
            '995',
            '925',
        ];

        $goldPurities = collect($baseGoldPurities)
            ->merge(
                $todayMetalRates
                    ->where('metal_type', 'gold')
                    ->pluck('purity')
                    ->filter()
            )
            ->unique()
            ->values()
            ->all();

        $silverPurities = collect($baseSilverPurities)
            ->merge(
                $todayMetalRates
                    ->where('metal_type', 'silver')
                    ->pluck('purity')
                    ->filter()
            )
            ->unique()
            ->values()
            ->all();

        $rateMap = $todayMetalRates
            ->keyBy(
                fn ($rate) =>
                    strtolower($rate->metal_type)
                    . '|'
                    . (string) ($rate->purity ?? '')
            )
            ->map(
                fn ($rate) => $rate->rate_per_gram
            )
            ->toArray();

        /*
        |--------------------------------------------------------------------------
        | Recent invoices
        |--------------------------------------------------------------------------
        */

        $recentInvoices = (clone $salesQ)
            ->whereBetween('invoice_date', [
                $from,
                $to,
            ])
            ->with('client')
            ->latest('invoice_date')
            ->latest('id')
            ->limit(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Recent purchases
        |--------------------------------------------------------------------------
        */

        $recentPurchases = (clone $purchaseQ)
            ->whereBetween('invoice_date', [
                $from,
                $to,
            ])
            ->with('supplier')
            ->latest('invoice_date')
            ->latest('id')
            ->limit(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Low stock items
        |--------------------------------------------------------------------------
        */

        $lowStockItems = (clone $itemQ)
            ->with('category')
            ->where('stock_qty', '<=', 5)
            ->orderBy('stock_qty')
            ->limit(5)
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Pending amounts
        |--------------------------------------------------------------------------
        */

        $todayPendingAmount = (clone $salesQ)
            ->whereDate('invoice_date', $todayDate)
            ->sum('balance');

        $monthPendingAmount = (clone $salesQ)
            ->whereBetween('invoice_date', [
                $from,
                $to,
            ])
            ->sum('balance');

        $totalPendingAmount = (clone $salesQ)
            ->sum('balance');

        /*
        |--------------------------------------------------------------------------
        | Return dashboard
        |--------------------------------------------------------------------------
        */

        return view('dashboard', compact(
            'todayProfitAmount',
            'todayItemSaleAmount',
            'todayItemCostAmount',
            'todayProfitPercent',

            'today',
            'business',
            'bid',

            'from',
            'to',
            'preset',

            'todaySalesAmount',
            'todaySalesCount',
            'monthSalesAmount',
            'totalSalesAmount',

            'todayPurchasesAmount',
            'monthPurchasesAmount',
            'totalPurchasesAmount',

            'totalItems',
            'totalStockQty',
            'lowStockCount',

            'todayMetalRates',
            'goldPurities',
            'silverPurities',
            'rateMap',

            'recentInvoices',
            'recentPurchases',
            'lowStockItems',

            'todayPendingAmount',
            'monthPendingAmount',
            'totalPendingAmount'
        ));
    }




    /**
 * Delete users who registered more than 10 days ago
 * but never created a business or started a plan.
 */
private function deleteAbandonedRegisteredUsers(?int $excludeUserId = null): int
{
    try {
        $cutoffDate = now()->subDays(10);

        /*
        |--------------------------------------------------------------------------
        | Base abandoned users query
        |--------------------------------------------------------------------------
        */

        $query = User::query()
            ->withTrashed()
            ->where('created_at', '<', $cutoffDate);

        /*
        |--------------------------------------------------------------------------
        | Current logged-in user ko delete nahi karna
        |--------------------------------------------------------------------------
        */

        if ($excludeUserId) {
            $query->where('id', '!=', $excludeUserId);
        }

        /*
        |--------------------------------------------------------------------------
        | Super admin / admin users ko delete nahi karna
        |--------------------------------------------------------------------------
        |
        | Agar users table mein role column hai tabhi ye condition lagegi.
        |
        */

        if (Schema::hasColumn('users', 'role')) {
            $query->where(function ($roleQuery) {
                $roleQuery
                    ->whereNull('role')
                    ->orWhereNotIn('role', [
                        'super_admin',
                        'admin',
                        'owner',
                    ]);
            });
        }

        /*
        |--------------------------------------------------------------------------
        | User ka koi business attached nahi hona chahiye
        |--------------------------------------------------------------------------
        */

        if (Schema::hasTable('business_user')) {
            $query->whereNotExists(function ($businessQuery) {
                $businessQuery
                    ->selectRaw('1')
                    ->from('business_user')
                    ->whereColumn(
                        'business_user.user_id',
                        'users.id'
                    );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Direct businesses.user_id check
        |--------------------------------------------------------------------------
        |
        | Kuch projects mein business_user pivot hota hai aur kuch mein
        | businesses table ke andar user_id/owner_id hota hai.
        |
        */

        if (
            Schema::hasTable('businesses')
            && Schema::hasColumn('businesses', 'user_id')
        ) {
            $query->whereNotExists(function ($businessQuery) {
                $businessQuery
                    ->selectRaw('1')
                    ->from('businesses')
                    ->whereColumn(
                        'businesses.user_id',
                        'users.id'
                    );
            });
        }

        if (
            Schema::hasTable('businesses')
            && Schema::hasColumn('businesses', 'owner_id')
        ) {
            $query->whereNotExists(function ($businessQuery) {
                $businessQuery
                    ->selectRaw('1')
                    ->from('businesses')
                    ->whereColumn(
                        'businesses.owner_id',
                        'users.id'
                    );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | User subscription check
        |--------------------------------------------------------------------------
        */

        if (
            Schema::hasTable('user_subscriptions')
            && Schema::hasColumn('user_subscriptions', 'user_id')
        ) {
            $query->whereNotExists(function ($subscriptionQuery) {
                $subscriptionQuery
                    ->selectRaw('1')
                    ->from('user_subscriptions')
                    ->whereColumn(
                        'user_subscriptions.user_id',
                        'users.id'
                    );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Alternative subscriptions table check
        |--------------------------------------------------------------------------
        */

        if (
            Schema::hasTable('subscriptions')
            && Schema::hasColumn('subscriptions', 'user_id')
        ) {
            $query->whereNotExists(function ($subscriptionQuery) {
                $subscriptionQuery
                    ->selectRaw('1')
                    ->from('subscriptions')
                    ->whereColumn(
                        'subscriptions.user_id',
                        'users.id'
                    );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | User packages table check
        |--------------------------------------------------------------------------
        */

        if (
            Schema::hasTable('user_packages')
            && Schema::hasColumn('user_packages', 'user_id')
        ) {
            $query->whereNotExists(function ($packageQuery) {
                $packageQuery
                    ->selectRaw('1')
                    ->from('user_packages')
                    ->whereColumn(
                        'user_packages.user_id',
                        'users.id'
                    );
            });
        }

        /*
        |--------------------------------------------------------------------------
        | Plan subscriptions table check
        |--------------------------------------------------------------------------
        */

        if (
            Schema::hasTable('plan_subscriptions')
            && Schema::hasColumn('plan_subscriptions', 'user_id')
        ) {
            $query->whereNotExists(function ($planQuery) {
                $planQuery
                    ->selectRaw('1')
                    ->from('plan_subscriptions')
                    ->whereColumn(
                        'plan_subscriptions.user_id',
                        'users.id'
                    );
            });
        }

        $deletedCount = 0;

        /*
        |--------------------------------------------------------------------------
        | Chunk mein users delete karna
        |--------------------------------------------------------------------------
        |
        | Isse bahut saare users hone par memory issue nahi hoga.
        |
        */

        $query
            ->orderBy('id')
            ->chunkById(100, function ($users) use (&$deletedCount) {
                foreach ($users as $abandonedUser) {
                    DB::transaction(function () use (
                        $abandonedUser,
                        &$deletedCount
                    ) {
                        /*
                        |--------------------------------------------------------------------------
                        | User se related safe pivot/session data cleanup
                        |--------------------------------------------------------------------------
                        */

                        if (Schema::hasTable('business_user')) {
                            DB::table('business_user')
                                ->where(
                                    'user_id',
                                    $abandonedUser->id
                                )
                                ->delete();
                        }

                        if (
                            Schema::hasTable('sessions')
                            && Schema::hasColumn('sessions', 'user_id')
                        ) {
                            DB::table('sessions')
                                ->where(
                                    'user_id',
                                    $abandonedUser->id
                                )
                                ->delete();
                        }

                        if (
                            Schema::hasTable('personal_access_tokens')
                            && Schema::hasColumn(
                                'personal_access_tokens',
                                'tokenable_id'
                            )
                        ) {
                            DB::table('personal_access_tokens')
                                ->where(
                                    'tokenable_type',
                                    User::class
                                )
                                ->where(
                                    'tokenable_id',
                                    $abandonedUser->id
                                )
                                ->delete();
                        }

                        /*
                        |--------------------------------------------------------------------------
                        | Permanently delete user
                        |--------------------------------------------------------------------------
                        */

                        $abandonedUser->forceDelete();

                        $deletedCount++;
                    });
                }
            });

        if ($deletedCount > 0) {
            Log::info('Abandoned registered users permanently deleted.', [
                'deleted_count' => $deletedCount,
                'cutoff_date'   => $cutoffDate->toDateTimeString(),
            ]);
        }

        return $deletedCount;
    } catch (\Throwable $exception) {
        /*
        |--------------------------------------------------------------------------
        | Cleanup error se dashboard ko crash nahi hone dena
        |--------------------------------------------------------------------------
        */

        Log::error('Abandoned user cleanup failed.', [
            'message' => $exception->getMessage(),
            'file'    => $exception->getFile(),
            'line'    => $exception->getLine(),
        ]);

        return 0;
    }
}
}