<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Invoice;
use App\Models\Item;
use App\Models\MetalRate;
use App\Models\Purchase;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DashboardController extends Controller
{

    public function index(Request $request)
    {
        
        $user = $request->user();
        if (!$user) abort(401);

        if (!$user->businesses()->exists()) {
            return redirect()
                ->route('no-business.whatsapp')
                ->with('info', 'Please configure WhatsApp API and send PDFs directly.');
        }

        $tz = config('app.timezone', 'Asia/Kolkata');

        // ✅ Date Filters (GET)
        // Default: month start -> today
        $today      = \Carbon\Carbon::now($tz)->startOfDay();
        $defaultFrom = \Carbon\Carbon::now($tz)->startOfMonth()->startOfDay();
        $defaultTo   = \Carbon\Carbon::now($tz)->endOfDay();

        $from = $request->filled('from')
            ? \Carbon\Carbon::parse($request->query('from'), $tz)->startOfDay()
            : $defaultFrom;

        $to = $request->filled('to')
            ? \Carbon\Carbon::parse($request->query('to'), $tz)->endOfDay()
            : $defaultTo;

        // ✅ safety: from > to swap
        if ($from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        // optional preset support (month/7d/today)
        $preset = strtolower(trim((string)$request->query('preset', '')));
        if ($preset === 'today') {
            $from = \Carbon\Carbon::now($tz)->startOfDay();
            $to   = \Carbon\Carbon::now($tz)->endOfDay();
        } elseif ($preset === '7d') {
            $from = \Carbon\Carbon::now($tz)->subDays(6)->startOfDay();
            $to   = \Carbon\Carbon::now($tz)->endOfDay();
        } elseif ($preset === 'month') {
            $from = \Carbon\Carbon::now($tz)->startOfMonth()->startOfDay();
            $to   = \Carbon\Carbon::now($tz)->endOfDay();
        }

        $bid = auth()->user()->current_business_id ?? session('active_business_id');
        $business = \App\Models\Business::find($bid);

        $invoiceQ  = \App\Models\Invoice::query();
        $purchaseQ = \App\Models\Purchase::query();
        $itemQ     = \App\Models\Item::query();
        $rateQ     = \App\Models\MetalRate::query();

        if ($bid) {
            $invoiceQ->where('business_id', $bid);
            $purchaseQ->where('business_id', $bid);
            $itemQ->where('business_id', $bid);
            $rateQ->where('business_id', $bid);
        }

        // ✅ SALES only TAX
        $salesQ = (clone $invoiceQ)->where(function ($q) {
            $q->where('invoice_type', 'tax')
                ->orWhereNull('invoice_type'); // optional
        });

        // -----------------------------------
        // ✅ SUMMARY (Range based)
        // -----------------------------------
        // "Today" cards ke liye today date
        $todayDate = \Carbon\Carbon::now($tz)->toDateString();

        $todaySalesAmount = (clone $salesQ)->whereDate('invoice_date', $todayDate)->sum('total');
        $todaySalesCount  = (clone $salesQ)->whereDate('invoice_date', $todayDate)->count();

        // ✅ Range sales (monthSalesAmount ko ab rangeSalesAmount bana do, ya same variable use karo)
        $monthSalesAmount = (clone $salesQ)->whereBetween('invoice_date', [$from, $to])->sum('total');
        $totalSalesAmount = (clone $salesQ)->sum('total');

        $todayPurchasesAmount = (clone $purchaseQ)->whereDate('invoice_date', $todayDate)->sum('total_amount');
        $monthPurchasesAmount = (clone $purchaseQ)->whereBetween('invoice_date', [$from, $to])->sum('total_amount');
        $totalPurchasesAmount = (clone $purchaseQ)->sum('total_amount');

        $totalItems    = (clone $itemQ)->count();
        $totalStockQty = (clone $itemQ)->sum('stock_qty');
        $lowStockCount = (clone $itemQ)->where('stock_qty', '<=', 2)->count();

        // today metal rates same
        $todayMetalRates = (clone $rateQ)
            ->whereDate('rate_date', $todayDate)
            ->where('is_active', true)
            ->get();

        $baseGoldPurities   = ['24K', '22K', '20K', '18K'];
        $baseSilverPurities = ['999', '995', '925'];

        $goldPurities = collect($baseGoldPurities)
            ->merge($todayMetalRates->where('metal_type', 'gold')->pluck('purity')->filter())
            ->unique()->values()->all();

        $silverPurities = collect($baseSilverPurities)
            ->merge($todayMetalRates->where('metal_type', 'silver')->pluck('purity')->filter())
            ->unique()->values()->all();

        $rateMap = $todayMetalRates
            ->keyBy(fn ($r) => strtolower($r->metal_type) . '|' . (string)($r->purity ?? ''))
            ->map->rate_per_gram
            ->toArray();

        // ✅ Recent lists (Range filter)
        $recentInvoices = (clone $salesQ)
            ->whereBetween('invoice_date', [$from, $to])
            ->with('client')
            ->latest('invoice_date')->latest('id')
            ->limit(5)->get();

        $recentPurchases = (clone $purchaseQ)
            ->whereBetween('invoice_date', [$from, $to])
            ->with('supplier')
            ->latest('invoice_date')->latest('id')
            ->limit(5)->get();

        $lowStockItems = (clone $itemQ)->with('category')
            ->where('stock_qty', '<=', 5)->orderBy('stock_qty')->limit(5)->get();

        // Pending / due (range + today)
        $todayPendingAmount = (clone $salesQ)->whereDate('invoice_date', $todayDate)->sum('balance');
        $monthPendingAmount = (clone $salesQ)->whereBetween('invoice_date', [$from, $to])->sum('balance');
        $totalPendingAmount = (clone $salesQ)->sum('balance');

        return view('dashboard', compact(
            'today', 'business',
            'from', 'to', 'preset', // ✅ send to blade
            'todaySalesAmount', 'todaySalesCount',
            'monthSalesAmount', 'totalSalesAmount',
            'todayPurchasesAmount', 'monthPurchasesAmount', 'totalPurchasesAmount',
            'totalItems', 'totalStockQty', 'lowStockCount',
            'todayMetalRates', 'goldPurities', 'silverPurities', 'rateMap',
            'recentInvoices', 'recentPurchases', 'lowStockItems',
            'todayPendingAmount', 'monthPendingAmount', 'totalPendingAmount'
        ));
    }


}
