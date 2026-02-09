<?php

namespace App\Http\Controllers;

use App\Exports\InvoicesExport;
use App\Models\AdditionalCharge;
use App\Models\BankAccount;
use App\Models\Business;
use App\Models\Invoice;
use App\Models\Client;
use App\Models\InvoiceCharge;
use App\Models\InvoiceItem;
use App\Models\InvoicePayment;
use App\Models\Item;
use App\Models\MetalRate;
use App\Services\InvoiceNumber;
use App\Services\StockService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;


class InvoiceController extends Controller
{

    protected StockService $stock;   // 👈 ADD

    public function __construct(StockService $stock)   // 👈 ADD
    {
        $this->stock = $stock;
    }


    public function index(\Illuminate\Http\Request $request)
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
                $w->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('client', function ($c) use ($search) {
                        $c->where('name', 'like', "%{$search}%");
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

        $business = Business::findOrFail($bid);

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
        $items = Item::where('business_id', $bid)
            ->where('is_active', true)
            ->orderBy('name')
            ->get([
                'id','name','type','sku','description','tax_rate','making_charge','sac',
                'gold_weight','gold_purity',
                'silver_weight','silver_purity',
                'stone_weight','stone_charges',
                'diamond_weight','diamond_charges',
                'price'
            ]);

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

            // ✅ NEW
            'docType'            => $docType,
        ]);
    }




    public function edit(Request $request, \App\Models\Invoice $invoice)
    {
        // ✅ relations load
        $invoice = $invoice->load([
            'client',
            'items',      // invoice_items
            'business',
            'payments'    // if exists
        ]);

        // ✅ Active business resolve
        $bid = $request->user()->current_business_id ?? session('active_business_id');
        if (!$bid) $bid = $invoice->business_id;

        // ✅ Safety
        if ((int)$invoice->business_id !== (int)$bid) {
            abort(403, 'Unauthorized invoice access.');
        }

        $today = \Carbon\Carbon::parse($invoice->invoice_date)->toDateString();

        // ✅ detect doc type from invoice (tax/proforma/quotation)
        $docType = strtolower(trim((string)($invoice->invoice_type ?? 'tax')));
        if (!in_array($docType, ['tax','proforma','quotation'], true)) $docType = 'tax';

        $business = \App\Models\Business::findOrFail($bid);

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

        // ✅ suggested prefix (for display only; edit me hum existing prefix hi use karenge)
        $suggestedPrefix = \App\Services\InvoiceNumber::previewPrefix($today, $base);

        // ✅ Clients
        $clients = \App\Models\Client::where('business_id', $bid)->where('is_save', true)->orWhere('id', $invoice->client_id)
            ->orderBy('name')
            ->get(['id','name','mobile','address','state','state_code','gstin','pincode']);

        // ✅ Items master
        $items = \App\Models\Item::where('business_id', $bid)
            ->where('is_active', true)
            ->orderBy('name')
            ->get([
                'id','name','type','sku','description','tax_rate','making_charge','sac',
                'gold_weight','gold_purity',
                'silver_weight','silver_purity',
                'stone_weight','stone_charges',
                'diamond_weight','diamond_charges',
                'price'
            ]);

        // ✅ Metal rates
        $metalRates = \App\Models\MetalRate::where('business_id', $bid)
            ->whereDate('rate_date', $today)
            ->where('is_active', true)
            ->get(['metal_type','purity','rate_per_gram']);

        // ✅ Banks
        $banks = \App\Models\BankAccount::where('business_id', $bid)
            ->orderBy('bank_name')
            ->get(['id','bank_name','account_holder','account_no','ifsc']);

        // ✅ invoice JSON for prefill
        $invoiceJson = [
            'id' => $invoice->id,
            'client_id' => $invoice->client_id,
            'invoice_date' => $today,
            'invoice_number' => (string)($invoice->invoice_number ?? ''),
            'invoice_prefix' => (string)($invoice->invoice_prefix ?? $suggestedPrefix),

            'gst_no' => (string)($invoice->gst_no ?? ''),
            'transport_mode' => (string)($invoice->transport_mode ?? ''),
            'reverse_charge' => (int)($invoice->reverse_charge ?? 0),

            // totals / extras
            'discount_type' => (string)($invoice->discount_type ?? 'flat'),
            'discount_value' => (float)($invoice->discount_value ?? 0),

            'charges_json' => $invoice->charges_json ?? null, // string/array (jo aap store kar rahe)
            'charge_total' => (float)($invoice->charge_total ?? 0),

            'tcs_percent' => (float)($invoice->tcs_percent ?? 0),
            'tcs_amount'  => (float)($invoice->tcs_amount ?? 0),

            'round_off' => (float)($invoice->round_off ?? 0),

            'payment_method' => (string)($invoice->payment_method ?? 'cash'),
            'bank_account_id' => $invoice->bank_account_id ?? null,

            // received fields (agar aapke table me hai)
            'received' => (float)($invoice->received ?? 0),

            // invoice items map
            'items' => $invoice->items->map(function($it){
                return [
                    'item_id' => $it->item_id ?? null,
                    'item_type' => $it->item_type ?? null,

                    'description' => $it->description ?? '',
                    'hsn' => $it->hsn ?? '',
                    'quantity' => (float)($it->quantity ?? 1),

                    'making_rate' => (float)($it->making_rate ?? 0),
                    'gold_purity' => $it->gold_purity ?? null,
                    'silver_purity' => $it->silver_purity ?? null,

                    'gold_rate' => (float)($it->gold_rate ?? 0),
                    'silver_rate' => (float)($it->silver_rate ?? 0),
                    'silver_wt' => (float)($it->silver_wt ?? 0),
                    'gold_wt' => (float)($it->gold_wt ?? 0),
                    'gemstone_wt' => (float)($it->gemstone_wt ?? 0),
                    'diamond_wt' => (float)($it->diamond_wt ?? 0),

                    'service_rate' => (float)($it->service_rate ?? 0),
                    'tax_percent' => (float)($it->tax_percent ?? 0),

                    // for edit screen: amount final (tax included)
                    'amount' => (float)($it->amount ?? 0),
                ];
            })->values(),
        ];

        return view('invoices.edit_kapoor_style', [
            'invoice'            => $invoice,

            'today'              => $today,
            'clientsJson'        => $clients->values()->toJson(),
            'itemsJson'          => $items->values()->toJson(),
            'metalRatesJson'     => $metalRates->values()->toJson(),
            'banksJson'          => $banks->values()->toJson(),

            'suggestedPrefix'    => $suggestedPrefix,
            'basePrefix'         => $base,
            'defaultTerms'       => $business->terms,

            'businessState'      => $business->state,
            'businessStateCode'  => $business->state_code,
            'businessGstin'      => $business->gstin,

            'docType'            => $docType,

            // ✅ prefill
            'invoiceJson'        => json_encode($invoiceJson, JSON_UNESCAPED_UNICODE),
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


    public function show(Invoice $invoice)
    {
        // invoice number safe for filename
        $safeNumber = str_replace(['/', '\\'], '-', (string)($invoice->invoice_number ?? 'INV'));

        // -------------------------------
        // 1️⃣ If PDF already saved in DB
        // -------------------------------
//        if (!empty($invoice->pdf_url)) {
//
//            $path = $this->normalizePdfPath($invoice->pdf_url);
//
//            if ($path && Storage::disk('public')->exists($path)) {
//                return response()->file(
//                    Storage::disk('public')->path($path),
//                    [
//                        'Content-Type'        => 'application/pdf',
//                        'Content-Disposition' => 'inline; filename="Invoice-'.$safeNumber.'.pdf"',
//                    ]
//                );
//            }
//        }

        // --------------------------------------------------
        // 2️⃣ PDF missing (DB empty OR file deleted)
        //    → Generate + Save + Show
        // --------------------------------------------------

        // Always use fresh relations for PDF
        $invoice = $invoice->fresh(['client', 'items', 'business']);

        // Build PDF
        $pdf = $this->simplePdfBuild($invoice);
        // OR: $pdf = $this->buildInvoicePdf($invoice);

        $fileName = 'invoices/Invoice-' . $safeNumber . '.pdf';

        // Save PDF to storage
        Storage::disk('public')->put($fileName, $pdf->output());

        // Save path in DB (only relative path)
        $invoice->update([
            'pdf_url' => $fileName,
        ]);

        // Show PDF
        return response()->file(
            Storage::disk('public')->path($fileName),
            [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'inline; filename="Invoice-'.$safeNumber.'.pdf"',
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





    public function store(Request $r, StockService $stock, $docType)
    {
        $docType = strtolower(trim((string)$docType));
        if (!in_array($docType, ['tax', 'proforma', 'quotation'], true)) {
            $docType = 'tax';
        }

        $bid = $r->user()->current_business_id ?? session('active_business_id');
        if (!$bid) {
            return back()->withErrors(['business' => 'Active business select/attach नहीं है.'])->withInput();
        }

        $data = $r->validate([
            'client_id'      => ['required','exists:clients,id'],
            'invoice_date'   => ['required','date'],
            'invoice_prefix' => ['nullable','string','max:255'],

            'transport_mode' => ['nullable','string','max:255'],
            'gst_no'         => ['nullable','string','max:50'],
            'reverse_charge' => ['nullable'],

            'notes'          => ['nullable','string','max:2000'],
            'terms'          => ['nullable','string','max:2000'],

            'items_json'     => ['required','string'],

            'charges_json'   => ['nullable','string'],
            'discount_total' => ['nullable','numeric','min:0'],
            'charge_total'   => ['nullable','numeric','min:0'],
            'tcs_percent'    => ['nullable','numeric','min:0'],
            'tcs_amount'     => ['nullable','numeric','min:0'],
            'round_off'      => ['nullable','numeric'],
            'less_amount'    => ['nullable','numeric','min:0'],

            'cgst_percent'   => ['nullable','numeric','min:0'],
            'sgst_percent'   => ['nullable','numeric','min:0'],
            'igst_percent'   => ['nullable','numeric','min:0'],

            'payment_method'  => ['nullable','string','max:255'],
            'bank_account_id' => ['nullable','integer'],
            'signature' => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],
            'kots_json' => ['nullable','string','max:5000'],
        ]);

        // ✅ Proforma में payment validation skip
        $pay = [];
        if ($docType === 'tax') {
            $pay = $r->validate([
                'pay_cash'            => ['nullable','numeric','min:0'],
                'pay_upi'             => ['nullable','numeric','min:0'],
                'pay_card'            => ['nullable','numeric','min:0'],
                'pay_cheque'          => ['nullable','numeric','min:0'],

                'credit_sales_excess' => ['nullable','numeric','min:0'],
                'advance_amount'      => ['nullable','numeric','min:0'],

                'online_mode'         => ['nullable','string','max:30'],
                'online_ref'          => ['nullable','string','max:100'],
                'upi_id'              => ['nullable','string','max:100'],

                'card_last4'          => ['nullable','string','max:4'],
                'card_ref'            => ['nullable','string','max:100'],

                'cheque_no'           => ['nullable','string','max:50'],
                'bank_name'           => ['nullable','string','max:100'],
                'notes'               => ['nullable','string','max:2000'],
            ]);
        }

        // -------- prefix helper --------
        $computePrefix = function (string $date, string $base = 'INV'): string {
            $ts = strtotime($date);
            $y  = (int)date('Y', $ts);
            $m  = (int)date('n', $ts);
            $startYY = ($m >= 4) ? ($y % 100) : (($y - 1) % 100);
            $a = str_pad((string)$startYY, 2, '0', STR_PAD_LEFT);
            $b = str_pad((string)(($startYY + 1) % 100), 2, '0', STR_PAD_LEFT);
            $fy = "{$a}-{$b}";
            $base = rtrim($base, '/');
            return "{$base}/{$fy}/";
        };

        $invoiceDate = \Carbon\Carbon::parse($data['invoice_date'])->toDateString();
        $prefix      = trim($data['invoice_prefix'] ?? '');
        $base = ($docType === 'proforma') ? 'PF' : (($docType === 'quotation') ? 'QT' : 'INV');
        if ($prefix === '') $prefix = $computePrefix($invoiceDate, $base);


        // -------- items parse --------
        $rows = json_decode($data['items_json'], true);
        if (!is_array($rows) || count($rows) < 1) {
            return back()->withErrors(['items' => 'कम से कम 1 line item जरूरी है.'])->withInput();
        }

        // -------- normalize state_code helper --------
        $normCode = function ($v) {
            $s = trim((string)$v);
            $s = preg_replace('/\D+/', '', $s);
            $s = ltrim($s, '0');
            return $s;
        };

        /**
         * ✅ IMPORTANT FIX:
         * Tax अब "avgTaxPercent" से taxableAmount पर नहीं निकलेगा,
         * बल्कि हर line का tax sum होगा (slab-wise exact).
         */

        $subtotal     = 0.0;     // sum of base amounts
        $weightedTax  = 0.0;     // base * tax% (for display avg%)
        $itemsTaxTotal = 0.0;    // ✅ sum of line tax (EXACT)
        $cleanRows    = [];

        foreach ($rows as $i => $row) {
            $itemId = $row['item_id'] ?? null;
            if (empty($itemId)) {
                return back()->withErrors(['items' => "Row ".($i+1)." में Item select नहीं है."])->withInput();
            }

            $itemType = strtolower(trim((string)($row['item_type'] ?? 'product')));
            if (!in_array($itemType, ['product','service'], true)) $itemType = 'product';

            $desc = trim($row['description'] ?? '');
            if ($desc === '') {
                return back()->withErrors(['items' => "Row ".($i+1)." description missing."])->withInput();
            }

            $hsn = trim($row['hsn'] ?? '');
            $qty = (int)($row['quantity'] ?? 1);
            $qty = $qty < 1 ? 1 : $qty;

            $taxPct = (float)($row['tax_percent'] ?? 0);
            if ($taxPct < 0 || $taxPct > 100) {
                return back()->withErrors(['items' => "Row ".($i+1)." tax % invalid."])->withInput();
            }

            // ===== SERVICE =====
            if ($itemType === 'service') {
                $serviceRate = (float)($row['service_rate'] ?? 0);
                if ($serviceRate < 0) {
                    return back()->withErrors(['items' => "Row ".($i+1)." service rate invalid."])->withInput();
                }

                $lineBase = round($serviceRate * $qty, 2);
                $lineTax  = round($lineBase * ($taxPct/100), 2); // ✅ exact slab

                $subtotal      += $lineBase;
                $weightedTax   += ($lineBase * $taxPct);
                $itemsTaxTotal += $lineTax;

                $cleanRows[] = [
                    'item_id'       => (int)$itemId,
                    'item_type'     => 'service',
                    'description'   => $desc,
                    'hsn'           => $hsn,
                    'qty'           => $qty,
                    'tax_percent'   => round($taxPct,2),

                    // service_rate store per unit (tax excluded)
                    'service_rate'  => round($serviceRate,2),

                    // base/tax/amount (FINAL) store for later usage
                    'rate'          => $lineBase,                 // base
                    'tax_amount'    => $lineTax,                  // tax
                    'amount'        => round($lineBase + $lineTax, 2), // base+tax

                    // keep your old keys if other code expects them
                    'making_charge' => round($serviceRate,2),

                    // product fields empty
                    'gold_wt' => 0,'silver_wt'=>0,'gold_rate'=>0,'silver_rate'=>0,
                    'gemstone_wt'=>0,'diamond_wt'=>0,'making_rate'=>0,
                    'stone_charges'=>0,
                ];
                continue;
            }

            // ===== PRODUCT =====
            $goldWt     = (float)($row['gold_wt'] ?? 0);
            $silverWt   = (float)($row['silver_wt'] ?? 0);
            $goldRate   = (float)($row['gold_rate'] ?? 0);
            $silverRate = (float)($row['silver_rate'] ?? 0);
            $makingRate = (float)($row['making_rate'] ?? 0);

            $gemCt = (float)($row['gemstone_wt'] ?? 0);
            $diaCt = (float)($row['diamond_wt'] ?? 0);

            if ($goldWt < 0 || $silverWt < 0 || $goldRate < 0 || $silverRate < 0 || $makingRate < 0) {
                return back()->withErrors(['items' => "Row ".($i+1)." invalid values."])->withInput();
            }

            $lineBase = round((($goldWt * $goldRate) + ($silverWt * $silverRate) + $makingRate) * $qty, 2);
            $lineTax  = round($lineBase * ($taxPct/100), 2); // ✅ exact slab

            $subtotal      += $lineBase;
            $weightedTax   += ($lineBase * $taxPct);
            $itemsTaxTotal += $lineTax;

            $cleanRows[] = [
                'item_id'     => (int)$itemId,
                'item_type'   => 'product',
                'description' => $desc,
                'hsn'         => $hsn,
                'qty'         => $qty,
                'tax_percent' => round($taxPct,2),

                'gold_wt'     => round($goldWt,3),
                'silver_wt'   => round($silverWt,3),
                'gold_rate'   => round($goldRate,2),
                'silver_rate' => round($silverRate,2),

                'gemstone_wt' => round($gemCt,3),
                'diamond_wt'  => round($diaCt,3),

                'making_rate' => round($makingRate,2),
                'making_charge'=> null,
                'stone_charges'=> null,

                // ✅ base/tax/amount
                'rate'        => $lineBase,
                'tax_amount'  => $lineTax,
                'amount'      => round($lineBase + $lineTax, 2),
            ];
        }

        $subtotal      = round($subtotal, 2);
        $itemsTaxTotal = round($itemsTaxTotal, 2);

        // display-only avg tax (no longer used for tax math)
        $avgTaxPercentRaw = ($subtotal > 0) ? ($weightedTax / $subtotal) : 0;
        $avgTaxPercent    = round($avgTaxPercentRaw, 2);

        // invoice-level adjustments
        $discountTotal = round((float)($data['discount_total'] ?? 0), 2);
        $chargeTotal   = round((float)($data['charge_total'] ?? 0), 2);

        // taxable = subtotal - discount + charges
        $taxableAmount = round(max(0, $subtotal - $discountTotal + $chargeTotal), 2);

        // ✅ TAX FIX: EXACT slab-wise tax = sum(lineTax)
        // NOTE: Charges taxable? If YES add charges tax, if NO keep only itemsTaxTotal.
        $chargesTax = 0.0; // set charge taxable OFF by default
        // If you want charges taxable, uncomment next line:
        // $chargesTax = round($chargeTotal * ($avgTaxPercentRaw/100), 2);

        $taxAmount = round($itemsTaxTotal + $chargesTax, 2);

        // TCS
        $tcsPercent = round((float)($data['tcs_percent'] ?? 0), 2);
        $tcsAmount  = round((float)($data['tcs_amount'] ?? 0), 2);
        if($tcsPercent > 0){
            $tcsAmount = round($taxableAmount * ($tcsPercent/100), 2);
        }

        $roundOff   = round((float)($data['round_off'] ?? 0), 2);
        $lessAmount = round((float)($data['less_amount'] ?? $discountTotal), 2);

        $grandTotal = round($taxableAmount + $taxAmount + $tcsAmount + $roundOff, 2);

        // ✅ Tax invoice payment totals only
        $cash = $online = $card = $cheque = $credit = $advance = 0.0;
        $receivedTotal = 0.0;
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

        // charges json decode
        $chargesArr = [];
        if(!empty($data['charges_json'])){
            $tmp = json_decode($data['charges_json'], true);
            if(is_array($tmp)){
                foreach($tmp as $c){
                    $nm = trim((string)($c['name'] ?? ''));
                    $am = (float)($c['amount'] ?? 0);
                    if($nm !== '' && $am != 0){
                        $chargesArr[] = ['name'=>$nm, 'amount'=>round($am,2)];
                    }
                }
            }
        }

        $signaturePath = null;
        if ($r->hasFile('signature')) {
            $signaturePath = $r->file('signature')->store("invoices/{$bid}/signatures", 'public');
        }

        // ✅ KOT (multiple) parse
        $kots = [];
        if ($r->filled('kots_json')) {
            $tmp = json_decode($r->input('kots_json'), true);

            if (is_array($tmp)) {
                $kots = collect($tmp)
                    ->map(fn($v) => trim((string)$v))
                    ->filter(fn($v) => $v !== '')
                    ->unique()
                    ->values()
                    ->take(50) // safety
                    ->all();
            }
        }


        $invoice = null;

        try {
            DB::transaction(function () use (
                $r,
                $bid, $data, $invoiceDate, $prefix, $docType,
                $subtotal, $avgTaxPercent, $taxableAmount, $taxAmount,
                $discountTotal, $chargeTotal, $tcsPercent, $tcsAmount, $roundOff, $lessAmount,
                $grandTotal, $receivedTotal, $balance,
                $cash, $online, $card, $cheque, $credit, $advance,
                $pay, $cleanRows, $normCode, $chargesArr, $kots, &$invoice, $stock, $signaturePath
            ) {
                $biz    = Business::findOrFail($bid);
                $client = Client::where('business_id', $bid)->findOrFail($data['client_id']);

                $bizCode   = $normCode($biz->state_code ?? '');
                $partyCode = $normCode($client->state_code ?? '');
                $isIntra = ($bizCode !== '' && $partyCode !== '') ? ($bizCode === $partyCode) : false;

                // ✅ percent (display)
                $cgstPercent = $isIntra ? round($avgTaxPercent/2, 2) : 0;
                $sgstPercent = $isIntra ? round($avgTaxPercent/2, 2) : 0;
                $igstPercent = $isIntra ? 0 : round($avgTaxPercent, 2);

                // ✅ amount (exact) based on fixed $taxAmount
                $cgst = $isIntra ? round($taxAmount / 2, 2) : 0;
                $sgst = $isIntra ? round($taxAmount / 2, 2) : 0;
                $igst = $isIntra ? 0 : round($taxAmount, 2);

                // ✅ Invoice number allocate with docType sequence
                $alloc = \App\Services\InvoiceNumber::next((int)$bid, $invoiceDate, $prefix, 3, $docType);

                $invoice = Invoice::create([
                    'business_id'     => $bid,
                    'client_id'       => $data['client_id'],
                    'invoice_date'    => $invoiceDate,

                    'invoice_prefix'  => $prefix,
                    'invoice_number'  => $alloc['full'],

                    'invoice_type'    => $docType,   // tax / proforma

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
                    'signature_path'  => $signaturePath,
                    'created_by' => auth()->user()->id ?? null,
                    'updated_by' => auth()->user()->id ?? null,
                    'kots_json' => json_encode($kots),
                ]);

                // additional charges rows
                foreach($chargesArr as $c){
                    \App\Models\InvoiceAdditionalCharge::create([
                        'invoice_id' => $invoice->id,
                        'additional_charge_id' => null,
                        'name' => $c['name'],
                        'amount' => $c['amount'],
                    ]);
                }

                // invoice items rows
                foreach ($cleanRows as $row) {
                    $qty = (int)($row['qty'] ?? 1);

                    // ✅ base/tax/amount already computed above
                    $rate      = round((float)($row['rate'] ?? 0), 2);
                    $lineTax   = round((float)($row['tax_amount'] ?? 0), 2);
                    $lineTotal = round((float)($row['amount'] ?? ($rate + $lineTax)), 2);

                    InvoiceItem::create([
                        'invoice_id'   => $invoice->id,
                        'item_id'      => $row['item_id'],
                        'description'  => $row['description'] ?? '',
                        'sac_code'     => null,
                        'hsn_code'     => $row['hsn'] ?: null,
                        'quantity'     => $qty,

                        'gold_wt'      => (float)($row['gold_wt'] ?? 0),
                        'silver_wt'    => (float)($row['silver_wt'] ?? 0),
                        'gold_rate'    => (float)($row['gold_rate'] ?? 0),
                        'silver_rate'  => (float)($row['silver_rate'] ?? 0),

                        'gemstone_wt_ct' => (float)($row['gemstone_wt'] ?? 0),
                        'diamond_wt_ct'  => (float)($row['diamond_wt'] ?? 0),

                        'making_charge' => ($row['item_type']==='service') ? (float)($row['service_rate'] ?? $row['making_charge'] ?? 0) : null,
                        'making_rate'   => ($row['item_type']==='product') ? (float)($row['making_rate'] ?? 0) : null,

                        'discount'    => 0,
                        'tax_percent' => (float)($row['tax_percent'] ?? 0),

                        // ✅ base stored here
                        'rate'        => $rate,

                        // ✅ IMPORTANT: amount should NOT be 0
                        // store final (base + tax)
                        'amount'      => $lineTotal,
                    ]);
                }

                // ✅ Payments + Stock + Bank only for TAX invoice
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
                        'notes'   => $pay['notes'] ?? null,
                        'meta'    => null,
                        'paid_at' => $receivedTotal > 0 ? now() : null,
                    ]);

                    // ✅ stock cut only on tax invoice
                    $invoice->load(['items']);
                    $stock->recordSale($invoice);

                    // ✅ bank balance add only on tax invoice
                    $bankAccountId = $r->input('bank_account_id');
                    $mode = strtolower(trim((string)($data['payment_method'] ?? '')));
                    $bankModes = ['upi','bank','card','cheque'];

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

            $pdf = $this->simplePdfBuild($invoice);

            $dir = "invoices/{$bid}/" . now()->format('Y-m');
            $safeName = preg_replace('/[^A-Za-z0-9\-_\.]/', '-', (string)$invoice->invoice_number);
            $filename = $safeName . ".pdf";
            $path = $dir . "/" . $filename;

            Storage::disk('public')->put($path, $pdf->output());
            $invoice->update(['pdf_url' => $path]);

        } catch (\Throwable $e) {
            report($e);
            return back()->withErrors(['invoice' => 'Invoice save करते समय error आया: '.$e->getMessage()])->withInput();
        }

//        return redirect()->route('invoices.index')
//            ->with('success', ($docType === 'proforma' ? 'Proforma created successfully.' : 'Invoice created successfully.'));
        return redirect()->route('invoices.preview', $invoice->id)
            ->with('success', ($docType === 'proforma' ? 'Proforma created successfully.' : 'Invoice created successfully.'));
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


    public function update(Request $r, \App\Models\Invoice $invoice, \App\Services\StockService $stock)
    {
        $invoice = $invoice->load(['items', 'client', 'business']);

        $docType = strtolower(trim((string)($invoice->invoice_type ?? 'tax')));
        if (!in_array($docType, ['tax','proforma','quotation'], true)) $docType = 'tax';

        $bid = $r->user()->current_business_id ?? session('active_business_id');
        if (!$bid) $bid = $invoice->business_id;

        if ((int)$invoice->business_id !== (int)$bid) {
            abort(403, 'Unauthorized invoice access.');
        }

        $data = $r->validate([
            'client_id'      => ['required','exists:clients,id'],
            'invoice_date'   => ['required','date'],
            'invoice_prefix' => ['nullable','string','max:255'],

            'transport_mode' => ['nullable','string','max:255'],
            'gst_no'         => ['nullable','string','max:50'],
            'reverse_charge' => ['nullable'],

            'notes'          => ['nullable','string','max:2000'],
            'terms'          => ['nullable','string','max:2000'],

            'items_json'     => ['required','string'],

            'charges_json'   => ['nullable','string'],
            'discount_total' => ['nullable','numeric','min:0'],
            'charge_total'   => ['nullable','numeric','min:0'],
            'tcs_percent'    => ['nullable','numeric','min:0'],
            'tcs_amount'     => ['nullable','numeric','min:0'],
            'round_off'      => ['nullable','numeric'],
            'less_amount'    => ['nullable','numeric','min:0'],

            'cgst_percent'   => ['nullable','numeric','min:0'],
            'sgst_percent'   => ['nullable','numeric','min:0'],
            'igst_percent'   => ['nullable','numeric','min:0'],

            'payment_method'  => ['nullable','string','max:255'],
            'bank_account_id' => ['nullable','integer'],
            'signature' => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],
        ]);

        // ✅ payment validate only for TAX
        $pay = [];
        if ($docType === 'tax') {
            $pay = $r->validate([
                'pay_cash'            => ['nullable','numeric','min:0'],
                'pay_upi'             => ['nullable','numeric','min:0'],
                'pay_card'            => ['nullable','numeric','min:0'],
                'pay_cheque'          => ['nullable','numeric','min:0'],

                'credit_sales_excess' => ['nullable','numeric','min:0'],
                'advance_amount'      => ['nullable','numeric','min:0'],

                'online_mode'         => ['nullable','string','max:30'],
                'online_ref'          => ['nullable','string','max:100'],
                'upi_id'              => ['nullable','string','max:100'],

                'card_last4'          => ['nullable','string','max:4'],
                'card_ref'            => ['nullable','string','max:100'],

                'cheque_no'           => ['nullable','string','max:50'],
                'bank_name'           => ['nullable','string','max:100'],
                'notes'               => ['nullable','string','max:2000'],
            ]);
        }

        $invoiceDate = \Carbon\Carbon::parse($data['invoice_date'])->toDateString();
        $prefix      = trim($data['invoice_prefix'] ?? '');
        if ($prefix === '') $prefix = $invoice->invoice_prefix; // keep old if blank

        // -------- items parse --------
        $rows = json_decode($data['items_json'], true);
        if (!is_array($rows) || count($rows) < 1) {
            return back()->withErrors(['items' => 'कम से कम 1 line item जरूरी है.'])->withInput();
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

        foreach ($rows as $i => $row) {
            $itemId = $row['item_id'] ?? null;
            if (empty($itemId)) {
                return back()->withErrors(['items' => "Row ".($i+1)." में Item select नहीं है."])->withInput();
            }

            $itemType = strtolower(trim((string)($row['item_type'] ?? 'product')));
            if (!in_array($itemType, ['product','service'], true)) $itemType = 'product';

            $desc = trim($row['description'] ?? '');
            if ($desc === '') {
                return back()->withErrors(['items' => "Row ".($i+1)." description missing."])->withInput();
            }

            $hsn = trim($row['hsn'] ?? '');
            $qty = (int)($row['quantity'] ?? 1);
            $qty = $qty < 1 ? 1 : $qty;

            $taxPct = (float)($row['tax_percent'] ?? 0);
            if ($taxPct < 0 || $taxPct > 100) {
                return back()->withErrors(['items' => "Row ".($i+1)." tax % invalid."])->withInput();
            }

            if ($itemType === 'service') {
                $serviceRate = (float)($row['service_rate'] ?? 0);
                if ($serviceRate < 0) {
                    return back()->withErrors(['items' => "Row ".($i+1)." service rate invalid."])->withInput();
                }

                $lineBase = round($serviceRate * $qty, 2);
                $lineTax  = round($lineBase * ($taxPct/100), 2);

                $subtotal      += $lineBase;
                $weightedTax   += ($lineBase * $taxPct);
                $itemsTaxTotal += $lineTax;

                $cleanRows[] = [
                    'item_id'      => (int)$itemId,
                    'item_type'    => 'service',
                    'description'  => $desc,
                    'hsn'          => $hsn,
                    'qty'          => $qty,
                    'tax_percent'  => round($taxPct,2),

                    'service_rate' => round($serviceRate,2),

                    'rate'       => $lineBase,
                    'tax_amount' => $lineTax,
                    'amount'     => round($lineBase + $lineTax, 2),

                    // keep compatibility keys
                    'making_charge' => round($serviceRate,2),

                    'gold_wt'=>0,'silver_wt'=>0,'gold_rate'=>0,'silver_rate'=>0,
                    'gemstone_wt'=>0,'diamond_wt'=>0,'making_rate'=>0,
                    'stone_charges'=>0,
                ];
                continue;
            }

            // product
            $goldWt     = (float)($row['gold_wt'] ?? 0);
            $silverWt   = (float)($row['silver_wt'] ?? 0);
            $goldRate   = (float)($row['gold_rate'] ?? 0);
            $silverRate = (float)($row['silver_rate'] ?? 0);
            $makingRate = (float)($row['making_rate'] ?? 0);

            $gemCt = (float)($row['gemstone_wt'] ?? 0);
            $diaCt = (float)($row['diamond_wt'] ?? 0);

            if ($goldWt < 0 || $silverWt < 0 || $goldRate < 0 || $silverRate < 0 || $makingRate < 0) {
                return back()->withErrors(['items' => "Row ".($i+1)." invalid values."])->withInput();
            }

            $lineBase = round((($goldWt * $goldRate) + ($silverWt * $silverRate) + $makingRate) * $qty, 2);
            $lineTax  = round($lineBase * ($taxPct/100), 2);

            $subtotal      += $lineBase;
            $weightedTax   += ($lineBase * $taxPct);
            $itemsTaxTotal += $lineTax;

            $cleanRows[] = [
                'item_id'     => (int)$itemId,
                'item_type'   => 'product',
                'description' => $desc,
                'hsn'         => $hsn,
                'qty'         => $qty,
                'tax_percent' => round($taxPct,2),

                'gold_wt'     => round($goldWt,3),
                'silver_wt'   => round($silverWt,3),
                'gold_rate'   => round($goldRate,2),
                'silver_rate' => round($silverRate,2),

                'gemstone_wt' => round($gemCt,3),
                'diamond_wt'  => round($diaCt,3),

                'making_rate' => round($makingRate,2),

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

        // charges taxable OFF by default
        $chargesTax = 0.0;
        $taxAmount  = round($itemsTaxTotal + $chargesTax, 2);

        // TCS
        $tcsPercent = round((float)($data['tcs_percent'] ?? 0), 2);
        $tcsAmount  = round((float)($data['tcs_amount'] ?? 0), 2);
        if ($tcsPercent > 0) {
            $tcsAmount = round($taxableAmount * ($tcsPercent/100), 2);
        }

        $roundOff   = round((float)($data['round_off'] ?? 0), 2);
        $lessAmount = round((float)($data['less_amount'] ?? $discountTotal), 2);

        $grandTotal = round($taxableAmount + $taxAmount + $tcsAmount + $roundOff, 2);

        // payments totals (tax only)
        $cash = $online = $card = $cheque = $credit = $advance = 0.0;
        $receivedTotal = (float)($invoice->received_amount ?? 0);
        $balance       = $grandTotal;

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

        // charges json decode
        $chargesArr = [];
        if (!empty($data['charges_json'])) {
            $tmp = json_decode($data['charges_json'], true);
            if (is_array($tmp)) {
                foreach ($tmp as $c) {
                    $nm = trim((string)($c['name'] ?? ''));
                    $am = (float)($c['amount'] ?? 0);
                    if ($nm !== '' && $am != 0) $chargesArr[] = ['name'=>$nm, 'amount'=>round($am,2)];
                }
            }
        }

        // signature upload replace
        $signaturePath = $invoice->signature_path;
        if ($r->hasFile('signature')) {
            $signaturePath = $r->file('signature')->store("invoices/{$bid}/signatures", 'public');
        }

        try {
            \DB::transaction(function () use (
                $r, $bid, $invoice, $data, $invoiceDate, $prefix, $docType,
                $subtotal, $avgTaxPercent, $taxableAmount, $taxAmount,
                $discountTotal, $chargeTotal, $tcsPercent, $tcsAmount, $roundOff, $lessAmount,
                $grandTotal, $receivedTotal, $balance,
                $cash, $online, $card, $cheque, $credit, $advance,
                $pay, $cleanRows, $normCode, $chargesArr, $stock, $signaturePath
            ) {
                $biz    = \App\Models\Business::findOrFail($bid);
                $client = \App\Models\Client::where('business_id', $bid)->findOrFail($data['client_id']);

                $bizCode   = $normCode($biz->state_code ?? '');
                $partyCode = $normCode($client->state_code ?? '');
                $isIntra = ($bizCode !== '' && $partyCode !== '') ? ($bizCode === $partyCode) : false;

                $cgstPercent = $isIntra ? round($avgTaxPercent/2, 2) : 0;
                $sgstPercent = $isIntra ? round($avgTaxPercent/2, 2) : 0;
                $igstPercent = $isIntra ? 0 : round($avgTaxPercent, 2);

                $cgst = $isIntra ? round($taxAmount / 2, 2) : 0;
                $sgst = $isIntra ? round($taxAmount / 2, 2) : 0;
                $igst = $isIntra ? 0 : round($taxAmount, 2);

                // ✅ update invoice (number keep same, prefix can update)
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
                    'updated_by' => auth()->user()->id ?? null,
                ]);

                // ✅ Replace additional charges table rows (optional)
                if (class_exists(\App\Models\InvoiceAdditionalCharge::class)) {
                    \App\Models\InvoiceAdditionalCharge::where('invoice_id', $invoice->id)->delete();
                    foreach ($chargesArr as $c) {
                        \App\Models\InvoiceAdditionalCharge::create([
                            'invoice_id' => $invoice->id,
                            'additional_charge_id' => null,
                            'name' => $c['name'],
                            'amount' => $c['amount'],
                        ]);
                    }
                }

                // ✅ Replace invoice items
                \App\Models\InvoiceItem::where('invoice_id', $invoice->id)->delete();

                foreach ($cleanRows as $row) {
                    $qty = (int)($row['qty'] ?? 1);
                    $rate      = round((float)($row['rate'] ?? 0), 2);
                    $lineTax   = round((float)($row['tax_amount'] ?? 0), 2);
                    $lineTotal = round((float)($row['amount'] ?? ($rate + $lineTax)), 2);

                    \App\Models\InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'item_id'    => $row['item_id'],
                        'description'=> $row['description'] ?? '',
                        'sac_code'   => null,
                        'hsn_code'   => $row['hsn'] ?: null,
                        'quantity'   => $qty,

                        'gold_wt'    => (float)($row['gold_wt'] ?? 0),
                        'silver_wt'  => (float)($row['silver_wt'] ?? 0),
                        'gold_rate'  => (float)($row['gold_rate'] ?? 0),
                        'silver_rate'=> (float)($row['silver_rate'] ?? 0),

                        'gemstone_wt_ct' => (float)($row['gemstone_wt'] ?? 0),
                        'diamond_wt_ct'  => (float)($row['diamond_wt'] ?? 0),

                        'making_charge' => ($row['item_type']==='service') ? (float)($row['service_rate'] ?? $row['making_charge'] ?? 0) : null,
                        'making_rate'   => ($row['item_type']==='product') ? (float)($row['making_rate'] ?? 0) : null,

                        'discount'   => 0,
                        'tax_percent'=> (float)($row['tax_percent'] ?? 0),

                        'rate'       => $rate,
                        'amount'     => $lineTotal,
                    ]);
                }

                // ✅ TAX only: update InvoicePayment + bank + stock
                if ($docType === 'tax') {

                    // Update or create payment row
                    $payRow = \App\Models\InvoicePayment::where('invoice_id', $invoice->id)->latest('id')->first();
                    if (!$payRow) $payRow = new \App\Models\InvoicePayment();

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
                        'advance_amount' => $advance,

                        'received_total' => $receivedTotal,
                        'notes' => $pay['notes'] ?? null,
                        'paid_at' => $receivedTotal > 0 ? now() : null,
                    ]);
                    $payRow->save();

                    /**
                     * ✅ STOCK NOTE:
                     * Agar aap stock cut kar rahe ho on tax invoice,
                     * update pe pehle old sale rollback karna chahiye, fir new record.
                     *
                     * Implement one of these in StockService:
                     * 1) $stock->rollbackSale($invoice);
                     * 2) ya invoice->items previous snapshot ke through reverse entries
                     */
                    if (method_exists($stock, 'rollbackSale')) {
                        $stock->rollbackSale($invoice);
                    }
                    $invoice->load('items');
                    $stock->recordSale($invoice);

                    // ✅ bank balance update (simple approach)
                    // NOTE: yaha previous received total ka delta handle nahi hai.
                    // Best: bank ledger table banake entries store karo.
                    $bankAccountId = $r->input('bank_account_id');
                    $mode = strtolower(trim((string)($data['payment_method'] ?? '')));
                    $bankModes = ['upi','bank','card','cheque'];

                    if ($bankAccountId && in_array($mode, $bankModes, true) && $receivedTotal > 0) {
                        $bank = \App\Models\BankAccount::where('business_id', $bid)->where('id', $bankAccountId)->first();
                        if ($bank) {
                            // ⚠️ This adds full received again (delta not handled)
                            // Better: delta = newReceived - oldReceived
                            $bank->balance = round(((float)$bank->balance) + $receivedTotal, 2);
                            $bank->save();
                        }
                    }
                }
            });

            // regenerate PDF (optional)
            $pdf = $this->simplePdfBuild($invoice->fresh(['client','items','business']));

            $dir = "invoices/{$bid}/" . now()->format('Y-m');
            $safeName = preg_replace('/[^A-Za-z0-9\-_\.]/', '-', (string)$invoice->invoice_number);
            $filename = $safeName . ".pdf";
            $path = $dir . "/" . $filename;

            Storage::disk('public')->put($path, $pdf->output());
            $invoice->update(['pdf_url' => $path]);

        } catch (\Throwable $e) {
            report($e);
            return back()->withErrors(['invoice' => 'Invoice update करते समय error आया: '.$e->getMessage()])->withInput();
        }

//        return redirect()->route('invoices.index')
//            ->with('success', ucfirst($docType).' updated successfully.');
        return redirect()->route('invoices.preview', $invoice->id)
            ->with('success', ucfirst($docType).' updated successfully.');
    }





    public function export(Request $r)
    {
        return Excel::download(new InvoicesExport($r->all()), 'invoices-report.xlsx');
    }

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


    protected function simplePdfBuild(Invoice $invoice): \Barryvdh\DomPDF\PDF
    {
        $invoice->load(['client','items','business']);

        $inv    = $invoice;
        $biz    = $invoice->business;
        $client = $invoice->client;
        $items  = $invoice->items ?? collect();

        // ✅ payment row (same logic)
        $payRow = InvoicePayment::where('invoice_id', $inv->id)
            ->latest('id')
            ->first();

        // ✅ charges (same logic)
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
                            'amount' => (float)($c['amount'] ?? 0),
                        ];
                    }
                }
            }
            $charges = collect($arr);
        }

        // ✅ totals (same as buildInvoicePdf)
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

        $taxAmount = (float)($inv->igst_amount + $inv->igst_amount + $inv->igst_amount);


        // ✅ data URIs (simple pdf me bhi logo/sign aa sakta hai)
        $logoDataUri = $this->imageDataUri($biz?->logo);
        $signDataUri = $this->imageDataUri($biz?->signature);
        $type = $invoice->invoice_type;



        $vm = compact(
            'inv','invoice','biz','client','items','charges','type', 'taxAmount',
            'logoDataUri','signDataUri',
            'subtotal','tax_total','discount_total','charges_total',
            'tcs_percent','tcs_amount','round_off','less_amount',
            'grand_total','received','balance',
            'cgst_amount','sgst_amount','igst_amount',
            'payRow'
        );

        // aliases
        $vm['logo'] = $logoDataUri;
        $vm['sign'] = $signDataUri;

        // ❌ letter_head deliberately NOT passed
        // $vm['letter_head'] = null;

//        return Pdf::loadView('invoices.pdf_simple', $vm)->setPaper('a4');
        // $view = 'invoices.' . ($biz->pdf_template_id ?? 'pdf_simple');
        $view = 'invoices.' . ('pdf_krinoscco');

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



}
