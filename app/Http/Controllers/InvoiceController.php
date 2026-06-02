<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\BillRequest;
use App\Models\Business;
use App\Models\Category;
use App\Models\Invoice;
use App\Models\Client;
use App\Models\InvoiceItem;
use App\Models\InvoicePayment;
use App\Models\Item;
use App\Models\MetalRate;
use App\Models\UserPlan;
use App\Services\StockService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;


class InvoiceController extends Controller
{

    protected StockService $stock;   // 👈 ADD

    public function __construct(StockService $stock)   // 👈 ADD
    {
        $this->stock = $stock;
    }


    public function index(Request $request)
    {
        $me  = $request->user();

        // ✅ Active business resolve
        $bid = $me->current_business_id ?? session('active_business_id');
        if (!$bid) {
            $bid = $me->businesses()->pluck('businesses.id')->first();
        }
        if (!$bid) {
            return back()->withErrors(['business' => 'Active business select/attach नहीं है.']);
        }

        // ✅ Tab type (tax / proforma / quotation)
        $type = strtolower(trim((string) $request->get('type', 'tax')));
        if (!in_array($type, ['tax', 'proforma', 'quotation'], true)) {
            $type = 'tax';
        }

        // ✅ Permission mapping by type
        $permByType = [
            'tax'       => 'show invoices',
            'proforma'  => 'show proformas',
            'quotation' => 'show quotations',
        ];

        $requiredPerm = $permByType[$type] ?? 'show invoices';

        // ✅ Permission check
        if (!$me->can($requiredPerm)) {
            abort(403, "You don't have permission: {$requiredPerm}");
            // or: return back()->with('error', "Permission denied: {$requiredPerm}");
        }

        // ✅ Filters
        $search   = trim((string)$request->get('search', ''));
        $fromDate = $request->get('from_date');
        $toDate   = $request->get('to_date');
        $status   = $request->get('status');

        $q = \App\Models\Invoice::query()
            ->with(['client:id,name','createdBy','updatedBy'])
            ->where('business_id', $bid)
            ->where('invoice_type', $type);

        // ✅ Search (invoice_number OR client name)

        if ($search !== '') {
            $q->where(function ($w) use ($search) {

                // invoice number search
                $w->where('invoice_number', 'like', "%{$search}%")
                // ✅ amount search
                ->orWhere('total', 'like', "%{$search}%")
                ->orWhere('balance', 'like', "%{$search}%")
                ->orWhere('received_amount', 'like', "%{$search}%")
                // client details search
                ->orWhereHas('client', function ($c) use ($search) {
                    $c->where('name', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhere('gstin', 'like', "%{$search}%")
                    ->orWhere('pan', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
                });

            });
        }

        // ✅ Date filter
        if (!empty($fromDate)) $q->whereDate('invoice_date', '>=', $fromDate);
        if (!empty($toDate))   $q->whereDate('invoice_date', '<=', $toDate);

        // ✅ Status filter
        if (!empty($status)) {
            if ($status === 'paid') {
                $q->where('balance', '<=', 0);
            } elseif ($status === 'unpaid') {
                $q->where('received_amount', '<=', 0);
            } elseif ($status === 'partial') {
                $q->where('received_amount', '>', 0)->where('balance', '>', 0);
            }
        }

        $invoices = $q->orderByDesc('invoice_date')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        // ✅ Counts (optional) - but ONLY if user has permission for that tab
        $taxCount = $me->can('show invoices')
            ? \App\Models\Invoice::where('business_id', $bid)->where('invoice_type', 'tax')->count()
            : null;

        $proCount = $me->can('show proformas')
            ? \App\Models\Invoice::where('business_id', $bid)->where('invoice_type', 'proforma')->count()
            : null;

        $quoCount = $me->can('show quotations')
            ? \App\Models\Invoice::where('business_id', $bid)->where('invoice_type', 'quotation')->count()
            : null;

        return view('invoices.index', [
            'invoices'   => $invoices,
            'type'       => $type,
            'taxCount'   => $taxCount,
            'proCount'   => $proCount,
            'quoCount'   => $quoCount,
        ]);
    }


    public function create(Request $request, $type = 'proforma')
    {
        $today = now()->toDateString();

        // ✅ detect doc type from route OR query
        $docType = strtolower(trim((string)$type));
        if (!in_array($docType, ['tax','proforma','quotation'], true)) {
            $docType = 'proforma';
        }

        // Active business resolve
        $bid = $request->user()->current_business_id ?? session('active_business_id');
        if (!$bid) {
            $bid = $request->user()->businesses()->pluck('businesses.id')->first();
        }

        // $business = Business::findOrFail($bid);

        $business = Business::with('businessType.itemFields')->findOrFail($bid);

        $allowedFields = [];

        if ($business && $business->businessType) {
            $allowedFields = $business->businessType->itemFields
                ->pluck('field_name')
                ->toArray();
        }

        // ✅ base prefix (tax => business setting, proforma => PF, quotation => QT)
        $taxBase = optional(
                $request->user()->businesses()->where('businesses.id', $bid)->first()
            )->invoice_base_prefix ?? 'RV/SL';

        if ($docType === 'proforma') {
            $base = 'PF';
        } elseif ($docType === 'quotation') {
            $base = 'QT';
        } else {
            $base = $taxBase;
        }

        $suggestedPrefix = \App\Services\InvoiceNumber::previewPrefix($today, $base);

        // ✅ Clients
        $clients = Client::where('business_id', $bid)->where('is_save', true)
            ->orderBy('name')
            ->get(['id','name','mobile','address','state','state_code','gstin']);

        // ✅ Items

        $items = Item::where('items.business_id', $bid)
        ->where('items.is_active', true)
        ->leftJoin('invoice_items', 'items.id', '=', 'invoice_items.item_id')
        ->select(
            'items.id',
            'items.name',
            'items.type',
            'items.sku',
            'items.description',
            'items.tax_rate',
            'items.making_charge',
            'items.sac',
            'items.gold_weight',
            'items.gold_purity',
            'items.silver_weight',
            'items.silver_purity',
            'items.stone_weight',
            'items.stone_charges',
            'items.diamond_weight',
            'items.making_charge_type',
            'items.making_charge',
            'items.price',
            DB::raw('COALESCE(SUM(invoice_items.quantity),0) as total_sold')
        )
        ->groupBy(
            'items.id',
            'items.name',
            'items.type',
            'items.sku',
            'items.description',
            'items.tax_rate',
            'items.making_charge',
            'items.sac',
            'items.gold_weight',
            'items.gold_purity',
            'items.silver_weight',
            'items.silver_purity',
            'items.stone_weight',
            'items.stone_charges',
            'items.diamond_weight',
            'items.diamond_charges',
            'items.price',
            'items.making_charge_type',
            'items.making_charge',
        )
        ->orderByDesc('total_sold') // 🔥 Most sold first
        ->get();

        $categories = Category::where('business_id', $bid)
            ->orderBy('name')
            ->get(['id', 'name']);
        // ✅ Metal rates
        $metalRates = MetalRate::where('business_id', $bid)
            ->whereDate('rate_date', $today)
            ->where('is_active', true)
            ->get(['metal_type','purity','rate_per_gram']);

        // ✅ preview number (tax/proforma/quotation different sequence)
        $preview = \App\Services\InvoiceNumber::peek($bid, $today, $suggestedPrefix, 3, $docType);

        $banks = BankAccount::where('business_id', $bid)
            ->orderBy('bank_name')
            ->get(['id','bank_name','account_holder','account_no','ifsc']);

        return view('invoices.create_kapoor_style', [
            'today'              => $today,
            'clientsJson'        => $clients->values()->toJson(),
            'itemsJson'          => $items->values()->toJson(),
            'categoriesJson' => $categories->values()->toJson(),
            'metalRatesJson'     => $metalRates->values()->toJson(),
            'banksJson'          => $banks->values()->toJson(),

            'suggestedPrefix'    => $suggestedPrefix,
            'basePrefix'         => $base,
            'initialInvoiceNo'   => $preview['full'] ?? 'Auto',
            'defaultTerms'       => $business->terms,

            'businessState'      => $business->state,
            'businessStateCode'  => $business->state_code,
            'businessGstin'      => $business->gstin,
            'businessName'      => $business->name,
            'businessSignature'  => $business->signature,

            // ✅ NEW
            'docType'            => $docType,
            'allowedFields' => $allowedFields,
        ]);
    }




    // public function edit(Request $request, \App\Models\Invoice $invoice)
    // {
    //     // ✅ relations load
    //     $invoice = $invoice->load([
    //         'client',
    //         'items',      // invoice_items
    //         'business',
    //         'payments'    // if exists
    //     ]);

    //     // ✅ Active business resolve
    //     $bid = $request->user()->current_business_id ?? session('active_business_id');
    //     if (!$bid) $bid = $invoice->business_id;

    //     // ✅ Safety
    //     if ((int)$invoice->business_id !== (int)$bid) {
    //         abort(403, 'Unauthorized invoice access.');
    //     }

    //     $today = \Carbon\Carbon::parse($invoice->invoice_date)->toDateString();

    //     // ✅ detect doc type from invoice (tax/proforma/quotation)
    //     $docType = strtolower(trim((string)($invoice->invoice_type ?? 'tax')));
    //     if (!in_array($docType, ['tax','proforma','quotation'], true)) $docType = 'tax';

    //     $business = \App\Models\Business::findOrFail($bid);

    //     // ✅ base prefix (tax => business setting, proforma => PF, quotation => QT)
    //     $taxBase = optional(
    //             $request->user()->businesses()->where('businesses.id', $bid)->first()
    //         )->invoice_base_prefix ?? 'RV/SL';

    //     if ($docType === 'proforma') {
    //         $base = 'PF';
    //     } elseif ($docType === 'quotation') {
    //         $base = 'QT';
    //     } else {
    //         $base = $taxBase;
    //     }

    //     // ✅ suggested prefix (for display only; edit me hum existing prefix hi use karenge)
    //     $suggestedPrefix = \App\Services\InvoiceNumber::previewPrefix($today, $base);

    //     // ✅ Clients
    //     $clients = \App\Models\Client::where('business_id', $bid)->where('is_save', true)->orWhere('id', $invoice->client_id)
    //         ->orderBy('name')
    //         ->get(['id','name','mobile','address','state','state_code','gstin','pincode']);

    //     // ✅ Items master
    //     // $items = \App\Models\Item::where('business_id', $bid)
    //     //     ->where('is_active', true)
    //     //     ->orderBy('name')
    //     //     ->get([
    //     //         'id','name','type','sku','description','tax_rate','making_charge','sac',
    //     //         'gold_weight','gold_purity',
    //     //         'silver_weight','silver_purity',
    //     //         'stone_weight','stone_charges',
    //     //         'diamond_weight','diamond_charges',
    //     //         'price'
    //     //     ]);

    //     $items = Item::where('items.business_id', $bid)
    //     ->where('items.is_active', true)
    //     ->leftJoin('invoice_items', 'items.id', '=', 'invoice_items.item_id')
    //     ->select(
    //         'items.id',
    //         'items.name',
    //         'items.type',
    //         'items.sku',
    //         'items.description',
    //         'items.tax_rate',
    //         'items.making_charge',
    //         'items.sac',
    //         'items.gold_weight',
    //         'items.gold_purity',
    //         'items.silver_weight',
    //         'items.silver_purity',
    //         'items.stone_weight',
    //         'items.stone_charges',
    //         'items.diamond_weight',
    //         'items.diamond_charges',
    //         'items.price',
    //         DB::raw('COALESCE(SUM(invoice_items.quantity),0) as total_sold')
    //     )
    //     ->groupBy(
    //         'items.id',
    //         'items.name',
    //         'items.type',
    //         'items.sku',
    //         'items.description',
    //         'items.tax_rate',
    //         'items.making_charge',
    //         'items.sac',
    //         'items.gold_weight',
    //         'items.gold_purity',
    //         'items.silver_weight',
    //         'items.silver_purity',
    //         'items.stone_weight',
    //         'items.stone_charges',
    //         'items.diamond_weight',
    //         'items.diamond_charges',
    //         'items.price'
    //     )
    //     ->orderByDesc('total_sold') // 🔥 Most sold first
    //     ->get();
    //     // ✅ Metal rates
    //     $metalRates = \App\Models\MetalRate::where('business_id', $bid)
    //         ->whereDate('rate_date', $today)
    //         ->where('is_active', true)
    //         ->get(['metal_type','purity','rate_per_gram']);

    //     // ✅ Banks
    //     $banks = \App\Models\BankAccount::where('business_id', $bid)
    //         ->orderBy('bank_name')
    //         ->get(['id','bank_name','account_holder','account_no','ifsc']);

    //     // ✅ invoice JSON for prefill
    //     $invoiceJson = [
    //         'id' => $invoice->id,
    //         'client_id' => $invoice->client_id,
    //         'invoice_date' => $today,
    //         'invoice_number' => (string)($invoice->invoice_number ?? ''),
    //         'invoice_prefix' => (string)($invoice->invoice_prefix ?? $suggestedPrefix),

    //         'gst_no' => (string)($invoice->gst_no ?? ''),
    //         'transport_mode' => (string)($invoice->transport_mode ?? ''),
    //         'reverse_charge' => (int)($invoice->reverse_charge ?? 0),

    //         // totals / extras
    //         'discount_type' => (string)($invoice->discount_type ?? 'flat'),
    //         'discount_value' => (float)($invoice->discount_value ?? 0),

    //         'charges_json' => $invoice->charges_json ?? null, // string/array (jo aap store kar rahe)
    //         'charge_total' => (float)($invoice->charge_total ?? 0),

    //         'tcs_percent' => (float)($invoice->tcs_percent ?? 0),
    //         'tcs_amount'  => (float)($invoice->tcs_amount ?? 0),

    //         'round_off' => (float)($invoice->round_off ?? 0),

    //         'payment_method' => (string)($invoice->payment_method ?? 'cash'),
    //         'bank_account_id' => $invoice->bank_account_id ?? null,

    //         // received fields (agar aapke table me hai)
    //         'received' => (float)($invoice->received ?? 0),

    //         // invoice items map
    //         'items' => $invoice->items->map(function($it){
    //             return [
    //                 'item_id' => $it->item_id ?? null,
    //                 'item_type' => $it->item_type ?? null,

    //                 'description' => $it->description ?? '',
    //                 'hsn' => $it->hsn ?? '',
    //                 'quantity' => (float)($it->quantity ?? 1),

    //                 'making_rate' => (float)($it->making_rate ?? 0),
    //                 'gold_purity' => $it->gold_purity ?? null,
    //                 'silver_purity' => $it->silver_purity ?? null,

    //                 'gold_rate' => (float)($it->gold_rate ?? 0),
    //                 'silver_rate' => (float)($it->silver_rate ?? 0),
    //                 'silver_wt' => (float)($it->silver_wt ?? 0),
    //                 'gold_wt' => (float)($it->gold_wt ?? 0),
    //                 'gemstone_wt' => (float)($it->gemstone_wt ?? 0),
    //                 'diamond_wt' => (float)($it->diamond_wt ?? 0),

    //                 // 'service_rate' => (float)($it->service_rate ?? 0),

    //                'service_rate' => 
    //              (float)($it->rate ?? $it->making_charge ?? 0),
                   

    //                 'tax_percent' => (float)($it->tax_percent ?? 0),

    //                 // for edit screen: amount final (tax included)
    //                 'amount' => (float)($it->amount ?? 0),
    //             ];
    //         })->values(),
    //     ];


    //     return view('invoices.edit_kapoor_style', [
    //         'invoice'            => $invoice,

    //         'today'              => $today,
    //         'clientsJson'        => $clients->values()->toJson(),
    //         'itemsJson'          => $items->values()->toJson(),
    //         'metalRatesJson'     => $metalRates->values()->toJson(),
    //         'banksJson'          => $banks->values()->toJson(),

    //         'suggestedPrefix'    => $suggestedPrefix,
    //         'basePrefix'         => $base,
    //         'defaultTerms'       => $business->terms,

    //         'businessState'      => $business->state,
    //         'businessStateCode'  => $business->state_code,
    //         'businessGstin'      => $business->gstin,

    //         'docType'            => $docType,

    //         // ✅ prefill
    //         'invoiceJson'        => json_encode($invoiceJson, JSON_UNESCAPED_UNICODE),
    //     ]);
    // }

//     public function edit(Request $request, \App\Models\Invoice $invoice)
// {
//     // ✅ Relations load
//     // NOTE: InvoiceItem model me item() relation hona chahiye:
//     // public function item(){ return $this->belongsTo(\App\Models\Item::class, 'item_id'); }
//     $invoice = $invoice->load([
//         'client',
//         'items.item',
//         'business',
//         'payments',
//     ]);

//     // ✅ Active business resolve
//     $bid = $request->user()->current_business_id ?? session('active_business_id');

//     if (!$bid) {
//         $bid = $invoice->business_id;
//     }

//     // ✅ Safety
//     if ((int) $invoice->business_id !== (int) $bid) {
//         abort(403, 'Unauthorized invoice access.');
//     }

//     $today = \Carbon\Carbon::parse($invoice->invoice_date)->toDateString();

//     // ✅ Detect doc type
//     $docType = strtolower(trim((string) ($invoice->invoice_type ?? 'tax')));

//     if (!in_array($docType, ['tax', 'proforma', 'quotation'], true)) {
//         $docType = 'tax';
//     }

//     $business = \App\Models\Business::findOrFail($bid);

//     // ✅ Base prefix
//     $taxBase = optional(
//         $request->user()
//             ->businesses()
//             ->where('businesses.id', $bid)
//             ->first()
//     )->invoice_base_prefix ?? 'RV/SL';

//     if ($docType === 'proforma') {
//         $base = 'PF';
//     } elseif ($docType === 'quotation') {
//         $base = 'QT';
//     } else {
//         $base = $taxBase;
//     }

//     $suggestedPrefix = \App\Services\InvoiceNumber::previewPrefix($today, $base);

//     // ✅ Clients
//     $clients = \App\Models\Client::where('business_id', $bid)
//         ->where(function ($q) use ($invoice) {
//             $q->where('is_save', true)
//                 ->orWhere('id', $invoice->client_id);
//         })
//         ->orderBy('name')
//         ->get([
//             'id',
//             'name',
//             'mobile',
//             'address',
//             'state',
//             'state_code',
//             'gstin',
//             'pincode',
//         ]);

//     // ✅ Items master with most sold sorting
//     $items = \App\Models\Item::where('items.business_id', $bid)
//         ->where('items.is_active', true)
//         ->leftJoin('invoice_items', 'items.id', '=', 'invoice_items.item_id')
//         ->select(
//             'items.id',
//             'items.name',
//             'items.type',
//             'items.sku',
//             'items.description',
//             'items.tax_rate',
//             'items.making_charge',
//             'items.sac',
//             'items.gold_weight',
//             'items.gold_purity',
//             'items.silver_weight',
//             'items.silver_purity',
//             'items.stone_weight',
//             'items.stone_charges',
//             'items.diamond_weight',
//             'items.diamond_charges',
//             'items.price',
//             \DB::raw('COALESCE(SUM(invoice_items.quantity),0) as total_sold')
//         )
//         ->groupBy(
//             'items.id',
//             'items.name',
//             'items.type',
//             'items.sku',
//             'items.description',
//             'items.tax_rate',
//             'items.making_charge',
//             'items.sac',
//             'items.gold_weight',
//             'items.gold_purity',
//             'items.silver_weight',
//             'items.silver_purity',
//             'items.stone_weight',
//             'items.stone_charges',
//             'items.diamond_weight',
//             'items.diamond_charges',
//             'items.price'
//         )
//         ->orderByDesc('total_sold')
//         ->orderBy('items.name')
//         ->get();

//     // ✅ Metal rates
//     $metalRates = \App\Models\MetalRate::where('business_id', $bid)
//         ->whereDate('rate_date', $today)
//         ->where('is_active', true)
//         ->get([
//             'metal_type',
//             'purity',
//             'rate_per_gram',
//         ]);

//     // ✅ Banks
//     $banks = \App\Models\BankAccount::where('business_id', $bid)
//         ->orderBy('bank_name')
//         ->get([
//             'id',
//             'bank_name',
//             'account_holder',
//             'account_no',
//             'ifsc',
//         ]);

//     // ✅ Existing charges decode
//     $chargesJson = $invoice->charges_json ?? null;

//     if (is_string($chargesJson)) {
//         $decodedCharges = json_decode($chargesJson, true);
//         $chargesJson = is_array($decodedCharges) ? $decodedCharges : [];
//     }

//     if (!is_array($chargesJson)) {
//         $chargesJson = [];
//     }

//     // ✅ Payment data
//     $payment = $invoice->payments->first();

//     $receivedAmount = (float) (
//         $invoice->received_amount
//         ?? $payment->received_total
//         ?? 0
//     );

//     $paymentMode = (string) (
//         $invoice->payment_method
//         ?? 'cash'
//     );

//     // ✅ Invoice JSON for Alpine prefill
//     $invoiceJson = [
//         'id' => $invoice->id,
//         'client_id' => $invoice->client_id,
//         'invoice_date' => $today,
//         'invoice_number' => (string) ($invoice->invoice_number ?? ''),
//         'invoice_prefix' => (string) ($invoice->invoice_prefix ?? $suggestedPrefix),

//         'gst_no' => (string) ($invoice->gst_no ?? ''),
//         'transport_mode' => (string) ($invoice->transport_mode ?? 'By Hand'),
//         'reverse_charge' => (int) ($invoice->reverse_charge ?? 0),

//         'discount_type' => (string) ($invoice->discount_type ?? 'flat'),
//         'discount_value' => (float) (
//             $invoice->discount_value
//             ?? $invoice->discount_total
//             ?? 0
//         ),

//         'charges_json' => $chargesJson,
//         'charge_total' => (float) ($invoice->charge_total ?? 0),

//         'tcs_percent' => (float) ($invoice->tcs_percent ?? 0),
//         'tcs_amount' => (float) ($invoice->tcs_amount ?? 0),

//         'round_off' => (float) ($invoice->round_off ?? 0),

//         'payment_method' => $paymentMode,
//         'bank_account_id' => $invoice->bank_account_id ?? null,

//         'received' => $receivedAmount,

//         'pay_cash' => (float) ($payment->cash_amount ?? 0),
//         'pay_upi' => (float) ($payment->online_amount ?? 0),
//         'pay_card' => (float) ($payment->card_amount ?? 0),
//         'pay_cheque' => (float) ($payment->cheque_amount ?? 0),
//         'credit_sales_excess' => (float) ($payment->credit_sales_excess_amount ?? 0),
//         'advance_amount' => (float) ($payment->advance_amount ?? 0),

//         'online_mode' => (string) ($payment->online_mode ?? ''),
//         'online_ref' => (string) ($payment->online_ref ?? ''),
//         'upi_id' => (string) ($payment->upi_id ?? ''),
//         'card_last4' => (string) ($payment->card_last4 ?? ''),
//         'card_ref' => (string) ($payment->card_ref ?? ''),
//         'cheque_no' => (string) ($payment->cheque_no ?? ''),
//         'bank_name' => (string) ($payment->bank_name ?? ''),

//         // ✅ Important: item_type master item se detect hoga
//         'items' => $invoice->items->map(function ($it) {
//             $master = $it->item ?? null;

//             $type = strtolower(trim((string) (
//                 $master->type
//                 ?? $it->item_type
//                 ?? 'product'
//             )));

//             if (!in_array($type, ['product', 'service'], true)) {
//                 $type = 'product';
//             }

//             $hsn = $it->hsn_code ?? $it->sac_code ?? '';

//             $qty = (float) ($it->quantity ?? 1);
//             if ($qty <= 0) {
//                 $qty = 1;
//             }

//             $taxPercent = (float) ($it->tax_percent ?? 0);

//             // invoice_items.amount me final amount hai
//             $finalAmount = (float) ($it->amount ?? 0);

//             // invoice_items.rate me taxable/base amount hai
//             $baseAmount = (float) ($it->rate ?? 0);

//             // fixed price per unit nikalna
//             $masterPrice = (float) ($master->price ?? 0);
//             $fixedPrice = $masterPrice > 0
//                 ? $masterPrice
//                 : ($qty > 0 ? round($baseAmount / $qty, 2) : 0);

//             // Service rate per unit
//             $serviceRate = 0;
//             if ($type === 'service') {
//                 $serviceRate = $masterPrice > 0
//                     ? $masterPrice
//                     : ($qty > 0 ? round($baseAmount / $qty, 2) : (float) ($it->making_charge ?? 0));
//             }

//             $searchName = '';
//             if ($master) {
//                 $searchName = $master->sku
//                     ? ($master->name . ' (' . $master->sku . ')')
//                     : $master->name;
//             }

//             return [
//                 '_k' => now()->timestamp . rand(1000, 9999),

//                 'item_id' => $it->item_id ?? null,
//                 'item_type' => $type,

//                 'search' => $searchName,

//                 'ddOpen' => false,
//                 'ddHi' => 0,
//                 'ddStyle' => '',
//                 'ddPreviewName' => '',
//                 'ddPreview' => '',

//                 'description' => (string) ($it->description ?? ''),
//                 'hsn' => (string) $hsn,
//                 'quantity' => $qty,

//                 'making_rate' => $type === 'product'
//                     ? (float) ($it->making_rate ?? 0)
//                     : 0,

//                 'gold_purity' => $master->gold_purity ?? null,
//                 'silver_purity' => $master->silver_purity ?? null,

//                 'gold_rate' => (float) ($it->gold_rate ?? 0),
//                 'silver_rate' => (float) ($it->silver_rate ?? 0),

//                 'silver_wt' => (float) ($it->silver_wt ?? 0),
//                 'gold_wt' => (float) ($it->gold_wt ?? 0),

//                 'gemstone_wt' => (float) ($it->gemstone_wt_ct ?? 0),
//                 'diamond_wt' => (float) ($it->diamond_wt_ct ?? 0),

//                 // ✅ fixed price support
//                 'fixed_price' => $type === 'product' ? $fixedPrice : 0,

//                 'service_rate' => $type === 'service' ? $serviceRate : 0,

//                 'tax_percent' => $taxPercent,

//                 // ✅ edit screen me existing invoice amount same dikhe
//                 'amount_mode' => 'manual',
//                 'manual_amount' => $finalAmount,
//                 'amount' => $finalAmount,
//             ];
//         })->values(),
//     ];

//     return view('invoices.edit_kapoor_style', [
//         'invoice' => $invoice,

//         'today' => $today,
//         'clientsJson' => $clients->values()->toJson(),
//         'itemsJson' => $items->values()->toJson(),
//         'metalRatesJson' => $metalRates->values()->toJson(),
//         'banksJson' => $banks->values()->toJson(),

//         'suggestedPrefix' => $suggestedPrefix,
//         'basePrefix' => $base,
//         'defaultTerms' => $business->terms,

//         'businessState' => $business->state,
//         'businessStateCode' => $business->state_code,
//         'businessGstin' => $business->gstin,

//         'docType' => $docType,

//         'invoiceJson' => json_encode($invoiceJson, JSON_UNESCAPED_UNICODE),
//     ]);
// }

public function edit(Request $request, \App\Models\Invoice $invoice)
{
    $invoice = $invoice->load([
        'client',
        'items.item',
        'business',
        'payments',
    ]);

    $bid = $request->user()->current_business_id ?? session('active_business_id');

    if (!$bid) {
        $bid = $invoice->business_id;
    }

    if ((int) $invoice->business_id !== (int) $bid) {
        abort(403, 'Unauthorized invoice access.');
    }

    $today = \Carbon\Carbon::parse($invoice->invoice_date)->toDateString();

    $docType = strtolower(trim((string) ($invoice->invoice_type ?? 'tax')));

    if (!in_array($docType, ['tax', 'proforma', 'quotation'], true)) {
        $docType = 'tax';
    }

    // $business = \App\Models\Business::findOrFail($bid);

    $business = \App\Models\Business::with('businessType.itemFields')->findOrFail($bid);

    $allowedFields = [];

    if ($business && $business->businessType) {
        $allowedFields = $business->businessType->itemFields
            ->pluck('field_name')
            ->toArray();
    }

    $taxBase = optional(
        $request->user()
            ->businesses()
            ->where('businesses.id', $bid)
            ->first()
    )->invoice_base_prefix ?? 'RV/SL';

    if ($docType === 'proforma') {
        $base = 'PF';
    } elseif ($docType === 'quotation') {
        $base = 'QT';
    } else {
        $base = $taxBase;
    }

    $suggestedPrefix = \App\Services\InvoiceNumber::previewPrefix($today, $base);

    $clients = \App\Models\Client::where('business_id', $bid)
        ->where(function ($q) use ($invoice) {
            $q->where('is_save', true)
                ->orWhere('id', $invoice->client_id);
        })
        ->orderBy('name')
        ->get([
            'id',
            'name',
            'mobile',
            'address',
            'state',
            'state_code',
            'gstin',
            'pincode',
        ]);

    $items = \App\Models\Item::where('items.business_id', $bid)
        ->where('items.is_active', true)
        ->leftJoin('invoice_items', 'items.id', '=', 'invoice_items.item_id')
        ->select(
            'items.id',
            'items.name',
            'items.type',
            'items.sku',
            'items.description',
            'items.tax_rate',
            'items.making_charge',
            'items.sac',
            'items.gold_weight',
            'items.gold_purity',
            'items.silver_weight',
            'items.silver_purity',
            'items.stone_weight',
            'items.stone_charges',
            'items.diamond_weight',
            'items.diamond_charges',
            'items.price',
            'items.making_charge_type',
            \DB::raw('COALESCE(SUM(invoice_items.quantity),0) as total_sold')
        )
        ->groupBy(
            'items.id',
            'items.name',
            'items.type',
            'items.sku',
            'items.description',
            'items.tax_rate',
            'items.making_charge',
            'items.sac',
            'items.gold_weight',
            'items.gold_purity',
            'items.silver_weight',
            'items.silver_purity',
            'items.stone_weight',
            'items.stone_charges',
            'items.diamond_weight',
            'items.diamond_charges',
            'items.price',
            'items.making_charge_type',
        )
        ->orderByDesc('total_sold')
        ->orderBy('items.name')
        ->get();

    $metalRates = \App\Models\MetalRate::where('business_id', $bid)
        ->whereDate('rate_date', $today)
        ->where('is_active', true)
        ->get([
            'metal_type',
            'purity',
            'rate_per_gram',
        ]);

    $banks = \App\Models\BankAccount::where('business_id', $bid)
        ->orderBy('bank_name')
        ->get([
            'id',
            'bank_name',
            'account_holder',
            'account_no',
            'ifsc',
        ]);

    $chargesJson = $invoice->charges_json ?? null;

    if (is_string($chargesJson)) {
        $decodedCharges = json_decode($chargesJson, true);
        $chargesJson = is_array($decodedCharges) ? $decodedCharges : [];
    }

    if (!is_array($chargesJson)) {
        $chargesJson = [];
    }

    $payment = $invoice->payments->first();

    $receivedAmount = (float) (
        $invoice->received_amount
        ?? $payment->received_total
        ?? 0
    );

    $paymentMode = (string) (
        $invoice->payment_method
        ?? 'cash'
    );

    $invoiceJson = [
        'id' => $invoice->id,
        'client_id' => $invoice->client_id,
        'invoice_date' => $today,
        'invoice_number' => (string) ($invoice->invoice_number ?? ''),
        'invoice_prefix' => (string) ($invoice->invoice_prefix ?? $suggestedPrefix),

        'gst_no' => (string) ($invoice->gst_no ?? ''),
        'transport_mode' => (string) ($invoice->transport_mode ?? 'By Hand'),
        'reverse_charge' => (int) ($invoice->reverse_charge ?? 0),

        'discount_type' => (string) ($invoice->discount_type ?? 'flat'),
        'discount_value' => (float) (
            $invoice->discount_value
            ?? $invoice->discount_total
            ?? 0
        ),

        'charges_json' => $chargesJson,
        'charge_total' => (float) ($invoice->charge_total ?? 0),

        'tcs_percent' => (float) ($invoice->tcs_percent ?? 0),
        'tcs_amount' => (float) ($invoice->tcs_amount ?? 0),

        'round_off' => (float) ($invoice->round_off ?? 0),

        'payment_method' => $paymentMode,
        'bank_account_id' => $invoice->bank_account_id ?? null,

        'received' => $receivedAmount,

        'pay_cash' => (float) ($payment->cash_amount ?? 0),
        'pay_upi' => (float) ($payment->online_amount ?? 0),
        'pay_card' => (float) ($payment->card_amount ?? 0),
        'pay_cheque' => (float) ($payment->cheque_amount ?? 0),
        'credit_sales_excess' => (float) ($payment->credit_sales_excess_amount ?? 0),
        'advance_amount' => (float) ($payment->advance_amount ?? 0),

        'online_mode' => (string) ($payment->online_mode ?? ''),
        'online_ref' => (string) ($payment->online_ref ?? ''),
        'upi_id' => (string) ($payment->upi_id ?? ''),
        'card_last4' => (string) ($payment->card_last4 ?? ''),
        'card_ref' => (string) ($payment->card_ref ?? ''),
        'cheque_no' => (string) ($payment->cheque_no ?? ''),
        'bank_name' => (string) ($payment->bank_name ?? ''),

        'items' => $invoice->items->map(function ($it) {
            $master = $it->item ?? null;

            $type = strtolower(trim((string) (
                $master->type
                ?? $it->item_type
                ?? 'product'
            )));

            if (!in_array($type, ['product', 'service'], true)) {
                $type = 'product';
            }

            $hsn = $it->hsn_code ?? $it->sac_code ?? '';

            $qty = (float) ($it->quantity ?? 1);
            if ($qty <= 0) {
                $qty = 1;
            }

            $taxPercent = (float) ($it->tax_percent ?? 0);

            $finalAmount = (float) ($it->amount ?? 0);

            // ✅ IMPORTANT FIX:
            // Edit page par price master items.price se nahi,
            // invoice_items.rate / quantity se aayega.
            // $baseAmount = (float) ($it->rate ?? 0);
            // $savedUnitPrice = $qty > 0 ? round($baseAmount / $qty, 2) : 0;

            // $fixedPrice = $type === 'product' ? $savedUnitPrice : 0;
            // $serviceRate = $type === 'service' ? $savedUnitPrice : 0;

           $savedRate = (float) ($it->rate ?? 0);
            $savedMakingRate = (float) ($it->making_rate ?? 0);

            $makingChargeType = strtolower(trim((string) (
                $it->making_charge_type
                ?? $master->making_charge_type
                ?? 'percent'
            )));

            if (!in_array($makingChargeType, ['percent', 'fixed', 'per_gram'], true)) {
                $makingChargeType = 'percent';
            }

            $savedUnitPriceWithMaking = $qty > 0
                ? round($savedRate / $qty, 2)
                : 0;

            if ($savedMakingRate > 0) {
                if ($makingChargeType === 'percent') {
                    $savedUnitPrice = round($savedUnitPriceWithMaking / (1 + ($savedMakingRate / 100)), 2);
                } elseif ($makingChargeType === 'fixed') {
                    $savedUnitPrice = round($savedUnitPriceWithMaking - $savedMakingRate, 2);
                } elseif ($makingChargeType === 'per_gram') {
                    $totalWt = (float) ($it->gold_wt ?? 0) + (float) ($it->silver_wt ?? 0);
                    $makingAmount = $savedMakingRate * $totalWt;
                    $savedUnitPrice = round($savedUnitPriceWithMaking - ($makingAmount / $qty), 2);
                } else {
                    $savedUnitPrice = $savedUnitPriceWithMaking;
                }
            } else {
                $savedUnitPrice = $savedUnitPriceWithMaking;
            }

            $savedUnitPrice = max(0, $savedUnitPrice);

            // $savedUnitPriceWithMaking = $qty > 0
            //     ? round($savedRate / $qty, 2)
            //     : 0;

            // // ✅ rate में making already included है,
            // // इसलिए edit page पर fixed_price में से making reverse करके original price दिखाएंगे
            // if ($savedMakingRate > 0) {
            //     $savedUnitPrice = round($savedUnitPriceWithMaking / (1 + ($savedMakingRate / 100)), 2);
            // } else {
            //     $savedUnitPrice = $savedUnitPriceWithMaking;
            // }

            $fixedPrice = $savedUnitPrice;
            $serviceRate = $savedUnitPrice;

            $searchName = '';
            if ($master) {
                $searchName = $master->sku
                    ? ($master->name . ' (' . $master->sku . ')')
                    : $master->name;
            }

            return [
                '_k' => now()->timestamp . rand(1000, 9999),

                'item_id' => $it->item_id ?? null,
                'item_type' => $type,

                'search' => $searchName,

                'ddOpen' => false,
                'ddHi' => 0,
                'ddStyle' => '',
                'ddPreviewName' => '',
                'ddPreview' => '',

                'description' => (string) ($it->description ?? ''),
                'hsn' => (string) $hsn,
                'quantity' => $qty,

                'making_rate' => $type === 'product'
                    ? (float) ($it->making_rate ?? 0)
                    : 0,

                'gold_purity' => $master->gold_purity ?? null,
                'silver_purity' => $master->silver_purity ?? null,

                'gold_rate' => (float) ($it->gold_rate ?? 0),
                'silver_rate' => (float) ($it->silver_rate ?? 0),

                'silver_wt' => (float) ($it->silver_wt ?? 0),
                'gold_wt' => (float) ($it->gold_wt ?? 0),

                'gemstone_wt' => (float) ($it->gemstone_wt_ct ?? 0),
                'diamond_wt' => (float) ($it->diamond_wt_ct ?? 0),

                'gemstone_charge' => (float) ($it->gemstone_charge ?? $it->stone_charge ?? 0),
                'diamond_charge' => (float) ($it->diamond_charge ?? 0),

                'fixed_price' => $fixedPrice,
                'service_rate' => $serviceRate,

                'tax_percent' => $taxPercent,

                'amount_mode' => 'manual',
                'manual_amount' => $finalAmount,
                'amount' => $finalAmount,
                'making_charge_type' => $type === 'product' ? $makingChargeType : 'percent',
            ];
        })->values(),
    ];

    return view('invoices.edit_kapoor_style', [
        'invoice' => $invoice,

        'today' => $today,
        'clientsJson' => $clients->values()->toJson(),
        'itemsJson' => $items->values()->toJson(),
        'metalRatesJson' => $metalRates->values()->toJson(),
        'banksJson' => $banks->values()->toJson(),

        'suggestedPrefix' => $suggestedPrefix,
        'basePrefix' => $base,
        'defaultTerms' => $business->terms,

        'businessState' => $business->state,
        'businessStateCode' => $business->state_code,
        'businessGstin' => $business->gstin,

        'docType' => $docType,

        'invoiceJson' => json_encode($invoiceJson, JSON_UNESCAPED_UNICODE),
        'allowedFields' => $allowedFields,
    ]);
}





    public function destroy(Invoice $invoice)
    {
        $invoice->delete();
        return back()->with('success','Deleted.');
    }




    public function download(Invoice $invoice)
    {

        // 2) Otherwise — generate fresh PDF (fallback)
        $pdf = $this->simplePdfBuild($invoice);

        $safeNumber = str_replace(['/', '\\'], '-', (string)($invoice->invoice_number ?? 'INV'));
        return $pdf->download('Invoice-'.$safeNumber.'.pdf');
    }


    private function imageDataUri(?string $path): ?string
    {
        if (!$path) return null;

        try {
            // public disk preferred
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
                $full = \Illuminate\Support\Facades\Storage::disk('public')->path($path);
                $mime = @mime_content_type($full) ?: 'image/png';
                $data = @file_get_contents($full);
                if ($data === false) return null;
                return "data:{$mime};base64," . base64_encode($data);
            }

            // absolute public path fallback
            $full = public_path($path);
            if (is_file($full)) {
                $mime = @mime_content_type($full) ?: 'image/png';
                $data = @file_get_contents($full);
                if ($data === false) return null;
                return "data:{$mime};base64," . base64_encode($data);
            }
        } catch (\Throwable $e) {
            report($e);
        }

        return null;
    }

    public function previewNumber(Request $request)
    {
        $request->validate([
            'invoice_date'   => ['required','date'],
            'invoice_prefix' => ['required','string'],
        ]);

        $bid = $request->user()->current_business_id ?? session('active_business_id');
        if (!$bid) {
            $bid = $request->user()->businesses()->pluck('businesses.id')->first();
        }

        $peek = \App\Services\InvoiceNumber::peek((int)$bid, $request->invoice_date, $request->invoice_prefix, 3);

        return response()->json([
            'ok'     => true,
            'number' => $peek['full'] ?? null,
        ]);
    }


    // public function show(Invoice $invoice)
    // {
    //     // invoice number safe for filename
    //     $safeNumber = str_replace(['/', '\\'], '-', (string)($invoice->invoice_number ?? 'INV'));

    //     // -------------------------------
    //     // 1️⃣ If PDF already saved in DB
    //     // -------------------------------
    //     //        if (!empty($invoice->pdf_url)) {
    //     //
    //     //            $path = $this->normalizePdfPath($invoice->pdf_url);
    //     //
    //     //            if ($path && Storage::disk('public')->exists($path)) {
    //     //                return response()->file(
    //     //                    Storage::disk('public')->path($path),
    //     //                    [
    //     //                        'Content-Type'        => 'application/pdf',
    //     //                        'Content-Disposition' => 'inline; filename="Invoice-'.$safeNumber.'.pdf"',
    //     //                    ]
    //     //                );
    //     //            }
    //     //        }

    //     // --------------------------------------------------
    //     // 2️⃣ PDF missing (DB empty OR file deleted)
    //     //    → Generate + Save + Show
    //     // --------------------------------------------------

    //     // Always use fresh relations for PDF
    //     $invoice = $invoice->fresh(['client', 'items', 'business']);

    //     // Build PDF
    //     $pdf = $this->simplePdfBuild($invoice);
    //     // OR: $pdf = $this->buildInvoicePdf($invoice);

    //     $fileName = 'invoices/Invoice-' . $safeNumber . '.pdf';

    //     // Save PDF to storage
    //     Storage::disk('public')->put($fileName, $pdf->output());

    //     // Save path in DB (only relative path)
    //     $invoice->update([
    //         'pdf_url' => $fileName,
    //     ]);

    //     // Show PDF
    //     return response()->file(
    //         Storage::disk('public')->path($fileName),
    //         [
    //             'Content-Type'        => 'application/pdf',
    //             'Content-Disposition' => 'inline; filename="Invoice-'.$safeNumber.'.pdf"',
    //         ]
    //     );
    // }


    public function show(Invoice $invoice)
    {
        $safeNumber = str_replace(['/', '\\'], '-', (string)($invoice->invoice_number ?? 'INV'));

        // agar pdf_url already hai aur file exist karti hai to wahi dikhao
        // if (!empty($invoice->pdf_url)) {
        //     $path = ltrim((string)$invoice->pdf_url, '/');

        //     if (str_starts_with($path, 'storage/')) {
        //         $path = substr($path, 8); // "storage/" hata do for public disk
        //     }

        //     if (Storage::disk('public')->exists($path)) {
        //         return response()->file(
        //             Storage::disk('public')->path($path),
        //             [
        //                 'Content-Type'        => 'application/pdf',
        //                 'Content-Disposition' => 'inline; filename="Invoice-' . $safeNumber . '.pdf"',
        //             ]
        //         );
        //     }
        // }

        // fresh relations
        $invoice = $invoice->fresh(['client', 'items', 'business']);

        // pdf build
        $pdf = $this->simplePdfBuild($invoice);

        // file name
        $fileName = 'invoices/Invoice-' . $safeNumber . '.pdf';

        // save in storage/app/public/invoices
        Storage::disk('public')->put($fileName, $pdf->output());

        // db me relative path save
        $invoice->update([
            'pdf_url' => $fileName,
        ]);

        // inline show
        return response()->file(
            Storage::disk('public')->path($fileName),
            [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'inline; filename="Invoice-' . $safeNumber . '.pdf"',
            ]
        );
    }

    public function preview(Invoice $invoice)
    {
        // ✅ Ensure PDF exists (same logic as show but no file return)
        $safeNumber = str_replace(['/', '\\'], '-', (string)($invoice->invoice_number ?? 'INV'));

        if (!empty($invoice->pdf_url)) {
            $path = $this->normalizePdfPath($invoice->pdf_url);
            if (!$path || !Storage::disk('public')->exists($path)) {
                $invoice->update(['pdf_url' => null]);
            }
        }

        if (empty($invoice->pdf_url)) {
            $invoice = $invoice->fresh(['client', 'items', 'business']);
            $pdf = $this->simplePdfBuild($invoice);
            $fileName = 'invoices/Invoice-' . $safeNumber . '.pdf';

            Storage::disk('public')->put($fileName, $pdf->output());
            $invoice->update(['pdf_url' => $fileName]);
        }

        return view('invoices.preview', [
            'invoice' => $invoice,
            'pdfSrc'  => route('invoices.show', $invoice->id), // iframe src
        ]);
    }


    // public function store(Request $r, StockService $stock, $docType)
    // {

    //     $user = $r->user();

    //     // ✅ Active Business Resolve
    //     $bid = $user->businesses->first()?->id;

    //     if (!$bid) {
    //         $bid = $user->businesses()->pluck('businesses.id')->first();
    //     }

    //     if (!$bid) {
    //         return back()
    //             ->withErrors(['business' => 'Active business select/attach नहीं है.'])
    //             ->withInput();
    //     }

    //     // ✅ Business wise plan expiry validation
    //     // super_admin/admin ko bypass dena ho to ye if rakho
    //     // if (!$user->hasAnyRole(['super_admin', 'admin'])) {
    //     //     $activePlan = UserPlan::where('user_id', $user->id)
    //     //         ->orWhere('business_id', $bid)
    //     //         ->where('status', 'active')
    //     //         ->whereDate('start_date', '<=', today())
    //     //         ->whereDate('expiry_date', '>=', today())
    //     //         ->latest('id')
    //     //         ->first();

    //     //     if (!$activePlan) {
    //     //         return back()
    //     //             ->withErrors([
    //     //                 'plan' => 'इस business का plan expire हो चुका है या active plan available नहीं है. Invoice create करने के लिए कृपया plan renew करें.'
    //     //             ])
    //     //             ->withInput();
    //     //     }
    //     // }


        
    //     if (!$user->hasAnyRole(['super_admin', 'admin'])) {

    //         $activePlan = UserPlan::where('business_id', $bid)
    //             ->where(function ($q) {
    //                 $q->where('status', 'active')
    //                 ->orWhere('status', 1);
    //             })
    //             ->whereDate('start_date', '<=', today())
    //             ->where(function ($q) {
    //                 $q->whereNull('expiry_date')
    //                 ->orWhereDate('expiry_date', '>=', today());
    //             })
    //             ->latest('id')
    //             ->first();


    //         if (!$activePlan) {
    //             return back()
    //                 ->withErrors([
    //                     'plan' => 'इस business का active plan available नहीं है या plan expire हो चुका है. Invoice create करने के लिए कृपया plan renew करें.'
    //                 ])
    //                 ->withInput();
    //         }
    //     }

    //     $docType = strtolower(trim((string)$docType));

    //     if (!in_array($docType, ['tax', 'proforma', 'quotation'], true)) {
    //         $docType = 'tax';
    //     }

    //     // ✅ नीचे आपका बाकी पुराना code same रहेगा

    //     $docType = strtolower(trim((string)$docType));
    //     if (!in_array($docType, ['tax', 'proforma', 'quotation'], true)) {
    //         $docType = 'tax';
    //     }

    //     $bid = $r->user()->current_business_id ?? session('active_business_id');
    //     if (!$bid) {
    //         return back()->withErrors(['business' => 'Active business select/attach नहीं है.'])->withInput();
    //     }

    //     $data = $r->validate([
    //         'client_id'      => ['required','exists:clients,id'],
    //         'invoice_date'   => ['required','date'],
    //         'invoice_prefix' => ['nullable','string','max:255'],
    //         'invoice_number' => ['required','string','max:255'],

    //         'transport_mode' => ['nullable','string','max:255'],
    //         'gst_no'         => ['nullable','string','max:50'],
    //         'reverse_charge' => ['nullable'],

    //         'notes'          => ['nullable','string','max:2000'],
    //         'terms'          => ['nullable','string','max:2000'],

    //         'items_json'     => ['required','string'],

    //         'charges_json'   => ['nullable','string'],
    //         'discount_total' => ['nullable','numeric','min:0'],
    //         'charge_total'   => ['nullable','numeric','min:0'],
    //         'tcs_percent'    => ['nullable','numeric','min:0'],
    //         'tcs_amount'     => ['nullable','numeric','min:0'],
    //         'round_off'      => ['nullable','numeric'],
    //         'less_amount'    => ['nullable','numeric','min:0'],

    //         'cgst_percent'   => ['nullable','numeric','min:0'],
    //         'sgst_percent'   => ['nullable','numeric','min:0'],
    //         'igst_percent'   => ['nullable','numeric','min:0'],

    //         'payment_method'  => ['nullable','string','max:255'],
    //         'bank_account_id' => ['nullable','integer'],
    //         'signature' => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],
    //         'kots_json' => ['nullable','string','max:5000'],
    //     ]);

    //     // ✅ Proforma में payment validation skip
    //     $pay = [];
    //     if ($docType === 'tax') {
    //         $pay = $r->validate([
    //             'pay_cash'            => ['nullable','numeric','min:0'],
    //             'pay_upi'             => ['nullable','numeric','min:0'],
    //             'pay_card'            => ['nullable','numeric','min:0'],
    //             'pay_cheque'          => ['nullable','numeric','min:0'],

    //             'credit_sales_excess' => ['nullable','numeric','min:0'],
    //             'advance_amount'      => ['nullable','numeric','min:0'],

    //             'online_mode'         => ['nullable','string','max:30'],
    //             'online_ref'          => ['nullable','string','max:100'],
    //             'upi_id'              => ['nullable','string','max:100'],

    //             'card_last4'          => ['nullable','string','max:4'],
    //             'card_ref'            => ['nullable','string','max:100'],

    //             'cheque_no'           => ['nullable','string','max:50'],
    //             'bank_name'           => ['nullable','string','max:100'],
    //             'notes'               => ['nullable','string','max:2000'],
    //         ]);
    //     }

    //     // -------- prefix helper --------
    //     $computePrefix = function (string $date, string $base = 'INV'): string {
    //         $ts = strtotime($date);
    //         $y  = (int)date('Y', $ts);
    //         $m  = (int)date('n', $ts);
    //         $startYY = ($m >= 4) ? ($y % 100) : (($y - 1) % 100);
    //         $a = str_pad((string)$startYY, 2, '0', STR_PAD_LEFT);
    //         $b = str_pad((string)(($startYY + 1) % 100), 2, '0', STR_PAD_LEFT);
    //         $fy = "{$a}-{$b}";
    //         $base = rtrim($base, '/');
    //         return "{$base}/{$fy}/";
    //     };

    //     $invoiceDate = \Carbon\Carbon::parse($data['invoice_date'])->toDateString();
    //     $prefix      = trim($data['invoice_prefix'] ?? '');
    //     $base = ($docType === 'proforma') ? 'PF' : (($docType === 'quotation') ? 'QT' : 'INV');
    //     if ($prefix === '') $prefix = $computePrefix($invoiceDate, $base);


    //     // -------- items parse --------
    //     $rows = json_decode($data['items_json'], true);
    //     if (!is_array($rows) || count($rows) < 1) {
    //         return back()->withErrors(['items' => 'कम से कम 1 line item जरूरी है.'])->withInput();
    //     }

    //     // -------- normalize state_code helper --------
    //     $normCode = function ($v) {
    //         $s = trim((string)$v);
    //         $s = preg_replace('/\D+/', '', $s);
    //         $s = ltrim($s, '0');
    //         return $s;
    //     };

    //     /**
    //      * ✅ IMPORTANT FIX:
    //      * Tax अब "avgTaxPercent" से taxableAmount पर नहीं निकलेगा,
    //      * बल्कि हर line का tax sum होगा (slab-wise exact).
    //      */

    //     $subtotal     = 0.0;     // sum of base amounts
    //     $weightedTax  = 0.0;     // base * tax% (for display avg%)
    //     $itemsTaxTotal = 0.0;    // ✅ sum of line tax (EXACT)
    //     $cleanRows    = [];

    //     foreach ($rows as $i => $row) {
    //         $itemId = $row['item_id'] ?? null;
    //         if (empty($itemId)) {
    //             return back()->withErrors(['items' => "Row ".($i+1)." में Item select नहीं है."])->withInput();
    //         }

    //         $itemType = strtolower(trim((string)($row['item_type'] ?? 'product')));
    //         if (!in_array($itemType, ['product','service'], true)) $itemType = 'product';

    //         $desc = trim($row['description'] ?? '');
    //         if ($desc === '') {
    //             return back()->withErrors(['items' => "Row ".($i+1)." description missing."])->withInput();
    //         }

    //         $hsn = trim($row['hsn'] ?? '');
    //         $qty = (int)($row['quantity'] ?? 1);
    //         $qty = $qty < 1 ? 1 : $qty;

    //         $taxPct = (float)($row['tax_percent'] ?? 0);
    //         if ($taxPct < 0 || $taxPct > 100) {
    //             return back()->withErrors(['items' => "Row ".($i+1)." tax % invalid."])->withInput();
    //         }

    //         // ===== SERVICE =====
    //         if ($itemType === 'service') {
    //             $serviceRate = (float)($row['service_rate'] ?? 0);
    //             if ($serviceRate < 0) {
    //                 return back()->withErrors(['items' => "Row ".($i+1)." service rate invalid."])->withInput();
    //             }

    //             $lineBase = round($serviceRate * $qty, 2);
    //             $lineTax  = round($lineBase * ($taxPct/100), 2); // ✅ exact slab

    //             $subtotal      += $lineBase;
    //             $weightedTax   += ($lineBase * $taxPct);
    //             $itemsTaxTotal += $lineTax;

    //             $cleanRows[] = [
    //                 'item_id'       => (int)$itemId,
    //                 'item_type'     => 'service',
    //                 'description'   => $desc,
    //                 'hsn'           => $hsn,
    //                 'qty'           => $qty,
    //                 'tax_percent'   => round($taxPct,2),

    //                 // service_rate store per unit (tax excluded)
    //                 'service_rate'  => round($serviceRate,2),

    //                 // base/tax/amount (FINAL) store for later usage
    //                 'rate'          => $lineBase,                 // base
    //                 'tax_amount'    => $lineTax,                  // tax
    //                 'amount'        => round($lineBase + $lineTax, 2), // base+tax

    //                 // keep your old keys if other code expects them
    //                 'making_charge' => round($serviceRate,2),

    //                 // product fields empty
    //                 'gold_wt' => 0,'silver_wt'=>0,'gold_rate'=>0,'silver_rate'=>0,
    //                 'gemstone_wt'=>0,'diamond_wt'=>0,'making_rate'=>0,
    //                 'stone_charges'=>0,
    //             ];
    //             continue;
    //         }

    //         // ===== PRODUCT =====
    //         $goldWt     = (float)($row['gold_wt'] ?? 0);
    //         $silverWt   = (float)($row['silver_wt'] ?? 0);
    //         $goldRate   = (float)($row['gold_rate'] ?? 0);
    //         $silverRate = (float)($row['silver_rate'] ?? 0);
    //         $makingRate = (float)($row['making_rate'] ?? 0);

    //         $gemCt = (float)($row['gemstone_wt'] ?? 0);
    //         $diaCt = (float)($row['diamond_wt'] ?? 0);

    //         if ($goldWt < 0 || $silverWt < 0 || $goldRate < 0 || $silverRate < 0 || $makingRate < 0) {
    //             return back()->withErrors(['items' => "Row ".($i+1)." invalid values."])->withInput();
    //         }

    //         $lineBase = round((($goldWt * $goldRate) + ($silverWt * $silverRate) + $makingRate) * $qty, 2);
    //         $lineTax  = round($lineBase * ($taxPct/100), 2); // ✅ exact slab

    //         $subtotal      += $lineBase;
    //         $weightedTax   += ($lineBase * $taxPct);
    //         $itemsTaxTotal += $lineTax;

    //         $cleanRows[] = [
    //             'item_id'     => (int)$itemId,
    //             'item_type'   => 'product',
    //             'description' => $desc,
    //             'hsn'         => $hsn,
    //             'qty'         => $qty,
    //             'tax_percent' => round($taxPct,2),

    //             'gold_wt'     => round($goldWt,3),
    //             'silver_wt'   => round($silverWt,3),
    //             'gold_rate'   => round($goldRate,2),
    //             'silver_rate' => round($silverRate,2),

    //             'gemstone_wt' => round($gemCt,3),
    //             'diamond_wt'  => round($diaCt,3),

    //             'making_rate' => round($makingRate,2),
    //             'making_charge'=> null,
    //             'stone_charges'=> null,

    //             // ✅ base/tax/amount
    //             'rate'        => $lineBase,
    //             'tax_amount'  => $lineTax,
    //             'amount'      => round($lineBase + $lineTax, 2),
    //         ];
    //     }

    //     $subtotal      = round($subtotal, 2);
    //     $itemsTaxTotal = round($itemsTaxTotal, 2);

    //     // display-only avg tax (no longer used for tax math)
    //     $avgTaxPercentRaw = ($subtotal > 0) ? ($weightedTax / $subtotal) : 0;
    //     $avgTaxPercent    = round($avgTaxPercentRaw, 2);

    //     // invoice-level adjustments
    //     $discountTotal = round((float)($data['discount_total'] ?? 0), 2);
    //     $chargeTotal   = round((float)($data['charge_total'] ?? 0), 2);

    //     // taxable = subtotal - discount + charges
    //     $taxableAmount = round(max(0, $subtotal - $discountTotal + $chargeTotal), 2);

    //     // ✅ TAX FIX: EXACT slab-wise tax = sum(lineTax)
    //     // NOTE: Charges taxable? If YES add charges tax, if NO keep only itemsTaxTotal.
    //     $chargesTax = 0.0; // set charge taxable OFF by default
    //     // If you want charges taxable, uncomment next line:
    //     // $chargesTax = round($chargeTotal * ($avgTaxPercentRaw/100), 2);

    //     $taxAmount = round($itemsTaxTotal + $chargesTax, 2);

    //     // TCS
    //     $tcsPercent = round((float)($data['tcs_percent'] ?? 0), 2);
    //     $tcsAmount  = round((float)($data['tcs_amount'] ?? 0), 2);
    //     if($tcsPercent > 0){
    //         $tcsAmount = round($taxableAmount * ($tcsPercent/100), 2);
    //     }

    //     $roundOff   = round((float)($data['round_off'] ?? 0), 2);
    //     $lessAmount = round((float)($data['less_amount'] ?? $discountTotal), 2);

    //     $grandTotal = round($taxableAmount + $taxAmount + $tcsAmount + $roundOff, 2);

    //     // ✅ Tax invoice payment totals only
    //     $cash = $online = $card = $cheque = $credit = $advance = 0.0;
    //     $receivedTotal = 0.0;
    //     $balance = $grandTotal;

    //     if ($docType === 'tax') {
    //         $cash    = (float)($pay['pay_cash'] ?? 0);
    //         $online  = (float)($pay['pay_upi'] ?? 0);
    //         $card    = (float)($pay['pay_card'] ?? 0);
    //         $cheque  = (float)($pay['pay_cheque'] ?? 0);
    //         $credit  = (float)($pay['credit_sales_excess'] ?? 0);
    //         $advance = (float)($pay['advance_amount'] ?? 0);

    //         $receivedTotal = round($cash + $online + $card + $cheque, 2);
    //         $balance = round(max(0, $grandTotal - $receivedTotal - $advance - $credit), 2);
    //     }

    //     // charges json decode
    //     $chargesArr = [];
    //     if(!empty($data['charges_json'])){
    //         $tmp = json_decode($data['charges_json'], true);
    //         if(is_array($tmp)){
    //             foreach($tmp as $c){
    //                 $nm = trim((string)($c['name'] ?? ''));
    //                 $am = (float)($c['amount'] ?? 0);
    //                 if($nm !== '' && $am != 0){
    //                     $chargesArr[] = ['name'=>$nm, 'amount'=>round($am,2)];
    //                 }
    //             }
    //         }
    //     }

    //     $signaturePath = null;
    //     if ($r->hasFile('signature')) {
    //         $signaturePath = $r->file('signature')->store("invoices/{$bid}/signatures", 'public');
    //     }

    //     // ✅ KOT (multiple) parse
    //     $kots = [];
    //     if ($r->filled('kots_json')) {
    //         $tmp = json_decode($r->input('kots_json'), true);

    //         if (is_array($tmp)) {
    //             $kots = collect($tmp)
    //                 ->map(fn($v) => trim((string)$v))
    //                 ->filter(fn($v) => $v !== '')
    //                 ->unique()
    //                 ->values()
    //                 ->take(50) // safety
    //                 ->all();
    //         }
    //     }


    //     $invoice = null;

    //     try {
    //         DB::transaction(function () use (
    //             $r,
    //             $bid, $data, $invoiceDate, $prefix, $docType,
    //             $subtotal, $avgTaxPercent, $taxableAmount, $taxAmount,
    //             $discountTotal, $chargeTotal, $tcsPercent, $tcsAmount, $roundOff, $lessAmount,
    //             $grandTotal, $receivedTotal, $balance,
    //             $cash, $online, $card, $cheque, $credit, $advance,
    //             $pay, $cleanRows, $normCode, $chargesArr, $kots, &$invoice, $stock, $signaturePath
    //         ) {
    //             $biz    = Business::findOrFail($bid);
    //             $client = Client::where('business_id', $bid)->findOrFail($data['client_id']);

    //             $bizCode   = $normCode($biz->state_code ?? '');
    //             $partyCode = $normCode($client->state_code ?? '');
    //             $isIntra = ($bizCode !== '' && $partyCode !== '') ? ($bizCode === $partyCode) : false;

    //             // ✅ percent (display)
    //             $cgstPercent = $isIntra ? round($avgTaxPercent/2, 2) : 0;
    //             $sgstPercent = $isIntra ? round($avgTaxPercent/2, 2) : 0;
    //             $igstPercent = $isIntra ? 0 : round($avgTaxPercent, 2);

    //             // ✅ amount (exact) based on fixed $taxAmount
    //             $cgst = $isIntra ? round($taxAmount / 2, 2) : 0;
    //             $sgst = $isIntra ? round($taxAmount / 2, 2) : 0;
    //             $igst = $isIntra ? 0 : round($taxAmount, 2);

    //             // ✅ Invoice number allocate with docType sequence
    //             // $alloc = \App\Services\InvoiceNumber::next((int)$bid, $invoiceDate, $prefix, 3, $docType);

    //             // ✅ request se invoice number lo
    //             $reqInvoiceNo = trim((string)($data['invoice_number'] ?? ''));

    //             if ($reqInvoiceNo === '') {
    //                 // optional safety fallback (agar kabhi form se na aaye)
    //                 $alloc = \App\Services\InvoiceNumber::next((int)$bid, $invoiceDate, $prefix, 3, $docType);
    //                 $reqInvoiceNo = $alloc['full'];
    //             }

    //             // ✅ invoice_sequences sync: sirf match hone par +1
    //             \App\Services\InvoiceNumber::syncNextSeqIfMatches((int)$bid, $invoiceDate, $reqInvoiceNo, 3, $docType);

    //             $invoice = Invoice::create([
    //                 'business_id'     => $bid,
    //                 'client_id'       => $data['client_id'],
    //                 'invoice_date'    => $invoiceDate,

    //                 'invoice_prefix'  => $prefix,
    //                 // 'invoice_number'  => $alloc['full'],
    //                 'invoice_number'  => $reqInvoiceNo,

    //                 'invoice_type'    => $docType,   // tax / proforma

    //                 'subtotal'        => $subtotal,
    //                 'discount_total'  => $discountTotal,
    //                 'charge_total'    => $chargeTotal,
    //                 'less_amount'     => $lessAmount,

    //                 'tax_amount'      => $taxAmount,

    //                 'cgst_percent'    => $cgstPercent,
    //                 'cgst_amount'     => $cgst,
    //                 'sgst_percent'    => $sgstPercent,
    //                 'sgst_amount'     => $sgst,
    //                 'igst_percent'    => $igstPercent,
    //                 'igst_amount'     => $igst,

    //                 'tcs_percent'     => $tcsPercent,
    //                 'tcs_amount'      => $tcsAmount,

    //                 'round_off'       => $roundOff,

    //                 'total'           => $grandTotal,
    //                 'received_amount' => $receivedTotal,
    //                 'balance'         => $balance,

    //                 'payment_method'  => $data['payment_method'] ?? null,

    //                 'gst_no'          => $data['gst_no'] ?? null,
    //                 'transport_mode'  => $data['transport_mode'] ?? null,
    //                 'reverse_charge'  => !empty($data['reverse_charge']) ? 1 : 0,

    //                 'place_of_supply_state' => $client->state ?? null,
    //                 'place_of_supply_code'  => $client->state_code ?? null,

    //                 'notes'           => $data['notes'] ?? null,
    //                 'terms'           => $data['terms'] ?? null,

    //                 'charges_json'    => json_encode($chargesArr),
    //                 'items_json'      => json_encode($cleanRows),

    //                 'amount_in_words' => '',
    //                 'signature_path'  => $signaturePath,
    //                 'created_by' => auth()->user()->id ?? null,
    //                 'updated_by' => auth()->user()->id ?? null,
    //                 'kots_json' => json_encode($kots),
    //             ]);

    //             // additional charges rows
    //             foreach($chargesArr as $c){
    //                 \App\Models\InvoiceAdditionalCharge::create([
    //                     'invoice_id' => $invoice->id,
    //                     'additional_charge_id' => null,
    //                     'name' => $c['name'],
    //                     'amount' => $c['amount'],
    //                 ]);
    //             }

    //             // invoice items rows
    //             foreach ($cleanRows as $row) {
    //                 $qty = (int)($row['qty'] ?? 1);

    //                 // ✅ base/tax/amount already computed above
    //                 $rate      = round((float)($row['rate'] ?? 0), 2);
    //                 $lineTax   = round((float)($row['tax_amount'] ?? 0), 2);
    //                 $lineTotal = round((float)($row['amount'] ?? ($rate + $lineTax)), 2);

    //                 InvoiceItem::create([
    //                     'invoice_id'   => $invoice->id,
    //                     'item_id'      => $row['item_id'],
    //                     'description'  => $row['description'] ?? '',
    //                     'sac_code'     => null,
    //                     'hsn_code'     => $row['hsn'] ?: null,
    //                     'quantity'     => $qty,

    //                     'gold_wt'      => (float)($row['gold_wt'] ?? 0),
    //                     'silver_wt'    => (float)($row['silver_wt'] ?? 0),
    //                     'gold_rate'    => (float)($row['gold_rate'] ?? 0),
    //                     'silver_rate'  => (float)($row['silver_rate'] ?? 0),

    //                     'gemstone_wt_ct' => (float)($row['gemstone_wt'] ?? 0),
    //                     'diamond_wt_ct'  => (float)($row['diamond_wt'] ?? 0),

    //                     'making_charge' => ($row['item_type']==='service') ? (float)($row['service_rate'] ?? $row['making_charge'] ?? 0) : null,
    //                     'making_rate'   => ($row['item_type']==='product') ? (float)($row['making_rate'] ?? 0) : null,

    //                     'discount'    => 0,
    //                     'tax_percent' => (float)($row['tax_percent'] ?? 0),

    //                     // ✅ base stored here
    //                     'rate'        => $rate,

    //                     // ✅ IMPORTANT: amount should NOT be 0
    //                     // store final (base + tax)
    //                     'amount'      => $lineTotal,
    //                 ]);
    //             }

    //             // ✅ Payments + Stock + Bank only for TAX invoice
    //             if ($docType === 'tax') {

    //                 InvoicePayment::create([
    //                     'business_id' => $bid,
    //                     'invoice_id'  => $invoice->id,
    //                     'client_id'   => $data['client_id'],

    //                     'total_value' => $grandTotal,

    //                     'cash_amount'   => $cash,
    //                     'online_amount' => $online,
    //                     'card_amount'   => $card,
    //                     'cheque_amount' => $cheque,

    //                     'online_mode' => $pay['online_mode'] ?? null,
    //                     'online_ref'  => $pay['online_ref'] ?? null,
    //                     'upi_id'      => $pay['upi_id'] ?? null,

    //                     'card_last4'  => $pay['card_last4'] ?? null,
    //                     'card_ref'    => $pay['card_ref'] ?? null,

    //                     'cheque_no'   => $pay['cheque_no'] ?? null,
    //                     'bank_name'   => $pay['bank_name'] ?? null,

    //                     'credit_sales_excess_amount' => $credit,
    //                     'advance_amount'             => $advance,

    //                     'received_total' => $receivedTotal,
    //                     'notes'   => $pay['notes'] ?? null,
    //                     'meta'    => null,
    //                     'paid_at' => $receivedTotal > 0 ? now() : null,
    //                 ]);

    //                 // ✅ stock cut only on tax invoice
    //                 $invoice->load(['items']);
    //                 $stock->recordSale($invoice);

    //                 // ✅ bank balance add only on tax invoice
    //                 $bankAccountId = $r->input('bank_account_id');
    //                 $mode = strtolower(trim((string)($data['payment_method'] ?? '')));
    //                 $bankModes = ['upi','bank','card','cheque'];

    //                 if ($bankAccountId && in_array($mode, $bankModes, true) && $receivedTotal > 0) {
    //                     $bank = \App\Models\BankAccount::where('business_id', $bid)
    //                         ->where('id', $bankAccountId)
    //                         ->first();

    //                     if ($bank) {
    //                         $bank->balance = round(((float)$bank->balance) + $receivedTotal, 2);
    //                         $bank->save();
    //                     }
    //                 }
    //             }
    //         });

    //         $pdf = $this->simplePdfBuild($invoice);

    //         $dir = "invoices/{$bid}/" . now()->format('Y-m');
    //         $safeName = preg_replace('/[^A-Za-z0-9\-_\.]/', '-', (string)$invoice->invoice_number);
    //         $filename = $safeName . ".pdf";
    //         $path = $dir . "/" . $filename;

    //         Storage::disk('public')->put($path, $pdf->output());
    //         $invoice->update(['pdf_url' => $path]);

    //     } catch (\Throwable $e) {
    //         report($e);
    //         return back()->withErrors(['invoice' => 'Invoice save करते समय error आया: '.$e->getMessage()])->withInput();
    //     }

    //     //        return redirect()->route('invoices.index')
    //     //            ->with('success', ($docType === 'proforma' ? 'Proforma created successfully.' : 'Invoice created successfully.'));
    //     return redirect()->route('invoices.preview', $invoice->id)
    //         ->with('success', ($docType === 'proforma' ? 'Proforma created successfully.' : 'Invoice created successfully.'));
    // }



    // public function store(Request $r, StockService $stock, $docType)
    // {
    //     $user = $r->user();

    //     $bid = $user->current_business_id ?? session('active_business_id');

    //     if (!$bid) {
    //         $bid = $user->businesses()->pluck('businesses.id')->first();
    //     }

    //     if (!$bid) {
    //         return back()
    //             ->withErrors(['business' => 'Active business select/attach नहीं है.'])
    //             ->withInput();
    //     }

    //     if (!$user->hasAnyRole(['super_admin', 'admin'])) {
    //         $activePlan = UserPlan::where('business_id', $bid)
    //             ->where(function ($q) {
    //                 $q->where('status', 'active')
    //                     ->orWhere('status', 1);
    //             })
    //             ->whereDate('start_date', '<=', today())
    //             ->where(function ($q) {
    //                 $q->whereNull('expiry_date')
    //                     ->orWhereDate('expiry_date', '>=', today());
    //             })
    //             ->latest('id')
    //             ->first();

    //         if (!$activePlan) {
    //             return back()
    //                 ->withErrors([
    //                     'plan' => 'इस business का active plan available नहीं है या plan expire हो चुका है. Invoice create करने के लिए कृपया plan renew करें.'
    //                 ])
    //                 ->withInput();
    //         }
    //     }

    //     $docType = strtolower(trim((string) $docType));
    //     if (!in_array($docType, ['tax', 'proforma', 'quotation'], true)) {
    //         $docType = 'tax';
    //     }

    //     $data = $r->validate([
    //         'client_id'      => ['required', 'exists:clients,id'],
    //         'invoice_date'   => ['required', 'date'],
    //         'invoice_prefix' => ['nullable', 'string', 'max:255'],
    //         'invoice_number' => ['required', 'string', 'max:255'],

    //         'transport_mode' => ['nullable', 'string', 'max:255'],
    //         'gst_no'         => ['nullable', 'string', 'max:50'],
    //         'reverse_charge' => ['nullable'],

    //         'notes'          => ['nullable', 'string', 'max:2000'],
    //         'terms'          => ['nullable', 'string', 'max:2000'],
    //         'items_json'     => ['required', 'string'],

    //         'charges_json'   => ['nullable', 'string'],
    //         'discount_total' => ['nullable', 'numeric', 'min:0'],
    //         'charge_total'   => ['nullable', 'numeric', 'min:0'],
    //         'tcs_percent'    => ['nullable', 'numeric', 'min:0'],
    //         'tcs_amount'     => ['nullable', 'numeric', 'min:0'],
    //         'round_off'      => ['nullable', 'numeric'],
    //         'less_amount'    => ['nullable', 'numeric', 'min:0'],

    //         'cgst_percent'   => ['nullable', 'numeric', 'min:0'],
    //         'sgst_percent'   => ['nullable', 'numeric', 'min:0'],
    //         'igst_percent'   => ['nullable', 'numeric', 'min:0'],

    //         'payment_method'   => ['nullable', 'string', 'max:255'],
    //         'bank_account_id'  => ['nullable', 'integer'],
    //         'signature'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
    //         'kots_json'        => ['nullable', 'string', 'max:5000'],
    //         'remove_signature' => ['nullable', 'boolean'],
    //     ]);

    //     $pay = [];
    //     if ($docType === 'tax') {
    //         $pay = $r->validate([
    //             'pay_cash'            => ['nullable', 'numeric', 'min:0'],
    //             'pay_upi'             => ['nullable', 'numeric', 'min:0'],
    //             'pay_card'            => ['nullable', 'numeric', 'min:0'],
    //             'pay_cheque'          => ['nullable', 'numeric', 'min:0'],

    //             'credit_sales_excess' => ['nullable', 'numeric', 'min:0'],
    //             'advance_amount'      => ['nullable', 'numeric', 'min:0'],

    //             'online_mode'         => ['nullable', 'string', 'max:30'],
    //             'online_ref'          => ['nullable', 'string', 'max:100'],
    //             'upi_id'              => ['nullable', 'string', 'max:100'],

    //             'card_last4'          => ['nullable', 'string', 'max:4'],
    //             'card_ref'            => ['nullable', 'string', 'max:100'],

    //             'cheque_no'           => ['nullable', 'string', 'max:50'],
    //             'bank_name'           => ['nullable', 'string', 'max:100'],
    //             'notes'               => ['nullable', 'string', 'max:2000'],
    //         ]);
    //     }

    //     $computePrefix = function (string $date, string $base = 'INV'): string {
    //         $ts = strtotime($date);
    //         $y  = (int) date('Y', $ts);
    //         $m  = (int) date('n', $ts);

    //         $startYY = ($m >= 4) ? ($y % 100) : (($y - 1) % 100);

    //         $a = str_pad((string) $startYY, 2, '0', STR_PAD_LEFT);
    //         $b = str_pad((string) (($startYY + 1) % 100), 2, '0', STR_PAD_LEFT);

    //         $base = rtrim($base, '/');

    //         return "{$base}/{$a}-{$b}/";
    //     };

    //     $invoiceDate = \Carbon\Carbon::parse($data['invoice_date'])->toDateString();
    //     $prefix = trim($data['invoice_prefix'] ?? '');

    //     $base = ($docType === 'proforma') ? 'PF' : (($docType === 'quotation') ? 'QT' : 'INV');

    //     if ($prefix === '') {
    //         $prefix = $computePrefix($invoiceDate, $base);
    //     }

    //     $rows = json_decode($data['items_json'], true);

    //     if (!is_array($rows) || count($rows) < 1) {
    //         return back()->withErrors(['items' => 'कम से कम 1 line item जरूरी है.'])->withInput();
    //     }

    //     $normCode = function ($v) {
    //         $s = trim((string) $v);
    //         $s = preg_replace('/\D+/', '', $s);
    //         $s = ltrim($s, '0');
    //         return $s;
    //     };

    //     $subtotal      = 0.0;
    //     $weightedTax   = 0.0;
    //     $itemsTaxTotal = 0.0;
    //     $cleanRows     = [];

    //     foreach ($rows as $i => $row) {
    //         $itemId = $row['item_id'] ?? null;

    //         if (empty($itemId)) {
    //             return back()->withErrors(['items' => 'Row ' . ($i + 1) . ' में Item select नहीं है.'])->withInput();
    //         }

    //         $itemType = strtolower(trim((string) ($row['item_type'] ?? 'product')));
    //         if (!in_array($itemType, ['product', 'service'], true)) {
    //             $itemType = 'product';
    //         }

    //         $desc = trim((string) ($row['description'] ?? ''));
    //         if ($desc === '') {
    //             return back()->withErrors(['items' => 'Row ' . ($i + 1) . ' description missing.'])->withInput();
    //         }

    //         $hsn = trim((string) ($row['hsn'] ?? ''));
    //         $qty = (int) ($row['quantity'] ?? 1);
    //         $qty = $qty < 1 ? 1 : $qty;

    //         $taxPct = (float) ($row['tax_percent'] ?? 0);
    //         if ($taxPct < 0 || $taxPct > 100) {
    //             return back()->withErrors(['items' => 'Row ' . ($i + 1) . ' tax % invalid.'])->withInput();
    //         }

    //         $manualAmount = (float) ($row['manual_amount'] ?? $row['amount'] ?? 0);
    //         $amountMode   = strtolower(trim((string) ($row['amount_mode'] ?? 'auto')));

    //         if ($manualAmount < 0) {
    //             return back()->withErrors(['items' => 'Row ' . ($i + 1) . ' manual amount invalid.'])->withInput();
    //         }

    //         if ($itemType === 'service') {
    //             $fixedPrice  = (float) ($row['fixed_price'] ?? $row['price'] ?? 0);
    //             $serviceRate = (float) ($row['service_rate'] ?? 0);

    //             if ($fixedPrice > 0) {
    //                 $serviceRate = $fixedPrice;
    //             }

    //             if ($serviceRate < 0) {
    //                 return back()->withErrors(['items' => 'Row ' . ($i + 1) . ' service rate invalid.'])->withInput();
    //             }

    //             if ($amountMode === 'manual' && $manualAmount > 0) {
    //                 $finalAmount = round($manualAmount, 2);
    //                 $lineBase = round($finalAmount / (1 + ($taxPct / 100)), 2);
    //                 $lineTax = round($finalAmount - $lineBase, 2);
    //             } else {
    //                 $lineBase = round($serviceRate * $qty, 2);
    //                 $lineTax  = round($lineBase * ($taxPct / 100), 2);
    //                 $finalAmount = round($lineBase + $lineTax, 2);
    //             }

    //             $subtotal      += $lineBase;
    //             $weightedTax   += ($lineBase * $taxPct);
    //             $itemsTaxTotal += $lineTax;

    //             $cleanRows[] = [
    //                 'item_id'       => (int) $itemId,
    //                 'item_type'     => 'service',
    //                 'description'   => $desc,
    //                 'hsn'           => $hsn,
    //                 'qty'           => $qty,
    //                 'tax_percent'   => round($taxPct, 2),

    //                 'fixed_price'   => round($fixedPrice, 2),
    //                 'service_rate'  => round($serviceRate, 2),

    //                 'rate'          => $lineBase,
    //                 'tax_amount'    => $lineTax,
    //                 'amount'        => $finalAmount,

    //                 'making_charge' => round($serviceRate, 2),

    //                 'gold_wt'       => 0,
    //                 'silver_wt'     => 0,
    //                 'gold_rate'     => 0,
    //                 'silver_rate'   => 0,
    //                 'gemstone_wt'   => 0,
    //                 'diamond_wt'    => 0,
    //                 'making_rate'   => 0,
    //                 'stone_charges' => 0,
    //             ];

    //             continue;
    //         }

    //         $goldWt     = (float) ($row['gold_wt'] ?? 0);
    //         $silverWt   = (float) ($row['silver_wt'] ?? 0);
    //         $goldRate   = (float) ($row['gold_rate'] ?? 0);
    //         $silverRate = (float) ($row['silver_rate'] ?? 0);
    //         $makingRate = (float) ($row['making_rate'] ?? 0);

    //         $gemCt = (float) ($row['gemstone_wt'] ?? 0);
    //         $diaCt = (float) ($row['diamond_wt'] ?? 0);

    //         $fixedPrice = (float) ($row['fixed_price'] ?? $row['price'] ?? 0);

    //         if (
    //             $goldWt < 0 ||
    //             $silverWt < 0 ||
    //             $goldRate < 0 ||
    //             $silverRate < 0 ||
    //             $makingRate < 0 ||
    //             $fixedPrice < 0
    //         ) {
    //             return back()->withErrors(['items' => 'Row ' . ($i + 1) . ' invalid values.'])->withInput();
    //         }

    //         $productBase = $fixedPrice > 0
    //             ? $fixedPrice
    //             : (($goldWt * $goldRate) + ($silverWt * $silverRate));

    //         $makingAmount = round($productBase * ($makingRate / 100), 2);

    //         if ($amountMode === 'manual' && $manualAmount > 0) {
    //             $finalAmount = round($manualAmount, 2);
    //             $lineBase = round($finalAmount / (1 + ($taxPct / 100)), 2);
    //             $lineTax = round($finalAmount - $lineBase, 2);
    //         } else {
    //             $lineBase = round(($productBase + $makingAmount) * $qty, 2);
    //             $lineTax = round($lineBase * ($taxPct / 100), 2);
    //             $finalAmount = round($lineBase + $lineTax, 2);
    //         }

    //         $subtotal      += $lineBase;
    //         $weightedTax   += ($lineBase * $taxPct);
    //         $itemsTaxTotal += $lineTax;

    //         $cleanRows[] = [
    //             'item_id'     => (int) $itemId,
    //             'item_type'   => 'product',
    //             'description' => $desc,
    //             'hsn'         => $hsn,
    //             'qty'         => $qty,
    //             'tax_percent' => round($taxPct, 2),

    //             'fixed_price' => round($fixedPrice, 2),

    //             'gold_wt'     => round($goldWt, 3),
    //             'silver_wt'   => round($silverWt, 3),
    //             'gold_rate'   => round($goldRate, 2),
    //             'silver_rate' => round($silverRate, 2),

    //             'gemstone_wt' => round($gemCt, 3),
    //             'diamond_wt'  => round($diaCt, 3),

    //             'making_rate'   => round($makingRate, 2),
    //             'making_charge' => round($makingAmount, 2),
    //             'stone_charges' => null,

    //             'rate'        => $lineBase,
    //             'tax_amount'  => $lineTax,
    //             'amount'      => $finalAmount,
    //         ];
    //     }

    //     $subtotal = round($subtotal, 2);
    //     $itemsTaxTotal = round($itemsTaxTotal, 2);

    //     $avgTaxPercentRaw = ($subtotal > 0) ? ($weightedTax / $subtotal) : 0;
    //     $avgTaxPercent = round($avgTaxPercentRaw, 2);

    //     $discountTotal = round((float) ($data['discount_total'] ?? 0), 2);
    //     $chargeTotal   = round((float) ($data['charge_total'] ?? 0), 2);

    //     $taxableAmount = round(max(0, $subtotal - $discountTotal + $chargeTotal), 2);

    //     $chargesTax = 0.0;
    //     $taxAmount = round($itemsTaxTotal + $chargesTax, 2);

    //     $tcsPercent = round((float) ($data['tcs_percent'] ?? 0), 2);
    //     $tcsAmount  = round((float) ($data['tcs_amount'] ?? 0), 2);

    //     if ($tcsPercent > 0) {
    //         $tcsAmount = round($taxableAmount * ($tcsPercent / 100), 2);
    //     }

    //     $roundOff   = round((float) ($data['round_off'] ?? 0), 2);
    //     $lessAmount = round((float) ($data['less_amount'] ?? $discountTotal), 2);

    //     $grandTotal = round($taxableAmount + $taxAmount + $tcsAmount + $roundOff, 2);

    //     $cash = $online = $card = $cheque = $credit = $advance = 0.0;
    //     $receivedTotal = 0.0;
    //     $balance = $grandTotal;

    //     if ($docType === 'tax') {
    //         $cash    = (float) ($pay['pay_cash'] ?? 0);
    //         $online  = (float) ($pay['pay_upi'] ?? 0);
    //         $card    = (float) ($pay['pay_card'] ?? 0);
    //         $cheque  = (float) ($pay['pay_cheque'] ?? 0);
    //         $credit  = (float) ($pay['credit_sales_excess'] ?? 0);
    //         $advance = (float) ($pay['advance_amount'] ?? 0);

    //         $receivedTotal = round($cash + $online + $card + $cheque, 2);
    //         $balance = round(max(0, $grandTotal - $receivedTotal - $advance - $credit), 2);
    //     }

    //     $chargesArr = [];
    //     if (!empty($data['charges_json'])) {
    //         $tmp = json_decode($data['charges_json'], true);

    //         if (is_array($tmp)) {
    //             foreach ($tmp as $c) {
    //                 $nm = trim((string) ($c['name'] ?? ''));
    //                 $am = (float) ($c['amount'] ?? 0);

    //                 if ($nm !== '' && $am != 0) {
    //                     $chargesArr[] = [
    //                         'name'   => $nm,
    //                         'amount' => round($am, 2),
    //                     ];
    //                 }
    //             }
    //         }
    //     }

    //     $business = Business::findOrFail($bid);
    //     $signaturePath = null;

    //     if (!$r->boolean('remove_signature')) {
    //         if ($r->hasFile('signature')) {
    //             $signaturePath = $r->file('signature')->store("invoices/{$bid}/signatures", 'public');

    //             $business->update([
    //                 'signature' => $signaturePath,
    //             ]);
    //         } elseif (!empty($business->signature)) {
    //             $signaturePath = $business->signature;
    //         }
    //     }

    //     $kots = [];
    //     if ($r->filled('kots_json')) {
    //         $tmp = json_decode($r->input('kots_json'), true);

    //         if (is_array($tmp)) {
    //             $kots = collect($tmp)
    //                 ->map(fn ($v) => trim((string) $v))
    //                 ->filter(fn ($v) => $v !== '')
    //                 ->unique()
    //                 ->values()
    //                 ->take(50)
    //                 ->all();
    //         }
    //     }

    //     $invoice = null;

    //     try {
    //         DB::transaction(function () use (
    //             $r,
    //             $bid,
    //             $data,
    //             $invoiceDate,
    //             $prefix,
    //             $docType,
    //             $subtotal,
    //             $avgTaxPercent,
    //             $taxableAmount,
    //             $taxAmount,
    //             $discountTotal,
    //             $chargeTotal,
    //             $tcsPercent,
    //             $tcsAmount,
    //             $roundOff,
    //             $lessAmount,
    //             $grandTotal,
    //             $receivedTotal,
    //             $balance,
    //             $cash,
    //             $online,
    //             $card,
    //             $cheque,
    //             $credit,
    //             $advance,
    //             $pay,
    //             $cleanRows,
    //             $normCode,
    //             $chargesArr,
    //             $kots,
    //             &$invoice,
    //             $stock,
    //             $signaturePath
    //         ) {
    //             $biz = Business::findOrFail($bid);
    //             $client = Client::where('business_id', $bid)->findOrFail($data['client_id']);

    //             $bizCode   = $normCode($biz->state_code ?? '');
    //             $partyCode = $normCode($client->state_code ?? '');

    //             $isIntra = ($bizCode !== '' && $partyCode !== '') ? ($bizCode === $partyCode) : false;

    //             $cgstPercent = $isIntra ? round($avgTaxPercent / 2, 2) : 0;
    //             $sgstPercent = $isIntra ? round($avgTaxPercent / 2, 2) : 0;
    //             $igstPercent = $isIntra ? 0 : round($avgTaxPercent, 2);

    //             $cgst = $isIntra ? round($taxAmount / 2, 2) : 0;
    //             $sgst = $isIntra ? round($taxAmount / 2, 2) : 0;
    //             $igst = $isIntra ? 0 : round($taxAmount, 2);

    //             $reqInvoiceNo = trim((string) ($data['invoice_number'] ?? ''));

    //             if ($reqInvoiceNo === '') {
    //                 $alloc = \App\Services\InvoiceNumber::next((int) $bid, $invoiceDate, $prefix, 3, $docType);
    //                 $reqInvoiceNo = $alloc['full'];
    //             }

    //             \App\Services\InvoiceNumber::syncNextSeqIfMatches((int) $bid, $invoiceDate, $reqInvoiceNo, 3, $docType);

    //             $invoice = Invoice::create([
    //                 'business_id'     => $bid,
    //                 'client_id'       => $data['client_id'],
    //                 'invoice_date'    => $invoiceDate,

    //                 'invoice_prefix'  => $prefix,
    //                 'invoice_number'  => $reqInvoiceNo,
    //                 'invoice_type'    => $docType,

    //                 'subtotal'        => $subtotal,
    //                 'discount_total'  => $discountTotal,
    //                 'charge_total'    => $chargeTotal,
    //                 'less_amount'     => $lessAmount,

    //                 'tax_amount'      => $taxAmount,

    //                 'cgst_percent'    => $cgstPercent,
    //                 'cgst_amount'     => $cgst,
    //                 'sgst_percent'    => $sgstPercent,
    //                 'sgst_amount'     => $sgst,
    //                 'igst_percent'    => $igstPercent,
    //                 'igst_amount'     => $igst,

    //                 'tcs_percent'     => $tcsPercent,
    //                 'tcs_amount'      => $tcsAmount,
    //                 'round_off'       => $roundOff,

    //                 'total'           => $grandTotal,
    //                 'received_amount' => $receivedTotal,
    //                 'balance'         => $balance,

    //                 'payment_method'  => $data['payment_method'] ?? null,

    //                 'gst_no'          => $data['gst_no'] ?? null,
    //                 'transport_mode'  => $data['transport_mode'] ?? null,
    //                 'reverse_charge'  => !empty($data['reverse_charge']) ? 1 : 0,

    //                 'place_of_supply_state' => $client->state ?? null,
    //                 'place_of_supply_code'  => $client->state_code ?? null,

    //                 'notes'           => $data['notes'] ?? null,
    //                 'terms'           => $data['terms'] ?? null,

    //                 'charges_json'    => json_encode($chargesArr),
    //                 'items_json'      => json_encode($cleanRows),

    //                 'amount_in_words' => '',
    //                 'signature'       => $signaturePath,

    //                 'created_by'      => auth()->user()->id ?? null,
    //                 'updated_by'      => auth()->user()->id ?? null,
    //                 'kots_json'       => json_encode($kots),
    //             ]);

    //             foreach ($chargesArr as $c) {
    //                 \App\Models\InvoiceAdditionalCharge::create([
    //                     'invoice_id'            => $invoice->id,
    //                     'additional_charge_id'  => null,
    //                     'name'                  => $c['name'],
    //                     'amount'                => $c['amount'],
    //                 ]);
    //             }

    //             foreach ($cleanRows as $row) {
    //                 $qty = (int) ($row['qty'] ?? 1);

    //                 $rate      = round((float) ($row['rate'] ?? 0), 2);
    //                 $lineTotal = round((float) ($row['amount'] ?? $rate), 2);

    //                 InvoiceItem::create([
    //                     'invoice_id'   => $invoice->id,
    //                     'item_id'      => $row['item_id'],
    //                     'description'  => $row['description'] ?? '',
    //                     'sac_code'     => null,
    //                     'hsn_code'     => $row['hsn'] ?: null,
    //                     'quantity'     => $qty,

    //                     'gold_wt'      => (float) ($row['gold_wt'] ?? 0),
    //                     'silver_wt'    => (float) ($row['silver_wt'] ?? 0),
    //                     'gold_rate'    => (float) ($row['gold_rate'] ?? 0),
    //                     'silver_rate'  => (float) ($row['silver_rate'] ?? 0),

    //                     'gemstone_wt_ct' => (float) ($row['gemstone_wt'] ?? 0),
    //                     'diamond_wt_ct'  => (float) ($row['diamond_wt'] ?? 0),

    //                     'making_charge' => (float) ($row['making_charge'] ?? 0),

    //                     'making_rate' => ($row['item_type'] === 'product')
    //                         ? (float) ($row['making_rate'] ?? 0)
    //                         : null,

    //                     'discount'    => 0,
    //                     'tax_percent' => (float) ($row['tax_percent'] ?? 0),

    //                     'rate'        => $rate,
    //                     'amount'      => $lineTotal,
    //                 ]);
    //             }

    //             if ($docType === 'tax') {
    //                 InvoicePayment::create([
    //                     'business_id' => $bid,
    //                     'invoice_id'  => $invoice->id,
    //                     'client_id'   => $data['client_id'],

    //                     'total_value' => $grandTotal,

    //                     'cash_amount'   => $cash,
    //                     'online_amount' => $online,
    //                     'card_amount'   => $card,
    //                     'cheque_amount' => $cheque,

    //                     'online_mode' => $pay['online_mode'] ?? null,
    //                     'online_ref'  => $pay['online_ref'] ?? null,
    //                     'upi_id'      => $pay['upi_id'] ?? null,

    //                     'card_last4'  => $pay['card_last4'] ?? null,
    //                     'card_ref'    => $pay['card_ref'] ?? null,

    //                     'cheque_no'   => $pay['cheque_no'] ?? null,
    //                     'bank_name'   => $pay['bank_name'] ?? null,

    //                     'credit_sales_excess_amount' => $credit,
    //                     'advance_amount'             => $advance,

    //                     'received_total' => $receivedTotal,
    //                     'notes'          => $pay['notes'] ?? null,
    //                     'meta'           => null,
    //                     'paid_at'        => $receivedTotal > 0 ? now() : null,
    //                 ]);

    //                 $invoice->load(['items']);
    //                 $stock->recordSale($invoice);

    //                 $bankAccountId = $r->input('bank_account_id');
    //                 $mode = strtolower(trim((string) ($data['payment_method'] ?? '')));
    //                 $bankModes = ['upi', 'bank', 'card', 'cheque'];

    //                 if ($bankAccountId && in_array($mode, $bankModes, true) && $receivedTotal > 0) {
    //                     $bank = \App\Models\BankAccount::where('business_id', $bid)
    //                         ->where('id', $bankAccountId)
    //                         ->first();

    //                     if ($bank) {
    //                         $bank->balance = round(((float) $bank->balance) + $receivedTotal, 2);
    //                         $bank->save();
    //                     }
    //                 }
    //             }
    //         });

    //         $pdf = $this->simplePdfBuild($invoice);

    //         $dir = "invoices/{$bid}/" . now()->format('Y-m');
    //         $safeName = preg_replace('/[^A-Za-z0-9\-_\.]/', '-', (string) $invoice->invoice_number);
    //         $filename = $safeName . ".pdf";
    //         $path = $dir . "/" . $filename;

    //         Storage::disk('public')->put($path, $pdf->output());

    //         $invoice->update([
    //             'pdf_url' => $path,
    //         ]);

    //     } catch (\Throwable $e) {
    //         report($e);

    //         return back()
    //             ->withErrors(['invoice' => 'Invoice save करते समय error आया: ' . $e->getMessage()])
    //             ->withInput();
    //     }

    //     return redirect()
    //         ->route('invoices.preview', $invoice->id)
    //         ->with('success', $docType === 'proforma' ? 'Proforma created successfully.' : 'Invoice created successfully.');
    // }


    public function store(Request $r, StockService $stock, $docType)
    {
        $user = $r->user();

        $bid = $user->current_business_id ?? session('active_business_id');

        if (!$bid) {
            $bid = $user->businesses()->pluck('businesses.id')->first();
        }

        if (!$bid) {
            return back()
                ->withErrors(['business' => 'Active business select/attach नहीं है.'])
                ->withInput();
        }

        if (!$user->hasAnyRole(['super_admin', 'admin'])) {
            $activePlan = UserPlan::where('business_id', $bid)
                ->where(function ($q) {
                    $q->where('status', 'active')
                        ->orWhere('status', 1);
                })
                ->whereDate('start_date', '<=', today())
                ->where(function ($q) {
                    $q->whereNull('expiry_date')
                        ->orWhereDate('expiry_date', '>=', today());
                })
                ->latest('id')
                ->first();

            if (!$activePlan) {
                return back()
                    ->withErrors([
                        'plan' => 'इस business का active plan available नहीं है या plan expire हो चुका है. Invoice create करने के लिए कृपया plan renew करें.'
                    ])
                    ->withInput();
            }
        }

        $docType = strtolower(trim((string) $docType));
        if (!in_array($docType, ['tax', 'proforma', 'quotation'], true)) {
            $docType = 'tax';
        }

        $data = $r->validate([
            'client_id'      => ['required', 'exists:clients,id'],
            'invoice_date'   => ['required', 'date'],
            'invoice_prefix' => ['nullable', 'string', 'max:255'],
            'invoice_number' => ['required', 'string', 'max:255'],

            'transport_mode' => ['nullable', 'string', 'max:255'],
            'gst_no'         => ['nullable', 'string', 'max:50'],
            'reverse_charge' => ['nullable'],

            'notes'          => ['nullable', 'string', 'max:2000'],
            'terms'          => ['nullable', 'string', 'max:2000'],
            'items_json'     => ['required', 'string'],

            'charges_json'   => ['nullable', 'string'],
            'discount_total' => ['nullable', 'numeric', 'min:0'],
            'charge_total'   => ['nullable', 'numeric', 'min:0'],
            'tcs_percent'    => ['nullable', 'numeric', 'min:0'],
            'tcs_amount'     => ['nullable', 'numeric', 'min:0'],
            'round_off'      => ['nullable', 'numeric'],
            'less_amount'    => ['nullable', 'numeric', 'min:0'],

            'cgst_percent'   => ['nullable', 'numeric', 'min:0'],
            'sgst_percent'   => ['nullable', 'numeric', 'min:0'],
            'igst_percent'   => ['nullable', 'numeric', 'min:0'],

            'payment_method'   => ['nullable', 'string', 'max:255'],
            'bank_account_id'  => ['nullable', 'integer'],
            'signature'        => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'kots_json'        => ['nullable', 'string', 'max:5000'],
            'remove_signature' => ['nullable', 'boolean'],
            'items_json' => ['required', 'string'],
        ]);

        $pay = [];
        if ($docType === 'tax') {
            $pay = $r->validate([
                'pay_cash'            => ['nullable', 'numeric', 'min:0'],
                'pay_upi'             => ['nullable', 'numeric', 'min:0'],
                'pay_card'            => ['nullable', 'numeric', 'min:0'],
                'pay_cheque'          => ['nullable', 'numeric', 'min:0'],

                'credit_sales_excess' => ['nullable', 'numeric', 'min:0'],
                'advance_amount'      => ['nullable', 'numeric', 'min:0'],

                'online_mode'         => ['nullable', 'string', 'max:30'],
                'online_ref'          => ['nullable', 'string', 'max:100'],
                'upi_id'              => ['nullable', 'string', 'max:100'],

                'card_last4'          => ['nullable', 'string', 'max:4'],
                'card_ref'            => ['nullable', 'string', 'max:100'],

                'cheque_no'           => ['nullable', 'string', 'max:50'],
                'bank_name'           => ['nullable', 'string', 'max:100'],
                'notes'               => ['nullable', 'string', 'max:2000'],
            ]);
        }

        $computePrefix = function (string $date, string $base = 'INV'): string {
            $ts = strtotime($date);
            $y  = (int) date('Y', $ts);
            $m  = (int) date('n', $ts);

            $startYY = ($m >= 4) ? ($y % 100) : (($y - 1) % 100);

            $a = str_pad((string) $startYY, 2, '0', STR_PAD_LEFT);
            $b = str_pad((string) (($startYY + 1) % 100), 2, '0', STR_PAD_LEFT);

            $base = rtrim($base, '/');

            return "{$base}/{$a}-{$b}/";
        };

        $invoiceDate = \Carbon\Carbon::parse($data['invoice_date'])->toDateString();
        $prefix = trim($data['invoice_prefix'] ?? '');

        $base = ($docType === 'proforma') ? 'PF' : (($docType === 'quotation') ? 'QT' : 'INV');

        if ($prefix === '') {
            $prefix = $computePrefix($invoiceDate, $base);
        }

        $rows = json_decode($data['items_json'], true);

        if (!is_array($rows) || count($rows) < 1) {
            return back()->withErrors(['items' => 'कम से कम 1 line item जरूरी है.'])->withInput();
        }

        $normCode = function ($v) {
            $s = trim((string) $v);
            $s = preg_replace('/\D+/', '', $s);
            $s = ltrim($s, '0');
            return $s;
        };

        $subtotal      = 0.0;
        $weightedTax   = 0.0;
        $itemsTaxTotal = 0.0;
        $cleanRows     = [];

        foreach ($rows as $i => $row) {
            $itemId = $row['item_id'] ?? null;

            if (empty($itemId)) {
                return back()->withErrors(['items' => 'Row ' . ($i + 1) . ' में Item select नहीं है.'])->withInput();
            }

            
            $desc = trim((string) ($row['description'] ?? ''));
            if ($desc === '') {
                return back()->withErrors(['items' => 'Row ' . ($i + 1) . ' description missing.'])->withInput();
            }

            $hsn = trim((string) ($row['hsn'] ?? ''));
            $qty = max(1, (int) ($row['quantity'] ?? 1));

            $taxPct = (float) ($row['tax_percent'] ?? 0);
            if ($taxPct < 0 || $taxPct > 100) {
                return back()->withErrors(['items' => 'Row ' . ($i + 1) . ' tax % invalid.'])->withInput();
            }

            $fixedPrice = (float) ($row['fixed_price'] ?? $row['price'] ?? $row['service_rate'] ?? 0);

            $goldWt     = (float) ($row['gold_wt'] ?? 0);
            $silverWt   = (float) ($row['silver_wt'] ?? 0);
            $goldRate   = (float) ($row['gold_rate'] ?? 0);
            $silverRate = (float) ($row['silver_rate'] ?? 0);
            $makingRate = (float) ($row['making_rate'] ?? 0);

            // $makingChargeType = strtolower(trim((string) ($row['making_charge_type'] ?? 'percent')));

            // if (!in_array($makingChargeType, ['fixed', 'percent'], true)) {
            //     $makingChargeType = 'percent';
            // }

            $makingChargeType = strtolower(trim((string) ($row['making_charge_type'] ?? 'percentage')));

            $allowedMakingTypes = [
                'percentage',
                'fixed',
                'per_gram',
                'per_product',
            ];

            if (!in_array($makingChargeType, $allowedMakingTypes, true)) {
                $makingChargeType = 'percentage';
            }

            $gemCt = (float) ($row['gemstone_wt'] ?? 0);
            $diaCt = (float) ($row['diamond_wt'] ?? 0);

            $stoneCharges   = (float) ($row['gemstone_charge'] ?? $row['stone_charges'] ?? 0);
            $diamondCharges = (float) ($row['diamond_charge'] ?? $row['diamond_charges'] ?? 0);

            if (
                $fixedPrice < 0 ||
                $goldWt < 0 ||
                $silverWt < 0 ||
                $goldRate < 0 ||
                $silverRate < 0 ||
                $makingRate < 0 ||
                $gemCt < 0 ||
                $diaCt < 0 ||
                $stoneCharges < 0 ||
                $diamondCharges < 0
            ) {
                return back()->withErrors(['items' => 'Row ' . ($i + 1) . ' invalid values.'])->withInput();
            }

            $metalBase = ($goldWt * $goldRate) + ($silverWt * $silverRate);

            $basePrice = $fixedPrice > 0 ? $fixedPrice : $metalBase;

            // $makingAmount = round($basePrice * ($makingRate / 100), 2);

            // if ($makingChargeType === 'fixed') {
            //     $makingAmount = round($makingRate, 2);
            // } else {
            //     $makingAmount = round($basePrice * ($makingRate / 100), 2);
            // }


            if ($makingChargeType === 'percentage') {
                $makingAmount = round($basePrice * ($makingRate / 100), 2);

            } elseif ($makingChargeType === 'fixed') {
                $makingAmount = round($makingRate, 2);

            } elseif ($makingChargeType === 'per_gram') {
                $totalMetalWeight = $goldWt + $silverWt;
                $makingAmount = round($totalMetalWeight * $makingRate, 2);

            } elseif ($makingChargeType === 'per_product') {
                $makingAmount = round($makingRate, 2);

            } else {
                $makingAmount = 0;
            }

            $lineBase = round(
                ($basePrice + $makingAmount + $stoneCharges + $diamondCharges) * $qty,
                2
            );

            $lineTax = round($lineBase * ($taxPct / 100), 2);
            $finalAmount = round($lineBase + $lineTax, 2);

            $subtotal      += $lineBase;
            $weightedTax   += ($lineBase * $taxPct);
            $itemsTaxTotal += $lineTax;

            $cleanRows[] = [
                'item_id'     => (int) $itemId,
                'item_type'   => 'product',
                'description' => $desc,
                'hsn'         => $hsn,
                'qty'         => $qty,
                'tax_percent' => round($taxPct, 2),

                'fixed_price'  => round($fixedPrice, 2),
                'service_rate' => round($fixedPrice, 2),

                'gold_wt'     => round($goldWt, 3),
                'silver_wt'   => round($silverWt, 3),
                'gold_rate'   => round($goldRate, 2),
                'silver_rate' => round($silverRate, 2),

                'gemstone_wt' => round($gemCt, 3),
                'diamond_wt'  => round($diaCt, 3),

                'making_charge_type' => $makingChargeType,
                'making_rate'   => round($makingRate, 2),
                'making_charge' => round($makingAmount, 2),

                'stone_charges'   => round($stoneCharges, 2),
                'diamond_charges' => round($diamondCharges, 2),

                'rate'       => $lineBase,
                'tax_amount' => $lineTax,
                'amount'     => $finalAmount,
            ];
        }

        $subtotal = round($subtotal, 2);
        $itemsTaxTotal = round($itemsTaxTotal, 2);

        $avgTaxPercentRaw = ($subtotal > 0) ? ($weightedTax / $subtotal) : 0;
        $avgTaxPercent = round($avgTaxPercentRaw, 2);

        $discountTotal = round((float) ($data['discount_total'] ?? 0), 2);
        $chargeTotal   = round((float) ($data['charge_total'] ?? 0), 2);

        $taxableAmount = round(max(0, $subtotal - $discountTotal + $chargeTotal), 2);

        $chargesTax = 0.0;
        $taxAmount = round($itemsTaxTotal + $chargesTax, 2);

        $tcsPercent = round((float) ($data['tcs_percent'] ?? 0), 2);
        $tcsAmount  = round((float) ($data['tcs_amount'] ?? 0), 2);

        if ($tcsPercent > 0) {
            $tcsAmount = round($taxableAmount * ($tcsPercent / 100), 2);
        }

        $roundOff   = round((float) ($data['round_off'] ?? 0), 2);
        $lessAmount = round((float) ($data['less_amount'] ?? $discountTotal), 2);

        $grandTotal = round($taxableAmount + $taxAmount + $tcsAmount + $roundOff, 2);

        $cash = $online = $card = $cheque = $credit = $advance = 0.0;
        $receivedTotal = 0.0;
        $balance = $grandTotal;

        if ($docType === 'tax') {
            $cash    = (float) ($pay['pay_cash'] ?? 0);
            $online  = (float) ($pay['pay_upi'] ?? 0);
            $card    = (float) ($pay['pay_card'] ?? 0);
            $cheque  = (float) ($pay['pay_cheque'] ?? 0);
            $credit  = (float) ($pay['credit_sales_excess'] ?? 0);
            $advance = (float) ($pay['advance_amount'] ?? 0);

            $receivedTotal = round($cash + $online + $card + $cheque, 2);
            $balance = round(max(0, $grandTotal - $receivedTotal - $advance - $credit), 2);
        }

        $chargesArr = [];
        if (!empty($data['charges_json'])) {
            $tmp = json_decode($data['charges_json'], true);

            if (is_array($tmp)) {
                foreach ($tmp as $c) {
                    $nm = trim((string) ($c['name'] ?? ''));
                    $am = (float) ($c['amount'] ?? 0);

                    if ($nm !== '' && $am != 0) {
                        $chargesArr[] = [
                            'name'   => $nm,
                            'amount' => round($am, 2),
                        ];
                    }
                }
            }
        }

        $business = Business::findOrFail($bid);
        $signaturePath = null;

        if (!$r->boolean('remove_signature')) {
            if ($r->hasFile('signature')) {
                $signaturePath = $r->file('signature')->store("invoices/{$bid}/signatures", 'public');

                $business->update([
                    'signature' => $signaturePath,
                ]);
            } elseif (!empty($business->signature)) {
                $signaturePath = $business->signature;
            }
        }

        $kots = [];
        if ($r->filled('kots_json')) {
            $tmp = json_decode($r->input('kots_json'), true);

            if (is_array($tmp)) {
                $kots = collect($tmp)
                    ->map(fn ($v) => trim((string) $v))
                    ->filter(fn ($v) => $v !== '')
                    ->unique()
                    ->values()
                    ->take(50)
                    ->all();
            }
        }

        $invoice = null;

        try {
            DB::transaction(function () use (
                $r,
                $bid,
                $data,
                $invoiceDate,
                $prefix,
                $docType,
                $subtotal,
                $avgTaxPercent,
                $taxableAmount,
                $taxAmount,
                $discountTotal,
                $chargeTotal,
                $tcsPercent,
                $tcsAmount,
                $roundOff,
                $lessAmount,
                $grandTotal,
                $receivedTotal,
                $balance,
                $cash,
                $online,
                $card,
                $cheque,
                $credit,
                $advance,
                $pay,
                $cleanRows,
                $normCode,
                $chargesArr,
                $kots,
                &$invoice,
                $stock,
                $signaturePath
            ) {
                $biz = Business::findOrFail($bid);
                $client = Client::where('business_id', $bid)->findOrFail($data['client_id']);

                $bizCode   = $normCode($biz->state_code ?? '');
                $partyCode = $normCode($client->state_code ?? '');

                $isIntra = ($bizCode !== '' && $partyCode !== '') ? ($bizCode === $partyCode) : false;

                $cgstPercent = $isIntra ? round($avgTaxPercent / 2, 2) : 0;
                $sgstPercent = $isIntra ? round($avgTaxPercent / 2, 2) : 0;
                $igstPercent = $isIntra ? 0 : round($avgTaxPercent, 2);

                $cgst = $isIntra ? round($taxAmount / 2, 2) : 0;
                $sgst = $isIntra ? round($taxAmount / 2, 2) : 0;
                $igst = $isIntra ? 0 : round($taxAmount, 2);

                $reqInvoiceNo = trim((string) ($data['invoice_number'] ?? ''));

                if ($reqInvoiceNo === '') {
                    $alloc = \App\Services\InvoiceNumber::next((int) $bid, $invoiceDate, $prefix, 3, $docType);
                    $reqInvoiceNo = $alloc['full'];
                }

                \App\Services\InvoiceNumber::syncNextSeqIfMatches((int) $bid, $invoiceDate, $reqInvoiceNo, 3, $docType);

                $invoice = Invoice::create([
                    'business_id'     => $bid,
                    'client_id'       => $data['client_id'],
                    'invoice_date'    => $invoiceDate,

                    'invoice_prefix'  => $prefix,
                    'invoice_number'  => $reqInvoiceNo,
                    'invoice_type'    => $docType,

                    'subtotal'        => $subtotal,
                    'discount_total'  => $discountTotal,
                    'charge_total'    => $chargeTotal,
                    'less_amount'     => $lessAmount,

                    'tax_amount'      => $taxAmount,

                    'cgst_percent'    => $cgstPercent,
                    'cgst_amount'     => $cgst,
                    'sgst_percent'    => $sgstPercent,
                    'sgst_amount'     => $sgst,
                    'igst_percent'    => $igstPercent,
                    'igst_amount'     => $igst,

                    'tcs_percent'     => $tcsPercent,
                    'tcs_amount'      => $tcsAmount,
                    'round_off'       => $roundOff,

                    'total'           => $grandTotal,
                    'received_amount' => $receivedTotal,
                    'balance'         => $balance,

                    'payment_method'  => $data['payment_method'] ?? null,

                    'gst_no'          => $data['gst_no'] ?? null,
                    'transport_mode'  => $data['transport_mode'] ?? null,
                    'reverse_charge'  => !empty($data['reverse_charge']) ? 1 : 0,

                    'place_of_supply_state' => $client->state ?? null,
                    'place_of_supply_code'  => $client->state_code ?? null,

                    'notes'           => $data['notes'] ?? null,
                    'terms'           => $data['terms'] ?? null,

                    'charges_json'    => json_encode($chargesArr),
                    'items_json'      => json_encode($cleanRows),

                    'amount_in_words' => '',
                    'signature'       => $signaturePath,

                    'created_by'      => auth()->user()->id ?? null,
                    'updated_by'      => auth()->user()->id ?? null,
                    'kots_json'       => json_encode($kots),
                ]);

                foreach ($chargesArr as $c) {
                    \App\Models\InvoiceAdditionalCharge::create([
                        'invoice_id'            => $invoice->id,
                        'additional_charge_id'  => null,
                        'name'                  => $c['name'],
                        'amount'                => $c['amount'],
                    ]);
                }

                foreach ($cleanRows as $row) {
                    $qty = (int) ($row['qty'] ?? 1);

                    $rate      = round((float) ($row['rate'] ?? 0), 2);
                    $lineTotal = round((float) ($row['amount'] ?? $rate), 2);

                    InvoiceItem::create([
                        'invoice_id'   => $invoice->id,
                        'item_id'      => $row['item_id'],
                        'description'  => $row['description'] ?? '',
                        'sac_code'     => null,
                        'hsn_code'     => $row['hsn'] ?: null,
                        'quantity'     => $qty,

                        'gold_wt'      => (float) ($row['gold_wt'] ?? 0),
                        'silver_wt'    => (float) ($row['silver_wt'] ?? 0),
                        'gold_rate'    => (float) ($row['gold_rate'] ?? 0),
                        'silver_rate'  => (float) ($row['silver_rate'] ?? 0),

                        'gemstone_wt_ct' => (float) ($row['gemstone_wt'] ?? 0),
                        'diamond_wt_ct'  => (float) ($row['diamond_wt'] ?? 0),

                        'making_charge' => (float) ($row['making_charge'] ?? 0),

                        'making_rate' => (float) ($row['making_rate'] ?? 0),
                        // 'making_charge_type' => $row['making_charge_type'] ?? 'percent',

                        'making_charge_type' => $row['making_charge_type'] ?? 'percentage',

                        'discount'    => 0,
                        'tax_percent' => (float) ($row['tax_percent'] ?? 0),

                        'rate'        => $rate,
                        'amount'      => $lineTotal,
                    ]);
                }

                if ($docType === 'tax') {
                    InvoicePayment::create([
                        'business_id' => $bid,
                        'invoice_id'  => $invoice->id,
                        'client_id'   => $data['client_id'],

                        'total_value' => $grandTotal,

                        'cash_amount'   => $cash,
                        'online_amount' => $online,
                        'card_amount'   => $card,
                        'cheque_amount' => $cheque,

                        'online_mode' => $pay['online_mode'] ?? null,
                        'online_ref'  => $pay['online_ref'] ?? null,
                        'upi_id'      => $pay['upi_id'] ?? null,

                        'card_last4'  => $pay['card_last4'] ?? null,
                        'card_ref'    => $pay['card_ref'] ?? null,

                        'cheque_no'   => $pay['cheque_no'] ?? null,
                        'bank_name'   => $pay['bank_name'] ?? null,

                        'credit_sales_excess_amount' => $credit,
                        'advance_amount'             => $advance,

                        'received_total' => $receivedTotal,
                        'notes'          => $pay['notes'] ?? null,
                        'meta'           => null,
                        'paid_at'        => $receivedTotal > 0 ? now() : null,
                    ]);

                    $invoice->load(['items']);
                    $stock->recordSale($invoice);

                    $bankAccountId = $r->input('bank_account_id');
                    $mode = strtolower(trim((string) ($data['payment_method'] ?? '')));
                    $bankModes = ['upi', 'bank', 'card', 'cheque'];

                    if ($bankAccountId && in_array($mode, $bankModes, true) && $receivedTotal > 0) {
                        $bank = \App\Models\BankAccount::where('business_id', $bid)
                            ->where('id', $bankAccountId)
                            ->first();

                        if ($bank) {
                            $bank->balance = round(((float) $bank->balance) + $receivedTotal, 2);
                            $bank->save();
                        }
                    }
                }
            });

            $pdf = $this->simplePdfBuild($invoice);

            $dir = "invoices/{$bid}/" . now()->format('Y-m');
            $safeName = preg_replace('/[^A-Za-z0-9\-_\.]/', '-', (string) $invoice->invoice_number);
            $filename = $safeName . ".pdf";
            $path = $dir . "/" . $filename;

            Storage::disk('public')->put($path, $pdf->output());

            $invoice->update([
                'pdf_url' => $path,
            ]);

        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withErrors(['invoice' => 'Invoice save करते समय error आया: ' . $e->getMessage()])
                ->withInput();
        }

        return redirect()
            ->route('invoices.preview', $invoice->id)
            ->with('success', $docType === 'proforma' ? 'Proforma created successfully.' : 'Invoice created successfully.');
    }





//    public function update(Request $r, \App\Models\Invoice $invoice)
//    {
//        $bid = $r->user()->current_business_id ?? session('active_business_id');
//        if (!$bid) {
//            return back()
//                ->withErrors(['business' => 'Active business select/attach नहीं है.'])
//                ->withInput();
//        }
//
//        // Security: invoice belongs to business
//        abort_unless((int)$invoice->business_id === (int)$bid, 403);
//
//        // ----------------- VALIDATION -----------------
//        $data = $r->validate([
//            'client_id'      => ['required','exists:clients,id'],
//            'invoice_date'   => ['required','date'],
//            'invoice_prefix' => ['nullable','string','max:100'],
//
//            'transport_mode' => ['nullable','string','max:255'],
//            'gst_no'         => ['nullable','string','max:50'],
//            'reverse_charge' => ['nullable','boolean'],
//
//            'notes'          => ['nullable','string','max:2000'],
//            'terms'          => ['nullable','string','max:2000'],
//
//            'items_json'     => ['required','string'],
//
//            // totals hidden fields (optional)
//            'cgst_amount'    => ['nullable','numeric','min:0'],
//            'sgst_amount'    => ['nullable','numeric','min:0'],
//            'igst_amount'    => ['nullable','numeric','min:0'],
//        ]);
//
//        // Payment fields (invoice_payments)
//        $pay = $r->validate([
//            'pay_cash'            => ['nullable','numeric','min:0'],
//            'pay_upi'             => ['nullable','numeric','min:0'],
//            'pay_card'            => ['nullable','numeric','min:0'],
//            'pay_cheque'          => ['nullable','numeric','min:0'],
//
//            'credit_sales_excess' => ['nullable','numeric','min:0'],
//            'advance_amount'      => ['nullable','numeric','min:0'],
//
//            'online_mode'         => ['nullable','string','max:30'],
//            'online_ref'          => ['nullable','string','max:100'],
//            'upi_id'              => ['nullable','string','max:100'],
//
//            'card_last4'          => ['nullable','string','max:4'],
//            'card_ref'            => ['nullable','string','max:100'],
//
//            'cheque_no'           => ['nullable','string','max:50'],
//            'bank_name'           => ['nullable','string','max:100'],
//            'notes'               => ['nullable','string','max:2000'],
//        ]);
//
//        // ----------------- PREFIX (FY logic) -----------------
//        $computePrefix = function (string $date, string $base = 'INV'): string {
//            $ts = strtotime($date);
//            $y  = (int)date('Y', $ts);
//            $m  = (int)date('n', $ts);
//
//            $startYY = ($m >= 4) ? ($y % 100) : (($y - 1) % 100);
//            $a = str_pad((string)$startYY, 2, '0', STR_PAD_LEFT);
//            $b = str_pad((string)(($startYY + 1) % 100), 2, '0', STR_PAD_LEFT);
//            $fy = "{$a}-{$b}";
//
//            $base = rtrim($base, '/');
//            return "{$base}/{$fy}/";
//        };
//
//        $invoiceDate = \Carbon\Carbon::parse($data['invoice_date'])->toDateString();
//
//        // ✅ Edit mode me generally prefix lock hota hai
//        // Agar aap allow karna chahte ho to: $prefix = trim($data['invoice_prefix'] ?? $invoice->invoice_prefix ?? '');
//        $prefix = trim($invoice->invoice_prefix ?? '');
//        if ($prefix === '') {
//            // fallback
//            $prefix = trim($data['invoice_prefix'] ?? '');
//            if ($prefix === '') $prefix = $computePrefix($invoiceDate, 'INV');
//        }
//
//        // ----------------- ITEMS JSON PARSE -----------------
//        $rows = json_decode($data['items_json'], true);
//        if (!is_array($rows) || count($rows) < 1) {
//            return back()->withErrors(['items' => 'कम से कम 1 line item जरूरी है.'])->withInput();
//        }
//
//        // ----------------- TOTALS -----------------
//        $subtotal = 0.0;
//        $taxTotal = 0.0;
//        $cleanRows = [];
//
//        foreach ($rows as $i => $row) {
//
//            $desc = trim($row['description'] ?? '');
//            $hsn  = trim($row['hsn'] ?? '');
//
//            $qty      = (int)($row['quantity'] ?? 1);
//            $qty      = $qty < 1 ? 1 : $qty;
//
//            $taxPct   = (float)($row['tax_percent'] ?? 0);
//
//            $goldWt     = (float)($row['gold_wt'] ?? 0);
//            $silverWt   = (float)($row['silver_wt'] ?? 0);
//            $goldRate   = (float)($row['gold_rate'] ?? 0);
//            $silverRate = (float)($row['silver_rate'] ?? 0);
//            $making     = (float)($row['making_rate'] ?? 0);
//
//            $gemCt      = (float)($row['gemstone_wt'] ?? 0);
//            $diaCt      = (float)($row['diamond_wt'] ?? 0);
//
//            // basic validate
//            if ($desc === '' || $taxPct < 0 || $taxPct > 100 || $goldWt < 0 || $silverWt < 0 || $goldRate < 0 || $silverRate < 0 || $making < 0) {
//                return back()->withErrors(['items' => "Row ".($i+1)." invalid है."])->withInput();
//            }
//
//            // base = (gold+silver+making) * qty
//            $lineBase = round((($goldWt * $goldRate) + ($silverWt * $silverRate) + $making) * $qty, 2);
//
//            $discount = (float)($row['discount'] ?? 0);
//            if ($discount < 0) $discount = 0;
//
//            $taxable = max(0, round($lineBase - $discount, 2));
//            $lineTax = round($taxable * ($taxPct / 100), 2);
//            $lineAmt = round($taxable + $lineTax, 2);
//
//            $subtotal += $lineBase;
//            $taxTotal += $lineTax;
//
//            $cleanRows[] = [
//                'item_id'          => $row['item_id'] ?? null,
//                'description'      => $desc,
//
//                'hsn_code'         => $hsn ?: null,
//                'sac_code'         => $row['sac'] ?? null,
//
//                'quantity'         => $qty,
//
//                'gold_wt'          => $goldWt,
//                'silver_wt'        => $silverWt,
//
//                'gold_rate'        => $goldRate,
//                'silver_rate'      => $silverRate,
//
//                'gemstone_wt_ct'   => $gemCt,
//                'diamond_wt_ct'    => $diaCt,
//
//                'making_rate'      => $making,
//
//                'discount'         => round($discount, 2),
//                'tax_percent'      => round($taxPct, 2),
//
//                'rate'             => $lineBase,
//                'amount'           => $lineAmt,
//            ];
//        }
//
//        $subtotal   = round($subtotal, 2);
//        $taxTotal   = round($taxTotal, 2);
//        $grandTotal = round($subtotal + $taxTotal, 2);
//
//        // ----------------- PAYMENT TOTALS -----------------
//        $cash    = (float)($pay['pay_cash'] ?? 0);
//        $online  = (float)($pay['pay_upi'] ?? 0);
//        $card    = (float)($pay['pay_card'] ?? 0);
//        $cheque  = (float)($pay['pay_cheque'] ?? 0);
//
//        $credit  = (float)($pay['credit_sales_excess'] ?? 0);
//        $advance = (float)($pay['advance_amount'] ?? 0);
//
//        $receivedTotal = round($cash + $online + $card + $cheque, 2);
//        $balance       = round(max(0, $grandTotal - $receivedTotal - $advance - $credit), 2);
//
//        // ----------------- SAVE -----------------
//        try {
//            \DB::transaction(function () use (
//                $bid, $invoice, $data, $invoiceDate, $prefix,
//                $subtotal, $taxTotal, $grandTotal, $receivedTotal, $balance,
//                $cash, $online, $card, $cheque, $credit, $advance,
//                $pay, $cleanRows
//            ) {
//
//                // 1) update invoice header & totals
//                $invoice->update([
//                    'client_id'       => $data['client_id'],
//                    'invoice_date'    => $invoiceDate,
//
//                    'invoice_prefix'  => $prefix,
//                    // ✅ invoice_number same (do not change)
//                    // 'invoice_number' => $invoice->invoice_number,
//
//                    'subtotal'        => $subtotal,
//                    'tax_amount'      => $taxTotal,
//
//                    'cgst_amount'     => $data['cgst_amount'] ?? ($invoice->cgst_amount ?? 0),
//                    'sgst_amount'     => $data['sgst_amount'] ?? ($invoice->sgst_amount ?? 0),
//                    'igst_amount'     => $data['igst_amount'] ?? ($invoice->igst_amount ?? 0),
//
//                    'total'           => $grandTotal,
//                    'received_amount' => $receivedTotal,
//                    'balance'         => $balance,
//
//                    'transport_mode'  => $data['transport_mode'] ?? null,
//                    'gst_no'          => $data['gst_no'] ?? null,
//                    'reverse_charge'  => (int)($data['reverse_charge'] ?? 0),
//
//                    'notes'           => $data['notes'] ?? null,
//                    'terms'           => $data['terms'] ?? null,
//
//                    'items_json'      => json_encode($cleanRows),
//                ]);
//
//                // 2) replace invoice_items
//                \App\Models\InvoiceItem::where('invoice_id', $invoice->id)->delete();
//
//                foreach ($cleanRows as $row) {
//                    \App\Models\InvoiceItem::create([
//                        'invoice_id'       => $invoice->id,
//                        'item_id'          => $row['item_id'] ?? null,
//                        'description'      => $row['description'] ?? '',
//
//                        'sac_code'         => $row['sac_code'] ?? null,
//                        'hsn_code'         => $row['hsn_code'] ?? null,
//
//                        'quantity'         => (int)($row['quantity'] ?? 1),
//
//                        'gold_wt'          => (float)($row['gold_wt'] ?? 0),
//                        'silver_wt'        => (float)($row['silver_wt'] ?? 0),
//
//                        'gold_rate'        => (float)($row['gold_rate'] ?? 0),
//                        'silver_rate'      => (float)($row['silver_rate'] ?? 0),
//
//                        'gemstone_wt_ct'   => (float)($row['gemstone_wt_ct'] ?? 0),
//                        'diamond_wt_ct'    => (float)($row['diamond_wt_ct'] ?? 0),
//
//                        'making_rate'      => (float)($row['making_rate'] ?? 0),
//
//                        'discount'         => (float)($row['discount'] ?? 0),
//                        'tax_percent'      => (float)($row['tax_percent'] ?? 0),
//
//                        'rate'             => (float)($row['rate'] ?? 0),
//                        'amount'           => (float)($row['amount'] ?? 0),
//                    ]);
//                }
//
//                // 3) update latest payment record OR create new
//                $payment = \App\Models\InvoicePayment::where('business_id', $bid)
//                    ->where('invoice_id', $invoice->id)
//                    ->latest('id')
//                    ->first();
//
//                $payload = [
//                    'business_id' => $bid,
//                    'invoice_id'  => $invoice->id,
//                    'client_id'   => $data['client_id'],
//
//                    'total_value' => $grandTotal,
//
//                    'cash_amount'   => $cash,
//                    'online_amount' => $online,
//                    'card_amount'   => $card,
//                    'cheque_amount' => $cheque,
//
//                    'online_mode' => $pay['online_mode'] ?? null,
//                    'online_ref'  => $pay['online_ref'] ?? null,
//                    'upi_id'      => $pay['upi_id'] ?? null,
//
//                    'card_last4'  => $pay['card_last4'] ?? null,
//                    'card_ref'    => $pay['card_ref'] ?? null,
//
//                    'cheque_no'   => $pay['cheque_no'] ?? null,
//                    'bank_name'   => $pay['bank_name'] ?? null,
//
//                    'credit_sales_excess_amount' => $credit,
//                    'advance_amount'             => $advance,
//
//                    'received_total' => $receivedTotal,
//
//                    'notes'   => $pay['notes'] ?? null,
//                    'meta'    => null,
//                    'paid_at' => $receivedTotal > 0 ? now() : null,
//                ];
//
//                if ($payment) {
//                    $payment->update($payload);
//                } else {
//                    \App\Models\InvoicePayment::create($payload);
//                }
//            });
//        } catch (\Throwable $e) {
//            report($e);
//            return back()->withErrors(['invoice' => 'Invoice update करते समय error आया: '.$e->getMessage()])->withInput();
//        }
//
//        return redirect()
//            ->route('invoices.index')
//            ->with('success', 'Invoice updated successfully.');
//    }


    // public function update(Request $r, \App\Models\Invoice $invoice, \App\Services\StockService $stock)
    // {
    //     $invoice = $invoice->load(['items', 'client', 'business']);

    //     $docType = strtolower(trim((string)($invoice->invoice_type ?? 'tax')));
    //     if (!in_array($docType, ['tax','proforma','quotation'], true)) $docType = 'tax';

    //     $bid = $r->user()->current_business_id ?? session('active_business_id');
    //     if (!$bid) $bid = $invoice->business_id;

    //     if ((int)$invoice->business_id !== (int)$bid) {
    //         abort(403, 'Unauthorized invoice access.');
    //     }

    //     $data = $r->validate([
    //         'client_id'      => ['required','exists:clients,id'],
    //         'invoice_date'   => ['required','date'],
    //         'invoice_prefix' => ['nullable','string','max:255'],

    //         'transport_mode' => ['nullable','string','max:255'],
    //         'gst_no'         => ['nullable','string','max:50'],
    //         'reverse_charge' => ['nullable'],

    //         'notes'          => ['nullable','string','max:2000'],
    //         'terms'          => ['nullable','string','max:2000'],

    //         'items_json'     => ['required','string'],

    //         'charges_json'   => ['nullable','string'],
    //         'discount_total' => ['nullable','numeric','min:0'],
    //         'charge_total'   => ['nullable','numeric','min:0'],
    //         'tcs_percent'    => ['nullable','numeric','min:0'],
    //         'tcs_amount'     => ['nullable','numeric','min:0'],
    //         'round_off'      => ['nullable','numeric'],
    //         'less_amount'    => ['nullable','numeric','min:0'],

    //         'cgst_percent'   => ['nullable','numeric','min:0'],
    //         'sgst_percent'   => ['nullable','numeric','min:0'],
    //         'igst_percent'   => ['nullable','numeric','min:0'],

    //         'payment_method'  => ['nullable','string','max:255'],
    //         'bank_account_id' => ['nullable','integer'],
    //         'signature' => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],
    //     ]);

    //     // ✅ payment validate only for TAX
    //     $pay = [];
    //     if ($docType === 'tax') {
    //         $pay = $r->validate([
    //             'pay_cash'            => ['nullable','numeric','min:0'],
    //             'pay_upi'             => ['nullable','numeric','min:0'],
    //             'pay_card'            => ['nullable','numeric','min:0'],
    //             'pay_cheque'          => ['nullable','numeric','min:0'],

    //             'credit_sales_excess' => ['nullable','numeric','min:0'],
    //             'advance_amount'      => ['nullable','numeric','min:0'],

    //             'online_mode'         => ['nullable','string','max:30'],
    //             'online_ref'          => ['nullable','string','max:100'],
    //             'upi_id'              => ['nullable','string','max:100'],

    //             'card_last4'          => ['nullable','string','max:4'],
    //             'card_ref'            => ['nullable','string','max:100'],

    //             'cheque_no'           => ['nullable','string','max:50'],
    //             'bank_name'           => ['nullable','string','max:100'],
    //             'notes'               => ['nullable','string','max:2000'],
    //         ]);
    //     }

    //     $invoiceDate = \Carbon\Carbon::parse($data['invoice_date'])->toDateString();
    //     $prefix      = trim($data['invoice_prefix'] ?? '');
    //     if ($prefix === '') $prefix = $invoice->invoice_prefix; // keep old if blank

    //     // -------- items parse --------
    //     $rows = json_decode($data['items_json'], true);
    //     if (!is_array($rows) || count($rows) < 1) {
    //         return back()->withErrors(['items' => 'कम से कम 1 line item जरूरी है.'])->withInput();
    //     }

    //     $normCode = function ($v) {
    //         $s = trim((string)$v);
    //         $s = preg_replace('/\D+/', '', $s);
    //         $s = ltrim($s, '0');
    //         return $s;
    //     };

    //     $subtotal      = 0.0;
    //     $weightedTax   = 0.0;
    //     $itemsTaxTotal = 0.0;
    //     $cleanRows     = [];

    //     foreach ($rows as $i => $row) {
    //         $itemId = $row['item_id'] ?? null;
    //         if (empty($itemId)) {
    //             return back()->withErrors(['items' => "Row ".($i+1)." में Item select नहीं है."])->withInput();
    //         }

    //         $itemType = strtolower(trim((string)($row['item_type'] ?? 'product')));
    //         if (!in_array($itemType, ['product','service'], true)) $itemType = 'product';

    //         $desc = trim($row['description'] ?? '');
    //         if ($desc === '') {
    //             return back()->withErrors(['items' => "Row ".($i+1)." description missing."])->withInput();
    //         }

    //         $hsn = trim($row['hsn'] ?? '');
    //         $qty = (int)($row['quantity'] ?? 1);
    //         $qty = $qty < 1 ? 1 : $qty;

    //         $taxPct = (float)($row['tax_percent'] ?? 0);
    //         if ($taxPct < 0 || $taxPct > 100) {
    //             return back()->withErrors(['items' => "Row ".($i+1)." tax % invalid."])->withInput();
    //         }

    //         if ($itemType === 'service') {
    //             $serviceRate = (float)($row['service_rate'] ?? 0);
    //             if ($serviceRate < 0) {
    //                 return back()->withErrors(['items' => "Row ".($i+1)." service rate invalid."])->withInput();
    //             }

    //             $lineBase = round($serviceRate * $qty, 2);
    //             $lineTax  = round($lineBase * ($taxPct/100), 2);

    //             $subtotal      += $lineBase;
    //             $weightedTax   += ($lineBase * $taxPct);
    //             $itemsTaxTotal += $lineTax;

    //             $cleanRows[] = [
    //                 'item_id'      => (int)$itemId,
    //                 'item_type'    => 'service',
    //                 'description'  => $desc,
    //                 'hsn'          => $hsn,
    //                 'qty'          => $qty,
    //                 'tax_percent'  => round($taxPct,2),

    //                 'service_rate' => round($serviceRate,2),

    //                 'rate'       => $lineBase,
    //                 'tax_amount' => $lineTax,
    //                 'amount'     => round($lineBase + $lineTax, 2),

    //                 // keep compatibility keys
    //                 'making_charge' => round($serviceRate,2),

    //                 'gold_wt'=>0,'silver_wt'=>0,'gold_rate'=>0,'silver_rate'=>0,
    //                 'gemstone_wt'=>0,'diamond_wt'=>0,'making_rate'=>0,
    //                 'stone_charges'=>0,
    //             ];
    //             continue;
    //         }

    //         // product
    //         $goldWt     = (float)($row['gold_wt'] ?? 0);
    //         $silverWt   = (float)($row['silver_wt'] ?? 0);
    //         $goldRate   = (float)($row['gold_rate'] ?? 0);
    //         $silverRate = (float)($row['silver_rate'] ?? 0);
    //         $makingRate = (float)($row['making_rate'] ?? 0);

    //         $gemCt = (float)($row['gemstone_wt'] ?? 0);
    //         $diaCt = (float)($row['diamond_wt'] ?? 0);

    //         if ($goldWt < 0 || $silverWt < 0 || $goldRate < 0 || $silverRate < 0 || $makingRate < 0) {
    //             return back()->withErrors(['items' => "Row ".($i+1)." invalid values."])->withInput();
    //         }

    //         $lineBase = round((($goldWt * $goldRate) + ($silverWt * $silverRate) + $makingRate) * $qty, 2);
    //         $lineTax  = round($lineBase * ($taxPct/100), 2);

    //         $subtotal      += $lineBase;
    //         $weightedTax   += ($lineBase * $taxPct);
    //         $itemsTaxTotal += $lineTax;

    //         $cleanRows[] = [
    //             'item_id'     => (int)$itemId,
    //             'item_type'   => 'product',
    //             'description' => $desc,
    //             'hsn'         => $hsn,
    //             'qty'         => $qty,
    //             'tax_percent' => round($taxPct,2),

    //             'gold_wt'     => round($goldWt,3),
    //             'silver_wt'   => round($silverWt,3),
    //             'gold_rate'   => round($goldRate,2),
    //             'silver_rate' => round($silverRate,2),

    //             'gemstone_wt' => round($gemCt,3),
    //             'diamond_wt'  => round($diaCt,3),

    //             'making_rate' => round($makingRate,2),

    //             'rate'       => $lineBase,
    //             'tax_amount' => $lineTax,
    //             'amount'     => round($lineBase + $lineTax, 2),
    //         ];
    //     }

    //     $subtotal      = round($subtotal, 2);
    //     $itemsTaxTotal = round($itemsTaxTotal, 2);

    //     $avgTaxPercentRaw = ($subtotal > 0) ? ($weightedTax / $subtotal) : 0;
    //     $avgTaxPercent    = round($avgTaxPercentRaw, 2);

    //     $discountTotal = round((float)($data['discount_total'] ?? 0), 2);
    //     $chargeTotal   = round((float)($data['charge_total'] ?? 0), 2);

    //     $taxableAmount = round(max(0, $subtotal - $discountTotal + $chargeTotal), 2);

    //     // charges taxable OFF by default
    //     $chargesTax = 0.0;
    //     $taxAmount  = round($itemsTaxTotal + $chargesTax, 2);

    //     // TCS
    //     $tcsPercent = round((float)($data['tcs_percent'] ?? 0), 2);
    //     $tcsAmount  = round((float)($data['tcs_amount'] ?? 0), 2);
    //     if ($tcsPercent > 0) {
    //         $tcsAmount = round($taxableAmount * ($tcsPercent/100), 2);
    //     }

    //     $roundOff   = round((float)($data['round_off'] ?? 0), 2);
    //     $lessAmount = round((float)($data['less_amount'] ?? $discountTotal), 2);

    //     $grandTotal = round($taxableAmount + $taxAmount + $tcsAmount + $roundOff, 2);

    //     // payments totals (tax only)
    //     $cash = $online = $card = $cheque = $credit = $advance = 0.0;
    //     $receivedTotal = (float)($invoice->received_amount ?? 0);
    //     $balance       = $grandTotal;

    //     if ($docType === 'tax') {
    //         $cash    = (float)($pay['pay_cash'] ?? 0);
    //         $online  = (float)($pay['pay_upi'] ?? 0);
    //         $card    = (float)($pay['pay_card'] ?? 0);
    //         $cheque  = (float)($pay['pay_cheque'] ?? 0);
    //         $credit  = (float)($pay['credit_sales_excess'] ?? 0);
    //         $advance = (float)($pay['advance_amount'] ?? 0);

    //         $receivedTotal = round($cash + $online + $card + $cheque, 2);
    //         $balance = round(max(0, $grandTotal - $receivedTotal - $advance - $credit), 2);
    //     }

    //     // charges json decode
    //     $chargesArr = [];
    //     if (!empty($data['charges_json'])) {
    //         $tmp = json_decode($data['charges_json'], true);
    //         if (is_array($tmp)) {
    //             foreach ($tmp as $c) {
    //                 $nm = trim((string)($c['name'] ?? ''));
    //                 $am = (float)($c['amount'] ?? 0);
    //                 if ($nm !== '' && $am != 0) $chargesArr[] = ['name'=>$nm, 'amount'=>round($am,2)];
    //             }
    //         }
    //     }

    //     // signature upload replace
    //     $signaturePath = $invoice->signature_path;
    //     if ($r->hasFile('signature')) {
    //         $signaturePath = $r->file('signature')->store("invoices/{$bid}/signatures", 'public');
    //     }

    //     try {
    //         \DB::transaction(function () use (
    //             $r, $bid, $invoice, $data, $invoiceDate, $prefix, $docType,
    //             $subtotal, $avgTaxPercent, $taxableAmount, $taxAmount,
    //             $discountTotal, $chargeTotal, $tcsPercent, $tcsAmount, $roundOff, $lessAmount,
    //             $grandTotal, $receivedTotal, $balance,
    //             $cash, $online, $card, $cheque, $credit, $advance,
    //             $pay, $cleanRows, $normCode, $chargesArr, $stock, $signaturePath
    //         ) {
    //             $biz    = \App\Models\Business::findOrFail($bid);
    //             $client = \App\Models\Client::where('business_id', $bid)->findOrFail($data['client_id']);

    //             $bizCode   = $normCode($biz->state_code ?? '');
    //             $partyCode = $normCode($client->state_code ?? '');
    //             $isIntra = ($bizCode !== '' && $partyCode !== '') ? ($bizCode === $partyCode) : false;

    //             $cgstPercent = $isIntra ? round($avgTaxPercent/2, 2) : 0;
    //             $sgstPercent = $isIntra ? round($avgTaxPercent/2, 2) : 0;
    //             $igstPercent = $isIntra ? 0 : round($avgTaxPercent, 2);

    //             $cgst = $isIntra ? round($taxAmount / 2, 2) : 0;
    //             $sgst = $isIntra ? round($taxAmount / 2, 2) : 0;
    //             $igst = $isIntra ? 0 : round($taxAmount, 2);

    //             // ✅ update invoice (number keep same, prefix can update)
    //             $invoice->update([
    //                 'client_id'      => $data['client_id'],
    //                 'invoice_date'   => $invoiceDate,
    //                 'invoice_prefix' => $prefix,

    //                 'subtotal'       => $subtotal,
    //                 'discount_total' => $discountTotal,
    //                 'charge_total'   => $chargeTotal,
    //                 'less_amount'    => $lessAmount,

    //                 'tax_amount'     => $taxAmount,

    //                 'cgst_percent'   => $cgstPercent,
    //                 'cgst_amount'    => $cgst,
    //                 'sgst_percent'   => $sgstPercent,
    //                 'sgst_amount'    => $sgst,
    //                 'igst_percent'   => $igstPercent,
    //                 'igst_amount'    => $igst,

    //                 'tcs_percent'    => $tcsPercent,
    //                 'tcs_amount'     => $tcsAmount,
    //                 'round_off'      => $roundOff,

    //                 'total'          => $grandTotal,
    //                 'received_amount'=> ($docType === 'tax') ? $receivedTotal : 0,
    //                 'balance'        => ($docType === 'tax') ? $balance : $grandTotal,

    //                 'payment_method' => $data['payment_method'] ?? null,

    //                 'gst_no'         => $data['gst_no'] ?? null,
    //                 'transport_mode' => $data['transport_mode'] ?? null,
    //                 'reverse_charge' => !empty($data['reverse_charge']) ? 1 : 0,

    //                 'place_of_supply_state' => $client->state ?? null,
    //                 'place_of_supply_code'  => $client->state_code ?? null,

    //                 'notes'          => $data['notes'] ?? null,
    //                 'terms'          => $data['terms'] ?? null,

    //                 'charges_json'   => json_encode($chargesArr),
    //                 'items_json'     => json_encode($cleanRows),

    //                 'signature_path' => $signaturePath,
    //                 'updated_by' => auth()->user()->id ?? null,
    //             ]);

    //             // ✅ Replace additional charges table rows (optional)
    //             if (class_exists(\App\Models\InvoiceAdditionalCharge::class)) {
    //                 \App\Models\InvoiceAdditionalCharge::where('invoice_id', $invoice->id)->delete();
    //                 foreach ($chargesArr as $c) {
    //                     \App\Models\InvoiceAdditionalCharge::create([
    //                         'invoice_id' => $invoice->id,
    //                         'additional_charge_id' => null,
    //                         'name' => $c['name'],
    //                         'amount' => $c['amount'],
    //                     ]);
    //                 }
    //             }

    //             // ✅ Replace invoice items
    //             \App\Models\InvoiceItem::where('invoice_id', $invoice->id)->delete();

    //             foreach ($cleanRows as $row) {
    //                 $qty = (int)($row['qty'] ?? 1);
    //                 $rate      = round((float)($row['rate'] ?? 0), 2);
    //                 $lineTax   = round((float)($row['tax_amount'] ?? 0), 2);
    //                 $lineTotal = round((float)($row['amount'] ?? ($rate + $lineTax)), 2);

    //                 \App\Models\InvoiceItem::create([
    //                     'invoice_id' => $invoice->id,
    //                     'item_id'    => $row['item_id'],
    //                     'description'=> $row['description'] ?? '',
    //                     'sac_code'   => null,
    //                     'hsn_code'   => $row['hsn'] ?: null,
    //                     'quantity'   => $qty,

    //                     'gold_wt'    => (float)($row['gold_wt'] ?? 0),
    //                     'silver_wt'  => (float)($row['silver_wt'] ?? 0),
    //                     'gold_rate'  => (float)($row['gold_rate'] ?? 0),
    //                     'silver_rate'=> (float)($row['silver_rate'] ?? 0),

    //                     'gemstone_wt_ct' => (float)($row['gemstone_wt'] ?? 0),
    //                     'diamond_wt_ct'  => (float)($row['diamond_wt'] ?? 0),

    //                     'making_charge' => ($row['item_type']==='service') ? (float)($row['service_rate'] ?? $row['making_charge'] ?? 0) : null,
    //                     'making_rate'   => ($row['item_type']==='product') ? (float)($row['making_rate'] ?? 0) : null,

    //                     'discount'   => 0,
    //                     'tax_percent'=> (float)($row['tax_percent'] ?? 0),

    //                     'rate'       => $rate,
    //                     'amount'     => $lineTotal,
    //                 ]);
    //             }

    //             // ✅ TAX only: update InvoicePayment + bank + stock
    //             if ($docType === 'tax') {

    //                 // Update or create payment row
    //                 $payRow = \App\Models\InvoicePayment::where('invoice_id', $invoice->id)->latest('id')->first();
    //                 if (!$payRow) $payRow = new \App\Models\InvoicePayment();

    //                 $payRow->fill([
    //                     'business_id' => $bid,
    //                     'invoice_id'  => $invoice->id,
    //                     'client_id'   => $data['client_id'],

    //                     'total_value' => $grandTotal,

    //                     'cash_amount'   => $cash,
    //                     'online_amount' => $online,
    //                     'card_amount'   => $card,
    //                     'cheque_amount' => $cheque,

    //                     'online_mode' => $pay['online_mode'] ?? null,
    //                     'online_ref'  => $pay['online_ref'] ?? null,
    //                     'upi_id'      => $pay['upi_id'] ?? null,

    //                     'card_last4'  => $pay['card_last4'] ?? null,
    //                     'card_ref'    => $pay['card_ref'] ?? null,

    //                     'cheque_no'   => $pay['cheque_no'] ?? null,
    //                     'bank_name'   => $pay['bank_name'] ?? null,

    //                     'credit_sales_excess_amount' => $credit,
    //                     'advance_amount' => $advance,

    //                     'received_total' => $receivedTotal,
    //                     'notes' => $pay['notes'] ?? null,
    //                     'paid_at' => $receivedTotal > 0 ? now() : null,
    //                 ]);
    //                 $payRow->save();

    //                 /**
    //                  * ✅ STOCK NOTE:
    //                  * Agar aap stock cut kar rahe ho on tax invoice,
    //                  * update pe pehle old sale rollback karna chahiye, fir new record.
    //                  *
    //                  * Implement one of these in StockService:
    //                  * 1) $stock->rollbackSale($invoice);
    //                  * 2) ya invoice->items previous snapshot ke through reverse entries
    //                  */
    //                 if (method_exists($stock, 'rollbackSale')) {
    //                     $stock->rollbackSale($invoice);
    //                 }
    //                 $invoice->load('items');
    //                 $stock->recordSale($invoice);

    //                 // ✅ bank balance update (simple approach)
    //                 // NOTE: yaha previous received total ka delta handle nahi hai.
    //                 // Best: bank ledger table banake entries store karo.
    //                 $bankAccountId = $r->input('bank_account_id');
    //                 $mode = strtolower(trim((string)($data['payment_method'] ?? '')));
    //                 $bankModes = ['upi','bank','card','cheque'];

    //                 if ($bankAccountId && in_array($mode, $bankModes, true) && $receivedTotal > 0) {
    //                     $bank = \App\Models\BankAccount::where('business_id', $bid)->where('id', $bankAccountId)->first();
    //                     if ($bank) {
    //                         // ⚠️ This adds full received again (delta not handled)
    //                         // Better: delta = newReceived - oldReceived
    //                         $bank->balance = round(((float)$bank->balance) + $receivedTotal, 2);
    //                         $bank->save();
    //                     }
    //                 }
    //             }
    //         });

    //         // regenerate PDF (optional)
    //         $pdf = $this->simplePdfBuild($invoice->fresh(['client','items','business']));

    //         $dir = "invoices/{$bid}/" . now()->format('Y-m');
    //         $safeName = preg_replace('/[^A-Za-z0-9\-_\.]/', '-', (string)$invoice->invoice_number);
    //         $filename = $safeName . ".pdf";
    //         $path = $dir . "/" . $filename;

    //         Storage::disk('public')->put($path, $pdf->output());
    //         $invoice->update(['pdf_url' => $path]);

    //     } catch (\Throwable $e) {
    //         report($e);
    //         return back()->withErrors(['invoice' => 'Invoice update करते समय error आया: '.$e->getMessage()])->withInput();
    //     }

    //     //        return redirect()->route('invoices.index')
    //     //            ->with('success', ucfirst($docType).' updated successfully.');
    //     return redirect()->route('invoices.preview', $invoice->id)
    //         ->with('success', ucfirst($docType).' updated successfully.');
    // }


    public function update(Request $r, \App\Models\Invoice $invoice, \App\Services\StockService $stock)
    {
        $invoice = $invoice->load(['items', 'client', 'business']);

        $docType = strtolower(trim((string)($invoice->invoice_type ?? 'tax')));
        if (!in_array($docType, ['tax', 'proforma', 'quotation'], true)) {
            $docType = 'tax';
        }

        $bid = $r->user()->current_business_id ?? session('active_business_id');
        if (!$bid) {
            $bid = $invoice->business_id;
        }

        if ((int)$invoice->business_id !== (int)$bid) {
            abort(403, 'Unauthorized invoice access.');
        }

        $data = $r->validate([
            'client_id'      => ['required', 'exists:clients,id'],
            'invoice_date'   => ['required', 'date'],
            'invoice_prefix' => ['nullable', 'string', 'max:255'],

            'transport_mode' => ['nullable', 'string', 'max:255'],
            'gst_no'         => ['nullable', 'string', 'max:50'],
            'reverse_charge' => ['nullable'],

            'notes'          => ['nullable', 'string', 'max:2000'],
            'terms'          => ['nullable', 'string', 'max:2000'],

            'items_json'     => ['required', 'string'],

            'charges_json'   => ['nullable', 'string'],
            'discount_total' => ['nullable', 'numeric', 'min:0'],
            'charge_total'   => ['nullable', 'numeric', 'min:0'],
            'tcs_percent'    => ['nullable', 'numeric', 'min:0'],
            'tcs_amount'     => ['nullable', 'numeric', 'min:0'],
            'round_off'      => ['nullable', 'numeric'],
            'less_amount'    => ['nullable', 'numeric', 'min:0'],

            'cgst_percent'   => ['nullable', 'numeric', 'min:0'],
            'sgst_percent'   => ['nullable', 'numeric', 'min:0'],
            'igst_percent'   => ['nullable', 'numeric', 'min:0'],

            'payment_method'  => ['nullable', 'string', 'max:255'],
            'bank_account_id' => ['nullable', 'integer'],
            'signature'      => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $pay = [];
        if ($docType === 'tax') {
            $pay = $r->validate([
                'pay_cash'            => ['nullable', 'numeric', 'min:0'],
                'pay_upi'             => ['nullable', 'numeric', 'min:0'],
                'pay_card'            => ['nullable', 'numeric', 'min:0'],
                'pay_cheque'          => ['nullable', 'numeric', 'min:0'],

                'credit_sales_excess' => ['nullable', 'numeric', 'min:0'],
                'advance_amount'      => ['nullable', 'numeric', 'min:0'],

                'online_mode'         => ['nullable', 'string', 'max:30'],
                'online_ref'          => ['nullable', 'string', 'max:100'],
                'upi_id'              => ['nullable', 'string', 'max:100'],

                'card_last4'          => ['nullable', 'string', 'max:4'],
                'card_ref'            => ['nullable', 'string', 'max:100'],

                'cheque_no'           => ['nullable', 'string', 'max:50'],
                'bank_name'           => ['nullable', 'string', 'max:100'],
                'notes'               => ['nullable', 'string', 'max:2000'],
            ]);
        }

        $invoiceDate = \Carbon\Carbon::parse($data['invoice_date'])->toDateString();

        $prefix = trim($data['invoice_prefix'] ?? '');
        if ($prefix === '') {
            $prefix = $invoice->invoice_prefix;
        }

        $rows = json_decode($data['items_json'], true);

        if (!is_array($rows) || count($rows) < 1) {
            return back()
                ->withErrors(['items' => 'कम से कम 1 line item जरूरी है.'])
                ->withInput();
        }

        $normCode = function ($v) {
            $s = trim((string)$v);
            $s = preg_replace('/\D+/', '', $s);
            $s = ltrim($s, '0');

            return $s;
        };

        $subtotal      = 0.0;
        $weightedTax   = 0.0;
        $itemsTaxTotal = 0.0;
        $cleanRows     = [];

        // foreach ($rows as $i => $row) {
        //     $itemId = $row['item_id'] ?? null;

        //     if (empty($itemId)) {
        //         return back()
        //             ->withErrors(['items' => "Row " . ($i + 1) . " में Item select नहीं है."])
        //             ->withInput();
        //     }

        //     $itemType = strtolower(trim((string)($row['item_type'] ?? 'product')));

        //     if (!in_array($itemType, ['product', 'service'], true)) {
        //         $itemType = 'product';
        //     }

        //     $desc = trim((string)($row['description'] ?? ''));

        //     if ($desc === '') {
        //         return back()
        //             ->withErrors(['items' => "Row " . ($i + 1) . " description missing."])
        //             ->withInput();
        //     }

        //     $hsn = trim((string)($row['hsn'] ?? ''));

        //     $qty = (int)($row['quantity'] ?? 1);
        //     $qty = $qty < 1 ? 1 : $qty;

        //     $taxPct = (float)($row['tax_percent'] ?? 0);

        //     if ($taxPct < 0 || $taxPct > 100) {
        //         return back()
        //             ->withErrors(['items' => "Row " . ($i + 1) . " tax % invalid."])
        //             ->withInput();
        //     }

        //     // ================= SERVICE =================
        //     if ($itemType === 'service') {
        //         $fixedPrice  = (float)($row['fixed_price'] ?? $row['price'] ?? 0);
        //         $serviceRate = (float)($row['service_rate'] ?? 0);

        //         if ($fixedPrice > 0) {
        //             $serviceRate = $fixedPrice;
        //         }

        //         if ($serviceRate < 0) {
        //             return back()
        //                 ->withErrors(['items' => "Row " . ($i + 1) . " service rate invalid."])
        //                 ->withInput();
        //         }

        //         $lineBase = round($serviceRate * $qty, 2);
        //         $lineTax  = round($lineBase * ($taxPct / 100), 2);

        //         $subtotal      += $lineBase;
        //         $weightedTax   += ($lineBase * $taxPct);
        //         $itemsTaxTotal += $lineTax;

        //         $cleanRows[] = [
        //             'item_id'      => (int)$itemId,
        //             'item_type'    => 'service',
        //             'description'  => $desc,
        //             'hsn'          => $hsn,
        //             'qty'          => $qty,
        //             'tax_percent'  => round($taxPct, 2),

        //             'fixed_price'  => round($fixedPrice, 2),
        //             'service_rate' => round($serviceRate, 2),

        //             'rate'         => $lineBase,
        //             'tax_amount'   => $lineTax,
        //             'amount'       => round($lineBase + $lineTax, 2),

        //             'making_charge' => round($serviceRate, 2),

        //             'gold_wt'       => 0,
        //             'silver_wt'     => 0,
        //             'gold_rate'     => 0,
        //             'silver_rate'   => 0,
        //             'gemstone_wt'   => 0,
        //             'diamond_wt'    => 0,
        //             'making_rate'   => 0,
        //             'stone_charges' => 0,
        //         ];

        //         continue;
        //     }

        //     // ================= PRODUCT =================
        //     $goldWt     = (float)($row['gold_wt'] ?? 0);
        //     $silverWt   = (float)($row['silver_wt'] ?? 0);
        //     $goldRate   = (float)($row['gold_rate'] ?? 0);
        //     $silverRate = (float)($row['silver_rate'] ?? 0);
        //     $makingRate = (float)($row['making_rate'] ?? 0);

        //     $gemCt = (float)($row['gemstone_wt'] ?? 0);
        //     $diaCt = (float)($row['diamond_wt'] ?? 0);

        //     // ✅ Main fix: product price priority
        //     $fixedPrice   = (float)($row['fixed_price'] ?? $row['price'] ?? 0);
        //     $manualAmount = (float)($row['manual_amount'] ?? 0);
        //     $amountMode   = strtolower(trim((string)($row['amount_mode'] ?? 'auto')));

        //     if (
        //         $goldWt < 0 ||
        //         $silverWt < 0 ||
        //         $goldRate < 0 ||
        //         $silverRate < 0 ||
        //         $makingRate < 0 ||
        //         $fixedPrice < 0 ||
        //         $manualAmount < 0
        //     ) {
        //         return back()
        //             ->withErrors(['items' => "Row " . ($i + 1) . " invalid values."])
        //             ->withInput();
        //     }

        //     // ✅ Priority 1: fixed_price / item price
        //     $productBase = $fixedPrice > 0
        //         ? $fixedPrice
        //         : (($goldWt * $goldRate) + ($silverWt * $silverRate));

        //     $makingAmount = round($productBase * ($makingRate / 100), 2);

        //     if ($amountMode === 'manual' && $manualAmount > 0) {
        //         $lineBase = round($manualAmount / (1 + ($taxPct / 100)), 2);
        //     } else {
        //         $lineBase = round(($productBase + $makingAmount) * $qty, 2);
        //     }

        //     $lineTax = round($lineBase * ($taxPct / 100), 2);

        //     $subtotal      += $lineBase;
        //     $weightedTax   += ($lineBase * $taxPct);
        //     $itemsTaxTotal += $lineTax;

        //     $cleanRows[] = [
        //         'item_id'     => (int)$itemId,
        //         'item_type'   => 'product',
        //         'description' => $desc,
        //         'hsn'         => $hsn,
        //         'qty'         => $qty,
        //         'tax_percent' => round($taxPct, 2),

        //         'fixed_price' => round($fixedPrice, 2),

        //         'gold_wt'     => round($goldWt, 3),
        //         'silver_wt'   => round($silverWt, 3),
        //         'gold_rate'   => round($goldRate, 2),
        //         'silver_rate' => round($silverRate, 2),

        //         'gemstone_wt' => round($gemCt, 3),
        //         'diamond_wt'  => round($diaCt, 3),

        //         'making_rate'   => round($makingRate, 2),
        //         'making_charge' => round($makingAmount, 2),

        //         'rate'        => $lineBase,
        //         'tax_amount'  => $lineTax,
        //         'amount'      => round($lineBase + $lineTax, 2),
        //     ];
        // }

        foreach ($rows as $i => $row) {
            $itemId = $row['item_id'] ?? null;

            if (empty($itemId)) {
                return back()
                    ->withErrors(['items' => "Row " . ($i + 1) . " में Item select नहीं है."])
                    ->withInput();
            }

            $desc = trim((string)($row['description'] ?? ''));

            if ($desc === '') {
                return back()
                    ->withErrors(['items' => "Row " . ($i + 1) . " description missing."])
                    ->withInput();
            }

            $hsn = trim((string)($row['hsn'] ?? ''));

            $qty = (int)($row['quantity'] ?? 1);
            $qty = $qty < 1 ? 1 : $qty;

            $taxPct = (float)($row['tax_percent'] ?? 0);

            if ($taxPct < 0 || $taxPct > 100) {
                return back()
                    ->withErrors(['items' => "Row " . ($i + 1) . " tax % invalid."])
                    ->withInput();
            }

            $goldWt     = (float)($row['gold_wt'] ?? 0);
            $silverWt   = (float)($row['silver_wt'] ?? 0);
            $goldRate   = (float)($row['gold_rate'] ?? 0);
            $silverRate = (float)($row['silver_rate'] ?? 0);

            $makingRate = (float)($row['making_rate'] ?? 0);

            // $makingChargeType = strtolower(trim((string)($row['making_charge_type'] ?? 'percent')));

            // if (!in_array($makingChargeType, ['fixed', 'percent'], true)) {
            //     $makingChargeType = 'percent';
            // }

            $makingChargeType = strtolower(trim((string)($row['making_charge_type'] ?? 'percentage')));

            $allowedMakingTypes = [
                'percentage',
                'fixed',
                'per_gram',
                'per_product',
            ];

            if (!in_array($makingChargeType, $allowedMakingTypes, true)) {
                $makingChargeType = 'percentage';
            }

            $gemCt = (float)($row['gemstone_wt'] ?? 0);
            $diaCt = (float)($row['diamond_wt'] ?? 0);

            $stoneCharges = (float)($row['gemstone_charge'] ?? $row['stone_charges'] ?? 0);
            $diamondCharges = (float)($row['diamond_charge'] ?? $row['diamond_charges'] ?? 0);

            $fixedPrice = (float)($row['fixed_price'] ?? $row['price'] ?? $row['service_rate'] ?? 0);

            $manualAmount = (float)($row['manual_amount'] ?? 0);
            $amountMode = strtolower(trim((string)($row['amount_mode'] ?? 'auto')));

            if (
                $goldWt < 0 ||
                $silverWt < 0 ||
                $goldRate < 0 ||
                $silverRate < 0 ||
                $makingRate < 0 ||
                $gemCt < 0 ||
                $diaCt < 0 ||
                $stoneCharges < 0 ||
                $diamondCharges < 0 ||
                $fixedPrice < 0 ||
                $manualAmount < 0
            ) {
                return back()
                    ->withErrors(['items' => "Row " . ($i + 1) . " invalid values."])
                    ->withInput();
            }

            $metalBase = ($goldWt * $goldRate)
                + ($silverWt * $silverRate)
                + $stoneCharges
                + $diamondCharges;

            $productBase = $fixedPrice > 0 ? $fixedPrice : $metalBase;

            // $makingAmount = round($productBase * ($makingRate / 100), 2);

            // if ($makingChargeType === 'fixed') {
            //     $makingAmount = round($makingRate, 2);
            // } else {
            //     $makingAmount = round($productBase * ($makingRate / 100), 2);
            // }

            if ($makingChargeType === 'percentage') {
                $makingAmount = round($productBase * ($makingRate / 100), 2);

            } elseif ($makingChargeType === 'fixed') {
                $makingAmount = round($makingRate, 2);

            } elseif ($makingChargeType === 'per_gram') {
                $totalMetalWeight = $goldWt + $silverWt;
                $makingAmount = round($totalMetalWeight * $makingRate, 2);

            } elseif ($makingChargeType === 'per_product') {
                $makingAmount = round($makingRate, 2);

            } else {
                $makingAmount = 0;
            }

            if ($amountMode === 'manual' && $manualAmount > 0) {
                $lineBase = round($manualAmount / (1 + ($taxPct / 100)), 2);
            } else {
                $lineBase = round(($productBase + $makingAmount) * $qty, 2);
            }

            $lineTax = round($lineBase * ($taxPct / 100), 2);

            $subtotal      += $lineBase;
            $weightedTax   += ($lineBase * $taxPct);
            $itemsTaxTotal += $lineTax;

            $cleanRows[] = [
                'item_id'     => (int)$itemId,
                'item_type'   => 'product',
                'description' => $desc,
                'hsn'         => $hsn,
                'qty'         => $qty,
                'tax_percent' => round($taxPct, 2),

                'fixed_price' => round($fixedPrice, 2),

                'gold_wt'     => round($goldWt, 3),
                'silver_wt'   => round($silverWt, 3),
                'gold_rate'   => round($goldRate, 2),
                'silver_rate' => round($silverRate, 2),

                'gemstone_wt' => round($gemCt, 3),
                'diamond_wt'  => round($diaCt, 3),

                'making_charge_type' => $makingChargeType,
                'making_rate'        => round($makingRate, 2),
                'making_charge'      => round($makingAmount, 2),

                'stone_charges'   => round($stoneCharges, 2),
                'diamond_charges' => round($diamondCharges, 2),

                'rate'       => $lineBase,
                'tax_amount' => $lineTax,
                'amount'     => round($lineBase + $lineTax, 2),
            ];
        }

        $subtotal      = round($subtotal, 2);
        $itemsTaxTotal = round($itemsTaxTotal, 2);

        $avgTaxPercentRaw = ($subtotal > 0) ? ($weightedTax / $subtotal) : 0;
        $avgTaxPercent    = round($avgTaxPercentRaw, 2);

        $discountTotal = round((float)($data['discount_total'] ?? 0), 2);
        $chargeTotal   = round((float)($data['charge_total'] ?? 0), 2);

        $taxableAmount = round(max(0, $subtotal - $discountTotal + $chargeTotal), 2);

        $chargesTax = 0.0;
        $taxAmount  = round($itemsTaxTotal + $chargesTax, 2);

        $tcsPercent = round((float)($data['tcs_percent'] ?? 0), 2);
        $tcsAmount  = round((float)($data['tcs_amount'] ?? 0), 2);

        if ($tcsPercent > 0) {
            $tcsAmount = round($taxableAmount * ($tcsPercent / 100), 2);
        }

        $roundOff   = round((float)($data['round_off'] ?? 0), 2);
        $lessAmount = round((float)($data['less_amount'] ?? $discountTotal), 2);

        $grandTotal = round($taxableAmount + $taxAmount + $tcsAmount + $roundOff, 2);

        $cash = $online = $card = $cheque = $credit = $advance = 0.0;
        $receivedTotal = (float)($invoice->received_amount ?? 0);
        $balance = $grandTotal;

        if ($docType === 'tax') {
            $cash    = (float)($pay['pay_cash'] ?? 0);
            $online  = (float)($pay['pay_upi'] ?? 0);
            $card    = (float)($pay['pay_card'] ?? 0);
            $cheque  = (float)($pay['pay_cheque'] ?? 0);
            $credit  = (float)($pay['credit_sales_excess'] ?? 0);
            $advance = (float)($pay['advance_amount'] ?? 0);

            $receivedTotal = round($cash + $online + $card + $cheque, 2);
            $balance = round(max(0, $grandTotal - $receivedTotal - $advance - $credit), 2);
        }

        $chargesArr = [];
        if (!empty($data['charges_json'])) {
            $tmp = json_decode($data['charges_json'], true);

            if (is_array($tmp)) {
                foreach ($tmp as $c) {
                    $nm = trim((string)($c['name'] ?? ''));
                    $am = (float)($c['amount'] ?? 0);

                    if ($nm !== '' && $am != 0) {
                        $chargesArr[] = [
                            'name'   => $nm,
                            'amount' => round($am, 2),
                        ];
                    }
                }
            }
        }

        $signaturePath = $invoice->signature_path;
        if ($r->hasFile('signature')) {
            $signaturePath = $r->file('signature')->store("invoices/{$bid}/signatures", 'public');
        }

        try {
            \DB::transaction(function () use (
                $r,
                $bid,
                $invoice,
                $data,
                $invoiceDate,
                $prefix,
                $docType,
                $subtotal,
                $avgTaxPercent,
                $taxableAmount,
                $taxAmount,
                $discountTotal,
                $chargeTotal,
                $tcsPercent,
                $tcsAmount,
                $roundOff,
                $lessAmount,
                $grandTotal,
                $receivedTotal,
                $balance,
                $cash,
                $online,
                $card,
                $cheque,
                $credit,
                $advance,
                $pay,
                $cleanRows,
                $normCode,
                $chargesArr,
                $stock,
                $signaturePath
            ) {
                $biz = \App\Models\Business::findOrFail($bid);
                $client = \App\Models\Client::where('business_id', $bid)->findOrFail($data['client_id']);

                $bizCode   = $normCode($biz->state_code ?? '');
                $partyCode = $normCode($client->state_code ?? '');

                $isIntra = ($bizCode !== '' && $partyCode !== '')
                    ? ($bizCode === $partyCode)
                    : false;

                $cgstPercent = $isIntra ? round($avgTaxPercent / 2, 2) : 0;
                $sgstPercent = $isIntra ? round($avgTaxPercent / 2, 2) : 0;
                $igstPercent = $isIntra ? 0 : round($avgTaxPercent, 2);

                $cgst = $isIntra ? round($taxAmount / 2, 2) : 0;
                $sgst = $isIntra ? round($taxAmount / 2, 2) : 0;
                $igst = $isIntra ? 0 : round($taxAmount, 2);

                $invoice->update([
                    'client_id'      => $data['client_id'],
                    'invoice_date'   => $invoiceDate,
                    'invoice_prefix' => $prefix,

                    'subtotal'       => $subtotal,
                    'discount_total' => $discountTotal,
                    'charge_total'   => $chargeTotal,
                    'less_amount'    => $lessAmount,

                    'tax_amount'     => $taxAmount,

                    'cgst_percent'   => $cgstPercent,
                    'cgst_amount'    => $cgst,
                    'sgst_percent'   => $sgstPercent,
                    'sgst_amount'    => $sgst,
                    'igst_percent'   => $igstPercent,
                    'igst_amount'    => $igst,

                    'tcs_percent'    => $tcsPercent,
                    'tcs_amount'     => $tcsAmount,
                    'round_off'      => $roundOff,

                    'total'          => $grandTotal,
                    'received_amount'=> ($docType === 'tax') ? $receivedTotal : 0,
                    'balance'        => ($docType === 'tax') ? $balance : $grandTotal,

                    'payment_method' => $data['payment_method'] ?? null,

                    'gst_no'         => $data['gst_no'] ?? null,
                    'transport_mode' => $data['transport_mode'] ?? null,
                    'reverse_charge' => !empty($data['reverse_charge']) ? 1 : 0,

                    'place_of_supply_state' => $client->state ?? null,
                    'place_of_supply_code'  => $client->state_code ?? null,

                    'notes'          => $data['notes'] ?? null,
                    'terms'          => $data['terms'] ?? null,

                    'charges_json'   => json_encode($chargesArr),
                    'items_json'     => json_encode($cleanRows),

                    'signature_path' => $signaturePath,
                    'updated_by'     => auth()->user()->id ?? null,
                ]);

                if (class_exists(\App\Models\InvoiceAdditionalCharge::class)) {
                    \App\Models\InvoiceAdditionalCharge::where('invoice_id', $invoice->id)->delete();

                    foreach ($chargesArr as $c) {
                        \App\Models\InvoiceAdditionalCharge::create([
                            'invoice_id'            => $invoice->id,
                            'additional_charge_id'  => null,
                            'name'                  => $c['name'],
                            'amount'                => $c['amount'],
                        ]);
                    }
                }

                \App\Models\InvoiceItem::where('invoice_id', $invoice->id)->delete();

                foreach ($cleanRows as $row) {
                    $qty = (int)($row['qty'] ?? 1);

                    $rate      = round((float)($row['rate'] ?? 0), 2);
                    $lineTotal = round((float)($row['amount'] ?? $rate), 2);

                    \App\Models\InvoiceItem::create([
                        'invoice_id'  => $invoice->id,
                        'item_id'     => $row['item_id'],
                        'description' => $row['description'] ?? '',
                        'sac_code'    => null,
                        'hsn_code'    => $row['hsn'] ?: null,
                        'quantity'    => $qty,

                        'gold_wt'     => (float)($row['gold_wt'] ?? 0),
                        'silver_wt'   => (float)($row['silver_wt'] ?? 0),
                        'gold_rate'   => (float)($row['gold_rate'] ?? 0),
                        'silver_rate' => (float)($row['silver_rate'] ?? 0),

                        'gemstone_wt_ct' => (float)($row['gemstone_wt'] ?? 0),
                        'diamond_wt_ct'  => (float)($row['diamond_wt'] ?? 0),

                        // 'making_charge' => ($row['item_type'] === 'service')
                        //     ? (float)($row['service_rate'] ?? $row['making_charge'] ?? 0)
                        //     : null,

                        // 'making_charge_type' => $row['making_charge_type'] ?? 'percent',

                        'making_charge_type' => $row['making_charge_type'] ?? 'percentage',
                        'making_charge' => (float)($row['making_charge'] ?? 0),

                        'making_rate' => (float)($row['making_rate'] ?? 0),

                        'discount'    => 0,
                        'tax_percent' => (float)($row['tax_percent'] ?? 0),

                        'rate'        => $rate,
                        'amount'      => $lineTotal,
                    ]);
                }

                if ($docType === 'tax') {
                    $payRow = \App\Models\InvoicePayment::where('invoice_id', $invoice->id)
                        ->latest('id')
                        ->first();

                    if (!$payRow) {
                        $payRow = new \App\Models\InvoicePayment();
                    }

                    $payRow->fill([
                        'business_id' => $bid,
                        'invoice_id'  => $invoice->id,
                        'client_id'   => $data['client_id'],

                        'total_value' => $grandTotal,

                        'cash_amount'   => $cash,
                        'online_amount' => $online,
                        'card_amount'   => $card,
                        'cheque_amount' => $cheque,

                        'online_mode' => $pay['online_mode'] ?? null,
                        'online_ref'  => $pay['online_ref'] ?? null,
                        'upi_id'      => $pay['upi_id'] ?? null,

                        'card_last4'  => $pay['card_last4'] ?? null,
                        'card_ref'    => $pay['card_ref'] ?? null,

                        'cheque_no'   => $pay['cheque_no'] ?? null,
                        'bank_name'   => $pay['bank_name'] ?? null,

                        'credit_sales_excess_amount' => $credit,
                        'advance_amount'             => $advance,

                        'received_total' => $receivedTotal,
                        'notes'          => $pay['notes'] ?? null,
                        'paid_at'        => $receivedTotal > 0 ? now() : null,
                    ]);

                    $payRow->save();

                    if (method_exists($stock, 'rollbackSale')) {
                        $stock->rollbackSale($invoice);
                    }

                    $invoice->load('items');
                    $stock->recordSale($invoice);

                    $bankAccountId = $r->input('bank_account_id');
                    $mode = strtolower(trim((string)($data['payment_method'] ?? '')));
                    $bankModes = ['upi', 'bank', 'card', 'cheque'];

                    if ($bankAccountId && in_array($mode, $bankModes, true) && $receivedTotal > 0) {
                        $bank = \App\Models\BankAccount::where('business_id', $bid)
                            ->where('id', $bankAccountId)
                            ->first();

                        if ($bank) {
                            $bank->balance = round(((float)$bank->balance) + $receivedTotal, 2);
                            $bank->save();
                        }
                    }
                }
            });

            $pdf = $this->simplePdfBuild($invoice->fresh(['client', 'items', 'business']));

            $dir = "invoices/{$bid}/" . now()->format('Y-m');
            $safeName = preg_replace('/[^A-Za-z0-9\-_\.]/', '-', (string)$invoice->invoice_number);
            $filename = $safeName . ".pdf";
            $path = $dir . "/" . $filename;

            Storage::disk('public')->put($path, $pdf->output());

            $invoice->update([
                'pdf_url' => $path,
            ]);

        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withErrors(['invoice' => 'Invoice update करते समय error आया: ' . $e->getMessage()])
                ->withInput();
        }

        return redirect()
            ->route('invoices.preview', $invoice->id)
            ->with('success', ucfirst($docType) . ' updated successfully.');
    }





    // public function export(Request $r)
    // {
    //     return Excel::download(new InvoicesExport($r->all()), $r->from. ' to '.$r->to.' invoices-report.xlsx');
    // }

    // public function export(Request $r)
    // {
    //     // support both: from_date/to_date OR from/to
    //     $from = $r->input('from_date') ?? $r->input('from');
    //     $to   = $r->input('to_date')   ?? $r->input('to');

    //     // ✅ If any one is missing → ALL records filename
    //     if (empty($from) || empty($to)) {
    //         $fileName = "all-invoice-records.xlsx";
    //     } else {
    //         $fromLabel = Carbon::parse($from)->format('d-m-Y');
    //         $toLabel   = Carbon::parse($to)->format('d-m-Y');

    //         $fileName = "{$fromLabel}_to_{$toLabel}_invoices-report.xlsx";
    //     }

    //     return Excel::download(
    //         new InvoicesExport($r->all()),
    //         $fileName
    //     );
    // }

    public function send(Invoice $invoice)
    {
        // 1) Client + Business load
        $invoice->loadMissing('client', 'business');

        $client  = $invoice->client;
        $biz     = $invoice->business;

        if (!$client || empty($client->mobile)) {
            return back()->withErrors([
                'whatsapp' => 'Client ke mobile number ke bina WhatsApp par invoice nahi bhej sakte.',
            ]);
        }

        // 2) Mobile normalize
        $rawMobile = preg_replace('/\D+/', '', $client->mobile);

        if (strlen($rawMobile) === 10) {
            $number = '91' . $rawMobile;
        } else {
            if (str_starts_with($rawMobile, '91')) {
                $number = $rawMobile;
            } else {
                $number = '91' . $rawMobile;
            }
        }

        // 3) API config dynamic uthao (pehle business se, phir fallback user/global)
        $apiConfig = ApiKey::query()
            ->when($biz?->id, fn ($q) => $q->where('business_id', $biz->id))
            ->when(!$biz?->id, fn ($q) => $q->whereNull('business_id'))
            ->whereNotNull('base_url')
            ->first();

        // agar ऊपर वाला बहुत strict लगे to is तरह fallback chain bhi कर सकते हो:
        if (! $apiConfig) {
            // business-specific
            $apiConfig = ApiKey::where('business_id', $biz?->id)->whereNotNull('base_url')->first()
                // user-specific
                ?? ApiKey::where('user_id', auth()->id())->whereNotNull('base_url')->first()
                // global default (business_id/user_id null)
                ?? ApiKey::whereNull('business_id')->whereNull('user_id')->whereNotNull('base_url')->first();
        }

        if (! $apiConfig) {
            return back()->withErrors([
                'whatsapp' => 'WhatsApp API config nahi mila. Pehle Settings → API Keys me base_url set karein.',
            ]);
        }

        $apiBase = rtrim($apiConfig->base_url, '/');

        // 4) Ensure PDF exists
        $path = null;

        if (!empty($invoice->pdf_url)) {
            $maybePath = $this->normalizePdfPath($invoice->pdf_url);

            if ($maybePath && Storage::disk('public')->exists($maybePath)) {
                $path = $maybePath;
            }
        }

        if (!$path) {
            // fresh relations ke sath PDF build karo
            $invoice = $invoice->fresh(['client','items','business']);

            $pdf = $this->buildInvoicePdf($invoice);

            $safeNumber = str_replace(['/', '\\'], '-', (string)($invoice->invoice_number ?? 'INV'));
            $path       = 'invoices/Invoice-'.$safeNumber.'.pdf';

            Storage::disk('public')->put($path, $pdf->output());

            $invoice->update([
                'pdf_url' => $path, // relative path store
            ]);
        }

        // 5) Public URL
        $fileUrl = Storage::disk('public')->url($path);

        // 6) WhatsApp API call – yaha key/secret bhi bhej सकते हो
        try {
            $query = [
                'number'  => $number,
                'file'    => $fileUrl,
                'caption' => 'Your invoice '.$invoice->invoice_number.' from '.($biz->name ?? 'Our Business'),
            ];

            // agar tumhari 3rd-party API query string me key/secret leti hai:
            if (!empty($apiConfig->key)) {
                $query['key'] = $apiConfig->key;
            }
            if (!empty($apiConfig->secret)) {
                $query['secret'] = $apiConfig->secret;
            }

            $response = Http::timeout(20)->get($apiBase, $query);

            if (!$response->successful()) {
                return back()->withErrors([
                    'whatsapp' => 'WhatsApp API se error aaya: '.$response->status().' - '.$response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            report($e);

            return back()->withErrors([
                'whatsapp' => 'WhatsApp par invoice bhejte waqt error aaya: '.$e->getMessage(),
            ]);
        }

        return back()->with('success', 'Invoice WhatsApp par send kar diya gaya hai.');
    }

    protected function buildInvoicePdf(Invoice $invoice): \Barryvdh\DomPDF\PDF
    {
        $invoice->load(['client','items','business']);

        $inv    = $invoice;
        $biz    = $invoice->business;
        $client = $invoice->client;
        $items  = $invoice->items ?? collect();

        // ✅ pay row (1 invoice = 1 row)
        $payRow = InvoicePayment::where('invoice_id', $inv->id)
            ->latest('id')
            ->first();

        // charges...
        if (method_exists($invoice, 'additionalCharges')) {
            $charges = $invoice->additionalCharges()->get(['name','amount']);
        } else {
            $arr = [];
            if (!empty($invoice->charges_json)) {
                $decoded = json_decode($invoice->charges_json, true);
                if (is_array($decoded)) {
                    foreach ($decoded as $c) {
                        $arr[] = (object)[
                            'name'   => (string)($c['name'] ?? ''),
                            'amount' => (float) ($c['amount'] ?? 0),
                        ];
                    }
                }
            }
            $charges = collect($arr);
        }

        // totals...
        $subtotal       = (float)($inv->subtotal ?? 0);
        $tax_total      = (float)($inv->tax_amount ?? 0);
        $discount_total = (float)($inv->discount_total ?? 0);
        $charges_total  = (float)($inv->charge_total ?? 0);
        $tcs_percent    = (float)($inv->tcs_percent ?? 0);
        $tcs_amount     = (float)($inv->tcs_amount ?? 0);
        $round_off      = (float)($inv->round_off ?? 0);
        $less_amount    = (float)($inv->less_amount ?? 0);
        $received       = (float)($inv->received_amount ?? 0);
        $grand_total    = (float)($inv->total ?? 0);
        $balance        = (float)($inv->balance ?? 0);
        $cgst_amount    = (float)($inv->cgst_amount ?? 0);
        $sgst_amount    = (float)($inv->sgst_amount ?? 0);
        $igst_amount    = (float)($inv->igst_amount ?? 0);

        // data URIs
        $logoDataUri       = $this->imageDataUri($biz?->logo);
        $signDataUri       = $this->imageDataUri($biz?->signature);
        $letterHeadDataUri = $this->imageDataUri($biz?->letter_head);

        $vm = compact(
            'inv','invoice','biz','client','items','charges',
            'logoDataUri','signDataUri','letterHeadDataUri',

            'subtotal','tax_total','discount_total','charges_total',
            'tcs_percent','tcs_amount','round_off','less_amount',
            'grand_total','received','balance',
            'cgst_amount','sgst_amount','igst_amount',

            'payRow' // ✅ HERE
        );

        // aliases
        $vm['logo']        = $logoDataUri;
        $vm['sign']        = $signDataUri;
        $vm['letter_head'] = $letterHeadDataUri;

        return Pdf::loadView('invoices.pdf_kapoor', $vm)->setPaper('a4');
    }


    // protected function simplePdfBuild(Invoice $invoice): \Barryvdh\DomPDF\PDF
    // {
    //     // $invoice->load(['client','items','business']);

    //     $invoice->load(['client','items.item','business']);
        
    //     $inv    = $invoice;
    //     $biz    = $invoice->business;
    //     $client = $invoice->client;
    //     $items  = $invoice->items ?? collect();

    //     // ✅ payment row (same logic)
    //     $payRow = InvoicePayment::where('invoice_id', $inv->id)
    //         ->latest('id')
    //         ->first();

    //     // ✅ charges (same logic)
    //     if (method_exists($invoice, 'additionalCharges')) {
    //         $charges = $invoice->additionalCharges()->get(['name','amount']);
    //     } else {
    //         $arr = [];
    //         if (!empty($invoice->charges_json)) {
    //             $decoded = json_decode($invoice->charges_json, true);
    //             if (is_array($decoded)) {
    //                 foreach ($decoded as $c) {
    //                     $arr[] = (object)[
    //                         'name'   => (string)($c['name'] ?? ''),
    //                         'amount' => (float)($c['amount'] ?? 0),
    //                     ];
    //                 }
    //             }
    //         }
    //         $charges = collect($arr);
    //     }

    //     // ✅ totals (same as buildInvoicePdf)
    //     $subtotal       = (float)($inv->subtotal ?? 0);
    //     $tax_total      = (float)($inv->tax_amount ?? 0);
    //     $discount_total = (float)($inv->discount_total ?? 0);
    //     $charges_total  = (float)($inv->charge_total ?? 0);
    //     $tcs_percent    = (float)($inv->tcs_percent ?? 0);
    //     $tcs_amount     = (float)($inv->tcs_amount ?? 0);
    //     $round_off      = (float)($inv->round_off ?? 0);
    //     $less_amount    = (float)($inv->less_amount ?? 0);
    //     $received       = (float)($inv->received_amount ?? 0);
    //     $grand_total    = (float)($inv->total ?? 0);
    //     $balance        = (float)($inv->balance ?? 0);
    //     $cgst_amount    = (float)($inv->cgst_amount ?? 0);
    //     $sgst_amount    = (float)($inv->sgst_amount ?? 0);
    //     $igst_amount    = (float)($inv->igst_amount ?? 0);

    //     $taxAmount = (float)($inv->igst_amount + $inv->igst_amount + $inv->igst_amount);


    //     // ✅ data URIs (simple pdf me bhi logo/sign aa sakta hai)
    //     $logoDataUri = $this->imageDataUri($biz?->logo);
    //     $signDataUri = $this->imageDataUri($biz?->signature);
    //     $type = $invoice->invoice_type;



    //     $vm = compact(
    //         'inv','invoice','biz','client','items','charges','type', 'taxAmount',
    //         'logoDataUri','signDataUri',
    //         'subtotal','tax_total','discount_total','charges_total',
    //         'tcs_percent','tcs_amount','round_off','less_amount',
    //         'grand_total','received','balance',
    //         'cgst_amount','sgst_amount','igst_amount',
    //         'payRow'
    //     );

    //     // aliases
    //     $vm['logo'] = $logoDataUri;
    //     $vm['sign'] = $signDataUri;

    //     // ❌ letter_head deliberately NOT passed
    //     $view = 'invoices.' . ($biz->billTemplate->page_name ?? 'pdf_simple');
    //     // $view = 'invoices.' . ('pdf_krinoscco');

    //     return Pdf::loadView($view, $vm)
    //         ->setPaper('a4');
    // }


    protected function simplePdfBuild(Invoice $invoice): \Barryvdh\DomPDF\PDF
    {
        $invoice->load(['client', 'items.item', 'business.billTemplate']);

        $inv    = $invoice;
        $biz    = $invoice->business;
        $client = $invoice->client;
        $items  = $invoice->items ?? collect();

        $payRow = InvoicePayment::where('invoice_id', $inv->id)
            ->latest('id')
            ->first();

        if (method_exists($invoice, 'additionalCharges')) {
            $charges = $invoice->additionalCharges()->get(['name', 'amount']);
        } else {
            $arr = [];

            if (!empty($invoice->charges_json)) {
                $decoded = json_decode($invoice->charges_json, true);

                if (is_array($decoded)) {
                    foreach ($decoded as $c) {
                        $arr[] = (object) [
                            'name'   => (string) ($c['name'] ?? ''),
                            'amount' => (float) ($c['amount'] ?? 0),
                        ];
                    }
                }
            }

            $charges = collect($arr);
        }

        $subtotal       = (float) ($inv->subtotal ?? 0);
        $tax_total      = (float) ($inv->tax_amount ?? 0);
        $discount_total = (float) ($inv->discount_total ?? 0);
        $charges_total  = (float) ($inv->charge_total ?? 0);
        $tcs_percent    = (float) ($inv->tcs_percent ?? 0);
        $tcs_amount     = (float) ($inv->tcs_amount ?? 0);
        $round_off      = (float) ($inv->round_off ?? 0);
        $less_amount    = (float) ($inv->less_amount ?? 0);
        $received       = (float) ($inv->received_amount ?? 0);
        $grand_total    = (float) ($inv->total ?? 0);
        $balance        = (float) ($inv->balance ?? 0);
        $cgst_amount    = (float) ($inv->cgst_amount ?? 0);
        $sgst_amount    = (float) ($inv->sgst_amount ?? 0);
        $igst_amount    = (float) ($inv->igst_amount ?? 0);

        $taxAmount = $cgst_amount + $sgst_amount + $igst_amount;

        $logoDataUri = $this->imageDataUri($biz?->logo);
        $signDataUri = $this->imageDataUri($biz?->signature);

        $type = $invoice->invoice_type;

        $billTemplate = $biz?->billTemplate;

        $templateSetting = null;

        if ($biz && $billTemplate) {
            $templateSetting = \App\Models\BusinessBillTemplateSetting::where('business_id', $biz->id)
                ->where('bill_template_id', $billTemplate->id)
                ->first();
        }

        $vm = compact(
            'inv',
            'invoice',
            'biz',
            'client',
            'items',
            'charges',
            'type',
            'taxAmount',
            'logoDataUri',
            'signDataUri',
            'subtotal',
            'tax_total',
            'discount_total',
            'charges_total',
            'tcs_percent',
            'tcs_amount',
            'round_off',
            'less_amount',
            'grand_total',
            'received',
            'balance',
            'cgst_amount',
            'sgst_amount',
            'igst_amount',
            'payRow',
            'templateSetting'
        );

        $vm['logo'] = $logoDataUri;
        $vm['sign'] = $signDataUri;

        $view = 'invoices.' . ($billTemplate->page_name ?? 'pdf_simple');

        return Pdf::loadView($view, $vm)
            ->setPaper('a4');
    }



    protected function normalizePdfPath(?string $value): ?string
    {
        if (!$value) {
            return null;
        }

        $value = trim($value);

        // Full URL hai to path part nikal lo
        if (strpos($value, 'http://') === 0 || strpos($value, 'https://') === 0) {
            $path = parse_url($value, PHP_URL_PATH) ?: '';
            $value = ltrim($path, '/');
        }

        // Agar "storage/..." se start ho raha ho to uske baad ka part lo
        if (strpos($value, 'storage/') === 0) {
            $value = substr($value, strlen('storage/'));
        }

        // Last me ensure no leading slash
        return ltrim($value, '/');
    }



    public function convertToTax(Request $r, \App\Models\Invoice $invoice, \App\Services\StockService $stock)
    {
        $invoice = $invoice->load(['items', 'client', 'business']);

        // ✅ same business safety
        $bid = $r->user()->current_business_id ?? session('active_business_id') ?? $invoice->business_id;
        if ((int)$invoice->business_id !== (int)$bid) abort(403);

        $fromType = strtolower(trim((string)($invoice->invoice_type ?? '')));
        if (!in_array($fromType, ['quotation','proforma'], true)) {
            return back()->withErrors(['convert' => 'Only Quotation/Proforma can be converted to Tax invoice.']);
        }

        // ✅ already tax?
        if (strtolower((string)$invoice->invoice_type) === 'tax') {
            return back()->withErrors(['convert' => 'This invoice is already a Tax invoice.']);
        }

        // ✅ prevent double conversion (if column exists)
        if (!empty($invoice->converted_at)) {
            return back()->withErrors(['convert' => 'This invoice is already converted once.']);
        }

        // ✅ items_json is source of truth (NO 0 amounts issue)
        $rows = [];
        if (!empty($invoice->items_json)) {
            $rows = json_decode($invoice->items_json, true) ?: [];
        }
        if (!is_array($rows) || count($rows) < 1) {
            return back()->withErrors(['convert' => 'Items not found (items_json missing).']);
        }

        // ✅ Convert date: today (agar same date rakhna ho to $invoice->invoice_date use kar lo)
        $invoiceDate = now()->toDateString();

        // ===== TAX prefix series =====
        $taxBase = optional(
                $r->user()->businesses()->where('businesses.id', $bid)->first()
            )->invoice_base_prefix ?? 'RV/SL';

        $taxSeries = \App\Services\InvoiceNumber::previewPrefix($invoiceDate, $taxBase); // RV/SL/25-26/
        $alloc     = \App\Services\InvoiceNumber::next((int)$bid, $invoiceDate, $taxSeries, 3, 'tax');

        DB::transaction(function () use ($invoice, $invoiceDate, $taxSeries, $alloc, $rows, $stock) {

            // ✅ Update main invoice only (keep totals/charges/notes etc. unchanged)
            $invoice->update([
                'invoice_type'   => 'tax',

                // number/prefix/date change
                'invoice_date'   => $invoiceDate,
                'invoice_prefix' => $taxSeries,
                'invoice_number' => $alloc['full'],

                // ✅ make payments reset for tax conversion (optional)
                'received_amount'=> 0,
                'balance'        => (float)($invoice->total ?? 0),

                // ✅ keep items_json as-is (or re-encode normalized rows)
                'items_json'     => json_encode($rows),

                // ✅ flags (if columns exist)
                'converted_at'   => now(),
            ]);

            /**
             * ✅ IMPORTANT:
             * invoice_items rows ko update mat karo agar already correct hai.
             * BUT: agar aapke invoice_items me amount/rate 0 ho jate hain kabhi,
             * to below sync ON kar do (safe).
             */
            foreach ($rows as $row) {
                $itemId = (int)($row['item_id'] ?? 0);
                if (!$itemId) continue;

                $qty = (int)($row['qty'] ?? $row['quantity'] ?? 1);
                $qty = $qty < 1 ? 1 : $qty;

                $rate   = (float)($row['rate'] ?? 0);
                $amount = (float)($row['amount'] ?? 0);

                $type = strtolower(trim((string)($row['item_type'] ?? 'product')));

                // match by item_id (agar multiple same item_id ho sakte hain to index based match karo)
                $it = $invoice->items->firstWhere('item_id', $itemId);
                if ($it) {
                    $it->update([
                        'quantity' => $qty,
                        'tax_percent' => (float)($row['tax_percent'] ?? 0),
                        'rate'     => $rate,     // base
                        'amount'   => $amount,   // base+tax

                        // product fields
                        'gold_wt'     => (float)($row['gold_wt'] ?? 0),
                        'silver_wt'   => (float)($row['silver_wt'] ?? 0),
                        'gold_rate'   => (float)($row['gold_rate'] ?? 0),
                        'silver_rate' => (float)($row['silver_rate'] ?? 0),
                        'making_rate' => (float)($row['making_rate'] ?? 0),

                        // service field
                        'making_charge' => ($type === 'service')
                            ? (float)($row['service_rate'] ?? $row['making_charge'] ?? 0)
                            : null,
                    ]);
                }
            }

            // ✅ stock cut ONE time (tax invoice banne ke baad)
            $invoice->load(['items']);
            $stock->recordSale($invoice);

            // ✅ stock flag (if column exists)
            // $invoice->update(['stock_posted_at' => now()]);
        });

        // ✅ regenerate pdf (optional but recommended)
        // $pdf = $this->simplePdfBuild($invoice->fresh(['items','client','business']));
        // $dir = "invoices/{$bid}/" . now()->format('Y-m');
        // $safeName = preg_replace('/[^A-Za-z0-9\-_\.]/', '-', (string)$invoice->invoice_number);
        // $path = $dir . "/" . $safeName . ".pdf";
        // \Storage::disk('public')->put($path, $pdf->output());
        // $invoice->update(['pdf_url' => $path]);

        $billRequest = BillRequest::find($invoice->bil_request_id);
        if($billRequest){
            $billRequest->update(['status' => 'processed']);
        }

        return redirect()->route('invoices.edit', $invoice->id)
            ->with('success', 'Converted to Tax invoice successfully.');
    }




    public function paymentIn(Request $request, Invoice $invoice)
    {
//        $this->authorize('edit invoice'); // apne permission/guard ke hisab se adjust

        $data = $request->validate([
            'amount' => ['required','numeric','min:0.01'],
        ]);

        $payAmount = (float) $data['amount'];

        DB::transaction(function () use ($invoice, $payAmount) {

            // lock row to avoid race condition
            $inv = Invoice::whereKey($invoice->id)->lockForUpdate()->firstOrFail();

            $total    = (float) ($inv->total ?? 0);
            $received = (float) ($inv->received_amount ?? 0);

            $newReceived = $received + $payAmount;

            // overpayment protect (optional)
            if ($newReceived > $total) {
                $newReceived = $total;
            }

            $newBalance = max($total - $newReceived, 0);

            // status auto
            $status = 'unpaid';
            if ($newReceived >= $total && $total > 0) $status = 'paid';
            elseif ($newReceived > 0) $status = 'partial';

            $inv->update([
                'received_amount' => $newReceived,
                'balance'         => $newBalance,   // agar column nahi hai to remove karo
                'status'          => $status,       // agar status field hai
                'updated_by'      => auth()->id(),  // agar aap use karte ho
            ]);
        });
        return redirect()->route('invoices.preview', $invoice->id)
            ->with('success', 'Payment added successfully.');
    }


    // public function getLastClientInvoice(Request $request, Client $client)
    // {
    //     try {
    //         $docType = $request->get('doc_type', 'tax');

    //         $invoice = Invoice::with(['items', 'payments'])
    //             ->where('client_id', $client->id)
    //             ->where('invoice_type', $docType)   // ✅ doc_type nahi, invoice_type hai
    //             ->latest('id')
    //             ->first();

    //         if (!$invoice) {
    //             return response()->json([
    //                 'found' => false,
    //                 'message' => 'No previous invoice found for this party.',
    //             ]);
    //         }

    //         // last payment record
    //         $payment = $invoice->payments()->latest('id')->first();

    //         $items = $invoice->items->map(function ($item) {

    //         $itemType = 'service';

    //         if (
    //             (float)($item->gold_wt ?? 0) > 0 ||
    //             (float)($item->silver_wt ?? 0) > 0 ||
    //             (float)($item->metal_weight ?? 0) > 0 ||
    //             (float)($item->making_rate ?? 0) > 0 ||
    //             (float)($item->making_charge ?? 0) > 0 ||
    //             (float)($item->gemstone_wt_ct ?? 0) > 0 ||
    //             (float)($item->diamond_wt_ct ?? 0) > 0
    //         ) {
    //             $itemType = 'product';
    //         }

    //         return [
    //             'item_id'       => $item->item_id,
    //             'item_type'     => $itemType,
    //             'description'   => $item->description ?? '',
    //             'hsn'           => $item->hsn_code ?? $item->sac_code ?? '',
    //             'quantity'      => (float) ($item->quantity ?? 1),

    //             'making_rate'   => (float) ($item->making_rate ?? $item->making_charge ?? 0),
    //             'gold_purity'   => null,
    //             'silver_purity' => null,

    //             'gold_rate'     => (float) ($item->gold_rate ?? 0),
    //             'silver_rate'   => (float) ($item->silver_rate ?? 0),
    //             'silver_wt'     => (float) ($item->silver_wt ?? 0),
    //             'gold_wt'       => (float) ($item->gold_wt ?? 0),
    //             'gemstone_wt'   => (float) ($item->gemstone_wt_ct ?? 0),
    //             'diamond_wt'    => (float) ($item->diamond_wt_ct ?? 0),

    //             'service_rate'  => (float) ($item->rate ?? 0),
    //             'tax_percent'   => (float) ($item->tax_percent ?? 0),
    //             'manual_amount' => (float) ($item->amount ?? 0),
    //         ];
    //     })->values();

    //         return response()->json([
    //             'found' => true,
    //             'invoice' => [
    //                 'id' => $invoice->id,
    //                 'invoice_number' => $invoice->invoice_number ?? '',
    //                 'invoice_date' => $invoice->invoice_date
    //                     ? $invoice->invoice_date->format('Y-m-d')
    //                     : null,

    //                 'terms' => $invoice->terms ?? '',
    //                 'reverse_charge' => (bool) ($invoice->reverse_charge ?? false),

    //                 // ✅ payment invoice_payments table se aayega
    //                 'pay_cash' => (float) ($payment->cash_amount ?? 0),
    //                 'pay_upi' => (float) ($payment->online_amount ?? 0),
    //                 'pay_card' => (float) ($payment->card_amount ?? 0),
    //                 'pay_cheque' => (float) ($payment->cheque_amount ?? 0),
    //                 'credit_sales_excess' => (float) ($payment->credit_sales_excess_amount ?? 0),
    //                 'advance_amount' => (float) ($payment->advance_amount ?? 0),

    //                 'online_mode' => $payment->online_mode ?? '',
    //                 'online_ref' => $payment->online_ref ?? '',
    //                 'upi_id' => $payment->upi_id ?? '',
    //                 'card_last4' => $payment->card_last4 ?? '',
    //                 'card_ref' => $payment->card_ref ?? '',
    //                 'cheque_no' => $payment->cheque_no ?? '',
    //                 'bank_name' => $payment->bank_name ?? '',
    //                 'bank_account_id' => null, // table me nahi dikh raha, isliye null

    //                 'discount_total' => (float) ($invoice->discount_total ?? 0),
    //                 'charge_total' => (float) ($invoice->charge_total ?? 0),
    //                 'tcs_percent' => (float) ($invoice->tcs_percent ?? 0),
    //                 'tcs_amount' => (float) ($invoice->tcs_amount ?? 0),
    //                 'round_off' => (float) ($invoice->round_off ?? 0),

    //                 'charges_json' => is_array($invoice->charges_json)
    //                     ? $invoice->charges_json
    //                     : (json_decode($invoice->charges_json ?? '[]', true) ?: []),

    //                 'items' => $items,
    //             ],
    //         ]);
    //     } catch (\Throwable $e) {
    //         Log::error('getLastClientInvoice error', [
    //             'message' => $e->getMessage(),
    //             'line' => $e->getLine(),
    //             'file' => $e->getFile(),
    //             'client_id' => $client->id ?? null,
    //             'doc_type' => $request->get('doc_type'),
    //         ]);

    //         return response()->json([
    //             'found' => false,
    //             'message' => 'Server error',
    //             'error' => $e->getMessage(),
    //             'line' => $e->getLine(),
    //         ], 500);
    //     }
    // }


    public function getLastClientInvoice(Request $request, Client $client)
    {
        try {
            $docType = $request->get('doc_type', 'tax');

            $invoice = Invoice::with(['items.item', 'payments'])
                ->where('client_id', $client->id)
                ->where('invoice_type', $docType)
                ->latest('id')
                ->first();

            if (!$invoice) {
                return response()->json([
                    'found' => false,
                    'message' => 'No previous invoice found for this party.',
                ]);
            }

            $payment = $invoice->payments()->latest('id')->first();

            $items = $invoice->items->map(function ($item) {
                $itemType = strtolower(trim($item->item->type ?? ''));

                if (!in_array($itemType, ['product', 'service'])) {
                    $itemType = 'service';
                }

                return [
                    'item_id'       => $item->item_id,
                    'item_type'     => $itemType,
                    'description'   => $item->description ?? '',
                    'hsn'           => $itemType === 'service'
                        ? ($item->sac_code ?? '')
                        : ($item->hsn_code ?? ''),
                    'quantity'      => (float) ($item->quantity ?? 1),

                    'making_rate'   => $itemType === 'product'
                        ? (float) ($item->making_rate ?? $item->making_charge ?? 0)
                        : 0,

                    'gold_purity'   => $item->item->gold_purity ?? null,
                    'silver_purity' => $item->item->silver_purity ?? null,

                    'gold_rate'     => $itemType === 'product' ? (float) ($item->gold_rate ?? 0) : 0,
                    'silver_rate'   => $itemType === 'product' ? (float) ($item->silver_rate ?? 0) : 0,
                    'silver_wt'     => $itemType === 'product' ? (float) ($item->silver_wt ?? 0) : 0,
                    'gold_wt'       => $itemType === 'product' ? (float) ($item->gold_wt ?? 0) : 0,
                    'gemstone_wt'   => $itemType === 'product' ? (float) ($item->gemstone_wt_ct ?? 0) : 0,
                    'diamond_wt'    => $itemType === 'product' ? (float) ($item->diamond_wt_ct ?? 0) : 0,

                    'service_rate'  => $itemType === 'service' ? (float) ($item->rate ?? 0) : 0,
                    'tax_percent'   => (float) ($item->tax_percent ?? 0),
                    'manual_amount' => (float) ($item->amount ?? 0),
                ];
            })->values();

            return response()->json([
                'found' => true,
                'invoice' => [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number ?? '',
                    'invoice_date' => $invoice->invoice_date
                        ? $invoice->invoice_date->format('Y-m-d')
                        : null,

                    'terms' => $invoice->terms ?? '',
                    'reverse_charge' => (bool) ($invoice->reverse_charge ?? false),

                    'pay_cash' => (float) ($payment->cash_amount ?? 0),
                    'pay_upi' => (float) ($payment->online_amount ?? 0),
                    'pay_card' => (float) ($payment->card_amount ?? 0),
                    'pay_cheque' => (float) ($payment->cheque_amount ?? 0),
                    'credit_sales_excess' => (float) ($payment->credit_sales_excess_amount ?? 0),
                    'advance_amount' => (float) ($payment->advance_amount ?? 0),

                    'online_mode' => $payment->online_mode ?? '',
                    'online_ref' => $payment->online_ref ?? '',
                    'upi_id' => $payment->upi_id ?? '',
                    'card_last4' => $payment->card_last4 ?? '',
                    'card_ref' => $payment->card_ref ?? '',
                    'cheque_no' => $payment->cheque_no ?? '',
                    'bank_name' => $payment->bank_name ?? '',
                    'bank_account_id' => null,

                    'discount_total' => (float) ($invoice->discount_total ?? 0),
                    'charge_total' => (float) ($invoice->charge_total ?? 0),
                    'tcs_percent' => (float) ($invoice->tcs_percent ?? 0),
                    'tcs_amount' => (float) ($invoice->tcs_amount ?? 0),
                    'round_off' => (float) ($invoice->round_off ?? 0),

                    'charges_json' => is_array($invoice->charges_json)
                        ? $invoice->charges_json
                        : (json_decode($invoice->charges_json ?? '[]', true) ?: []),

                    'items' => $items,
                ],
            ]);
        } catch (\Throwable $e) {
            Log::error('getLastClientInvoice error', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'client_id' => $client->id ?? null,
                'doc_type' => $request->get('doc_type'),
            ]);

            return response()->json([
                'found' => false,
                'message' => 'Server error',
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
            ], 500);
        }
    }



    public function reportsPage(Request $request)
    {
        $me  = $request->user();

        $bid = $me->current_business_id ?? session('active_business_id');
        if (!$bid) {
            $bid = $me->businesses()->pluck('businesses.id')->first();
        }

        if (!$bid) {
            return back()->withErrors(['business' => 'Active business select/attach नहीं है.']);
        }

        $type = strtolower(trim((string) $request->get('type', 'tax')));
        if (!in_array($type, ['tax', 'proforma', 'quotation'], true)) {
            $type = 'tax';
        }

        $permByType = [
            'tax'       => 'show invoices',
            'proforma'  => 'show proformas',
            'quotation' => 'show quotations',
        ];

        $requiredPerm = $permByType[$type] ?? 'show invoices';

        if (!$me->can($requiredPerm)) {
            abort(403, "You don't have permission: {$requiredPerm}");
        }

        return view('invoices.reports', [
            'activeType' => $type,
            'filters' => [
                'search'    => (string) $request->get('search', ''),
                'from_date' => $request->get('from_date', ''),
                'to_date'   => $request->get('to_date', ''),
                'status'    => $request->get('status', ''),
            ],
        ]);
    }


    public function export(Request $request)
    {
        $me  = $request->user();

        $bid = $me->current_business_id ?? session('active_business_id');
        if (!$bid) {
            $bid = $me->businesses()->pluck('businesses.id')->first();
        }

        if (!$bid) {
            return back()->withErrors(['business' => 'Active business select/attach नहीं है.']);
        }

        $type = strtolower(trim((string) $request->get('type', 'tax')));
        if (!in_array($type, ['tax', 'proforma', 'quotation'], true)) {
            $type = 'tax';
        }

        $permByType = [
            'tax'       => 'show invoices',
            'proforma'  => 'show proformas',
            'quotation' => 'show quotations',
        ];

        $requiredPerm = $permByType[$type] ?? 'show invoices';

        if (!$me->can($requiredPerm)) {
            abort(403, "You don't have permission: {$requiredPerm}");
        }

        $search   = trim((string) $request->get('search', ''));
        $fromDate = $request->get('from_date');
        $toDate   = $request->get('to_date');
        $status   = $request->get('status');

        $q = \App\Models\Invoice::query()
            ->with(['client:id,name,mobile'])
            ->where('business_id', $bid)
            ->where('invoice_type', $type);

        if ($search !== '') {
            $q->where(function ($w) use ($search) {
                $w->where('invoice_number', 'like', "%{$search}%")
                ->orWhere('total', 'like', "%{$search}%")
                ->orWhere('balance', 'like', "%{$search}%")
                ->orWhere('received_amount', 'like', "%{$search}%")
                ->orWhereHas('client', function ($c) use ($search) {
                    $c->where('name', 'like', "%{$search}%")
                        ->orWhere('mobile', 'like', "%{$search}%")
                        ->orWhere('gstin', 'like', "%{$search}%")
                        ->orWhere('pan', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%");
                });
            });
        }

        if (!empty($fromDate)) {
            $q->whereDate('invoice_date', '>=', $fromDate);
        }

        if (!empty($toDate)) {
            $q->whereDate('invoice_date', '<=', $toDate);
        }

        if (!empty($status)) {
            if ($status === 'paid') {
                $q->where('balance', '<=', 0);
            } elseif ($status === 'unpaid') {
                $q->where('received_amount', '<=', 0);
            } elseif ($status === 'partial') {
                $q->where('received_amount', '>', 0)->where('balance', '>', 0);
            }
        }

        $rows = $q->orderByDesc('invoice_date')
            ->orderByDesc('id')
            ->get();

        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\InvoicesExport($rows),
            'invoice-report-' . $type . '-' . now()->format('Y-m-d_H-i-s') . '.xlsx'
        );
    }

}
