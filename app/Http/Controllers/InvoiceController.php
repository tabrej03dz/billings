<?php

namespace App\Http\Controllers;

use App\Models\AdditionalCharge;
use App\Models\Business;
use App\Models\Invoice;
use App\Models\Client;
use App\Models\InvoiceCharge;
use App\Models\InvoiceItem;
use App\Models\Item;
use App\Models\MetalRate;
use App\Services\InvoiceNumber;
use App\Services\StockService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;



class InvoiceController extends Controller
{

    protected StockService $stock;   // 👈 ADD

    public function __construct(StockService $stock)   // 👈 ADD
    {
        $this->stock = $stock;
    }


    public function index(Request $r)
    {
        $invoices = Invoice::with('client')->latest()->paginate(15);
        return view('invoices.index', compact('invoices'));
    }



//    public function create(Request $request)
//    {
//        $today = now()->toDateString();
//
//        $bid = $request->user()->current_business_id ?? session('active_business_id');
//        if (!$bid) {
//            $bid = $request->user()->businesses()->pluck('businesses.id')->first();
//        }
//
//        // ✅ Load the active business (has `terms`)
//        $business = \App\Models\Business::find($bid);
//
//        $base = optional(
//                $request->user()->businesses()->where('businesses.id', $bid)->first()
//            )->invoice_base_prefix ?? 'RV/SL';
//
//        $suggestedPrefix = \App\Services\InvoiceNumber::previewPrefix($today, $base);
//
//        // Clients & Items
//        $clients = \App\Models\Client::where('business_id', $bid)
//            ->orderBy('name')->get(['id','name','mobile']);
//
//        $items = \App\Models\Item::where('is_active', true)
//            ->orderBy('name')->get(['id','name','sku','price','tax_rate','description','making_charge']);
//
//        $clientsJson = $clients->map(fn($c)=>[
//            'id'=>$c->id,'name'=>$c->name,'mobile'=>$c->mobile
//        ])->values()->toJson();
//
//        $itemsJson = $items->map(fn($i)=>[
//            'id'=>$i->id,'name'=>$i->name,'sku'=>$i->sku,
//            'price'=>(float)$i->price,'making_charge'=>(float)$i->making_charge,
//            'tax_rate'=>(float)$i->tax_rate,'description'=>$i->description
//        ])->values()->toJson();
//
//        // Additional Charges
//        $charges = \App\Models\AdditionalCharge::orderBy('name')->get(['id','name','amount']);
//        $chargesJson = $charges->map(fn($a)=>[
//            'id'=>$a->id,'name'=>$a->name,'amount'=>(float)$a->amount,
//        ])->values()->toJson();
//
//        // initial preview number
//        $preview = \App\Services\InvoiceNumber::peek((int)$bid, $today, $suggestedPrefix, 3);
//
//        return view('invoices.create', [
//            'today'            => $today,
//            'clientsJson'      => $clientsJson,
//            'itemsJson'        => $itemsJson,
//            'chargesJson'      => $chargesJson,
//            'suggestedPrefix'  => $suggestedPrefix,
//            'basePrefix'       => $base,
//            'initialInvoiceNo' => $preview['full'] ?? 'Auto',
//
//            // ✅ pass default terms for the textarea
//            'defaultTerms'     => $business?->terms,
//        ]);
//    }


    public function create(Request $request)
    {
        $today = now()->toDateString();

        // Active business resolve
        $bid = $request->user()->current_business_id ?? session('active_business_id');
        if (!$bid) {
            $bid = $request->user()->businesses()->pluck('businesses.id')->first();
        }

        // Business (terms ke liye)
        $business = Business::find($bid);

        // Invoice base prefix
        $base = optional(
                $request->user()->businesses()->where('businesses.id', $bid)->first()
            )->invoice_base_prefix ?? 'RV/SL';

        $suggestedPrefix = \App\Services\InvoiceNumber::previewPrefix($today, $base);

        // Clients
        $clients = Client::where('business_id', $bid)
            ->orderBy('name')
            ->get(['id', 'name', 'mobile']);

        // Items (metal + stone info included)
        $items = Item::where('business_id', $bid)
            ->where('is_active', true)
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'sku',
                'price',
                'tax_rate',
                'description',
                'making_charge',
                'sac',
                'metal_type',
                'purity',
                'gross_weight',
                'metal_weight',
                'stone_charges',
            ]);

        // Metal rates for today
        $metalRates = MetalRate::where('business_id', $bid)
            ->whereDate('rate_date', $today)
            ->where('is_active', true)
            ->get([
                'metal_type',
                'purity',
                'rate_per_gram',
            ]);

        // JSON payloads
        $clientsJson = $clients->map(fn($c) => [
            'id'     => $c->id,
            'name'   => $c->name,
            'mobile' => $c->mobile,
        ])->values()->toJson();

        $itemsJson = $items->map(fn($i) => [
            'id'            => $i->id,
            'name'          => $i->name,
            'sku'           => $i->sku,
            'price'         => (float) $i->price,
            'making_charge' => (float) $i->making_charge,
            'tax_rate'      => (float) $i->tax_rate,
            'description'   => $i->description,
            'sac'           => $i->sac,
            'metal_type'    => $i->metal_type,
            'purity'        => $i->purity,
            'gross_weight'  => (float) $i->gross_weight,
            'metal_weight'  => (float) $i->metal_weight,
            'stone_charges' => (float) $i->stone_charges,
        ])->values()->toJson();

        $metalRatesJson = $metalRates->map(fn($r) => [
            'metal_type'    => $r->metal_type,
            'purity'        => $r->purity,
            'rate_per_gram' => (float) $r->rate_per_gram,
        ])->values()->toJson();

        // Additional charges
        $charges = AdditionalCharge::orderBy('name')->get(['id','name','amount']);
        $chargesJson = $charges->map(fn($a) => [
            'id'     => $a->id,
            'name'   => $a->name,
            'amount' => (float) $a->amount,
        ])->values()->toJson();

        // Preview invoice number
        $preview = \App\Services\InvoiceNumber::peek((int) $bid, $today, $suggestedPrefix, 3);

        return view('invoices.create', [
            'today'            => $today,
            'clientsJson'      => $clientsJson,
            'itemsJson'        => $itemsJson,
            'chargesJson'      => $chargesJson,
            'metalRatesJson'   => $metalRatesJson,
            'suggestedPrefix'  => $suggestedPrefix,
            'basePrefix'       => $base,
            'initialInvoiceNo' => $preview['full'] ?? 'Auto',
            'defaultTerms'     => $business?->terms,
        ]);
    }




//    public function store(Request $r)
//    {
//        $bid = $r->user()->current_business_id ?? session('active_business_id');
//        if (!$bid) {
//            return back()
//                ->withErrors(['business' => 'Active business select/attach नहीं है.'])
//                ->withInput();
//        }
//
//        $data = $r->validate([
//            'client_id'       => ['required','exists:clients,id'],
//            'invoice_date'    => ['required','date'],
//            'invoice_prefix'  => ['nullable','string','max:100'],
//
//            'payment_terms'   => ['nullable','integer','min:0','max:365'],
//            'due_date'        => ['nullable','date'],
//            'notes'           => ['nullable','string','max:2000'],
//            'terms'           => ['nullable','string','max:2000'],
//
//            'discount_total'  => ['nullable','numeric','min:0'],
//            'charge_total'    => ['nullable','numeric','min:0'],
//            'tcs_percent'     => ['nullable','numeric','min:0','max:100'],
//            'round_off'       => ['nullable','numeric'],
//            'amount_received' => ['nullable','numeric','min:0'],
//            'payment_method'  => ['nullable','string','max:50'],
//
//            'items_json'      => ['required','string'],
//            'charges_json'    => ['nullable','string'],
//        ]);
//
//        // FY prefix compute helper
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
//        $invoiceDate  = \Carbon\Carbon::parse($data['invoice_date'])->toDateString();
//        $prefix       = trim($data['invoice_prefix'] ?? '');
//        if ($prefix === '') {
//            $prefix = $computePrefix($invoiceDate, 'INV');
//        }
//
//        $paymentTerms = (int)($data['payment_terms'] ?? 0);
//        $dueDate      = $data['due_date']
//            ?? \Carbon\Carbon::parse($invoiceDate)->addDays($paymentTerms)->toDateString();
//
//        // -------- ITEMS JSON PARSE --------
//        $rows = json_decode($data['items_json'], true);
//        if (!is_array($rows) || count($rows) < 1) {
//            return back()
//                ->withErrors(['items' => 'कम से कम 1 line item जरूरी है.'])
//                ->withInput();
//        }
//
//        // -------- CHARGES JSON PARSE --------
//        $chargesRaw = [];
//        if (filled($data['charges_json'] ?? '')) {
//            $decoded = json_decode($data['charges_json'], true);
//            if (is_array($decoded)) {
//                foreach ($decoded as $c) {
//                    $name   = trim(\Illuminate\Support\Arr::get($c, 'name', ''));
//                    $amount = (float)\Illuminate\Support\Arr::get($c, 'amount', 0);
//                    if ($name !== '' && $amount >= 0) {
//                        $chargesRaw[] = [
//                            'id'     => \Illuminate\Support\Arr::get($c, 'id'),
//                            'name'   => $name,
//                            'amount' => round($amount, 2),
//                        ];
//                    }
//                }
//            }
//        }
//        $charge_total = round(array_sum(array_map(
//            fn($c) => (float)$c['amount'],
//            $chargesRaw
//        )), 2);
//
//        // -------- TOTALS (PHP == JS LOGIC) --------
//        $subtotal  = 0.0;
//        $taxTotal  = 0.0;
//        $cleanRows = [];
//
//        foreach ($rows as $i => $row) {
//            $desc    = trim($row['description'] ?? '');
//            $sac     = trim($row['sac'] ?? '');
//            $qty     = (int)($row['qty'] ?? 0);
//
//            // metal fields from JS
//            $metal_type    = $row['metal_type']    ?? null;
//            $purity        = trim($row['purity']   ?? '');
//            $metal_weight  = (float)($row['metal_weight']  ?? 0);
//            $metal_rate    = (float)($row['metal_rate']    ?? 0);
//            $stoneCharges  = (float)($row['stone_charges'] ?? 0);
//
//            // price/making/discount/tax
//            $price   = (float)($row['price'] ?? 0);           // metal value per unit
//            $making  = (float)($row['making_charge'] ?? 0);   // per unit making
//            $disc    = (float)($row['discount'] ?? 0);        // line discount
//            $taxPct  = (float)($row['tax_percent'] ?? 0);
//            $item_id = $row['item_id'] ?? null;
//
//            if (
//                $desc === '' || $qty < 1 ||
//                $price < 0 || $making < 0 || $disc < 0 || $taxPct < 0 ||
//                $metal_weight < 0 || $metal_rate < 0 || $stoneCharges < 0
//            ) {
//                return back()
//                    ->withErrors(['items' => "Row ".($i+1)." invalid है."])
//                    ->withInput();
//            }
//
//            // JS logic:
//            // basePerUnit = price + making_charge + stone_charges
//            // base        = max(0, qty * basePerUnit - discount)
//            // tax         = base * (tax_percent/100)
//            $basePerUnit = $price + $making + $stoneCharges;
//            $lineBase    = max(0, ($qty * $basePerUnit) - $disc);
//            $lineTax     = $lineBase * ($taxPct / 100);
//            $lineAmt     = round($lineBase + $lineTax, 2);
//
//            $subtotal += $lineBase;
//            $taxTotal += $lineTax;
//
//            $clean = [
//                'description'    => $desc,
//                'sac_code'       => $sac ?: null,
//                'quantity'       => $qty,
//
//                // metal info persist in invoice_items
//                'metal_type'     => $metal_type ?: null,
//                'purity'         => $purity ?: null,
//                'metal_weight'   => $metal_weight ?: null,
//                'metal_rate'     => $metal_rate ?: null,
//                'stone_charges'  => $stoneCharges,
//
//                // pricing
//                'rate'           => $price,
//                'making_charge'  => $making,
//                'discount'       => $disc,
//                'tax_percent'    => $taxPct,
//                'amount'         => $lineAmt,
//            ];
//            if ($item_id) {
//                $clean['item_id'] = $item_id;
//            }
//
//            $cleanRows[] = $clean;
//        }
//
//        // -------- FINAL TOTAL FIELDS --------
//        $discount_total = (float)($data['discount_total'] ?? 0);
//        $tcs_percent    = (float)($data['tcs_percent'] ?? 0);
//        $round_off_in   = (float)($data['round_off'] ?? 0);
//        $received       = (float)($data['amount_received'] ?? 0);
//
//        $tcs_base   = max(0, $subtotal + $taxTotal - $discount_total);
//        $tcs_amount = round($tcs_base * ($tcs_percent / 100), 2);
//
//        $total_before_round = round(
//            $subtotal + $taxTotal - $discount_total + $charge_total + $tcs_amount,
//            2
//        );
//        $grand_total = round($total_before_round + $round_off_in, 2);
//        $balance     = round(max(0, $grand_total - $received), 2);
//
//        try {
//            DB::transaction(function () use (
//                $bid, $r, $data, $prefix, $invoiceDate, $paymentTerms, $dueDate,
//                $subtotal, $taxTotal, $discount_total, $charge_total, $tcs_percent, $tcs_amount,
//                $round_off_in, $grand_total, $received, $balance, $cleanRows, $chargesRaw
//            ) {
//                $alloc = \App\Services\InvoiceNumber::next((int)$bid, $invoiceDate, $prefix, 3);
//
//                /** @var \App\Models\Invoice $invoice */
//                $invoice = \App\Models\Invoice::create([
//                    'business_id'     => $bid,
//                    'client_id'       => $data['client_id'],
//                    'invoice_date'    => $invoiceDate,
//
//                    'invoice_prefix'  => $prefix,
//                    'invoice_number'  => $alloc['full'],
//
//                    'payment_terms'   => $paymentTerms,
//                    'due_date'        => $dueDate,
//
//                    'subtotal'        => $subtotal,
//                    'tax_amount'      => $taxTotal,
//                    'discount_total'  => $discount_total,
//                    'charge_total'    => $charge_total,
//                    'tcs_percent'     => $tcs_percent,
//                    'tcs_amount'      => $tcs_amount,
//                    'round_off'       => $round_off_in,
//                    'total'           => $grand_total,
//                    'received_amount' => $received,
//                    'balance'         => $balance,
//
//                    'payment_method'  => $data['payment_method'] ?? null,
//                    'notes'           => $data['notes'] ?? null,
//                    'terms'           => $data['terms'] ?? null,
//
//                    'items_json'      => $r->input('items_json'),
//                    'charges_json'    => !empty($chargesRaw) ? json_encode($chargesRaw) : null,
//
//                    'amount_in_words' => '',
//                ]);
//
//                // line items
//                foreach ($cleanRows as $row) {
//                    $row['invoice_id'] = $invoice->id;
//                    \App\Models\InvoiceItem::create($row);
//                }
//
//                // charges detail table (if model exists)
//                if (class_exists(\App\Models\InvoiceCharge::class) && !empty($chargesRaw)) {
//                    foreach ($chargesRaw as $c) {
//                        \App\Models\InvoiceCharge::create([
//                            'invoice_id' => $invoice->id,
//                            'charge_id'  => $c['id'] ?? null,
//                            'name'       => $c['name'],
//                            'amount'     => $c['amount'],
//                        ]);
//                    }
//                }
//            });
//        } catch (\Throwable $e) {
//            report($e);
//            return back()
//                ->withErrors(['invoice' => 'Invoice save करते समय error आया: '.$e->getMessage()])
//                ->withInput();
//        }
//
//        return redirect()
//            ->route('invoices.index')
//            ->with('success', 'Invoice created successfully.');
//    }

//    public function store(Request $r)
//    {
//        $bid = $r->user()->current_business_id ?? session('active_business_id');
//        if (!$bid) {
//            return back()
//                ->withErrors(['business' => 'Active business select/attach नहीं है.'])
//                ->withInput();
//        }
//
//        $data = $r->validate([
//            'client_id'       => ['required','exists:clients,id'],
//            'invoice_date'    => ['required','date'],
//            'invoice_prefix'  => ['nullable','string','max:100'],
//
//            'payment_terms'   => ['nullable','integer','min:0','max:365'],
//            'due_date'        => ['nullable','date'],
//            'notes'           => ['nullable','string','max:2000'],
//            'terms'           => ['nullable','string','max:2000'],
//
//            'discount_total'  => ['nullable','numeric','min:0'],
//            'charge_total'    => ['nullable','numeric','min:0'],
//            'tcs_percent'     => ['nullable','numeric','min:0','max:100'],
//            'round_off'       => ['nullable','numeric'],
//            'amount_received' => ['nullable','numeric','min:0'],
//            'payment_method'  => ['nullable','string','max:50'],
//
//            'items_json'      => ['required','string'],
//            'charges_json'    => ['nullable','string'],
//        ]);
//
//        // FY prefix compute helper
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
//        $invoiceDate  = \Carbon\Carbon::parse($data['invoice_date'])->toDateString();
//        $prefix       = trim($data['invoice_prefix'] ?? '');
//        if ($prefix === '') {
//            $prefix = $computePrefix($invoiceDate, 'INV');
//        }
//
//        $paymentTerms = (int)($data['payment_terms'] ?? 0);
//        $dueDate      = $data['due_date']
//            ?? \Carbon\Carbon::parse($invoiceDate)->addDays($paymentTerms)->toDateString();
//
//        // -------- ITEMS JSON PARSE --------
//        $rows = json_decode($data['items_json'], true);
//        if (!is_array($rows) || count($rows) < 1) {
//            return back()
//                ->withErrors(['items' => 'कम से कम 1 line item जरूरी है.'])
//                ->withInput();
//        }
//
//        // -------- CHARGES JSON PARSE --------
//        $chargesRaw = [];
//        if (filled($data['charges_json'] ?? '')) {
//            $decoded = json_decode($data['charges_json'], true);
//            if (is_array($decoded)) {
//                foreach ($decoded as $c) {
//                    $name   = trim(\Illuminate\Support\Arr::get($c, 'name', ''));
//                    $amount = (float)\Illuminate\Support\Arr::get($c, 'amount', 0);
//                    if ($name !== '' && $amount >= 0) {
//                        $chargesRaw[] = [
//                            'id'     => \Illuminate\Support\Arr::get($c, 'id'),
//                            'name'   => $name,
//                            'amount' => round($amount, 2),
//                        ];
//                    }
//                }
//            }
//        }
//        $charge_total = round(array_sum(array_map(
//            fn($c) => (float)$c['amount'],
//            $chargesRaw
//        )), 2);
//
//        // -------- TOTALS (PHP == JS LOGIC) --------
//        $subtotal  = 0.0;
//        $taxTotal  = 0.0;
//        $cleanRows = [];
//
//        foreach ($rows as $i => $row) {
//            $desc    = trim($row['description'] ?? '');
//            $sac     = trim($row['sac'] ?? '');
//            $qty     = (int)($row['qty'] ?? 0);
//
//            // metal fields from JS
//            $metal_type    = $row['metal_type']    ?? null;
//            $purity        = trim($row['purity']   ?? '');
//            $metal_weight  = (float)($row['metal_weight']  ?? 0);
//            $metal_rate    = (float)($row['metal_rate']    ?? 0);
//            $stoneCharges  = (float)($row['stone_charges'] ?? 0);
//
//            // price/making/discount/tax
//            $price   = (float)($row['price'] ?? 0);           // metal value per unit
//            $making  = (float)($row['making_charge'] ?? 0);   // per unit making
//            $disc    = (float)($row['discount'] ?? 0);        // line discount
//            $taxPct  = (float)($row['tax_percent'] ?? 0);
//            $item_id = $row['item_id'] ?? null;
//
//            if (
//                $desc === '' || $qty < 1 ||
//                $price < 0 || $making < 0 || $disc < 0 || $taxPct < 0 ||
//                $metal_weight < 0 || $metal_rate < 0 || $stoneCharges < 0
//            ) {
//                return back()
//                    ->withErrors(['items' => "Row ".($i+1)." invalid है."])
//                    ->withInput();
//            }
//
//            // JS logic:
//            // basePerUnit = price + making_charge + stone_charges
//            // base        = max(0, qty * basePerUnit - discount)
//            // tax         = base * (tax_percent/100)
//            $basePerUnit = $price + $making + $stoneCharges;
//            $lineBase    = max(0, ($qty * $basePerUnit) - $disc);
//            $lineTax     = $lineBase * ($taxPct / 100);
//            $lineAmt     = round($lineBase + $lineTax, 2);
//
//            $subtotal += $lineBase;
//            $taxTotal += $lineTax;
//
//            $clean = [
//                'description'    => $desc,
//                'sac_code'       => $sac ?: null,
//                'quantity'       => $qty,
//
//                // metal info persist in invoice_items
//                'metal_type'     => $metal_type ?: null,
//                'purity'         => $purity ?: null,
//                'metal_weight'   => $metal_weight ?: null,
//                'metal_rate'     => $metal_rate ?: null,
//                'stone_charges'  => $stoneCharges,
//
//                // pricing
//                'rate'           => $price,
//                'making_charge'  => $making,
//                'discount'       => $disc,
//                'tax_percent'    => $taxPct,
//                'amount'         => $lineAmt,
//            ];
//            if ($item_id) {
//                $clean['item_id'] = $item_id;
//            }
//
//            $cleanRows[] = $clean;
//        }
//
//        // -------- FINAL TOTAL FIELDS --------
//        $discount_total = (float)($data['discount_total'] ?? 0);
//        $tcs_percent    = (float)($data['tcs_percent'] ?? 0);
//        $round_off_in   = (float)($data['round_off'] ?? 0);
//        $received       = (float)($data['amount_received'] ?? 0);
//
//        $tcs_base   = max(0, $subtotal + $taxTotal - $discount_total);
//        $tcs_amount = round($tcs_base * ($tcs_percent / 100), 2);
//
//        $total_before_round = round(
//            $subtotal + $taxTotal - $discount_total + $charge_total + $tcs_amount,
//            2
//        );
//        $grand_total = round($total_before_round + $round_off_in, 2);
//        $balance     = round(max(0, $grand_total - $received), 2);
//
//        try {
//            DB::transaction(function () use (
//                $bid, $r, $data, $prefix, $invoiceDate, $paymentTerms, $dueDate,
//                $subtotal, $taxTotal, $discount_total, $charge_total, $tcs_percent, $tcs_amount,
//                $round_off_in, $grand_total, $received, $balance, $cleanRows, $chargesRaw
//            ) {
//                $alloc = \App\Services\InvoiceNumber::next((int)$bid, $invoiceDate, $prefix, 3);
//
//                /** @var \App\Models\Invoice $invoice */
//                $invoice = \App\Models\Invoice::create([
//                    'business_id'     => $bid,
//                    'client_id'       => $data['client_id'],
//                    'invoice_date'    => $invoiceDate,
//
//                    'invoice_prefix'  => $prefix,
//                    'invoice_number'  => $alloc['full'],
//
//                    'payment_terms'   => $paymentTerms,
//                    'due_date'        => $dueDate,
//
//                    'subtotal'        => $subtotal,
//                    'tax_amount'      => $taxTotal,
//                    'discount_total'  => $discount_total,
//                    'charge_total'    => $charge_total,
//                    'tcs_percent'     => $tcs_percent,
//                    'tcs_amount'      => $tcs_amount,
//                    'round_off'       => $round_off_in,
//                    'total'           => $grand_total,
//                    'received_amount' => $received,
//                    'balance'         => $balance,
//
//                    'payment_method'  => $data['payment_method'] ?? null,
//                    'notes'           => $data['notes'] ?? null,
//                    'terms'           => $data['terms'] ?? null,
//
//                    'items_json'      => $r->input('items_json'),
//                    'charges_json'    => !empty($chargesRaw) ? json_encode($chargesRaw) : null,
//
//                    'amount_in_words' => '',
//                ]);
//
//                // line items
//                foreach ($cleanRows as $row) {
//                    $row['invoice_id'] = $invoice->id;
//                    \App\Models\InvoiceItem::create($row);
//                }
//
//                // charges detail table (if model exists)
//                if (class_exists(\App\Models\InvoiceCharge::class) && !empty($chargesRaw)) {
//                    foreach ($chargesRaw as $c) {
//                        \App\Models\InvoiceCharge::create([
//                            'invoice_id' => $invoice->id,
//                            'charge_id'  => $c['id'] ?? null,
//                            'name'       => $c['name'],
//                            'amount'     => $c['amount'],
//                        ]);
//                    }
//                }
//
//                // ⭐ INVENTORY UPDATE: invoice_items se hi stock minus karenge
//                $invoice->load('items.item');   // items + unka linked Item model
//
//                foreach ($invoice->items as $invItem) {
//                    if ($invItem->item) { // sirf jinke paas linked item ho
//                        $item    = $invItem->item;
//
//                        // yahan fix hai 👇
//                        $current = (int)($item->stock_qty ?? 0);
//                        $qty     = (float)($invItem->quantity ?? 0);
//
//                        $newQty = $current - $qty;
//                        if ($newQty < 0) {
//                            $newQty = 0;
//                        }
//
//                        $item->stock_qty = $newQty;
//                        $item->save();
//                    }
//                }
//            });
//        } catch (\Throwable $e) {
//            report($e);
//            return back()
//                ->withErrors(['invoice' => 'Invoice save करते समय error आया: '.$e->getMessage()])
//                ->withInput();
//        }
//
//
//        return redirect()
//            ->route('invoices.index')
//            ->with('success', 'Invoice created successfully.');
//    }




    public function edit(\Illuminate\Http\Request $request, \App\Models\Invoice $invoice)
    {
        // -----------------------
        // Business context
        // -----------------------
        $bid = $invoice->business_id
            ?? ($request->user()->current_business_id ?? session('active_business_id'))
            ?? $request->user()->businesses()->pluck('businesses.id')->first();

        // -----------------------
        // Clients (same business + ensure invoice client present)
        // -----------------------
        $clients = \App\Models\Client::query()
            ->where('business_id', $bid)
            ->orWhere('id', $invoice->client_id)      // <- ensure selected client is in list
            ->orderBy('name')
            ->get(['id', 'name', 'mobile']);

        // -----------------------
        // Items (active + same business)
        // jewellery related fields bhi include
        // -----------------------
        $items = \App\Models\Item::where('is_active', true)
            ->where('business_id', $bid)
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'sku',
                'price',
                'tax_rate',
                'description',
                'making_charge',
                'sac',             // HSN/SAC
                'metal_type',
                'purity',
                'metal_weight',    // agar column name ye hai
                'gross_weight',    // fallback ke liye
                'stone_charges',   // stone amount
            ]);

        // ✅ JSON for Alpine (ids numeric) – same structure as CREATE
        $clientsJson = $clients->map(fn($c) => [
            'id'     => (int) $c->id,
            'name'   => $c->name,
            'mobile' => $c->mobile,
        ])->values()->toJson();

        $itemsJson = $items->map(function ($i) {
            return [
                'id'            => (int) $i->id,
                'name'          => $i->name,
                'sku'           => $i->sku,
                'price'         => (float) $i->price,
                'making_charge' => (float) $i->making_charge,
                'tax_rate'      => (float) $i->tax_rate,
                'description'   => $i->description,

                // jewellery fields
                'sac'           => $i->sac ?? '',
                'metal_type'    => $i->metal_type ?? null,
                'purity'        => $i->purity ?? null,
                'metal_weight'  => (float) ($i->metal_weight ?? 0),
                'gross_weight'  => (float) ($i->gross_weight ?? 0),
                'stone_charges' => (float) ($i->stone_charges ?? 0),
            ];
        })->values()->toJson();

        // -----------------------
        // Additional charges master
        // -----------------------
        $charges = \App\Models\AdditionalCharge::orderBy('name')
            ->get(['id', 'name', 'amount']);

        $chargesJson = $charges->map(fn($a) => [
            'id'     => (int) $a->id,
            'name'   => $a->name,
            'amount' => (float) $a->amount,
        ])->values()->toJson();

        // -----------------------
        // Metal rates master (for auto price from weight)
        // -----------------------
        $metalRates = \App\Models\MetalRate::where('business_id', $bid)
            ->orderBy('metal_type')
            ->orderBy('purity')
            ->get(['metal_type', 'purity', 'rate_per_gram']);

        $metalRatesJson = $metalRates->map(fn($r) => [
            'metal_type'    => $r->metal_type,
            'purity'        => $r->purity,
            'rate_per_gram' => (float) $r->rate_per_gram,
        ])->values()->toJson();

        // =========================
        // Existing line items -> UI
        // =========================
        $existingItems = [];

        if (method_exists($invoice, 'items')) {
            // invoice_items se fields (including jewellery)
            $existingItems = $invoice->items()
                ->get([
                    'item_id',
                    'description',
                    'sac_code',
                    'quantity',
                    'metal_type',
                    'purity',
                    'metal_weight',
                    'metal_rate',
                    'stone_charges',
                    'rate',
                    'making_charge',
                    'discount',
                    'tax_percent',
                    'amount',
                ])
                ->map(function ($r) {
                    return [
                        'item_id'       => $r->item_id ? (int) $r->item_id : null,
                        'description'   => $r->description,
                        'sac'           => $r->sac_code,
                        'qty'           => (float) $r->quantity,

                        // jewellery fields
                        'metal_type'    => $r->metal_type ?? null,
                        'purity'        => $r->purity ?? null,
                        'metal_weight'  => (float) ($r->metal_weight ?? 0),
                        'metal_rate'    => (float) ($r->metal_rate ?? 0),
                        'stone_charges' => (float) ($r->stone_charges ?? 0),

                        'price'         => (float) $r->rate,
                        'making_charge' => (float) $r->making_charge,
                        'discount'      => (float) ($r->discount ?? 0),
                        'tax_percent'   => (float) $r->tax_percent,
                        'amount'        => (float) $r->amount,
                    ];
                })->values()->all();
        } elseif (!empty($invoice->items_json)) {
            // JSON fallback (old invoices etc.)
            $decoded = json_decode($invoice->items_json, true) ?: [];
            $existingItems = array_map(function ($r) {
                return [
                    'item_id'       => isset($r['item_id']) ? (int) $r['item_id'] : null,
                    'description'   => $r['description'] ?? '',
                    'sac'           => $r['sac'] ?? '',

                    'qty'           => (float) ($r['qty'] ?? 0),

                    // jewellery fields from json if present
                    'metal_type'    => $r['metal_type']    ?? null,
                    'purity'        => $r['purity']        ?? null,
                    'metal_weight'  => (float) ($r['metal_weight']  ?? 0),
                    'metal_rate'    => (float) ($r['metal_rate']    ?? 0),
                    'stone_charges' => (float) ($r['stone_charges'] ?? 0),

                    'price'         => (float) ($r['price'] ?? 0),
                    'making_charge' => (float) ($r['making_charge'] ?? 0),
                    'discount'      => (float) ($r['discount'] ?? 0),
                    'tax_percent'   => (float) ($r['tax_percent'] ?? 0),
                    'amount'        => (float) ($r['amount'] ?? 0),
                ];
            }, $decoded);
        }

        // =========================
        // Existing additional charges
        // =========================
        $existingCharges = [];

        if (method_exists($invoice, 'additionalCharges')) {
            $existingCharges = $invoice->additionalCharges()
                ->get(['additional_charge_id as id', 'name', 'amount'])
                ->map(fn($r) => [
                    'id'     => $r->id ? (int) $r->id : null,
                    'name'   => $r->name,
                    'amount' => (float) $r->amount,
                ])->values()->all();
        } elseif (!empty($invoice->charges_json)) {
            $decoded = json_decode($invoice->charges_json, true) ?: [];
            $existingCharges = array_map(function ($r) {
                return [
                    'id'     => isset($r['id']) ? (int) $r['id'] : null,
                    'name'   => $r['name'] ?? '',
                    'amount' => (float) ($r['amount'] ?? 0),
                ];
            }, $decoded);
        }

        // =========================
        // Header/totals defaults (UI state)
        // =========================
        $invoiceDate = $invoice->invoice_date
            ? \Carbon\Carbon::parse($invoice->invoice_date)->toDateString()
            : now()->toDateString();

        $dueDate = $invoice->due_date
            ? \Carbon\Carbon::parse($invoice->due_date)->toDateString()
            : null;

        $hdr = [
            'date'  => $invoiceDate,
            'terms' => (int) ($invoice->payment_terms ?? 30),
            'due'   => $dueDate,
        ];

        $u = [
            'discount_total'  => (float) ($invoice->discount_total ?? 0),
            'tcs_percent'     => (float) ($invoice->tcs_percent ?? 0),
            'received'        => (float) ($invoice->received_amount ?? 0),
            'round_off'       => (float) abs($invoice->round_off ?? 0),
            'roundSign'       => ($invoice->round_off ?? 0) < 0 ? '-' : '+',
            'discountEnabled' => (float) ($invoice->discount_total ?? 0) > 0,
            'tcsEnabled'      => (float) ($invoice->tcs_percent ?? 0) > 0,
            'autoRound'       => false,
        ];

        return view('invoices.edit', [
            'invoice'        => $invoice,
            'clientsJson'    => $clientsJson,
            'itemsJson'      => $itemsJson,
            'chargesJson'    => $chargesJson,
            'metalRatesJson' => $metalRatesJson,   // ✅ EDIT view ko bhi mil gaya
            'initialItems'   => $existingItems,
            'initialCharges' => $existingCharges,
            'hdr'            => $hdr,
            'u'              => $u,
            'basePrefix'     => $invoice->invoice_prefix_base ?? ($invoice->invoice_prefix ?? 'RV/SL'),
            'invoicePrefix'  => $invoice->invoice_prefix ?? '',
            'invoiceNumber'  => $invoice->invoice_number ?? '',
            'paymentMethod'  => $invoice->payment_method ?? 'Cash',
        ]);
    }




//    public function update(Request $r, \App\Models\Invoice $invoice)
//    {
//        // 1) Basic validation (prefix/date editable per your UI)
//        $data = $r->validate([
//            'client_id'       => ['required','exists:clients,id'],
//            'invoice_date'    => ['required','date'],
//            'invoice_prefix'  => ['nullable','string','max:100'],
//
//            'payment_terms'   => ['nullable','integer','min:0','max:365'],
//            'due_date'        => ['nullable','date'],
//            'notes'           => ['nullable','string','max:2000'],
//            'terms'           => ['nullable','string','max:2000'],
//
//            'discount_total'  => ['nullable','numeric','min:0'],
//            'charge_total'    => ['nullable','numeric','min:0'], // ignored; recompute
//            'tcs_percent'     => ['nullable','numeric','min:0','max:100'],
//            'round_off'       => ['nullable','numeric'], // +/- allowed
//            'amount_received' => ['nullable','numeric','min:0'],
//            'payment_method'  => ['nullable','string','max:50'],
//
//            'items_json'      => ['required','string'],  // [{ item_id?, description, sac, qty, price, making_charge, stone_charges, discount, tax_percent, amount }]
//            'charges_json'    => ['nullable','string'],  // [{ id?, name, amount }]
//        ]);
//
//        $invoiceDate  = \Carbon\Carbon::parse($data['invoice_date'])->toDateString();
//
//        // Keep prefix/number; allow prefix change if UI sent (read-only in your UI though)
//        $prefix       = trim($data['invoice_prefix'] ?? $invoice->invoice_prefix ?? '');
//        if ($prefix === '') {
//            $prefix = $invoice->invoice_prefix ?? 'INV/';
//        }
//
//        $paymentTerms = (int)($data['payment_terms'] ?? 0);
//        $dueDate      = $data['due_date'] ?? \Carbon\Carbon::parse($invoiceDate)->addDays($paymentTerms)->toDateString();
//
//        // 2) Decode rows
//        $rows = json_decode($data['items_json'], true);
//        if (!is_array($rows) || count($rows) < 1) {
//            return back()
//                ->withErrors(['items' => 'कम से कम 1 line item जरूरी है.'])
//                ->withInput();
//        }
//
//        // 3) Decode charges and recompute total
//        $chargesRaw = [];
//        if (filled($data['charges_json'] ?? '')) {
//            $decoded = json_decode($data['charges_json'], true);
//            if (is_array($decoded)) {
//                foreach ($decoded as $c) {
//                    $name   = trim(\Illuminate\Support\Arr::get($c, 'name', ''));
//                    $amount = (float)\Illuminate\Support\Arr::get($c, 'amount', 0);
//                    if ($name !== '' && $amount >= 0) {
//                        $chargesRaw[] = [
//                            'id'     => \Illuminate\Support\Arr::get($c, 'id'),
//                            'name'   => $name,
//                            'amount' => round($amount, 2),
//                        ];
//                    }
//                }
//            }
//        }
//        $charge_total = round(array_sum(array_map(fn($c) => (float)$c['amount'], $chargesRaw)), 2);
//
//        // 4) Recompute totals (authoritative, JS ke hisaab se)
//        $subtotal  = 0.0;
//        $taxTotal  = 0.0;
//        $cleanRows = [];
//
//        foreach ($rows as $i => $row) {
//            $desc    = trim($row['description'] ?? '');
//            $sac     = trim($row['sac'] ?? '');
//
//            // qty & amounts
//            $qty     = (float)($row['qty'] ?? 0);
//            $price   = (float)($row['price'] ?? 0);            // metal price (or full price per unit)
//            $making  = (float)($row['making_charge'] ?? 0);    // making per unit
//            $stone   = (float)($row['stone_charges'] ?? 0);    // stone per unit
//            $disc    = (float)($row['discount'] ?? 0);         // discount total (line level)
//            $taxPct  = (float)($row['tax_percent'] ?? 0);
//            $item_id = $row['item_id'] ?? null;
//
//            // jewellery extras (DB me preserve karne ke liye)
//            $metal_type   = $row['metal_type']   ?? null;
//            $purity       = $row['purity']       ?? null;
//            $metal_weight = (float)($row['metal_weight'] ?? 0);
//            $metal_rate   = (float)($row['metal_rate'] ?? 0);
//
//            if ($desc === '' || $qty <= 0 || $price < 0 || $making < 0 || $stone < 0 || $disc < 0 || $taxPct < 0) {
//                return back()
//                    ->withErrors(['items' => "Row ".($i+1)." invalid है."])
//                    ->withInput();
//            }
//
//            // ✅ JS jaisa calculation:
//            // basePerUnit = price + making_charge + stone_charges
//            // base = max(0, qty * basePerUnit - discount)
//            $basePerUnit = max(0, $price + $making + $stone);
//            $lineBase    = max(0, ($qty * $basePerUnit) - $disc);
//            $lineTax     = $lineBase * ($taxPct / 100);
//            $lineAmt     = round($lineBase + $lineTax, 2);
//
//            $subtotal += $lineBase;
//            $taxTotal += $lineTax;
//
//            $clean = [
//                'description'    => $desc,
//                'sac_code'       => $sac ?: null,
//                'quantity'       => $qty,
//                'rate'           => $price,
//                'making_charge'  => $making,
//                'stone_charges'  => $stone,
//                'discount'       => $disc,
//                'tax_percent'    => $taxPct,
//                'amount'         => $lineAmt,
//
//                // jewellery fields persist
//                'metal_type'     => $metal_type ?: null,
//                'purity'         => $purity ?: null,
//                'metal_weight'   => $metal_weight,
//                'metal_rate'     => $metal_rate,
//            ];
//            if ($item_id) {
//                $clean['item_id'] = $item_id;
//            }
//            $cleanRows[] = $clean;
//        }
//
//        $discount_total = (float)($data['discount_total'] ?? 0);
//        $tcs_percent    = (float)($data['tcs_percent'] ?? 0);
//        $round_off_in   = (float)($data['round_off'] ?? 0);
//        $received       = (float)($data['amount_received'] ?? 0);
//
//        $tcs_base   = max(0, $subtotal + $taxTotal - $discount_total);
//        $tcs_amount = round($tcs_base * ($tcs_percent / 100), 2);
//
//        $total_before_round = round($subtotal + $taxTotal - $discount_total + $charge_total + $tcs_amount, 2);
//        $grand_total        = round($total_before_round + $round_off_in, 2);
//        $balance            = round(max(0, $grand_total - $received), 2);
//
//        // 5) Persist: replace items & charges; don't change invoice_number
//        try {
//            \DB::transaction(function () use (
//                $invoice, $data, $invoiceDate, $paymentTerms, $dueDate,
//                $prefix, $subtotal, $taxTotal, $discount_total, $charge_total,
//                $tcs_percent, $tcs_amount, $round_off_in, $grand_total,
//                $received, $balance, $cleanRows, $chargesRaw, $r
//            ) {
//                // Header + totals update
//                $invoice->update([
//                    'client_id'       => $data['client_id'],
//                    'invoice_date'    => $invoiceDate,
//                    'invoice_prefix'  => $prefix, // number stays same
//                    'payment_terms'   => $paymentTerms,
//                    'due_date'        => $dueDate,
//
//                    'subtotal'        => $subtotal,
//                    'tax_amount'      => $taxTotal,
//                    'discount_total'  => $discount_total,
//                    'charge_total'    => $charge_total,
//                    'tcs_percent'     => $tcs_percent,
//                    'tcs_amount'      => $tcs_amount,
//                    'round_off'       => $round_off_in,
//                    'total'           => $grand_total,
//                    'received_amount' => $received,
//                    'balance'         => $balance,
//
//                    'payment_method'  => $data['payment_method'] ?? null,
//                    'notes'           => $data['notes'] ?? null,
//                    'terms'           => $data['terms'] ?? null,
//
//                    // front-end ka raw JSON store, jisme saare jewellery fields bhi hain
//                    'items_json'      => $r->input('items_json'),
//                    'charges_json'    => !empty($chargesRaw) ? json_encode($chargesRaw) : null,
//                ]);
//
//                // Replace items
//                if (method_exists($invoice, 'items')) {
//                    $invoice->items()->delete();
//                    foreach ($cleanRows as $row) {
//                        $row['invoice_id'] = $invoice->id;
//                        \App\Models\InvoiceItem::create($row);
//                    }
//                }
//
//                // Replace additional charges if relation exists
//                if (class_exists(\App\Models\InvoiceCharge::class) && method_exists($invoice, 'additionalCharges')) {
//                    $invoice->additionalCharges()->delete();
//                    foreach ($chargesRaw as $c) {
//                        \App\Models\InvoiceCharge::create([
//                            'invoice_id'           => $invoice->id,
//                            'additional_charge_id' => $c['id'] ?? null,  // 🔹 alias ke hisaab se
//                            'name'                 => $c['name'],
//                            'amount'               => $c['amount'],
//                        ]);
//                    }
//                }
//            });
//        } catch (\Throwable $e) {
//            report($e);
//            return back()
//                ->withErrors(['invoice' => 'Invoice update करते समय error आया: '.$e->getMessage()])
//                ->withInput();
//        }
//
//        return redirect()
//            ->route('invoices.index')
//            ->with('success', 'Invoice updated successfully.');
//    }




//    public function update(Request $r, \App\Models\Invoice $invoice)
//    {
//        // 1) Basic validation (prefix/date editable per your UI)
//        $data = $r->validate([
//            'client_id'       => ['required','exists:clients,id'],
//            'invoice_date'    => ['required','date'],
//            'invoice_prefix'  => ['nullable','string','max:100'],
//
//            'payment_terms'   => ['nullable','integer','min:0','max:365'],
//            'due_date'        => ['nullable','date'],
//            'notes'           => ['nullable','string','max:2000'],
//            'terms'           => ['nullable','string','max:2000'],
//
//            'discount_total'  => ['nullable','numeric','min:0'],
//            'charge_total'    => ['nullable','numeric','min:0'], // ignored; recompute
//            'tcs_percent'     => ['nullable','numeric','min:0','max:100'],
//            'round_off'       => ['nullable','numeric'], // +/- allowed
//            'amount_received' => ['nullable','numeric','min:0'],
//            'payment_method'  => ['nullable','string','max:50'],
//
//            'items_json'      => ['required','string'],  // [{ item_id?, description, sac, qty, price, making_charge, stone_charges, discount, tax_percent, amount }]
//            'charges_json'    => ['nullable','string'],  // [{ id?, name, amount }]
//        ]);
//
//        $invoiceDate  = \Carbon\Carbon::parse($data['invoice_date'])->toDateString();
//
//        // Keep prefix/number; allow prefix change if UI sent (read-only in your UI though)
//        $prefix       = trim($data['invoice_prefix'] ?? $invoice->invoice_prefix ?? '');
//        if ($prefix === '') {
//            $prefix = $invoice->invoice_prefix ?? 'INV/';
//        }
//
//        $paymentTerms = (int)($data['payment_terms'] ?? 0);
//        $dueDate      = $data['due_date'] ?? \Carbon\Carbon::parse($invoiceDate)->addDays($paymentTerms)->toDateString();
//
//        // 2) Decode rows
//        $rows = json_decode($data['items_json'], true);
//        if (!is_array($rows) || count($rows) < 1) {
//            return back()
//                ->withErrors(['items' => 'कम से कम 1 line item जरूरी है.'])
//                ->withInput();
//        }
//
//        // 3) Decode charges and recompute total
//        $chargesRaw = [];
//        if (filled($data['charges_json'] ?? '')) {
//            $decoded = json_decode($data['charges_json'], true);
//            if (is_array($decoded)) {
//                foreach ($decoded as $c) {
//                    $name   = trim(\Illuminate\Support\Arr::get($c, 'name', ''));
//                    $amount = (float)\Illuminate\Support\Arr::get($c, 'amount', 0);
//                    if ($name !== '' && $amount >= 0) {
//                        $chargesRaw[] = [
//                            'id'     => \Illuminate\Support\Arr::get($c, 'id'),
//                            'name'   => $name,
//                            'amount' => round($amount, 2),
//                        ];
//                    }
//                }
//            }
//        }
//        $charge_total = round(array_sum(array_map(fn($c) => (float)$c['amount'], $chargesRaw)), 2);
//
//        // 4) Recompute totals (authoritative, JS ke hisaab se)
//        $subtotal  = 0.0;
//        $taxTotal  = 0.0;
//        $cleanRows = [];
//
//        foreach ($rows as $i => $row) {
//            $desc    = trim($row['description'] ?? '');
//            $sac     = trim($row['sac'] ?? '');
//
//            // qty & amounts
//            $qty     = (float)($row['qty'] ?? 0);
//            $price   = (float)($row['price'] ?? 0);            // metal price (or full price per unit)
//            $making  = (float)($row['making_charge'] ?? 0);    // making per unit
//            $stone   = (float)($row['stone_charges'] ?? 0);    // stone per unit
//            $disc    = (float)($row['discount'] ?? 0);         // discount total (line level)
//            $taxPct  = (float)($row['tax_percent'] ?? 0);
//            $item_id = $row['item_id'] ?? null;
//
//            // jewellery extras (DB me preserve karne ke liye)
//            $metal_type   = $row['metal_type']   ?? null;
//            $purity       = $row['purity']       ?? null;
//            $metal_weight = (float)($row['metal_weight'] ?? 0);
//            $metal_rate   = (float)($row['metal_rate'] ?? 0);
//
//            if ($desc === '' || $qty <= 0 || $price < 0 || $making < 0 || $stone < 0 || $disc < 0 || $taxPct < 0) {
//                return back()
//                    ->withErrors(['items' => "Row ".($i+1)." invalid है."])
//                    ->withInput();
//            }
//
//            // ✅ JS jaisa calculation:
//            // basePerUnit = price + making_charge + stone_charges
//            // base = max(0, qty * basePerUnit - discount)
//            $basePerUnit = max(0, $price + $making + $stone);
//            $lineBase    = max(0, ($qty * $basePerUnit) - $disc);
//            $lineTax     = $lineBase * ($taxPct / 100);
//            $lineAmt     = round($lineBase + $lineTax, 2);
//
//            $subtotal += $lineBase;
//            $taxTotal += $lineTax;
//
//            $clean = [
//                'description'    => $desc,
//                'sac_code'       => $sac ?: null,
//                'quantity'       => $qty,
//                'rate'           => $price,
//                'making_charge'  => $making,
//                'stone_charges'  => $stone,
//                'discount'       => $disc,
//                'tax_percent'    => $taxPct,
//                'amount'         => $lineAmt,
//
//                // jewellery fields persist
//                'metal_type'     => $metal_type ?: null,
//                'purity'         => $purity ?: null,
//                'metal_weight'   => $metal_weight,
//                'metal_rate'     => $metal_rate,
//            ];
//            if ($item_id) {
//                $clean['item_id'] = $item_id;
//            }
//            $cleanRows[] = $clean;
//        }
//
//        $discount_total = (float)($data['discount_total'] ?? 0);
//        $tcs_percent    = (float)($data['tcs_percent'] ?? 0);
//        $round_off_in   = (float)($data['round_off'] ?? 0);
//        $received       = (float)($data['amount_received'] ?? 0);
//
//        $tcs_base   = max(0, $subtotal + $taxTotal - $discount_total);
//        $tcs_amount = round($tcs_base * ($tcs_percent / 100), 2);
//
//        $total_before_round = round($subtotal + $taxTotal - $discount_total + $charge_total + $tcs_amount, 2);
//        $grand_total        = round($total_before_round + $round_off_in, 2);
//        $balance            = round(max(0, $grand_total - $received), 2);
//
//        // 5) Persist: replace items & charges; don't change invoice_number
//        try {
//            DB::transaction(function () use (
//                $invoice, $data, $invoiceDate, $paymentTerms, $dueDate,
//                $prefix, $subtotal, $taxTotal, $discount_total, $charge_total,
//                $tcs_percent, $tcs_amount, $round_off_in, $grand_total,
//                $received, $balance, $cleanRows, $chargesRaw, $r
//            ) {
//                // Header + totals update
//                $invoice->update([
//                    'client_id'       => $data['client_id'],
//                    'invoice_date'    => $invoiceDate,
//                    'invoice_prefix'  => $prefix, // number stays same
//                    'payment_terms'   => $paymentTerms,
//                    'due_date'        => $dueDate,
//
//                    'subtotal'        => $subtotal,
//                    'tax_amount'      => $taxTotal,
//                    'discount_total'  => $discount_total,
//                    'charge_total'    => $charge_total,
//                    'tcs_percent'     => $tcs_percent,
//                    'tcs_amount'      => $tcs_amount,
//                    'round_off'       => $round_off_in,
//                    'total'           => $grand_total,
//                    'received_amount' => $received,
//                    'balance'         => $balance,
//
//                    'payment_method'  => $data['payment_method'] ?? null,
//                    'notes'           => $data['notes'] ?? null,
//                    'terms'           => $data['terms'] ?? null,
//
//                    // front-end ka raw JSON store, jisme saare jewellery fields bhi hain
//                    'items_json'      => $r->input('items_json'),
//                    'charges_json'    => !empty($chargesRaw) ? json_encode($chargesRaw) : null,
//                ]);
//
//                // Replace items
//                if (method_exists($invoice, 'items')) {
//                    $invoice->items()->delete();
//                    foreach ($cleanRows as $row) {
//                        $row['invoice_id'] = $invoice->id;
//                        \App\Models\InvoiceItem::create($row);
//                    }
//                }
//
//                // Replace additional charges if relation exists
//                if (class_exists(\App\Models\InvoiceCharge::class) && method_exists($invoice, 'additionalCharges')) {
//                    $invoice->additionalCharges()->delete();
//                    foreach ($chargesRaw as $c) {
//                        \App\Models\InvoiceCharge::create([
//                            'invoice_id'           => $invoice->id,
//                            'additional_charge_id' => $c['id'] ?? null,
//                            'name'                 => $c['name'],
//                            'amount'               => $c['amount'],
//                        ]);
//                    }
//                }
//            });
//        } catch (\Throwable $e) {
//            report($e);
//            return back()
//                ->withErrors(['invoice' => 'Invoice update करते समय error आया: '.$e->getMessage()])
//                ->withInput();
//        }
//
//        return redirect()
//            ->route('invoices.index')
//            ->with('success', 'Invoice updated successfully.');
//    }


//    public function update(Request $r, \App\Models\Invoice $invoice)
//    {
//        // 1) Basic validation (prefix/date editable per your UI)
//        $data = $r->validate([
//            'client_id'       => ['required','exists:clients,id'],
//            'invoice_date'    => ['required','date'],
//            'invoice_prefix'  => ['nullable','string','max:100'],
//
//            'payment_terms'   => ['nullable','integer','min:0','max:365'],
//            'due_date'        => ['nullable','date'],
//            'notes'           => ['nullable','string','max:2000'],
//            'terms'           => ['nullable','string','max:2000'],
//
//            'discount_total'  => ['nullable','numeric','min:0'],
//            'charge_total'    => ['nullable','numeric','min:0'], // ignored; recompute
//            'tcs_percent'     => ['nullable','numeric','min:0','max:100'],
//            'round_off'       => ['nullable','numeric'], // +/- allowed
//            'amount_received' => ['nullable','numeric','min:0'],
//            'payment_method'  => ['nullable','string','max:50'],
//
//            'items_json'      => ['required','string'],  // [{ item_id?, description, sac, qty, price, making_charge, stone_charges, discount, tax_percent, amount }]
//            'charges_json'    => ['nullable','string'],  // [{ id?, name, amount }]
//        ]);
//
//        $invoiceDate  = \Carbon\Carbon::parse($data['invoice_date'])->toDateString();
//
//        // Keep prefix/number; allow prefix change if UI sent (read-only in your UI though)
//        $prefix       = trim($data['invoice_prefix'] ?? $invoice->invoice_prefix ?? '');
//        if ($prefix === '') {
//            $prefix = $invoice->invoice_prefix ?? 'INV/';
//        }
//
//        $paymentTerms = (int)($data['payment_terms'] ?? 0);
//        $dueDate      = $data['due_date'] ?? \Carbon\Carbon::parse($invoiceDate)->addDays($paymentTerms)->toDateString();
//
//        // 2) Decode rows
//        $rows = json_decode($data['items_json'], true);
//        if (!is_array($rows) || count($rows) < 1) {
//            return back()
//                ->withErrors(['items' => 'कम से कम 1 line item जरूरी है.'])
//                ->withInput();
//        }
//
//        // 3) Decode charges and recompute total
//        $chargesRaw = [];
//        if (filled($data['charges_json'] ?? '')) {
//            $decoded = json_decode($data['charges_json'], true);
//            if (is_array($decoded)) {
//                foreach ($decoded as $c) {
//                    $name   = trim(\Illuminate\Support\Arr::get($c, 'name', ''));
//                    $amount = (float)\Illuminate\Support\Arr::get($c, 'amount', 0);
//                    if ($name !== '' && $amount >= 0) {
//                        $chargesRaw[] = [
//                            'id'     => \Illuminate\Support\Arr::get($c, 'id'),
//                            'name'   => $name,
//                            'amount' => round($amount, 2),
//                        ];
//                    }
//                }
//            }
//        }
//        $charge_total = round(array_sum(array_map(fn($c) => (float)$c['amount'], $chargesRaw)), 2);
//
//        // 4) Recompute totals (authoritative, JS ke hisaab se)
//        $subtotal  = 0.0;
//        $taxTotal  = 0.0;
//        $cleanRows = [];
//
//        foreach ($rows as $i => $row) {
//            $desc    = trim($row['description'] ?? '');
//            $sac     = trim($row['sac'] ?? '');
//
//            // qty & amounts
//            $qty     = (float)($row['qty'] ?? 0);
//            $price   = (float)($row['price'] ?? 0);            // metal price (or full price per unit)
//            $making  = (float)($row['making_charge'] ?? 0);    // making per unit
//            $stone   = (float)($row['stone_charges'] ?? 0);    // stone per unit
//            $disc    = (float)($row['discount'] ?? 0);         // discount total (line level)
//            $taxPct  = (float)($row['tax_percent'] ?? 0);
//            $item_id = $row['item_id'] ?? null;
//
//            // jewellery extras (DB me preserve karne ke liye)
//            $metal_type   = $row['metal_type']   ?? null;
//            $purity       = $row['purity']       ?? null;
//            $metal_weight = (float)($row['metal_weight'] ?? 0);
//            $metal_rate   = (float)($row['metal_rate'] ?? 0);
//
//            if ($desc === '' || $qty <= 0 || $price < 0 || $making < 0 || $stone < 0 || $disc < 0 || $taxPct < 0) {
//                return back()
//                    ->withErrors(['items' => "Row ".($i+1)." invalid है."])
//                    ->withInput();
//            }
//
//            // ✅ JS jaisa calculation:
//            // basePerUnit = price + making_charge + stone_charges
//            // base = max(0, qty * basePerUnit - discount)
//            $basePerUnit = max(0, $price + $making + $stone);
//            $lineBase    = max(0, ($qty * $basePerUnit) - $disc);
//            $lineTax     = $lineBase * ($taxPct / 100);
//            $lineAmt     = round($lineBase + $lineTax, 2);
//
//            $subtotal += $lineBase;
//            $taxTotal += $lineTax;
//
//            $clean = [
//                'description'    => $desc,
//                'sac_code'       => $sac ?: null,
//                'quantity'       => $qty,
//                'rate'           => $price,
//                'making_charge'  => $making,
//                'stone_charges'  => $stone,
//                'discount'       => $disc,
//                'tax_percent'    => $taxPct,
//                'amount'         => $lineAmt,
//
//                // jewellery fields persist
//                'metal_type'     => $metal_type ?: null,
//                'purity'         => $purity ?: null,
//                'metal_weight'   => $metal_weight,
//                'metal_rate'     => $metal_rate,
//            ];
//            if ($item_id) {
//                $clean['item_id'] = $item_id;
//            }
//            $cleanRows[] = $clean;
//        }
//
//        $discount_total = (float)($data['discount_total'] ?? 0);
//        $tcs_percent    = (float)($data['tcs_percent'] ?? 0);
//        $round_off_in   = (float)($data['round_off'] ?? 0);
//        $received       = (float)($data['amount_received'] ?? 0);
//
//        $tcs_base   = max(0, $subtotal + $taxTotal - $discount_total);
//        $tcs_amount = round($tcs_base * ($tcs_percent / 100), 2);
//
//        $total_before_round = round($subtotal + $taxTotal - $discount_total + $charge_total + $tcs_amount, 2);
//        $grand_total        = round($total_before_round + $round_off_in, 2);
//        $balance            = round(max(0, $grand_total - $received), 2);
//
//        // 5) Persist with safe stock adjustment (DIFFERENCE based)
//        try {
//            DB::transaction(function () use (
//                $invoice, $data, $invoiceDate, $paymentTerms, $dueDate,
//                $prefix, $subtotal, $taxTotal, $discount_total, $charge_total,
//                $tcs_percent, $tcs_amount, $round_off_in, $grand_total,
//                $received, $balance, $cleanRows, $chargesRaw, $r
//            ) {
//                // 5.1) Pehle purane items ka total qty per item_id nikaalo
//                $oldLines = $invoice->items()
//                    ->select('item_id', DB::raw('SUM(quantity) as qty'))
//                    ->whereNotNull('item_id')
//                    ->groupBy('item_id')
//                    ->pluck('qty', 'item_id');   // [item_id => qty]
//
//                // 5.2) Header + totals update
//                $invoice->update([
//                    'client_id'       => $data['client_id'],
//                    'invoice_date'    => $invoiceDate,
//                    'invoice_prefix'  => $prefix, // number stays same
//                    'payment_terms'   => $paymentTerms,
//                    'due_date'        => $dueDate,
//
//                    'subtotal'        => $subtotal,
//                    'tax_amount'      => $taxTotal,
//                    'discount_total'  => $discount_total,
//                    'charge_total'    => $charge_total,
//                    'tcs_percent'     => $tcs_percent,
//                    'tcs_amount'      => $tcs_amount,
//                    'round_off'       => $round_off_in,
//                    'total'           => $grand_total,
//                    'received_amount' => $received,
//                    'balance'         => $balance,
//
//                    'payment_method'  => $data['payment_method'] ?? null,
//                    'notes'           => $data['notes'] ?? null,
//                    'terms'           => $data['terms'] ?? null,
//
//                    // front-end ka raw JSON store, jisme saare jewellery fields bhi hain
//                    'items_json'      => $r->input('items_json'),
//                    'charges_json'    => !empty($chargesRaw) ? json_encode($chargesRaw) : null,
//                ]);
//
//                // 5.3) Replace items
//                if (method_exists($invoice, 'items')) {
//                    $invoice->items()->delete();
//                    foreach ($cleanRows as $row) {
//                        $row['invoice_id'] = $invoice->id;
//                        \App\Models\InvoiceItem::create($row);
//                    }
//                }
//
//                // 5.4) Replace additional charges if relation exists
//                if (class_exists(\App\Models\InvoiceCharge::class) && method_exists($invoice, 'additionalCharges')) {
//                    $invoice->additionalCharges()->delete();
//                    foreach ($chargesRaw as $c) {
//                        \App\Models\InvoiceCharge::create([
//                            'invoice_id'           => $invoice->id,
//                            'additional_charge_id' => $c['id'] ?? null,
//                            'name'                 => $c['name'],
//                            'amount'               => $c['amount'],
//                        ]);
//                    }
//                }
//
//                // 5.5) Ab new lines ka total qty per item_id nikaalo (cleanRows se)
//                $newLines = collect($cleanRows)
//                    ->filter(fn($row) => !empty($row['item_id']))
//                    ->groupBy('item_id')
//                    ->map(fn($rows) => collect($rows)->sum('quantity')); // [item_id => qty]
//
//                // 5.6) Stock ko "difference" se adjust karo
//                // change = oldQty - newQty
//                $allItemIds = $oldLines->keys()->merge($newLines->keys())->unique();
//
//                foreach ($allItemIds as $itemId) {
//                    if (!$itemId) {
//                        continue;
//                    }
//
//                    $oldQty = (float)($oldLines[$itemId] ?? 0);
//                    $newQty = (float)($newLines[$itemId] ?? 0);
//
//                    // Agar qty same hai to stock ki zarurat nahi
//                    if ($oldQty == $newQty) {
//                        continue;
//                    }
//
//                    $item = \App\Models\Item::find($itemId);
//                    if (!$item) {
//                        continue;
//                    }
//
//                    $current  = (float)($item->stock_qty ?? 0);
//                    $delta    = $oldQty - $newQty;      // old > new -> stock wapas add
//                    // old < new -> stock minus
//                    $newStock = $current + $delta;
//
//                    if ($newStock < 0) {
//                        $newStock = 0;
//                    }
//
//                    $item->stock_qty = $newStock;
//                    $item->save();
//                }
//            });
//        } catch (\Throwable $e) {
//            report($e);
//            return back()
//                ->withErrors(['invoice' => 'Invoice update करते समय error आया: '.$e->getMessage()])
//                ->withInput();
//        }
//
//        return redirect()
//            ->route('invoices.index')
//            ->with('success', 'Invoice updated successfully.');
//    }






    public function destroy(Invoice $invoice)
    {
        $invoice->delete();
        return back()->with('success','Deleted.');
    }

    public function download(\App\Models\Invoice $invoice)
    {
        // relations
        $invoice->load(['client','items','business']);

        $biz    = $invoice->business;
        $client = $invoice->client;
        $items  = $invoice->items ?? collect();

        // charges: try relation if it exists, else fallback to charges_json
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

        // --- totals (server-side recompute, same as store() & JS) ---
        $subtotal = 0.0;
        $taxTotal = 0.0;

        foreach ($items as $it) {
            $qty    = (float) ($it->quantity ?? 0);
            $rate   = (float) ($it->rate ?? 0);             // metal value per unit
            $mk     = (float) ($it->making_charge ?? 0);    // making per unit
            $stone  = (float) ($it->stone_charges ?? 0);    // stone per unit / per line (we treat as per unit in same way as JS)
            $disc   = (float) ($it->discount ?? 0);         // line discount
            $tp     = (float) ($it->tax_percent ?? 0);

            $basePerUnit = $rate + $mk + $stone;
            $lineBase    = max(0, ($qty * $basePerUnit) - $disc);
            $lineTax     = $lineBase * ($tp / 100);

            $subtotal += $lineBase;
            $taxTotal += $lineTax;
        }

        $discountTotal = (float) ($invoice->discount_total ?? 0.0); // extra invoice-level discount
        $chargesTotal  = (float) $charges->reduce(
            fn($s, $r) => $s + (float) ($r->amount ?? 0),
            0.0
        );
        $tcsPercent    = (float) ($invoice->tcs_percent ?? 0.0);
        $received      = (float) ($invoice->received_amount ?? 0.0);
        $roundOff      = (float) ($invoice->round_off ?? 0.0);

        $tcsBase   = max(0, $subtotal + $taxTotal - $discountTotal);
        $tcsAmount = $tcsPercent > 0
            ? round($tcsBase * ($tcsPercent / 100), 2)
            : 0.0;

        $totalBeforeRound = $subtotal + $taxTotal - $discountTotal + $chargesTotal + $tcsAmount;
        $grandTotal       = (float) ($totalBeforeRound + $roundOff);
        $balance          = max(0, $grandTotal - $received);

        // data URIs
        [$logoDataUri, $signDataUri] = [
            $this->imageDataUri($biz?->logo),
            $this->imageDataUri($biz?->signature),
        ];

        $vm = compact(
            'invoice','biz','client','items','charges',
            'logoDataUri','signDataUri',
            'subtotal','taxTotal','discountTotal','chargesTotal','tcsPercent','tcsAmount',
            'roundOff','grandTotal','received','balance'
        );

        // rename keys to what blade expects
        $vm['inv']            = $invoice;
        $vm['logo']           = $logoDataUri;
        $vm['sign']           = $signDataUri;
        $vm['tax_total']      = $taxTotal;
        $vm['discount_total'] = $discountTotal;
        $vm['charges_total']  = $chargesTotal;
        $vm['tcs_percent']    = $tcsPercent;
        $vm['tcs_amount']     = $tcsAmount;
        $vm['grand_total']    = $grandTotal;

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('invoices.pdf', $vm)->setPaper('a4');

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





    public function previewNumber(Request $r)
    {
        $bid = $r->user()->current_business_id ?? session('active_business_id');
        if (!$bid) {
            return response()->json(['ok'=>false,'message'=>'No active business.'], 422);
        }

        $date   = $r->input('invoice_date');
        $prefix = $r->input('invoice_prefix');

        if (!$date) {
            return response()->json(['ok'=>false,'message'=>'invoice_date required'], 422);
        }

        // prefix optional → fallback to basePrefix+FY
        if (!$prefix) {
            $base   = optional(
                    $r->user()->businesses()->where('businesses.id',$bid)->first()
                )->invoice_base_prefix ?? 'RV/SL';
            $prefix = InvoiceNumber::previewPrefix($date, $base);
        }

        try {
            $peek = InvoiceNumber::peek((int)$bid, $date, $prefix, 3);
            return response()->json([
                'ok'     => true,
                'number' => $peek['full'],
                'prefix' => $peek['prefix'],
                'seq'    => $peek['seq'],
            ]);
        } catch (\Throwable $e) {
            return response()->json(['ok'=>false,'message'=>$e->getMessage()], 422);
        }
    }


//    public function show(\App\Models\Invoice $invoice)
//    {
//        $invoice->load(['client','items','business']);
//
//        // Additional charges (relation हो तो उससे, वरना JSON fallback)
//        if (method_exists($invoice, 'additionalCharges')) {
//            $charges = $invoice->additionalCharges()->get(['name','amount']);
//        } else {
//            $arr = [];
//            if (!empty($invoice->charges_json)) {
//                $decoded = json_decode($invoice->charges_json, true);
//                if (is_array($decoded)) {
//                    foreach ($decoded as $c) {
//                        $arr[] = (object)[
//                            'name'   => (string)($c['name'] ?? ''),
//                            'amount' => (float) ($c['amount'] ?? 0),
//                        ];
//                    }
//                }
//            }
//            $charges = collect($arr);
//        }
//
//        // same helper you used for PDF
//        [$logoDataUri, $signDataUri] = [
//            $this->imageDataUri($invoice->business->logo),
//            $this->imageDataUri($invoice->business->signature),
//        ];
//
//        return view('invoices.show', [
//            'inv'     => $invoice,
//            'charges' => $charges,
//            'logo'    => $logoDataUri,   // null or "data:image/...;base64,..."
//            'sign'    => $signDataUri,
//        ]);
//    }


    public function show(\App\Models\Invoice $invoice)
    {
        $invoice->load(['client', 'items', 'business']);

        $items = $invoice->items ?? collect();

        // --- Additional charges (relation हो तो उससे, वरना JSON fallback) ---
        if (method_exists($invoice, 'additionalCharges')) {
            $charges = $invoice->additionalCharges()->get(['name', 'amount']);
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

        // --- Totals (same formula as store() + download()) ---
        $subtotal = 0.0;
        $taxTotal = 0.0;

        foreach ($items as $it) {
            $qty    = (float)($it->quantity ?? 0);
            $rate   = (float)($it->rate ?? 0);             // metal value per unit
            $mk     = (float)($it->making_charge ?? 0);    // making per unit
            $stone  = (float)($it->stone_charges ?? 0);    // stone per unit
            $disc   = (float)($it->discount ?? 0);         // line discount
            $tp     = (float)($it->tax_percent ?? 0);

            $basePerUnit = $rate + $mk + $stone;
            $lineBase    = max(0, ($qty * $basePerUnit) - $disc);
            $lineTax     = $lineBase * ($tp / 100);

            $subtotal += $lineBase;
            $taxTotal += $lineTax;
        }

        $discountTotal = (float)($invoice->discount_total ?? 0.0);
        $chargesTotal  = (float)$charges->reduce(
            fn ($s, $r) => $s + (float)($r->amount ?? 0),
            0.0
        );
        $tcsPercent    = (float)($invoice->tcs_percent ?? 0.0);
        $roundOff      = (float)($invoice->round_off ?? 0.0);
        $received      = (float)($invoice->received_amount ?? 0.0);

        $tcsBase   = max(0, $subtotal + $taxTotal - $discountTotal);
        $tcsAmount = $tcsPercent > 0
            ? round($tcsBase * ($tcsPercent / 100), 2)
            : 0.0;

        $totalBeforeRound = $subtotal + $taxTotal - $discountTotal + $chargesTotal + $tcsAmount;
        $grandTotal       = (float)($totalBeforeRound + $roundOff);
        $balance          = max(0, $grandTotal - $received);

        // same helper you used for PDF
        [$logoDataUri, $signDataUri] = [
            $this->imageDataUri($invoice->business->logo ?? null),
            $this->imageDataUri($invoice->business->signature ?? null),
        ];

        return view('invoices.show', [
            'inv'            => $invoice,
            'charges'        => $charges,
            'logo'           => $logoDataUri,
            'sign'           => $signDataUri,

            // totals for Blade
            'subtotal'       => $subtotal,
            'taxTotal'       => $taxTotal,
            'discountTotal'  => $discountTotal,
            'chargesTotal'   => $chargesTotal,
            'tcsPercent'     => $tcsPercent,
            'tcsAmount'      => $tcsAmount,
            'roundOff'       => $roundOff,
            'grandTotal'     => $grandTotal,
            'received'       => $received,
            'balance'        => $balance,
        ]);
    }


    public function store(Request $r)
    {
        $bid = $r->user()->current_business_id ?? session('active_business_id');
        if (!$bid) {
            return back()
                ->withErrors(['business' => 'Active business select/attach नहीं है.'])
                ->withInput();
        }

        $data = $r->validate([
            'client_id'       => ['required','exists:clients,id'],
            'invoice_date'    => ['required','date'],
            'invoice_prefix'  => ['nullable','string','max:100'],

            'payment_terms'   => ['nullable','integer','min:0','max:365'],
            'due_date'        => ['nullable','date'],
            'notes'           => ['nullable','string','max:2000'],
            'terms'           => ['nullable','string','max:2000'],

            'discount_total'  => ['nullable','numeric','min:0'],
            'charge_total'    => ['nullable','numeric','min:0'], // ignored; recompute
            'tcs_percent'     => ['nullable','numeric','min:0','max:100'],
            'round_off'       => ['nullable','numeric'], // +/- allowed
            'amount_received' => ['nullable','numeric','min:0'],
            'payment_method'  => ['nullable','string','max:50'],

            'items_json'      => ['required','string'],
            'charges_json'    => ['nullable','string'],
        ]);

        // FY prefix compute helper
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

        $invoiceDate  = \Carbon\Carbon::parse($data['invoice_date'])->toDateString();
        $prefix       = trim($data['invoice_prefix'] ?? '');
        if ($prefix === '') {
            $prefix = $computePrefix($invoiceDate, 'INV');
        }

        $paymentTerms = (int)($data['payment_terms'] ?? 0);
        $dueDate      = $data['due_date']
            ?? \Carbon\Carbon::parse($invoiceDate)->addDays($paymentTerms)->toDateString();

        // -------- ITEMS JSON PARSE --------
        $rows = json_decode($data['items_json'], true);
        if (!is_array($rows) || count($rows) < 1) {
            return back()
                ->withErrors(['items' => 'कम से कम 1 line item जरूरी है.'])
                ->withInput();
        }

        // -------- CHARGES JSON PARSE --------
        $chargesRaw = [];
        if (filled($data['charges_json'] ?? '')) {
            $decoded = json_decode($data['charges_json'], true);
            if (is_array($decoded)) {
                foreach ($decoded as $c) {
                    $name   = trim(Arr::get($c, 'name', ''));
                    $amount = (float)Arr::get($c, 'amount', 0);
                    if ($name !== '' && $amount >= 0) {
                        $chargesRaw[] = [
                            'id'     => Arr::get($c, 'id'),
                            'name'   => $name,
                            'amount' => round($amount, 2),
                        ];
                    }
                }
            }
        }
        $charge_total = round(array_sum(array_map(
            fn($c) => (float)$c['amount'],
            $chargesRaw
        )), 2);

        // -------- TOTALS (PHP == JS LOGIC) --------
        $subtotal  = 0.0;
        $taxTotal  = 0.0;
        $cleanRows = [];

        foreach ($rows as $i => $row) {
            $desc    = trim($row['description'] ?? '');
            $sac     = trim($row['sac'] ?? '');
            $qty     = (int)($row['qty'] ?? 0);

            // metal fields from JS
            $metal_type    = $row['metal_type']    ?? null;
            $purity        = trim($row['purity']   ?? '');
            $metal_weight  = (float)($row['metal_weight']  ?? 0);
            $metal_rate    = (float)($row['metal_rate']    ?? 0);
            $stoneCharges  = (float)($row['stone_charges'] ?? 0);

            // price/making/discount/tax
            $price   = (float)($row['price'] ?? 0);           // metal value per unit
            $making  = (float)($row['making_charge'] ?? 0);   // per unit making
            $disc    = (float)($row['discount'] ?? 0);        // line discount
            $taxPct  = (float)($row['tax_percent'] ?? 0);
            $item_id = $row['item_id'] ?? null;

            if (
                $desc === '' || $qty < 1 ||
                $price < 0 || $making < 0 || $disc < 0 || $taxPct < 0 ||
                $metal_weight < 0 || $metal_rate < 0 || $stoneCharges < 0
            ) {
                return back()
                    ->withErrors(['items' => "Row ".($i+1)." invalid है."])
                    ->withInput();
            }

            // JS logic:
            // basePerUnit = price + making_charge + stone_charges
            // base        = max(0, qty * basePerUnit - discount)
            // tax         = base * (tax_percent/100)
            $basePerUnit = $price + $making + $stoneCharges;
            $lineBase    = max(0, ($qty * $basePerUnit) - $disc);
            $lineTax     = $lineBase * ($taxPct / 100);
            $lineAmt     = round($lineBase + $lineTax, 2);

            $subtotal += $lineBase;
            $taxTotal += $lineTax;

            $clean = [
                'description'    => $desc,
                'sac_code'       => $sac ?: null,
                'quantity'       => $qty,

                // metal info persist in invoice_items
                'metal_type'     => $metal_type ?: null,
                'purity'         => $purity ?: null,
                'metal_weight'   => $metal_weight ?: null,
                'metal_rate'     => $metal_rate ?: null,
                'stone_charges'  => $stoneCharges,

                // pricing
                'rate'           => $price,
                'making_charge'  => $making,
                'discount'       => $disc,
                'tax_percent'    => $taxPct,
                'amount'         => $lineAmt,
            ];
            if ($item_id) {
                $clean['item_id'] = $item_id;
            }

            $cleanRows[] = $clean;
        }

        // -------- FINAL TOTAL FIELDS --------
        $discount_total = (float)($data['discount_total'] ?? 0);
        $tcs_percent    = (float)($data['tcs_percent'] ?? 0);
        $round_off_in   = (float)($data['round_off'] ?? 0);
        $received       = (float)($data['amount_received'] ?? 0);

        $tcs_base   = max(0, $subtotal + $taxTotal - $discount_total);
        $tcs_amount = round($tcs_base * ($tcs_percent / 100), 2);

        $total_before_round = round(
            $subtotal + $taxTotal - $discount_total + $charge_total + $tcs_amount,
            2
        );
        $grand_total = round($total_before_round + $round_off_in, 2);
        $balance     = round(max(0, $grand_total - $received), 2);

        try {
            DB::transaction(function () use (
                $bid, $r, $data, $prefix, $invoiceDate, $paymentTerms, $dueDate,
                $subtotal, $taxTotal, $discount_total, $charge_total, $tcs_percent, $tcs_amount,
                $round_off_in, $grand_total, $received, $balance, $cleanRows, $chargesRaw
            ) {
                $alloc = \App\Services\InvoiceNumber::next((int)$bid, $invoiceDate, $prefix, 3);

                /** @var \App\Models\Invoice $invoice */
                $invoice = Invoice::create([
                    'business_id'     => $bid,
                    'client_id'       => $data['client_id'],
                    'invoice_date'    => $invoiceDate,

                    'invoice_prefix'  => $prefix,
                    'invoice_number'  => $alloc['full'],

                    'payment_terms'   => $paymentTerms,
                    'due_date'        => $dueDate,

                    'subtotal'        => $subtotal,
                    'tax_amount'      => $taxTotal,
                    'discount_total'  => $discount_total,
                    'charge_total'    => $charge_total,
                    'tcs_percent'     => $tcs_percent,
                    'tcs_amount'      => $tcs_amount,
                    'round_off'       => $round_off_in,
                    'total'           => $grand_total,
                    'received_amount' => $received,
                    'balance'         => $balance,

                    'payment_method'  => $data['payment_method'] ?? null,
                    'notes'           => $data['notes'] ?? null,
                    'terms'           => $data['terms'] ?? null,

                    'items_json'      => $r->input('items_json'),
                    'charges_json'    => !empty($chargesRaw) ? json_encode($chargesRaw) : null,

                    'amount_in_words' => '',
                ]);

                // line items
                foreach ($cleanRows as $row) {
                    $row['invoice_id'] = $invoice->id;
                    InvoiceItem::create($row);
                }

                // charges detail table (if model exists)
                if (class_exists(InvoiceCharge::class) && !empty($chargesRaw)) {
                    foreach ($chargesRaw as $c) {
                        InvoiceCharge::create([
                            'invoice_id' => $invoice->id,
                            'charge_id'  => $c['id'] ?? null,
                            'name'       => $c['name'],
                            'amount'     => $c['amount'],
                        ]);
                    }
                }

                // ⭐ INVENTORY UPDATE: StockService se movement banega
                $invoice->load('items.item');
                app(StockService::class)->recordSale($invoice);
            });
        } catch (\Throwable $e) {
            report($e);
            return back()
                ->withErrors(['invoice' => 'Invoice save करते समय error आया: '.$e->getMessage()])
                ->withInput();
        }

        return redirect()
            ->route('invoices.index')
            ->with('success', 'Invoice created successfully.');
    }



    public function update(Request $r, Invoice $invoice)
    {
        // 1) Basic validation
        $data = $r->validate([
            'client_id'       => ['required','exists:clients,id'],
            'invoice_date'    => ['required','date'],
            'invoice_prefix'  => ['nullable','string','max:100'],

            'payment_terms'   => ['nullable','integer','min:0','max:365'],
            'due_date'        => ['nullable','date'],
            'notes'           => ['nullable','string','max:2000'],
            'terms'           => ['nullable','string','max:2000'],

            'discount_total'  => ['nullable','numeric','min:0'],
            'charge_total'    => ['nullable','numeric','min:0'], // ignored; recompute
            'tcs_percent'     => ['nullable','numeric','min:0','max:100'],
            'round_off'       => ['nullable','numeric'], // +/- allowed
            'amount_received' => ['nullable','numeric','min:0'],
            'payment_method'  => ['nullable','string','max:50'],

            'items_json'      => ['required','string'],  // [{ item_id?, description, sac, qty, price, making_charge, stone_charges, discount, tax_percent, amount }]
            'charges_json'    => ['nullable','string'],  // [{ id?, name, amount }]
        ]);

        $invoiceDate  = \Carbon\Carbon::parse($data['invoice_date'])->toDateString();

        // prefix ko keep/change kar sakte ho
        $prefix       = trim($data['invoice_prefix'] ?? $invoice->invoice_prefix ?? '');
        if ($prefix === '') {
            $prefix = $invoice->invoice_prefix ?? 'INV/';
        }

        $paymentTerms = (int)($data['payment_terms'] ?? 0);
        $dueDate      = $data['due_date'] ?? \Carbon\Carbon::parse($invoiceDate)->addDays($paymentTerms)->toDateString();

        // 2) Decode rows
        $rows = json_decode($data['items_json'], true);
        if (!is_array($rows) || count($rows) < 1) {
            return back()
                ->withErrors(['items' => 'कम से कम 1 line item जरूरी है.'])
                ->withInput();
        }

        // 3) Decode charges and recompute total
        $chargesRaw = [];
        if (filled($data['charges_json'] ?? '')) {
            $decoded = json_decode($data['charges_json'], true);
            if (is_array($decoded)) {
                foreach ($decoded as $c) {
                    $name   = trim(Arr::get($c, 'name', ''));
                    $amount = (float)Arr::get($c, 'amount', 0);
                    if ($name !== '' && $amount >= 0) {
                        $chargesRaw[] = [
                            'id'     => Arr::get($c, 'id'),
                            'name'   => $name,
                            'amount' => round($amount, 2),
                        ];
                    }
                }
            }
        }
        $charge_total = round(array_sum(array_map(fn($c) => (float)$c['amount'], $chargesRaw)), 2);

        // 4) Recompute totals (authoritative, JS ke hisaab se)
        $subtotal  = 0.0;
        $taxTotal  = 0.0;
        $cleanRows = [];

        foreach ($rows as $i => $row) {
            $desc    = trim($row['description'] ?? '');
            $sac     = trim($row['sac'] ?? '');

            // qty & amounts
            $qty     = (float)($row['qty'] ?? 0);
            $price   = (float)($row['price'] ?? 0);            // metal price (or full price per unit)
            $making  = (float)($row['making_charge'] ?? 0);    // making per unit
            $stone   = (float)($row['stone_charges'] ?? 0);    // stone per unit
            $disc    = (float)($row['discount'] ?? 0);         // discount total (line level)
            $taxPct  = (float)($row['tax_percent'] ?? 0);
            $item_id = $row['item_id'] ?? null;

            // jewellery extras (DB me preserve karne ke liye)
            $metal_type   = $row['metal_type']   ?? null;
            $purity       = $row['purity']       ?? null;
            $metal_weight = (float)($row['metal_weight'] ?? 0);
            $metal_rate   = (float)($row['metal_rate'] ?? 0);

            if ($desc === '' || $qty <= 0 || $price < 0 || $making < 0 || $stone < 0 || $disc < 0 || $taxPct < 0) {
                return back()
                    ->withErrors(['items' => "Row ".($i+1)." invalid है."])
                    ->withInput();
            }

            // ✅ JS jaisa calculation:
            // basePerUnit = price + making_charge + stone_charges
            // base = max(0, qty * basePerUnit - discount)
            $basePerUnit = max(0, $price + $making + $stone);
            $lineBase    = max(0, ($qty * $basePerUnit) - $disc);
            $lineTax     = $lineBase * ($taxPct / 100);
            $lineAmt     = round($lineBase + $lineTax, 2);

            $subtotal += $lineBase;
            $taxTotal += $lineTax;

            $clean = [
                'description'    => $desc,
                'sac_code'       => $sac ?: null,
                'quantity'       => $qty,
                'rate'           => $price,
                'making_charge'  => $making,
                'stone_charges'  => $stone,
                'discount'       => $disc,
                'tax_percent'    => $taxPct,
                'amount'         => $lineAmt,

                // jewellery fields persist
                'metal_type'     => $metal_type ?: null,
                'purity'         => $purity ?: null,
                'metal_weight'   => $metal_weight,
                'metal_rate'     => $metal_rate,
            ];
            if ($item_id) {
                $clean['item_id'] = $item_id;
            }
            $cleanRows[] = $clean;
        }

        $discount_total = (float)($data['discount_total'] ?? 0);
        $tcs_percent    = (float)($data['tcs_percent'] ?? 0);
        $round_off_in   = (float)($data['round_off'] ?? 0);
        $received       = (float)($data['amount_received'] ?? 0);

        $tcs_base   = max(0, $subtotal + $taxTotal - $discount_total);
        $tcs_amount = round($tcs_base * ($tcs_percent / 100), 2);

        $total_before_round = round($subtotal + $taxTotal - $discount_total + $charge_total + $tcs_amount, 2);
        $grand_total        = round($total_before_round + $round_off_in, 2);
        $balance            = round(max(0, $grand_total - $received), 2);

        // 5) Persist with StockService-based adjustments
        try {
            DB::transaction(function () use (
                $invoice, $data, $invoiceDate, $paymentTerms, $dueDate,
                $prefix, $subtotal, $taxTotal, $discount_total, $charge_total,
                $tcs_percent, $tcs_amount, $round_off_in, $grand_total,
                $received, $balance, $cleanRows, $chargesRaw, $r
            ) {
                /** @var StockService $stock */
                $stock = app(StockService::class);

                // ⭐ 1) Purane movements rollback (old items ke hisaab se)
                $invoice->load('items.item');
                $stock->rollbackReference($invoice);

                // ⭐ 2) Header + totals update
                $invoice->update([
                    'client_id'       => $data['client_id'],
                    'invoice_date'    => $invoiceDate,
                    'invoice_prefix'  => $prefix, // number stays same
                    'payment_terms'   => $paymentTerms,
                    'due_date'        => $dueDate,

                    'subtotal'        => $subtotal,
                    'tax_amount'      => $taxTotal,
                    'discount_total'  => $discount_total,
                    'charge_total'    => $charge_total,
                    'tcs_percent'     => $tcs_percent,
                    'tcs_amount'      => $tcs_amount,
                    'round_off'       => $round_off_in,
                    'total'           => $grand_total,
                    'received_amount' => $received,
                    'balance'         => $balance,

                    'payment_method'  => $data['payment_method'] ?? null,
                    'notes'           => $data['notes'] ?? null,
                    'terms'           => $data['terms'] ?? null,

                    // front-end ka raw JSON store, jisme saare jewellery fields bhi hain
                    'items_json'      => $r->input('items_json'),
                    'charges_json'    => !empty($chargesRaw) ? json_encode($chargesRaw) : null,
                ]);

                // ⭐ 3) Purane items delete + naye create
                if (method_exists($invoice, 'items')) {
                    $invoice->items()->delete();
                    foreach ($cleanRows as $row) {
                        $row['invoice_id'] = $invoice->id;
                        InvoiceItem::create($row);
                    }
                }

                // ⭐ 4) Charges replace (agar model/table hai)
                if (class_exists(InvoiceCharge::class)) {
                    InvoiceCharge::where('invoice_id', $invoice->id)->delete();

                    foreach ($chargesRaw as $c) {
                        InvoiceCharge::create([
                            'invoice_id' => $invoice->id,
                            'charge_id'  => $c['id'] ?? null,
                            'name'       => $c['name'],
                            'amount'     => $c['amount'],
                        ]);
                    }
                }

                // ⭐ 5) Naye items ke basis pe fresh stock movements
                $invoice->load('items.item');
                $stock->recordSale($invoice);
            });
        } catch (\Throwable $e) {
            report($e);
            return back()
                ->withErrors(['invoice' => 'Invoice update करते समय error आया: '.$e->getMessage()])
                ->withInput();
        }

        return redirect()
            ->route('invoices.index')
            ->with('success', 'Invoice updated successfully.');
    }


}
