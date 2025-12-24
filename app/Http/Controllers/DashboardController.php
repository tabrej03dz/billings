<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Item;
use App\Models\MetalRate;
use App\Models\Purchase;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DashboardController extends Controller
{
//    public function index()
//    {
//        $today      = Carbon::today();
//        $monthStart = Carbon::now()->startOfMonth();
//
//        $bid = auth()->user()->current_business_id ?? session('active_business_id');
//
//        $invoiceQ  = Invoice::query();
//        $purchaseQ = Purchase::query();
//        $itemQ     = Item::query();
//        $rateQ     = MetalRate::query();
//
//        if ($bid) {
//            $invoiceQ->where('business_id', $bid);
//            $purchaseQ->where('business_id', $bid);
//            $itemQ->where('business_id', $bid);
//            $rateQ->where('business_id', $bid);
//        }
//
//        // --- Sales ---
//        $todaySalesAmount = (clone $invoiceQ)->whereDate('invoice_date', $today)->sum('total');
//        $todaySalesCount  = (clone $invoiceQ)->whereDate('invoice_date', $today)->count();
//        $monthSalesAmount = (clone $invoiceQ)->whereBetween('invoice_date', [$monthStart, $today])->sum('total');
//        $totalSalesAmount = (clone $invoiceQ)->sum('total');
//
//        // --- Purchases ---
//        $todayPurchasesAmount = (clone $purchaseQ)->whereDate('invoice_date', $today)->sum('total_amount');
//        $monthPurchasesAmount = (clone $purchaseQ)->whereBetween('invoice_date', [$monthStart, $today])->sum('total_amount');
//        $totalPurchasesAmount = (clone $purchaseQ)->sum('total_amount');
//
//        // --- Items / stock ---
//        $totalItems    = (clone $itemQ)->count();
//        $totalStockQty = (clone $itemQ)->sum('stock_qty');
//        $lowStockCount = (clone $itemQ)->where('stock_qty', '<=', 2)->count();
//
//        // --- Today metal rates ---
//        $todayMetalRates = (clone $rateQ)
//            ->whereDate('rate_date', $today)
//            ->where('is_active', true)
//            ->get();
//
//        // Fixed purities for form
//        $goldPurities   = ['24K', '22K', '20K', '18K'];
//        $silverPurities = ['999', '995', '925'];
//
//        // Map existing rates -> easy prefill
//        $rateMap = $todayMetalRates
//            ->keyBy(fn ($r) => $r->metal_type.'|'.($r->purity ?? ''))
//            ->map->rate_per_gram
//            ->toArray();
//
//        // Recent lists
//        $recentInvoices = (clone $invoiceQ)->with('client')
//            ->latest('invoice_date')->latest('id')->limit(5)->get();
//
//        $recentPurchases = (clone $purchaseQ)->with('supplier')
//            ->latest('invoice_date')->latest('id')->limit(5)->get();
//
//        $lowStockItems = (clone $itemQ)->with('category')
//            ->where('stock_qty', '<=', 5)->orderBy('stock_qty')->limit(5)->get();
//
//        return view('dashboard', compact(
//            'today',
//            'todaySalesAmount', 'todaySalesCount',
//            'monthSalesAmount', 'totalSalesAmount',
//            'todayPurchasesAmount', 'monthPurchasesAmount', 'totalPurchasesAmount',
//            'totalItems', 'totalStockQty', 'lowStockCount',
//            'todayMetalRates', 'goldPurities', 'silverPurities', 'rateMap',
//            'recentInvoices', 'recentPurchases', 'lowStockItems'
//        ));
//    }

    public function index(Request $request)
    {
//        dd(Hash::make('NVst@050868'));
        $user = $request->user();

        if (!$user) {
            abort(401);
        }

        // user kisi bhi business ko belong nahi karta
        if (!$user->businesses()->exists()) {
            return redirect()
                ->route('no-business.whatsapp')
                ->with('info', 'Please configure WhatsApp API and send PDFs directly.');
        }
        $today      = Carbon::today();
        $monthStart = Carbon::now()->startOfMonth();

        $bid = auth()->user()->current_business_id ?? session('active_business_id');

        $invoiceQ  = Invoice::query();
        $purchaseQ = Purchase::query();
        $itemQ     = Item::query();
        $rateQ     = MetalRate::query();

        if ($bid) {
            $invoiceQ->where('business_id', $bid);
            $purchaseQ->where('business_id', $bid);
            $itemQ->where('business_id', $bid);
            $rateQ->where('business_id', $bid);
        }

        // --- Sales ---
        $todaySalesAmount = (clone $invoiceQ)->whereDate('invoice_date', $today)->sum('total');
        $todaySalesCount  = (clone $invoiceQ)->whereDate('invoice_date', $today)->count();
        $monthSalesAmount = (clone $invoiceQ)->whereBetween('invoice_date', [$monthStart, $today])->sum('total');
        $totalSalesAmount = (clone $invoiceQ)->sum('total');

        // --- Purchases ---
        $todayPurchasesAmount = (clone $purchaseQ)->whereDate('invoice_date', $today)->sum('total_amount');
        $monthPurchasesAmount = (clone $purchaseQ)->whereBetween('invoice_date', [$monthStart, $today])->sum('total_amount');
        $totalPurchasesAmount = (clone $purchaseQ)->sum('total_amount');

        // --- Items / stock ---
        $totalItems    = (clone $itemQ)->count();
        $totalStockQty = (clone $itemQ)->sum('stock_qty');
        $lowStockCount = (clone $itemQ)->where('stock_qty', '<=', 2)->count();

        // --- Today metal rates ---
        $todayMetalRates = (clone $rateQ)
            ->whereDate('rate_date', $today)
            ->where('is_active', true)
            ->get();

        // Base (fixed) purities for form
        $baseGoldPurities   = ['24K', '22K', '20K', '18K'];
        $baseSilverPurities = ['999', '995', '925'];

        // ✅ Merge: base + DB se aaye huye saare (custom) purities
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

        // Map existing rates -> easy prefill (gold|24K etc.)
        $rateMap = $todayMetalRates
            ->keyBy(function ($r) {
                return strtolower($r->metal_type) . '|' . (string) ($r->purity ?? '');
            })
            ->map
            ->rate_per_gram
            ->toArray();

        // Recent lists
        $recentInvoices = (clone $invoiceQ)->with('client')
            ->latest('invoice_date')->latest('id')->limit(5)->get();

        $recentPurchases = (clone $purchaseQ)->with('supplier')
            ->latest('invoice_date')->latest('id')->limit(5)->get();

        $lowStockItems = (clone $itemQ)->with('category')
            ->where('stock_qty', '<=', 5)->orderBy('stock_qty')->limit(5)->get();

        return view('dashboard', compact(
            'today',
            'todaySalesAmount', 'todaySalesCount',
            'monthSalesAmount', 'totalSalesAmount',
            'todayPurchasesAmount', 'monthPurchasesAmount', 'totalPurchasesAmount',
            'totalItems', 'totalStockQty', 'lowStockCount',
            'todayMetalRates', 'goldPurities', 'silverPurities', 'rateMap',
            'recentInvoices', 'recentPurchases', 'lowStockItems'
        ));
    }

}
