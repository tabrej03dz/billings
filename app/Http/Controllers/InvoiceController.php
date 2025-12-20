<?php

namespace App\Http\Controllers;

use App\Exports\InvoicesExport;
use App\Models\AdditionalCharge;
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

    public function index(Request $r)
    {
        $query = Invoice::with('client')->latest();

        // Search by invoice number / client name / client email
        if ($r->filled('search')) {
            $search = $r->search;

            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('client', function ($sub) use ($search) {
                        $sub->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        // Date range filter
        if ($r->filled('from_date')) {
            $query->whereDate('invoice_date', '>=', $r->from_date);
        }

        if ($r->filled('to_date')) {
            $query->whereDate('invoice_date', '<=', $r->to_date);
        }

        // Status filter (Paid / Partial / Unpaid)
        if ($r->filled('status') && in_array($r->status, ['paid', 'partial', 'unpaid'])) {
            $status = $r->status;

            $query->where(function ($q) use ($status) {
                if ($status === 'paid') {
                    // fully paid: balance <= 0
                    $q->where('balance', '<=', 0);
                } elseif ($status === 'partial') {
                    // kuch amount mila hai, lekin balance bhi bacha hai
                    $q->where('received_amount', '>', 0)
                        ->where('balance', '>', 0);
                } elseif ($status === 'unpaid') {
                    // abhi tak kuch receive nahi hua
                    $q->where('received_amount', '<=', 0)
                        ->where('total', '>', 0);
                }
            });
        }

        $invoices = $query->paginate(15)->appends($r->query());

        return view('invoices.index', compact('invoices'));
    }

    public function create(Request $request)
    {
        $today = now()->toDateString();

        // Active business resolve
        $bid = $request->user()->current_business_id ?? session('active_business_id');
        if (!$bid) {
            $bid = $request->user()->businesses()->pluck('businesses.id')->first();
        }

        $business = Business::findOrFail($bid);

        // base prefix
        $base = optional(
                $request->user()->businesses()->where('businesses.id', $bid)->first()
            )->invoice_base_prefix ?? 'RV/SL';

        $suggestedPrefix = \App\Services\InvoiceNumber::previewPrefix($today, $base);

        // ✅ Clients (IMPORTANT fields)
        $clients = Client::where('business_id', $bid)
            ->orderBy('name')
            ->get(['id','name','mobile','address','state','state_code','gstin']);

        // Items
        $items = Item::where('business_id', $bid)
            ->where('is_active', true)
            ->orderBy('name')
            ->get([
                'id','name', 'type', 'sku','description','tax_rate','making_charge','sac',
                'gold_weight','gold_purity',
                'silver_weight','silver_purity',
                'stone_weight','stone_charges',
                'diamond_weight','diamond_charges',
                'price'
            ]);

        // Metal rates
        $metalRates = MetalRate::where('business_id', $bid)
            ->whereDate('rate_date', $today)
            ->where('is_active', true)
            ->get(['metal_type','purity','rate_per_gram']);

        $preview = \App\Services\InvoiceNumber::peek($bid, $today, $suggestedPrefix, 3);

        return view('invoices.create_kapoor_style', [
            'today'              => $today,
            'clientsJson'        => $clients->values()->toJson(),
            'itemsJson'          => $items->values()->toJson(),
            'metalRatesJson'     => $metalRates->values()->toJson(),
            'suggestedPrefix'    => $suggestedPrefix,
            'basePrefix'         => $base,
            'initialInvoiceNo'   => $preview['full'] ?? 'Auto',
            'defaultTerms'       => $business->terms,

            // ✅ REQUIRED FOR GST LOGIC (STATE CODE ONLY)
            'businessState'      => $business->state,
            'businessStateCode'  => $business->state_code,
            'businessGstin'      => $business->gstin, // (UI field me show ke liye ok, GST logic me use nahi hoga)
        ]);
    }

    public function edit(Request $request, \App\Models\Invoice $invoice)
    {
        $today = now()->toDateString();

        // Active business resolve
        $bid = $request->user()->current_business_id ?? session('active_business_id');
        if (!$bid) {
            $bid = $request->user()->businesses()->pluck('businesses.id')->first();
        }

        abort_unless((int)$invoice->business_id === (int)$bid, 403);

        $business = \App\Models\Business::find($bid);

        // ✅ business state code (IMPORTANT)
        $businessStateCode = (string)($business?->state_code ?? '');

        // Prefix preview
        $base = optional(
                $request->user()->businesses()->where('businesses.id', $bid)->first()
            )->invoice_base_prefix ?? 'RV/SL';

        $suggestedPrefix = \App\Services\InvoiceNumber::previewPrefix($invoice->invoice_date, $base);

        // Clients
        $clients = \App\Models\Client::where('business_id', $bid)
            ->orderBy('name')
            ->get(['id','name','mobile','address','state','state_code','gstin']);

        // Items master
        $items = \App\Models\Item::where('business_id', $bid)
            ->where('is_active', true)
            ->orderBy('name')
            ->get([
                'id','name','sku','description','tax_rate','making_charge','sac',
                'gold_weight','gold_purity',
                'silver_weight','silver_purity',
                'stone_weight','stone_charges',
                'diamond_weight','diamond_charges',
            ]);

        // Metal rates
        $metalRates = \App\Models\MetalRate::where('business_id', $bid)
            ->whereDate('rate_date', $today)
            ->where('is_active', true)
            ->get(['metal_type','purity','rate_per_gram']);

        // Prefill items
        $prefillItems = [];
        if (!empty($invoice->items_json)) {
            $prefillItems = json_decode($invoice->items_json, true) ?: [];
        }

        // Prefill payment (latest)
        $payment = \App\Models\InvoicePayment::where('business_id', $bid)
            ->where('invoice_id', $invoice->id)
            ->latest('id')
            ->first();

        $paymentPrefill = [
            'cash'          => (float)($payment->cash_amount ?? 0),
            'upi'           => (float)($payment->online_amount ?? 0),
            'card'          => (float)($payment->card_amount ?? 0),
            'cheque'        => (float)($payment->cheque_amount ?? 0),
            'credit_excess' => (float)($payment->credit_sales_excess_amount ?? 0),
            'advance'       => (float)($payment->advance_amount ?? 0),

            'online_mode'   => (string)($payment->online_mode ?? ''),
            'online_ref'    => (string)($payment->online_ref ?? ''),
            'upi_id'        => (string)($payment->upi_id ?? ''),

            'card_last4'    => (string)($payment->card_last4 ?? ''),
            'card_ref'      => (string)($payment->card_ref ?? ''),

            'cheque_no'     => (string)($payment->cheque_no ?? ''),
            'bank_name'     => (string)($payment->bank_name ?? ''),

            'notes'         => (string)($payment->notes ?? ''),
        ];

        return view('invoices.edit_kapoor_style', [
            'today'              => $today,
            'invoice'            => $invoice,

            'clientsJson'        => $clients->values()->toJson(),
            'itemsJson'          => $items->values()->toJson(),
            'metalRatesJson'     => $metalRates->values()->toJson(),

            'prefillItemsJson'   => collect($prefillItems)->values()->toJson(),
            'prefillPaymentJson' => collect($paymentPrefill)->toJson(),

            'suggestedPrefix'    => $suggestedPrefix,
            'basePrefix'         => $base,

            'initialInvoiceNo'   => $invoice->invoice_number,
            'defaultTerms'       => $business?->terms,

            // ✅ IMPORTANT for state_code logic
            'businessStateCode'  => $businessStateCode,
        ]);
    }


    public function destroy(Invoice $invoice)
    {
        $invoice->delete();
        return back()->with('success','Deleted.');
    }




    public function download(Invoice $invoice)
    {
        // 1) Check if invoice already has pdf_url saved
        //        if (!empty($invoice->pdf_url)) {
        //
        //            // normalize path (handles full URLs, storage/... etc.)
        //            $path = $this->normalizePdfPath($invoice->pdf_url);
        //
        //            // agar file storage/public me exist karta hai -> directly download
        //            if ($path && Storage::disk('public')->exists($path)) {
        //                $safeNumber = str_replace(['/', '\\'], '-', (string)($invoice->invoice_number ?? 'INV'));
        //
        //                return Storage::disk('public')->download($path, 'Invoice-' . $safeNumber . '.pdf');
        //            }
        //        }

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
        // 1) Agar invoice ke paas pdf_url hai, to pehle wahi try karte hain
        //        if (!empty($invoice->pdf_url)) {
        //            $path = $this->normalizePdfPath($invoice->pdf_url);
        //
        //            if ($path && Storage::disk('public')->exists($path)) {
        //                $safeNumber = str_replace(['/', '\\'], '-', (string)($invoice->invoice_number ?? 'INV'));
        //
        //                return response()->file(
        //                    Storage::disk('public')->path($path),
        //                    [
        //                        'Content-Type'        => 'application/pdf',
        //                        'Content-Disposition' => 'inline; filename="Invoice-'.$safeNumber.'.pdf"',
        //                    ]
        //                );
        //            }
        //        }

        // 2) Yaha tak aa gaye matlab:
        //    - ya to pdf_url empty hai
        //    - ya file missing hai
        //    => ab naya PDF generate karke save karein, aur phir dikhayein

        // fresh relations ke liye (optional but safe)
        $invoice = $invoice->fresh(['client','items','business']);

        $pdf = $this->simplePdfBuild($invoice);
        //        $pdf = $this->buildInvoicePdf($invoice);

        $safeNumber = str_replace(['/', '\\'], '-', (string)($invoice->invoice_number ?? 'INV'));
        $fileName   = 'invoices/Invoice-'.$safeNumber.'.pdf';

        // storage/app/public/invoices/Invoice-XXX.pdf
        Storage::disk('public')->put($fileName, $pdf->output());

        // pdf_url me relative path save kar rahe hain
        $invoice->update([
            'pdf_url' => $fileName,
        ]);

        return response()->file(
            Storage::disk('public')->path($fileName),
            [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'inline; filename="Invoice-'.$safeNumber.'.pdf"',
            ]
        );
    }

//    public function store(Request $r, StockService $stock)
//    {
//        $bid = $r->user()->current_business_id ?? session('active_business_id');
//        if (!$bid) {
//            return back()->withErrors(['business' => 'Active business select/attach नहीं है.'])->withInput();
//        }
//
//        // ----------------- VALIDATION -----------------
//        $data = $r->validate([
//            'client_id'      => ['required','exists:clients,id'],
//            'invoice_date'   => ['required','date'],
//            'invoice_prefix' => ['nullable','string','max:100'],
//
//            'transport_mode' => ['nullable','string','max:255'],
//            'gst_no'         => ['nullable','string','max:50'],
//            'reverse_charge' => ['nullable'],
//
//            'notes'          => ['nullable','string','max:2000'],
//            'terms'          => ['nullable','string','max:2000'],
//
//            'items_json'     => ['required','string'],
//        ]);
//
//        // Payment fields
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
//        $invoiceDate = Carbon::parse($data['invoice_date'])->toDateString();
//        $prefix      = trim($data['invoice_prefix'] ?? '');
//        if ($prefix === '') $prefix = $computePrefix($invoiceDate, 'INV');
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
//            $qty = (int)($row['quantity'] ?? 1);
//            $qty = $qty < 1 ? 1 : $qty;
//
//            $taxPct = (float)($row['tax_percent'] ?? 0);
//
//            $goldWt     = (float)($row['gold_wt'] ?? 0);
//            $silverWt   = (float)($row['silver_wt'] ?? 0);
//            $goldRate   = (float)($row['gold_rate'] ?? 0);
//            $silverRate = (float)($row['silver_rate'] ?? 0);
//            $making     = (float)($row['making_rate'] ?? 0);
//
//            $gemCt = (float)($row['gemstone_wt'] ?? 0);
//            $diaCt = (float)($row['diamond_wt'] ?? 0);
//
//            if ($desc === '' || $taxPct < 0 || $taxPct > 100 || $goldWt < 0 || $silverWt < 0 || $goldRate < 0 || $silverRate < 0 || $making < 0) {
//                return back()->withErrors(['items' => "Row ".($i+1)." invalid है."])->withInput();
//            }
//
//            // ✅ IMPORTANT: item_id must be present for stock
//            $itemId = $row['item_id'] ?? null;
//            if (empty($itemId)) {
//                return back()->withErrors(['items' => "Row ".($i+1)." में Item select नहीं है, इसलिए stock कट नहीं हो सकता."])->withInput();
//            }
//
//            // ✅ base = (gold+silver+making) * qty
//            $lineBase = round((($goldWt * $goldRate) + ($silverWt * $silverRate) + $making) * $qty, 2);
//
//            $discount = (float)($row['discount'] ?? 0);
//            if ($discount < 0) $discount = 0;
//
//            $taxable  = max(0, round($lineBase - $discount, 2));
//            $lineTax  = round($taxable * ($taxPct / 100), 2);
//            $lineAmt  = round($taxable + $lineTax, 2);
//
//            $subtotal += $lineBase;
//            $taxTotal += $lineTax;
//
//            $cleanRows[] = [
//                'item_id'          => $itemId,
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
//                'tax_amount'       => $lineTax,
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
//        // ✅ helper: normalize state_code (09 vs 9)
//        $normCode = function ($v) {
//            $s = trim((string)$v);
//            $s = preg_replace('/\D+/', '', $s);
//            $s = ltrim($s, '0');
//            return $s;
//        };
//
//        $invoice = null;
//
//        try {
//            DB::transaction(function () use (
//                $bid, $data, $invoiceDate, $prefix,
//                $subtotal, $taxTotal, $grandTotal, $receivedTotal, $balance,
//                $cash, $online, $card, $cheque, $credit, $advance,
//                $pay, $cleanRows, $normCode, &$invoice,
//                $stock
//            ) {
//                // ✅ Load business & client to decide GST (STATE CODE ONLY)
//                $biz    = Business::findOrFail($bid);
//                $client = Client::where('business_id', $bid)->findOrFail($data['client_id']);
//
//                $bizCode   = $normCode($biz->state_code ?? '');
//                $partyCode = $normCode($client->state_code ?? '');
//
//                $isIntra = false;
//                if ($bizCode !== '' && $partyCode !== '') {
//                    $isIntra = ($bizCode === $partyCode);
//                } else {
//                    $isIntra = false; // missing => IGST
//                }
//
//                $cgst = $isIntra ? round($taxTotal / 2, 2) : 0;
//                $sgst = $isIntra ? round($taxTotal / 2, 2) : 0;
//                $igst = $isIntra ? 0 : round($taxTotal, 2);
//
//                // allocate invoice number
//                $alloc = \App\Services\InvoiceNumber::next((int)$bid, $invoiceDate, $prefix, 3);
//
//                $invoice = Invoice::create([
//                    'business_id'     => $bid,
//                    'client_id'       => $data['client_id'],
//                    'invoice_date'    => $invoiceDate,
//
//                    'invoice_prefix'  => $prefix,
//                    'invoice_number'  => $alloc['full'],
//
//                    'subtotal'        => $subtotal,
//                    'tax_amount'      => $taxTotal,
//
//                    'cgst_amount'     => $cgst,
//                    'sgst_amount'     => $sgst,
//                    'igst_amount'     => $igst,
//
//                    'total'           => $grandTotal,
//                    'received_amount' => $receivedTotal,
//                    'balance'         => $balance,
//
//                    'gst_no'          => $data['gst_no'] ?? null,
//                    'transport_mode'  => $data['transport_mode'] ?? null,
//                    'reverse_charge'  => !empty($data['reverse_charge']) ? 1 : 0,
//
//                    'place_of_supply_state' => $client->state ?? null,
//                    'place_of_supply_code'  => $client->state_code ?? null,
//
//                    'notes'           => $data['notes'] ?? null,
//                    'terms'           => $data['terms'] ?? null,
//
//                    'items_json'      => json_encode($cleanRows),
//                    'amount_in_words' => '',
//                ]);
//
//                foreach ($cleanRows as $row) {
//                    InvoiceItem::create([
//                        'invoice_id'       => $invoice->id,
//                        'item_id'          => $row['item_id'],
//
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
//                InvoicePayment::create([
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
//                ]);
//
//                // ✅ STOCK CUT (sale movements)
//                $invoice->load(['items']); // items relation must exist
//                $stock->recordSale($invoice);
//            });
//
//            // ✅ PDF generate + save + update pdf_url (after transaction)
//            $pdf = $this->buildInvoicePdf($invoice);
//
//            $dir = "invoices/{$bid}/" . now()->format('Y-m');
//            $safeName = preg_replace('/[^A-Za-z0-9\-_\.]/', '-', (string)$invoice->invoice_number);
//            $filename = $safeName . ".pdf";
//            $path = $dir . "/" . $filename;
//
//            Storage::disk('public')->put($path, $pdf->output());
//            $invoice->update(['pdf_url' => $path]);
//
//        } catch (\Throwable $e) {
//            report($e);
//            return back()->withErrors(['invoice' => 'Invoice save करते समय error आया: '.$e->getMessage()])->withInput();
//        }
//
//        return redirect()->route('invoices.index')->with('success', 'Invoice created successfully (stock updated).');
//    }


//    public function store(Request $r, StockService $stock)
//    {
//        $bid = $r->user()->current_business_id ?? session('active_business_id');
//        if (!$bid) {
//            return back()->withErrors(['business' => 'Active business select/attach नहीं है.'])->withInput();
//        }
//
//        // ----------------- VALIDATION -----------------
//        $data = $r->validate([
//            'client_id'      => ['required','exists:clients,id'],
//            'invoice_date'   => ['required','date'],
//            'invoice_prefix' => ['nullable','string','max:100'],
//
//            'transport_mode' => ['nullable','string','max:255'],
//            'gst_no'         => ['nullable','string','max:50'],
//            'reverse_charge' => ['nullable'],
//
//            'notes'          => ['nullable','string','max:2000'],
//            'terms'          => ['nullable','string','max:2000'],
//
//            'items_json'     => ['required','string'],
//        ]);
//
//        // Payment fields
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
//        $prefix      = trim($data['invoice_prefix'] ?? '');
//        if ($prefix === '') $prefix = $computePrefix($invoiceDate, 'INV');
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
//            $qty = (int)($row['quantity'] ?? 1);
//            $qty = $qty < 1 ? 1 : $qty;
//
//            $taxPct = (float)($row['tax_percent'] ?? 0);
//
//            // ✅ item type (product/service)
//            $itemType = strtolower(trim((string)($row['item_type'] ?? 'product')));
//            if (!in_array($itemType, ['product','service'], true)) {
//                $itemType = 'product';
//            }
//
//            // ✅ item_id required (both types) because invoice item relation needs it
//            $itemId = $row['item_id'] ?? null;
//            if (empty($itemId)) {
//                return back()->withErrors(['items' => "Row ".($i+1)." में Item select नहीं है."])->withInput();
//            }
//
//            if ($desc === '' || $taxPct < 0 || $taxPct > 100) {
//                return back()->withErrors(['items' => "Row ".($i+1)." invalid है."])->withInput();
//            }
//
//            $discount = (float)($row['discount'] ?? 0);
//            if ($discount < 0) $discount = 0;
//
//            // ✅ SERVICE base = service_rate * qty  (NO metal rates)
//            if ($itemType === 'service') {
//
//                $serviceRate = (float)($row['service_rate'] ?? 0);
//                if ($serviceRate < 0) {
//                    return back()->withErrors(['items' => "Row ".($i+1)." service rate invalid है."])->withInput();
//                }
//
//                $lineBase = round(($serviceRate * $qty), 2);
//                $taxable  = max(0, round($lineBase - $discount, 2));
//                $lineTax  = round($taxable * ($taxPct / 100), 2);
//                $lineAmt  = round($taxable + $lineTax, 2);
//
//                $subtotal += $lineBase;
//                $taxTotal += $lineTax;
//
//                $cleanRows[] = [
//                    'item_id'          => $itemId,
//                    'item_type'        => 'service',
//
//                    'description'      => $desc,
//                    'hsn_code'         => null,
//                    'sac_code'         => $hsn ?: ($row['sac'] ?? null), // aapke UI me HSN/SAC same field hai
//
//                    'quantity'         => $qty,
//
//                    // product fields zero
//                    'gold_wt'          => 0,
//                    'silver_wt'        => 0,
//                    'gold_rate'        => 0,
//                    'silver_rate'      => 0,
//                    'gemstone_wt_ct'   => 0,
//                    'diamond_wt_ct'    => 0,
//                    'making_rate'      => 0,
//
//                    // ✅ service
//                    'service_rate'     => round($serviceRate, 2),
//
//                    'discount'         => round($discount, 2),
//                    'tax_percent'      => round($taxPct, 2),
//
//                    // store computed
//                    'rate'             => $lineBase,     // base
//                    'tax_amount'       => $lineTax,
//                    'amount'           => $lineAmt,
//                ];
//
//                continue;
//            }
//
//            // ✅ PRODUCT base = (gold+silver+making) * qty
//            $goldWt     = (float)($row['gold_wt'] ?? 0);
//            $silverWt   = (float)($row['silver_wt'] ?? 0);
//            $goldRate   = (float)($row['gold_rate'] ?? 0);
//            $silverRate = (float)($row['silver_rate'] ?? 0);
//            $making     = (float)($row['making_rate'] ?? 0);
//
//            $gemCt = (float)($row['gemstone_wt'] ?? 0);
//            $diaCt = (float)($row['diamond_wt'] ?? 0);
//
//            if ($goldWt < 0 || $silverWt < 0 || $goldRate < 0 || $silverRate < 0 || $making < 0) {
//                return back()->withErrors(['items' => "Row ".($i+1)." invalid है."])->withInput();
//            }
//
//            $lineBase = round((($goldWt * $goldRate) + ($silverWt * $silverRate) + $making) * $qty, 2);
//            $taxable  = max(0, round($lineBase - $discount, 2));
//            $lineTax  = round($taxable * ($taxPct / 100), 2);
//            $lineAmt  = round($taxable + $lineTax, 2);
//
//            $subtotal += $lineBase;
//            $taxTotal += $lineTax;
//
//            $cleanRows[] = [
//                'item_id'          => $itemId,
//                'item_type'        => 'product',
//
//                'description'      => $desc,
//
//                'hsn_code'         => $hsn ?: null,
//                'sac_code'         => $row['sac'] ?? null,
//
//                'quantity'         => $qty,
//
//                'gold_wt'          => $goldWt,
//                'silver_wt'        => $silverWt,
//                'gold_rate'        => $goldRate,
//                'silver_rate'      => $silverRate,
//
//                'gemstone_wt_ct'   => $gemCt,
//                'diamond_wt_ct'    => $diaCt,
//
//                'making_rate'      => $making,
//                'service_rate'     => 0,
//
//                'discount'         => round($discount, 2),
//                'tax_percent'      => round($taxPct, 2),
//
//                'rate'             => $lineBase,
//                'tax_amount'       => $lineTax,
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
//        // ✅ helper: normalize state_code (09 vs 9)
//        $normCode = function ($v) {
//            $s = trim((string)$v);
//            $s = preg_replace('/\D+/', '', $s);
//            $s = ltrim($s, '0');
//            return $s;
//        };
//
//        $invoice = null;
//
//        try {
//            DB::transaction(function () use (
//                $bid, $data, $invoiceDate, $prefix,
//                $subtotal, $taxTotal, $grandTotal, $receivedTotal, $balance,
//                $cash, $online, $card, $cheque, $credit, $advance,
//                $pay, $cleanRows, $normCode, &$invoice,
//                $stock
//            ) {
//                $biz    = Business::findOrFail($bid);
//                $client = Client::where('business_id', $bid)->findOrFail($data['client_id']);
//
//                $bizCode   = $normCode($biz->state_code ?? '');
//                $partyCode = $normCode($client->state_code ?? '');
//
//                $isIntra = ($bizCode !== '' && $partyCode !== '') ? ($bizCode === $partyCode) : false;
//
//                $cgst = $isIntra ? round($taxTotal / 2, 2) : 0;
//                $sgst = $isIntra ? round($taxTotal / 2, 2) : 0;
//                $igst = $isIntra ? 0 : round($taxTotal, 2);
//
//                $alloc = \App\Services\InvoiceNumber::next((int)$bid, $invoiceDate, $prefix, 3);
//
//                $invoice = Invoice::create([
//                    'business_id'     => $bid,
//                    'client_id'       => $data['client_id'],
//                    'invoice_date'    => $invoiceDate,
//
//                    'invoice_prefix'  => $prefix,
//                    'invoice_number'  => $alloc['full'],
//
//                    'subtotal'        => $subtotal,
//                    'tax_amount'      => $taxTotal,
//
//                    'cgst_amount'     => $cgst,
//                    'sgst_amount'     => $sgst,
//                    'igst_amount'     => $igst,
//
//                    'total'           => $grandTotal,
//                    'received_amount' => $receivedTotal,
//                    'balance'         => $balance,
//
//                    'gst_no'          => $data['gst_no'] ?? null,
//                    'transport_mode'  => $data['transport_mode'] ?? null,
//                    'reverse_charge'  => !empty($data['reverse_charge']) ? 1 : 0,
//
//                    'place_of_supply_state' => $client->state ?? null,
//                    'place_of_supply_code'  => $client->state_code ?? null,
//
//                    'notes'           => $data['notes'] ?? null,
//                    'terms'           => $data['terms'] ?? null,
//
//                    'items_json'      => json_encode($cleanRows),
//                    'amount_in_words' => '',
//                ]);
//
//                foreach ($cleanRows as $row) {
//                    InvoiceItem::create([
//                        'invoice_id'       => $invoice->id,
//                        'item_id'          => $row['item_id'],
//                        // ✅ store item_type (if column exists)
//                        'item_type'        => $row['item_type'] ?? null,
//
//                        'description'      => $row['description'] ?? '',
//
//                        'sac_code'         => $row['sac_code'] ?? null,
//                        'hsn_code'         => $row['hsn_code'] ?? null,
//
//                        'quantity'         => (int)($row['quantity'] ?? 1),
//
//                        'gold_wt'          => (float)($row['gold_wt'] ?? 0),
//                        'silver_wt'        => (float)($row['silver_wt'] ?? 0),
//                        'gold_rate'        => (float)($row['gold_rate'] ?? 0),
//                        'silver_rate'      => (float)($row['silver_rate'] ?? 0),
//
//                        'gemstone_wt_ct'   => (float)($row['gemstone_wt_ct'] ?? 0),
//                        'diamond_wt_ct'    => (float)($row['diamond_wt_ct'] ?? 0),
//
//                        'making_rate'      => (float)($row['making_rate'] ?? 0),
//
//                        // ✅ service
//                        'service_rate'     => (float)($row['service_rate'] ?? 0),
//
//                        'discount'         => (float)($row['discount'] ?? 0),
//                        'tax_percent'      => (float)($row['tax_percent'] ?? 0),
//
//                        'rate'             => (float)($row['rate'] ?? 0),
//                        'amount'           => (float)($row['amount'] ?? 0),
//                        'tax_amount'       => (float)($row['tax_amount'] ?? 0),
//                    ]);
//                }
//
//                InvoicePayment::create([
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
//                ]);
//
//                // ✅ STOCK CUT: ONLY PRODUCT rows
//                $invoice->load(['items']); // relation
//
//                // ✅ अगर आपका StockService invoice->items से ही कट करता है,
//                // तो invoice_items table में item_type जरूर होना चाहिए या clean check लगाएं
//                // BEST: recordSale के अंदर service ignore करना
//                $stock->recordSale($invoice);
//            });
//
//            // ✅ PDF generate + save + update pdf_url
//            $pdf = $this->buildInvoicePdf($invoice);
//
//            $dir = "invoices/{$bid}/" . now()->format('Y-m');
//            $safeName = preg_replace('/[^A-Za-z0-9\-_\.]/', '-', (string)$invoice->invoice_number);
//            $filename = $safeName . ".pdf";
//            $path = $dir . "/" . $filename;
//
//            Storage::disk('public')->put($path, $pdf->output());
//            $invoice->update(['pdf_url' => $path]);
//
//        } catch (\Throwable $e) {
//            report($e);
//            return back()->withErrors(['invoice' => 'Invoice save करते समय error आया: '.$e->getMessage()])->withInput();
//        }
//
//        return redirect()->route('invoices.index')->with('success', 'Invoice created successfully (stock updated).');
//    }


    public function store(Request $r, StockService $stock)
    {
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

            // NEW fields (white screenshot)
            'charges_json'   => ['nullable','string'],
            'discount_total' => ['nullable','numeric','min:0'],
            'charge_total'   => ['nullable','numeric','min:0'],
            'tcs_percent'    => ['nullable','numeric','min:0'],
            'tcs_amount'     => ['nullable','numeric','min:0'],
            'round_off'      => ['nullable','numeric'], // can be +/-
            'less_amount'    => ['nullable','numeric','min:0'],

            'cgst_percent'   => ['nullable','numeric','min:0'],
            'sgst_percent'   => ['nullable','numeric','min:0'],
            'igst_percent'   => ['nullable','numeric','min:0'],

            'payment_method' => ['nullable','string','max:255'],
        ]);

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

        // -------- prefix --------
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
        if ($prefix === '') $prefix = $computePrefix($invoiceDate, 'INV');

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

        // -------- compute subtotal (base sum) + weighted tax % --------
        $subtotal = 0.0;
        $weightedTax = 0.0;

        $cleanRows = [];

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

            // base
            if ($itemType === 'service') {
                $serviceRate = (float)($row['service_rate'] ?? 0);
                if ($serviceRate < 0) {
                    return back()->withErrors(['items' => "Row ".($i+1)." service rate invalid."])->withInput();
                }

                $lineBase = round($serviceRate * $qty, 2);

                $subtotal += $lineBase;
                $weightedTax += ($lineBase * $taxPct);

                $cleanRows[] = [
                    'item_id'     => (int)$itemId,
                    'item_type'   => 'service',
                    'description' => $desc,
                    'hsn'         => $hsn,
                    'qty'         => $qty,
                    'tax_percent' => round($taxPct,2),

                    // store service rate in making_charge (because column exists)
                    'making_charge' => round($serviceRate,2),

                    // keep product fields 0
                    'gold_wt'   => 0,
                    'silver_wt' => 0,
                    'gold_rate' => 0,
                    'silver_rate'=>0,
                    'gemstone_wt'=>0,
                    'diamond_wt'=>0,
                    'making_rate'=>0,
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

            $subtotal += $lineBase;
            $weightedTax += ($lineBase * $taxPct);

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
            ];
        }

        $subtotal = round($subtotal, 2);
        $avgTaxPercent = ($subtotal > 0) ? round($weightedTax / $subtotal, 2) : 0;

        // invoice-level adjustments
        $discountTotal = round((float)($data['discount_total'] ?? 0), 2);
        $chargeTotal   = round((float)($data['charge_total'] ?? 0), 2);

        $taxableAmount = round(max(0, $subtotal - $discountTotal + $chargeTotal), 2);
        $taxAmount     = round($taxableAmount * ($avgTaxPercent/100), 2);

        $tcsPercent = round((float)($data['tcs_percent'] ?? 0), 2);
        $tcsAmount  = round((float)($data['tcs_amount'] ?? 0), 2);
        // safer: recompute tcs from taxable if percent given
        if($tcsPercent > 0){
            $tcsAmount = round($taxableAmount * ($tcsPercent/100), 2);
        }

        $roundOff   = round((float)($data['round_off'] ?? 0), 2);
        $lessAmount = round((float)($data['less_amount'] ?? $discountTotal), 2);

        $grandTotal = round($taxableAmount + $taxAmount + $tcsAmount + $roundOff, 2);

        // payment totals
        $cash    = (float)($pay['pay_cash'] ?? 0);
        $online  = (float)($pay['pay_upi'] ?? 0);
        $card    = (float)($pay['pay_card'] ?? 0);
        $cheque  = (float)($pay['pay_cheque'] ?? 0);
        $credit  = (float)($pay['credit_sales_excess'] ?? 0);
        $advance = (float)($pay['advance_amount'] ?? 0);

        $receivedTotal = round($cash + $online + $card + $cheque, 2);
        $balance = round(max(0, $grandTotal - $receivedTotal - $advance - $credit), 2);

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

        $invoice = null;

        try {
            DB::transaction(function () use (
                $bid, $data, $invoiceDate, $prefix,
                $subtotal, $avgTaxPercent, $taxableAmount, $taxAmount,
                $discountTotal, $chargeTotal, $tcsPercent, $tcsAmount, $roundOff, $lessAmount,
                $grandTotal, $receivedTotal, $balance,
                $cash, $online, $card, $cheque, $credit, $advance,
                $pay, $cleanRows, $normCode, $chargesArr, &$invoice, $stock
            ) {
                $biz    = Business::findOrFail($bid);
                $client = Client::where('business_id', $bid)->findOrFail($data['client_id']);

                $bizCode   = $normCode($biz->state_code ?? '');
                $partyCode = $normCode($client->state_code ?? '');
                $isIntra = ($bizCode !== '' && $partyCode !== '') ? ($bizCode === $partyCode) : false;

                $cgstPercent = $isIntra ? round($avgTaxPercent/2, 2) : 0;
                $sgstPercent = $isIntra ? round($avgTaxPercent/2, 2) : 0;
                $igstPercent = $isIntra ? 0 : round($avgTaxPercent, 2);

                $cgst = $isIntra ? round($taxAmount / 2, 2) : 0;
                $sgst = $isIntra ? round($taxAmount / 2, 2) : 0;
                $igst = $isIntra ? 0 : round($taxAmount, 2);

                $alloc = \App\Services\InvoiceNumber::next((int)$bid, $invoiceDate, $prefix, 3);

                $invoice = Invoice::create([
                    'business_id'     => $bid,
                    'client_id'       => $data['client_id'],
                    'invoice_date'    => $invoiceDate,

                    'invoice_prefix'  => $prefix,
                    'invoice_number'  => $alloc['full'],

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
                ]);

                // Save invoice_additional_charges rows (your table screenshot)
                foreach($chargesArr as $c){
                    \App\Models\InvoiceAdditionalCharge::create([
                        'invoice_id' => $invoice->id,
                        'additional_charge_id' => null,
                        'name' => $c['name'],
                        'amount' => $c['amount'],
                    ]);
                }

                // Save invoice_items (based on your columns)
                foreach ($cleanRows as $row) {
                    InvoiceItem::create([
                        'invoice_id'   => $invoice->id,
                        'item_id'      => $row['item_id'],

                        'description'  => $row['description'] ?? '',
                        'sac_code'     => null,
                        'hsn_code'     => $row['hsn'] ?: null,
                        'quantity'     => (int)($row['qty'] ?? 1),

                        // service/product both supported via numeric fields
                        'gold_wt'      => (float)($row['gold_wt'] ?? 0),
                        'silver_wt'    => (float)($row['silver_wt'] ?? 0),
                        'gold_rate'    => (float)($row['gold_rate'] ?? 0),
                        'silver_rate'  => (float)($row['silver_rate'] ?? 0),

                        'gemstone_wt_ct' => (float)($row['gemstone_wt'] ?? 0),
                        'diamond_wt_ct'  => (float)($row['diamond_wt'] ?? 0),

                        // store service_rate in making_charge (because service_rate column not present)
                        'making_charge' => ($row['item_type']==='service') ? (float)($row['making_charge'] ?? 0) : null,
                        'making_rate'   => ($row['item_type']==='product') ? (float)($row['making_rate'] ?? 0) : null,

                        'discount'    => 0,
                        'tax_percent' => (float)($row['tax_percent'] ?? 0),

                        // NOTE: keep "rate" as line base (without invoice-level discount/charges)
                        // invoice-level adjustments are stored in invoice table
                        'rate'        => ($row['item_type']==='service')
                            ? round(((float)$row['making_charge']) * (int)$row['qty'], 2)
                            : round((($row['gold_wt']*$row['gold_rate']) + ($row['silver_wt']*$row['silver_rate']) + ($row['making_rate'])) * (int)$row['qty'], 2),

                        // amount is not line-final here (final total handled at invoice level)
                        'amount'      => 0,
                    ]);
                }

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

                // stock cut only for product items (BEST: recordSale me service ignore karo)
                $invoice->load(['items']);
                $stock->recordSale($invoice);
            });

            $pdf = $this->buildInvoicePdf($invoice);

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

        return redirect()->route('invoices.index')->with('success', 'Invoice created successfully.');
    }



    public function update(Request $r, \App\Models\Invoice $invoice)
    {
        $bid = $r->user()->current_business_id ?? session('active_business_id');
        if (!$bid) {
            return back()
                ->withErrors(['business' => 'Active business select/attach नहीं है.'])
                ->withInput();
        }

        // Security: invoice belongs to business
        abort_unless((int)$invoice->business_id === (int)$bid, 403);

        // ----------------- VALIDATION -----------------
        $data = $r->validate([
            'client_id'      => ['required','exists:clients,id'],
            'invoice_date'   => ['required','date'],
            'invoice_prefix' => ['nullable','string','max:100'],

            'transport_mode' => ['nullable','string','max:255'],
            'gst_no'         => ['nullable','string','max:50'],
            'reverse_charge' => ['nullable','boolean'],

            'notes'          => ['nullable','string','max:2000'],
            'terms'          => ['nullable','string','max:2000'],

            'items_json'     => ['required','string'],

            // totals hidden fields (optional)
            'cgst_amount'    => ['nullable','numeric','min:0'],
            'sgst_amount'    => ['nullable','numeric','min:0'],
            'igst_amount'    => ['nullable','numeric','min:0'],
        ]);

        // Payment fields (invoice_payments)
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

        // ----------------- PREFIX (FY logic) -----------------
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

        // ✅ Edit mode me generally prefix lock hota hai
        // Agar aap allow karna chahte ho to: $prefix = trim($data['invoice_prefix'] ?? $invoice->invoice_prefix ?? '');
        $prefix = trim($invoice->invoice_prefix ?? '');
        if ($prefix === '') {
            // fallback
            $prefix = trim($data['invoice_prefix'] ?? '');
            if ($prefix === '') $prefix = $computePrefix($invoiceDate, 'INV');
        }

        // ----------------- ITEMS JSON PARSE -----------------
        $rows = json_decode($data['items_json'], true);
        if (!is_array($rows) || count($rows) < 1) {
            return back()->withErrors(['items' => 'कम से कम 1 line item जरूरी है.'])->withInput();
        }

        // ----------------- TOTALS -----------------
        $subtotal = 0.0;
        $taxTotal = 0.0;
        $cleanRows = [];

        foreach ($rows as $i => $row) {

            $desc = trim($row['description'] ?? '');
            $hsn  = trim($row['hsn'] ?? '');

            $qty      = (int)($row['quantity'] ?? 1);
            $qty      = $qty < 1 ? 1 : $qty;

            $taxPct   = (float)($row['tax_percent'] ?? 0);

            $goldWt     = (float)($row['gold_wt'] ?? 0);
            $silverWt   = (float)($row['silver_wt'] ?? 0);
            $goldRate   = (float)($row['gold_rate'] ?? 0);
            $silverRate = (float)($row['silver_rate'] ?? 0);
            $making     = (float)($row['making_rate'] ?? 0);

            $gemCt      = (float)($row['gemstone_wt'] ?? 0);
            $diaCt      = (float)($row['diamond_wt'] ?? 0);

            // basic validate
            if ($desc === '' || $taxPct < 0 || $taxPct > 100 || $goldWt < 0 || $silverWt < 0 || $goldRate < 0 || $silverRate < 0 || $making < 0) {
                return back()->withErrors(['items' => "Row ".($i+1)." invalid है."])->withInput();
            }

            // base = (gold+silver+making) * qty
            $lineBase = round((($goldWt * $goldRate) + ($silverWt * $silverRate) + $making) * $qty, 2);

            $discount = (float)($row['discount'] ?? 0);
            if ($discount < 0) $discount = 0;

            $taxable = max(0, round($lineBase - $discount, 2));
            $lineTax = round($taxable * ($taxPct / 100), 2);
            $lineAmt = round($taxable + $lineTax, 2);

            $subtotal += $lineBase;
            $taxTotal += $lineTax;

            $cleanRows[] = [
                'item_id'          => $row['item_id'] ?? null,
                'description'      => $desc,

                'hsn_code'         => $hsn ?: null,
                'sac_code'         => $row['sac'] ?? null,

                'quantity'         => $qty,

                'gold_wt'          => $goldWt,
                'silver_wt'        => $silverWt,

                'gold_rate'        => $goldRate,
                'silver_rate'      => $silverRate,

                'gemstone_wt_ct'   => $gemCt,
                'diamond_wt_ct'    => $diaCt,

                'making_rate'      => $making,

                'discount'         => round($discount, 2),
                'tax_percent'      => round($taxPct, 2),

                'rate'             => $lineBase,
                'amount'           => $lineAmt,
            ];
        }

        $subtotal   = round($subtotal, 2);
        $taxTotal   = round($taxTotal, 2);
        $grandTotal = round($subtotal + $taxTotal, 2);

        // ----------------- PAYMENT TOTALS -----------------
        $cash    = (float)($pay['pay_cash'] ?? 0);
        $online  = (float)($pay['pay_upi'] ?? 0);
        $card    = (float)($pay['pay_card'] ?? 0);
        $cheque  = (float)($pay['pay_cheque'] ?? 0);

        $credit  = (float)($pay['credit_sales_excess'] ?? 0);
        $advance = (float)($pay['advance_amount'] ?? 0);

        $receivedTotal = round($cash + $online + $card + $cheque, 2);
        $balance       = round(max(0, $grandTotal - $receivedTotal - $advance - $credit), 2);

        // ----------------- SAVE -----------------
        try {
            \DB::transaction(function () use (
                $bid, $invoice, $data, $invoiceDate, $prefix,
                $subtotal, $taxTotal, $grandTotal, $receivedTotal, $balance,
                $cash, $online, $card, $cheque, $credit, $advance,
                $pay, $cleanRows
            ) {

                // 1) update invoice header & totals
                $invoice->update([
                    'client_id'       => $data['client_id'],
                    'invoice_date'    => $invoiceDate,

                    'invoice_prefix'  => $prefix,
                    // ✅ invoice_number same (do not change)
                    // 'invoice_number' => $invoice->invoice_number,

                    'subtotal'        => $subtotal,
                    'tax_amount'      => $taxTotal,

                    'cgst_amount'     => $data['cgst_amount'] ?? ($invoice->cgst_amount ?? 0),
                    'sgst_amount'     => $data['sgst_amount'] ?? ($invoice->sgst_amount ?? 0),
                    'igst_amount'     => $data['igst_amount'] ?? ($invoice->igst_amount ?? 0),

                    'total'           => $grandTotal,
                    'received_amount' => $receivedTotal,
                    'balance'         => $balance,

                    'transport_mode'  => $data['transport_mode'] ?? null,
                    'gst_no'          => $data['gst_no'] ?? null,
                    'reverse_charge'  => (int)($data['reverse_charge'] ?? 0),

                    'notes'           => $data['notes'] ?? null,
                    'terms'           => $data['terms'] ?? null,

                    'items_json'      => json_encode($cleanRows),
                ]);

                // 2) replace invoice_items
                \App\Models\InvoiceItem::where('invoice_id', $invoice->id)->delete();

                foreach ($cleanRows as $row) {
                    \App\Models\InvoiceItem::create([
                        'invoice_id'       => $invoice->id,
                        'item_id'          => $row['item_id'] ?? null,
                        'description'      => $row['description'] ?? '',

                        'sac_code'         => $row['sac_code'] ?? null,
                        'hsn_code'         => $row['hsn_code'] ?? null,

                        'quantity'         => (int)($row['quantity'] ?? 1),

                        'gold_wt'          => (float)($row['gold_wt'] ?? 0),
                        'silver_wt'        => (float)($row['silver_wt'] ?? 0),

                        'gold_rate'        => (float)($row['gold_rate'] ?? 0),
                        'silver_rate'      => (float)($row['silver_rate'] ?? 0),

                        'gemstone_wt_ct'   => (float)($row['gemstone_wt_ct'] ?? 0),
                        'diamond_wt_ct'    => (float)($row['diamond_wt_ct'] ?? 0),

                        'making_rate'      => (float)($row['making_rate'] ?? 0),

                        'discount'         => (float)($row['discount'] ?? 0),
                        'tax_percent'      => (float)($row['tax_percent'] ?? 0),

                        'rate'             => (float)($row['rate'] ?? 0),
                        'amount'           => (float)($row['amount'] ?? 0),
                    ]);
                }

                // 3) update latest payment record OR create new
                $payment = \App\Models\InvoicePayment::where('business_id', $bid)
                    ->where('invoice_id', $invoice->id)
                    ->latest('id')
                    ->first();

                $payload = [
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
                ];

                if ($payment) {
                    $payment->update($payload);
                } else {
                    \App\Models\InvoicePayment::create($payload);
                }
            });
        } catch (\Throwable $e) {
            report($e);
            return back()->withErrors(['invoice' => 'Invoice update करते समय error आया: '.$e->getMessage()])->withInput();
        }

        return redirect()
            ->route('invoices.index')
            ->with('success', 'Invoice updated successfully.');
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

        // ✅ data URIs (simple pdf me bhi logo/sign aa sakta hai)
        $logoDataUri = $this->imageDataUri($biz?->logo);
        $signDataUri = $this->imageDataUri($biz?->signature);

        $vm = compact(
            'inv','invoice','biz','client','items','charges',
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
        $view = 'invoices.' . ($biz->pdf_template_id ?? 'pdf_simple');

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

}
