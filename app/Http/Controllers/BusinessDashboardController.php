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
use Illuminate\Support\Str;

class BusinessDashboardController extends Controller
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
        | Resolve active business type
        |--------------------------------------------------------------------------
        |
        | Service business ke dashboard par stock-related cards, charts aur tables
        | nahi dikhaye jayenge. business_type_id aur old type column dono support
        | kiye gaye hain.
        |
        */

        $businessTypeName = '';

        if (!empty($business->type)) {
            $businessTypeName = (string) DB::table('business_types')
                ->where('id', $business->type)
                ->value('name');
        }

        if ($businessTypeName === '') {
            $businessTypeName = (string) ($business->type ?? '');
        }

        $isServiceBusiness = Str::slug($businessTypeName) === 'services' || Str::slug($businessTypeName) === 'service';

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
        $lowStockLimit = 5;

        $totalItems = (clone $itemQ)->count();

        $totalStockQty = 0;
        $healthyStockCount = 0;
        $lowStockCount = 0;
        $outOfStockCount = 0;

        if (!$isServiceBusiness) {
            $totalStockQty = (clone $itemQ)
                ->sum('stock_qty');

            $healthyStockCount = (clone $itemQ)
                ->where('stock_qty', '>', $lowStockLimit)
                ->count();

            $lowStockCount = (clone $itemQ)
                ->where('stock_qty', '>', 0)
                ->where('stock_qty', '<=', $lowStockLimit)
                ->count();

            $outOfStockCount = (clone $itemQ)
                ->where('stock_qty', '<=', 0)
                ->count();
        }

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
        $lowStockItems = collect();

        if (!$isServiceBusiness) {
            $lowStockItems = (clone $itemQ)
                ->with('category')
                ->where('stock_qty', '>', 0)
                ->where('stock_qty', '<=', $lowStockLimit)
                ->orderBy('stock_qty')
                ->orderBy('name')
                ->limit(5)
                ->get();
        }

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
        | Dashboard charts
        |--------------------------------------------------------------------------
        |
        | Selected date range ke andar daily sales aur purchase trend banaya ja
        | raha hai. Missing dates ko zero value ke saath fill kiya gaya hai, taaki
        | graph continuous aur readable rahe.
        |
        */

        $salesTrendRows = (clone $salesQ)
            ->whereBetween('invoice_date', [$from, $to])
            ->selectRaw('DATE(invoice_date) as chart_date, COALESCE(SUM(total), 0) as chart_total')
            ->groupByRaw('DATE(invoice_date)')
            ->orderByRaw('DATE(invoice_date)')
            ->pluck('chart_total', 'chart_date');

        $purchaseTrendRows = (clone $purchaseQ)
            ->whereBetween('invoice_date', [$from, $to])
            ->selectRaw('DATE(invoice_date) as chart_date, COALESCE(SUM(total_amount), 0) as chart_total')
            ->groupByRaw('DATE(invoice_date)')
            ->orderByRaw('DATE(invoice_date)')
            ->pluck('chart_total', 'chart_date');

        $chartLabels = [];
        $salesChartData = [];
        $purchaseChartData = [];

        $chartCursor = $from->copy()->startOfDay();
        $chartEnd = $to->copy()->startOfDay();

        while ($chartCursor->lte($chartEnd)) {
            $dateKey = $chartCursor->toDateString();

            $chartLabels[] = $chartCursor->format('d M');
            $salesChartData[] = round((float) ($salesTrendRows[$dateKey] ?? 0), 2);
            $purchaseChartData[] = round((float) ($purchaseTrendRows[$dateKey] ?? 0), 2);

            $chartCursor->addDay();
        }

        /* Payment collection chart */
        $filteredSalesAmount = round((float) $monthSalesAmount, 2);
        $filteredPendingAmount = round((float) $monthPendingAmount, 2);
        $filteredCollectedAmount = max(
            round($filteredSalesAmount - $filteredPendingAmount, 2),
            0
        );

        $paymentChartData = [
            $filteredCollectedAmount,
            $filteredPendingAmount,
        ];

        /* Stock overview chart */
        $stockChartData = [
            $healthyStockCount,
            $lowStockCount,
            $outOfStockCount,
        ];

        /*
        |--------------------------------------------------------------------------
        | Dashboard getting-started suggestion
        |--------------------------------------------------------------------------
        |
        | Item, tax invoice aur purchase me se jis module me abhi tak record nahi
        | bana hai, dashboard par uska quick action suggestion dikhaya jayega.
        |
        */

        $dashboardItemCount = (clone $itemQ)->count();

        $dashboardInvoiceCount = (clone $salesQ)->count();

        $dashboardPurchaseCount = (clone $purchaseQ)->count();

        $showDashboardSuggestion =
            $dashboardItemCount < 1
            || $dashboardInvoiceCount < 1
            || $dashboardPurchaseCount < 1;

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
            'businessTypeName',
            'isServiceBusiness',

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
            'lowStockLimit',
            'healthyStockCount',
            'lowStockCount',
            'outOfStockCount',

            'todayMetalRates',
            'goldPurities',
            'silverPurities',
            'rateMap',

            'recentInvoices',
            'recentPurchases',
            'lowStockItems',

            'todayPendingAmount',
            'monthPendingAmount',
            'totalPendingAmount',

            'dashboardItemCount',
            'dashboardInvoiceCount',
            'dashboardPurchaseCount',
            'showDashboardSuggestion',

            'chartLabels',
            'salesChartData',
            'purchaseChartData',
            'paymentChartData',
            'stockChartData',
            'filteredCollectedAmount',
            'filteredPendingAmount',
        ));
    }
}