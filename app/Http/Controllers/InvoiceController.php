<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Client;
use App\Models\InvoiceItem;
use App\Models\Item;
use App\Services\InvoiceNumber;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;



class InvoiceController extends Controller
{
    public function index(Request $r)
    {
        $invoices = Invoice::with('client')->latest()->paginate(15);
        return view('invoices.index', compact('invoices'));
    }

//    public function create(Request $r)
//    {
//        $clients = Client::orderBy('name')->get(['id','name','mobile']);
//        $items   = Item::orderBy('name')->get(['id','name','price','tax_rate','description','sku']);
//        $today   = now()->toDateString();
//        return view('invoices.create', compact('clients','items','today'));
//    }

//    public function create()
//    {
//        $clients = Client::orderBy('name')->get(['id','name','mobile']);
//        $items   = Item::orderBy('name')->get(['id','name','sku','price','tax_rate','description']);
//        $today   = now()->toDateString();
//        $clientsJson = $clients->map(function($c){ return ['id'=>$c->id,'name'=>$c->name,'mobile'=>$c->mobile]; })->values()->toJson();
//        $itemsJson   = $items->map(function($i){ return ['id'=>$i->id,'name'=>$i->name,'sku'=>$i->sku,'price'=>(float)$i->price,'tax_rate'=>(float)$i->tax_rate,'description'=>$i->description]; })->values()->toJson();
//        return view('invoices.create', compact('today','clientsJson','itemsJson'));
//    }




//    public function create(Request $request)
//    {
//        $today = now()->toDateString();
//
//        $bid = $request->user()->current_business_id ?? session('active_business_id');
//        if (!$bid) {
//            $bid = $request->user()->businesses()->pluck('businesses.id')->first();
//        }
//
//        $base = optional(
//                $request->user()->businesses()->where('businesses.id', $bid)->first()
//            )->invoice_base_prefix ?? 'RV/SL';
//
//        $suggestedPrefix = \App\Services\InvoiceNumber::previewPrefix($today, $base);
//
//        $clients = \App\Models\Client::where('business_id', $bid)
//            ->orderBy('name')->get(['id','name','mobile']);
//
//        $items = \App\Models\Item::where('is_active', true)
//            ->orderBy('name')->get(['id','name','sku','price','tax_rate','description']);
//
//        $clientsJson = $clients->map(fn($c)=>[
//            'id'=>$c->id,'name'=>$c->name,'mobile'=>$c->mobile
//        ])->values()->toJson();
//
//        $itemsJson = $items->map(fn($i)=>[
//            'id'=>$i->id,'name'=>$i->name,'sku'=>$i->sku,
//            'price'=>(float)$i->price,'tax_rate'=>(float)$i->tax_rate,
//            'description'=>$i->description
//        ])->values()->toJson();
//
//        $charges = \App\Models\AdditionalCharge::orderBy('name')->get(['id','name','amount']);
//        $chargesJson = $charges->map(fn($a)=>[
//            'id'     => $a->id,
//            'name'   => $a->name,
//            'amount' => (float) $a->amount,
//        ])->values()->toJson();
//
//        // initial preview number
//        $preview = \App\Services\InvoiceNumber::peek((int)$bid, $today, $suggestedPrefix, 3);
//
//        return view('invoices.create', [
//            'today'            => $today,
//            'clientsJson'      => $clientsJson,
//            'itemsJson'        => $itemsJson,
//            'suggestedPrefix'  => $suggestedPrefix,
//            'basePrefix'       => $base,
//            'chargesJson'       => $chargesJson,
//            'initialInvoiceNo' => $preview['full'] ?? 'Auto',
//        ]);
//    }


    public function create(Request $request)
    {
        $today = now()->toDateString();

        $bid = $request->user()->current_business_id ?? session('active_business_id');
        if (!$bid) {
            $bid = $request->user()->businesses()->pluck('businesses.id')->first();
        }

        $base = optional(
                $request->user()->businesses()->where('businesses.id', $bid)->first()
            )->invoice_base_prefix ?? 'RV/SL';

        $suggestedPrefix = \App\Services\InvoiceNumber::previewPrefix($today, $base);

        // Clients & Items
        $clients = \App\Models\Client::where('business_id', $bid)
            ->orderBy('name')->get(['id','name','mobile']);

        $items = \App\Models\Item::where('is_active', true)
            ->orderBy('name')->get(['id','name','sku','price','tax_rate','description', 'making_charge']);

        $clientsJson = $clients->map(fn($c)=>[
            'id'=>$c->id,'name'=>$c->name,'mobile'=>$c->mobile
        ])->values()->toJson();

        $itemsJson = $items->map(fn($i)=>[
            'id'=>$i->id,'name'=>$i->name,'sku'=>$i->sku,
            'price'=>(float)$i->price,'making_charge'=>(float)$i->making_charge,'tax_rate'=>(float)$i->tax_rate,
            'description'=>$i->description
        ])->values()->toJson();


        // Additional Charges (NEW)
        $charges = \App\Models\AdditionalCharge::orderBy('name')->get(['id','name','amount']);
        $chargesJson = $charges->map(fn($a)=>[
            'id'     => $a->id,
            'name'   => $a->name,
            'amount' => (float) $a->amount,
        ])->values()->toJson();

        // initial preview number
        $preview = \App\Services\InvoiceNumber::peek((int)$bid, $today, $suggestedPrefix, 3);

        return view('invoices.create', [
            'today'            => $today,
            'clientsJson'      => $clientsJson,
            'itemsJson'        => $itemsJson,
            'chargesJson'      => $chargesJson,      // NEW
            'suggestedPrefix'  => $suggestedPrefix,
            'basePrefix'       => $base,
            'initialInvoiceNo' => $preview['full'] ?? 'Auto',
        ]);
    }





//    public function store(Request $r)
//    {
//        // 1) Business resolve
//        $bid = $r->user()->current_business_id ?? session('active_business_id');
//        if (!$bid) {
//            return back()->withErrors(['business' => 'Active business select/attach नहीं है.'])->withInput();
//        }
//
//        // 2) Validate (invoice_prefix अब required नहीं है)
//        $data = $r->validate([
//            'client_id'      => ['required','exists:clients,id'],
//            'invoice_date'   => ['required','date'],
//            'invoice_prefix' => ['nullable','string','max:100'],   // <-- changed
//
//            'payment_terms'  => ['nullable','integer','min:0','max:365'],
//            'due_date'       => ['nullable','date'],
//            'notes'          => ['nullable','string','max:2000'],
//            'terms'          => ['nullable','string','max:2000'],
//
//            'discount_total'   => ['nullable','numeric','min:0'],
//            'charge_total'     => ['nullable','numeric','min:0'],
//            'tcs_percent'      => ['nullable','numeric','min:0','max:100'],
//            'round_off'        => ['nullable','numeric'],
//            'amount_received'  => ['nullable','numeric','min:0'],
//            'payment_method'   => ['nullable','string','max:50'],
//
//            'items_json'       => ['required','string'],
//        ]);
//
//        // 2.a) अगर prefix नहीं आया है तो यहाँ generate करें (FY Apr–Mar)
//        $computePrefix = function (string $date, string $base = 'INV'): string {
//            $ts = strtotime($date);
//            $y  = (int)date('Y', $ts);
//            $m  = (int)date('n', $ts); // 1..12
//            // FY starts in April
//            $startYY = ($m >= 4) ? ($y % 100) : (($y - 1) % 100);
//            $a = str_pad((string)$startYY, 2, '0', STR_PAD_LEFT);
//            $b = str_pad((string)(($startYY + 1) % 100), 2, '0', STR_PAD_LEFT);
//            $fy = "{$a}-{$b}";
//            $base = rtrim($base, '/');
//            return "{$base}/{$fy}/";
//        };
//
//        $prefix = trim($data['invoice_prefix'] ?? '');
//        if ($prefix === '') {
//            // चाहें तो यहाँ Business setting से base prefix निकालें, फिलहाल 'INV' default
//            $prefix = $computePrefix($data['invoice_date'], 'INV');
//        }
//
//        // 3) Decode items safely
//        $rows = json_decode($data['items_json'], true);
//        if (!is_array($rows) || count($rows) < 1) {
//            return back()->withErrors(['items' => 'कम से कम 1 line item जरूरी है.'])->withInput();
//        }
//
//        // 4) Recompute totals on server
//        $subtotal = 0.0;
//        $taxTotal = 0.0;
//        $cleanRows = [];
//
//        foreach ($rows as $i => $row) {
//            $desc   = trim($row['description'] ?? '');
//            $sac    = trim($row['sac'] ?? '');
//            $qty    = (int)  ($row['qty'] ?? 0);
//            $price  = (float)($row['price'] ?? 0);
//            $disc   = (float)($row['discount'] ?? 0);
//            $taxPct = (float)($row['tax_percent'] ?? 0);
//
//            if ($desc === '' || $qty < 1 || $price < 0 || $taxPct < 0) {
//                return back()->withErrors(['items' => "Row ".($i+1)." invalid है."])->withInput();
//            }
//
//            $lineBase = max(0, ($qty * $price) - $disc);
//            $lineTax  = $lineBase * ($taxPct / 100);
//            $lineAmt  = round($lineBase + $lineTax, 2);
//
//            $subtotal += $lineBase;
//            $taxTotal += $lineTax;
//
//            $cleanRows[] = [
//                'description' => $desc,
//                'sac_code'    => $sac ?: null,
//                'quantity'    => $qty,
//                'rate'        => $price,
//                'tax_percent' => $taxPct,
//                'amount'      => $lineAmt,
//            ];
//        }
//
//        $discount_total = (float)($data['discount_total'] ?? 0);
//        $charge_total   = (float)($data['charge_total'] ?? 0);
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
//        // 5) Allocate number + save
//        try {
//            DB::transaction(function () use (
//                $bid, $data, $prefix, $subtotal, $taxTotal, $tcs_percent, $tcs_amount,
//                $discount_total, $charge_total, $round_off_in, $grand_total, $received, $balance, $cleanRows
//            ) {
//                // Allocate number (atomic)
//                $alloc = \App\Services\InvoiceNumber::next(
//                    (int)$bid,
//                    $data['invoice_date'],
//                    $prefix,     // यहाँ normalized/auto prefix pass कर रहे हैं
//                    3            // padding width (001, 002, ...)
//                );
//
//                $invoice = \App\Models\Invoice::create([
//                    'business_id'     => $bid,
//                    'client_id'       => $data['client_id'],
//                    'invoice_date'    => $data['invoice_date'],
//                    'invoice_number'  => $alloc['full'],  // e.g. "INV/25-26/001"
//                    'subtotal'        => $subtotal,
//                    'tax_amount'      => $taxTotal,
//                    'total'           => $grand_total,
//                    'received_amount' => $received,
//                    'balance'         => $balance,
//                    'amount_in_words' => '',
//                ]);
//
//                foreach ($cleanRows as $r) {
//                    $r['invoice_id'] = $invoice->id;
//                    \App\Models\InvoiceItem::create($r);
//                }
//            });
//        } catch (\Throwable $e) {
//            report($e);
//            return back()->withErrors([
//                'invoice' => 'Invoice save करते समय error आया: '.$e->getMessage(),
//            ])->withInput();
//        }
//
//        return redirect()->route('invoices.index')->with('success', 'Invoice created successfully.');
//    }





//    public function store(Request $r)
//    {
////        dd(session('active_business_id'));
//        // 1) Resolve Active Business
//        $bid = $r->user()->current_business_id ?? session('active_business_id');
//        if (!$bid) {
//            return back()
//                ->withErrors(['business' => 'Active business select/attach नहीं है.'])
//                ->withInput();
//        }
//
//        // 2) Validate inputs (invoice_prefix nullable)
//        $data = $r->validate([
//            'client_id'        => ['required','exists:clients,id'],
//            'invoice_date'     => ['required','date'],
//            'invoice_prefix'   => ['nullable','string','max:100'],
//
//            'payment_terms'    => ['nullable','integer','min:0','max:365'],
//            'due_date'         => ['nullable','date'],
//            'notes'            => ['nullable','string','max:2000'],
//            'terms'            => ['nullable','string','max:2000'],
//
//            'discount_total'   => ['nullable','numeric','min:0'],
//            'charge_total'     => ['nullable','numeric','min:0'],  // will be ignored (recomputed from charges_json)
//            'tcs_percent'      => ['nullable','numeric','min:0','max:100'],
//            'round_off'        => ['nullable','numeric'],           // can be +/- based on UI
//            'amount_received'  => ['nullable','numeric','min:0'],
//            'payment_method'   => ['nullable','string','max:50'],
//
//            'items_json'       => ['required','string'],            // [{ item_id?, description, sac, qty, price, making_charge?, discount, tax_percent, amount }]
//            'charges_json'     => ['nullable','string'],            // [{ id?, name, amount }]
//        ]);
//
//        // 2.a) Compute/normalize invoice prefix (FY Apr–Mar)
//        $computePrefix = function (string $date, string $base = 'INV'): string {
//            $ts = strtotime($date);
//            $y  = (int)date('Y', $ts);
//            $m  = (int)date('n', $ts); // 1..12
//            $startYY = ($m >= 4) ? ($y % 100) : (($y - 1) % 100);
//            $a = str_pad((string)$startYY, 2, '0', STR_PAD_LEFT);
//            $b = str_pad((string)(($startYY + 1) % 100), 2, '0', STR_PAD_LEFT);
//            $fy = "{$a}-{$b}";
//            $base = rtrim($base, '/');
//            return "{$base}/{$fy}/";
//        };
//
//        $invoiceDate   = Carbon::parse($data['invoice_date'])->toDateString();
//        $prefix        = trim($data['invoice_prefix'] ?? '');
//        if ($prefix === '') {
//            // TODO: load a per-business base from settings if available
//            $prefix = $computePrefix($invoiceDate, 'INV');
//        }
//
//        // 2.b) Payment terms / due date
//        $paymentTerms  = (int)($data['payment_terms'] ?? 0);
//        $dueDate       = $data['due_date'] ?? Carbon::parse($invoiceDate)->addDays($paymentTerms)->toDateString();
//
//        // 3) Decode items safely
//        $rows = json_decode($data['items_json'], true);
//        if (!is_array($rows) || count($rows) < 1) {
//            return back()->withErrors(['items' => 'कम से कम 1 line item जरूरी है.'])->withInput();
//        }
//
//        // 4) Decode charges safely & recompute charge_total (ignore posted charge_total)
//        $chargesRaw = [];
//        if (filled($data['charges_json'] ?? '')) {
//            $decoded = json_decode($data['charges_json'], true);
//            if (is_array($decoded)) {
//                foreach ($decoded as $c) {
//                    $name   = trim(Arr::get($c, 'name', ''));
//                    $amount = (float)Arr::get($c, 'amount', 0);
//                    if ($name !== '' && $amount >= 0) {
//                        $chargesRaw[] = [
//                            'id'     => Arr::get($c, 'id'),
//                            'name'   => $name,
//                            'amount' => round($amount, 2),
//                        ];
//                    }
//                }
//            }
//        }
//        $charge_total = round(array_sum(array_map(fn($c) => (float)$c['amount'], $chargesRaw)), 2);
//
//        // 5) Recompute totals on the server (authoritative)
//        $subtotal  = 0.0;
//        $taxTotal  = 0.0;
//        $cleanRows = [];
//
//        foreach ($rows as $i => $row) {
//            $desc    = trim($row['description'] ?? '');
//            $sac     = trim($row['sac'] ?? '');
//            $qty     = (int)  ($row['qty'] ?? 0);
//            $price   = (float)($row['price'] ?? 0);
//            $making  = (float)($row['making_charge'] ?? 0);   // NEW: making charge
//            $disc    = (float)($row['discount'] ?? 0);
//            $taxPct  = (float)($row['tax_percent'] ?? 0);
//            $item_id = $row['item_id'] ?? null;
//
//            if ($desc === '' || $qty < 1 || $price < 0 || $making < 0 || $disc < 0 || $taxPct < 0) {
//                return back()->withErrors(['items' => "Row ".($i+1)." invalid है."])->withInput();
//            }
//
//            // Base = (Qty * Price) + Making - Discount
//            $lineBase = max(0, ($qty * $price) + $making - $disc);
//            $lineTax  = $lineBase * ($taxPct / 100);
//            $lineAmt  = round($lineBase + $lineTax, 2);
//
//            $subtotal += $lineBase;
//            $taxTotal += $lineTax;
//
//            $clean = [
//                'description'    => $desc,
//                'sac_code'       => $sac ?: null,
//                'quantity'       => $qty,
//                'rate'           => $price,
//                'making_charge'  => $making,   // requires column in invoice_items
//                'discount'       => $disc,     // requires column in invoice_items
//                'tax_percent'    => $taxPct,
//                'amount'         => $lineAmt,
//            ];
//            if ($item_id) {
//                $clean['item_id'] = $item_id;
//            }
//            $cleanRows[] = $clean;
//        }
//
//        $discount_total = (float)($data['discount_total'] ?? 0);
//        $tcs_percent    = (float)($data['tcs_percent'] ?? 0);
//        $round_off_in   = (float)($data['round_off'] ?? 0);         // can be +/-
//        $received       = (float)($data['amount_received'] ?? 0);
//
//        // TCS base = subtotal + tax - discount_total
//        $tcs_base   = max(0, $subtotal + $taxTotal - $discount_total);
//        $tcs_amount = round($tcs_base * ($tcs_percent / 100), 2);
//
//        // Totals
//        $total_before_round = round($subtotal + $taxTotal - $discount_total + $charge_total + $tcs_amount, 2);
//        $grand_total        = round($total_before_round + $round_off_in, 2);
//        $balance            = round(max(0, $grand_total - $received), 2);
//
//        // 6) Persist
//        try {
//            DB::transaction(function () use (
//                $bid, $r, $data, $prefix, $invoiceDate, $paymentTerms, $dueDate,
//                $subtotal, $taxTotal, $discount_total, $charge_total, $tcs_percent, $tcs_amount,
//                $round_off_in, $grand_total, $received, $balance, $cleanRows, $chargesRaw
//            ) {
//                // Atomic number allocation (implements invoice_sequences under the hood)
//                $alloc = \App\Services\InvoiceNumber::next(
//                    (int)$bid,
//                    $invoiceDate,
//                    $prefix,   // normalized prefix (e.g., "RV/SL/25-26/")
//                    3          // padding width (001, 002, ...)
//                );
//
//                /** @var \App\Models\Invoice $invoice */
//                $invoice = \App\Models\Invoice::create([
//                    'business_id'     => $bid,
//                    'client_id'       => $data['client_id'],
//                    'invoice_date'    => $invoiceDate,
//
//                    'invoice_prefix'  => $prefix,         // requires column
//                    'invoice_number'  => $alloc['full'],  // e.g. "RV/SL/25-26/001"
//
//                    'payment_terms'   => $paymentTerms,   // requires column
//                    'due_date'        => $dueDate,        // requires column
//
//                    'subtotal'        => $subtotal,
//                    'tax_amount'      => $taxTotal,
//                    'discount_total'  => $discount_total, // requires column
//                    'charge_total'    => $charge_total,   // requires column
//                    'tcs_percent'     => $tcs_percent,    // requires column
//                    'tcs_amount'      => $tcs_amount,     // requires column
//                    'round_off'       => $round_off_in,   // requires column
//                    'total'           => $grand_total,
//                    'received_amount' => $received,
//                    'balance'         => $balance,
//
//                    'payment_method'  => $data['payment_method'] ?? null, // requires column
//                    'notes'           => $data['notes'] ?? null,          // requires column
//                    'terms'           => $data['terms'] ?? null,          // requires column
//
//                    // Snapshots (optional but handy for audit)
//                    'items_json'      => $r->input('items_json'),                           // requires column
//                    'charges_json'    => !empty($chargesRaw) ? json_encode($chargesRaw) : null, // requires column
//
//                    'amount_in_words' => '', // fill later if needed
//                ]);
//
//                // Items
//                foreach ($cleanRows as $row) {
//                    $row['invoice_id'] = $invoice->id;
//                    \App\Models\InvoiceItem::create($row);
//                }
//
//                // Charges (normalized) — only if you created the model & table
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
//            return back()->withErrors([
//                'invoice' => 'Invoice save करते समय error आया: '.$e->getMessage(),
//            ])->withInput();
//        }
//
//        return redirect()->route('invoices.index')->with('success', 'Invoice created successfully.');
//    }



    public function store(Request $r)
    {
        $bid = $r->user()->current_business_id ?? session('active_business_id');
        if (!$bid) {
            return back()->withErrors(['business' => 'Active business select/attach नहीं है.'])->withInput();
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
            'charge_total'    => ['nullable','numeric','min:0'],
            'tcs_percent'     => ['nullable','numeric','min:0','max:100'],
            'round_off'       => ['nullable','numeric'],
            'amount_received' => ['nullable','numeric','min:0'],
            'payment_method'  => ['nullable','string','max:50'],

            'items_json'      => ['required','string'],
            'charges_json'    => ['nullable','string'],
        ]);

        // Compute prefix
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
        $prefix = trim($data['invoice_prefix'] ?? '');
        if ($prefix === '') {
            $prefix = $computePrefix($invoiceDate, 'INV');
        }

        $paymentTerms = (int)($data['payment_terms'] ?? 0);
        $dueDate      = $data['due_date'] ?? \Carbon\Carbon::parse($invoiceDate)->addDays($paymentTerms)->toDateString();

        // items
        $rows = json_decode($data['items_json'], true);
        if (!is_array($rows) || count($rows) < 1) {
            return back()->withErrors(['items' => 'कम से कम 1 line item जरूरी है.'])->withInput();
        }

        // charges
        $chargesRaw = [];
        if (filled($data['charges_json'] ?? '')) {
            $decoded = json_decode($data['charges_json'], true);
            if (is_array($decoded)) {
                foreach ($decoded as $c) {
                    $name   = trim(\Illuminate\Support\Arr::get($c, 'name', ''));
                    $amount = (float)\Illuminate\Support\Arr::get($c, 'amount', 0);
                    if ($name !== '' && $amount >= 0) {
                        $chargesRaw[] = [
                            'id'     => \Illuminate\Support\Arr::get($c, 'id'),
                            'name'   => $name,
                            'amount' => round($amount, 2),
                        ];
                    }
                }
            }
        }
        $charge_total = round(array_sum(array_map(fn($c) => (float)$c['amount'], $chargesRaw)), 2);

        // totals
        $subtotal = 0.0; $taxTotal = 0.0; $cleanRows = [];
        foreach ($rows as $i => $row) {
            $desc    = trim($row['description'] ?? '');
            $sac     = trim($row['sac'] ?? '');
            $qty     = (int)  ($row['qty'] ?? 0);
            $price   = (float)($row['price'] ?? 0);
            $making  = (float)($row['making_charge'] ?? 0);
            $disc    = (float)($row['discount'] ?? 0);
            $taxPct  = (float)($row['tax_percent'] ?? 0);
            $item_id = $row['item_id'] ?? null;

            if ($desc === '' || $qty < 1 || $price < 0 || $making < 0 || $disc < 0 || $taxPct < 0) {
                return back()->withErrors(['items' => "Row ".($i+1)." invalid है."])->withInput();
            }

            // base = (qty * price) + making - discount
            $lineBase = max(0, ($qty * $price) + $making - $disc);
            $lineTax  = $lineBase * ($taxPct / 100);
            $lineAmt  = round($lineBase + $lineTax, 2);

            $subtotal += $lineBase; $taxTotal += $lineTax;

            $clean = [
                'description'    => $desc,
                'sac_code'       => $sac ?: null,
                'quantity'       => $qty,
                'rate'           => $price,
                'making_charge'  => $making,
                'discount'       => $disc,
                'tax_percent'    => $taxPct,
                'amount'         => $lineAmt,
            ];
            if ($item_id) $clean['item_id'] = $item_id;
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

        try {
            \DB::transaction(function () use (
                $bid, $r, $data, $prefix, $invoiceDate, $paymentTerms, $dueDate,
                $subtotal, $taxTotal, $discount_total, $charge_total, $tcs_percent, $tcs_amount,
                $round_off_in, $grand_total, $received, $balance, $cleanRows, $chargesRaw
            ) {
                $alloc = \App\Services\InvoiceNumber::next((int)$bid, $invoiceDate, $prefix, 3);

                /** @var \App\Models\Invoice $invoice */
                $invoice = \App\Models\Invoice::create([
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

                foreach ($cleanRows as $row) {
                    $row['invoice_id'] = $invoice->id;
                    \App\Models\InvoiceItem::create($row);
                }

                if (class_exists(\App\Models\InvoiceCharge::class) && !empty($chargesRaw)) {
                    foreach ($chargesRaw as $c) {
                        \App\Models\InvoiceCharge::create([
                            'invoice_id' => $invoice->id,
                            'charge_id'  => $c['id'] ?? null,
                            'name'       => $c['name'],
                            'amount'     => $c['amount'],
                        ]);
                    }
                }
            });
        } catch (\Throwable $e) {
            report($e);
            return back()->withErrors(['invoice' => 'Invoice save करते समय error आया: '.$e->getMessage()])->withInput();
        }

        return redirect()->route('invoices.index')->with('success', 'Invoice created successfully.');
    }





//    public function edit(Invoice $invoice)
//    {
//        $invoice->load('client','items');
//        $clients = Client::orderBy('name')->get(['id','name','mobile']);
//        $items   = Item::orderBy('name')->get(['id','name','price','tax_rate','description','sku']);
//        return view('invoices.edit', compact('invoice','clients','items'));
//    }

//    public function edit(\Illuminate\Http\Request $request, \App\Models\Invoice $invoice)
//    {
//        // Business context
//        $bid = $invoice->business_id
//            ?? ($request->user()->current_business_id ?? session('active_business_id'))
//            ?? $request->user()->businesses()->pluck('businesses.id')->first();
//
//        // Masters: clients & items
//        $clients = \App\Models\Client::where('business_id', $bid)
//            ->orderBy('name')->get(['id','name','mobile']);
//
//        $items = \App\Models\Item::where('is_active', true)
//            ->orderBy('name')->get(['id','name','sku','price','tax_rate','description', 'making_charge']);
//
//        $clientsJson = $clients->map(fn($c)=>[
//            'id'=>(int)$c->id,'name'=>$c->name,'mobile'=>$c->mobile
//        ])->values()->toJson();
//
//        $itemsJson = $items->map(fn($i)=>[
//            'id'=>$i->id,'name'=>$i->name,'sku'=>$i->sku,
//            'price'=>(float)$i->price,'making_charge'=>(float)$i->making_charge,'tax_rate'=>(float)$i->tax_rate,
//            'description'=>$i->description
//        ])->values()->toJson();
//
//        // Additional charges master
//        $charges = \App\Models\AdditionalCharge::orderBy('name')->get(['id','name','amount']);
//        $chargesJson = $charges->map(fn($a)=>[
//            'id'=>$a->id,'name'=>$a->name,'amount'=>(float)$a->amount
//        ])->values()->toJson();
//
//        // Existing line items (DB schema -> UI keys)
//        $existingItems = [];
//        if (method_exists($invoice, 'items')) {
//            $existingItems = $invoice->items()
//                ->get(['item_id','description','sac_code','quantity','rate','making_charge','tax_percent','amount'])
//                ->map(fn($r)=>[
//                    'item_id'     => $r->item_id,
//                    'description' => $r->description,
//                    'sac'         => $r->sac_code,             // map to UI
//                    'qty'         => (float)$r->quantity,      // map to UI
//                    'price'       => (float)$r->rate,
//                    'making_charge'       => (float)$r->making_charge, // map to UI
//                    'discount'    => 0.0,                      // DB में discount नहीं; UI compatible
//                    'tax_percent' => (float)$r->tax_percent,
//                    // amount UI में फिर से calc होता है, फिर भी भेज रहे
//                    'amount'      => (float)$r->amount,
//                ])->values()->all();
//        } elseif (!empty($invoice->items_json)) {
//            // अगर कभी JSON में सेव कर रखा है
//            $decoded = json_decode($invoice->items_json, true) ?: [];
//            $existingItems = array_map(function($r){
//                return [
//                    'item_id'     => $r['item_id'] ?? null,
//                    'description' => $r['description'] ?? '',
//                    'sac'         => $r['sac'] ?? '',
//                    'qty'         => (float)($r['qty'] ?? 0),
//                    'price'       => (float)($r['price'] ?? 0),
//                    'making_charge'=> (float)($r['making_charge'] ?? 0),
//                    'discount'    => (float)($r['discount'] ?? 0),
//                    'tax_percent' => (float)($r['tax_percent'] ?? 0),
//                    'amount'      => (float)($r['amount'] ?? 0),
//                ];
//            }, $decoded);
//        }
//
//        // Existing additional charges (relation या JSON)
//        $existingCharges = [];
//        if (method_exists($invoice, 'additionalCharges')) {
//            $existingCharges = $invoice->additionalCharges()
//                ->get(['additional_charge_id as id','name','amount'])
//                ->map(fn($r)=>[
//                    'id'=>$r->id,
//                    'name'=>$r->name,
//                    'amount'=>(float)$r->amount,
//                ])->values()->all();
//        } elseif (!empty($invoice->charges_json)) {
//            $decoded = json_decode($invoice->charges_json, true) ?: [];
//            $existingCharges = array_map(function($r){
//                return [
//                    'id'=>$r['id'] ?? null,
//                    'name'=>$r['name'] ?? '',
//                    'amount'=>(float)($r['amount'] ?? 0),
//                ];
//            }, $decoded);
//        }
//
//        // Header/totals defaults (UI state)
//        $hdr = [
//            'date'  => optional($invoice->invoice_date)->toDateString() ?? now()->toDateString(),
//            'terms' => (int)($invoice->payment_terms ?? 30),
//            'due'   => optional($invoice->due_date)->toDateString(),
//        ];
//
//        $u = [
//            'discount_total' => (float)($invoice->discount_total ?? 0),
//            'tcs_percent'    => (float)($invoice->tcs_percent ?? 0),
//            'received'       => (float)($invoice->received_amount ?? 0),
//            'round_off'      => (float)abs($invoice->round_off ?? 0),
//            'roundSign'      => ($invoice->round_off ?? 0) < 0 ? '-' : '+',
//            'discountEnabled'=> (float)($invoice->discount_total ?? 0) > 0,
//            'tcsEnabled'     => (float)($invoice->tcs_percent ?? 0) > 0,
//            'autoRound'      => false,
//        ];
//
//        return view('invoices.edit', [
//            'invoice'        => $invoice,
//            'clientsJson'    => $clientsJson,
//            'itemsJson'      => $itemsJson,
//            'chargesJson'    => $chargesJson,
//            'initialItems'   => $existingItems,
//            'initialCharges' => $existingCharges,
//            'hdr'            => $hdr,
//            'u'              => $u,
//            'basePrefix'     => $invoice->invoice_prefix_base ?? ($invoice->invoice_prefix ?? 'RV/SL'),
//            'invoicePrefix'  => $invoice->invoice_prefix ?? '',
//            'invoiceNumber'  => $invoice->invoice_number ?? '',
//            'paymentMethod'  => $invoice->payment_method ?? 'Cash',
//        ]);
//    }


    public function edit(\Illuminate\Http\Request $request, \App\Models\Invoice $invoice)
    {
        // Business context
        $bid = $invoice->business_id
            ?? ($request->user()->current_business_id ?? session('active_business_id'))
            ?? $request->user()->businesses()->pluck('businesses.id')->first();

        // Masters: clients & items
        $clients = \App\Models\Client::where('business_id', $bid)
            ->orderBy('name')->get(['id','name','mobile']);

        $items = \App\Models\Item::where('is_active', true)
            ->orderBy('name')->get(['id','name','sku','price','tax_rate','description','making_charge']);

        // ✅ id को int cast किया गया
        $clientsJson = $clients->map(fn($c)=>[
            'id'     => (int) $c->id,
            'name'   => $c->name,
            'mobile' => $c->mobile,
        ])->values()->toJson();

        $itemsJson = $items->map(fn($i)=>[
            'id'            => (int) $i->id,
            'name'          => $i->name,
            'sku'           => $i->sku,
            'price'         => (float) $i->price,
            'making_charge' => (float) $i->making_charge,
            'tax_rate'      => (float) $i->tax_rate,
            'description'   => $i->description,
        ])->values()->toJson();

        // Additional charges master
        $charges = \App\Models\AdditionalCharge::orderBy('name')->get(['id','name','amount']);
        $chargesJson = $charges->map(fn($a)=>[
            'id'     => (int) $a->id,
            'name'   => $a->name,
            'amount' => (float) $a->amount,
        ])->values()->toJson();

        // Existing line items -> UI keys
        $existingItems = [];
        if (method_exists($invoice, 'items')) {
            $existingItems = $invoice->items()
                ->get(['item_id','description','sac_code','quantity','rate','making_charge','tax_percent','amount'])
                ->map(fn($r)=>[
                    'item_id'       => $r->item_id ? (int)$r->item_id : null,
                    'description'   => $r->description,
                    'sac'           => $r->sac_code,
                    'qty'           => (float)$r->quantity,
                    'price'         => (float)$r->rate,
                    'making_charge' => (float)$r->making_charge,
                    'discount'      => 0.0, // DB में नहीं, UI compatible
                    'tax_percent'   => (float)$r->tax_percent,
                    'amount'        => (float)$r->amount,
                ])->values()->all();
        } elseif (!empty($invoice->items_json)) {
            $decoded = json_decode($invoice->items_json, true) ?: [];
            $existingItems = array_map(function($r){
                return [
                    'item_id'       => isset($r['item_id']) ? (int)$r['item_id'] : null,
                    'description'   => $r['description'] ?? '',
                    'sac'           => $r['sac'] ?? '',
                    'qty'           => (float)($r['qty'] ?? 0),
                    'price'         => (float)($r['price'] ?? 0),
                    'making_charge' => (float)($r['making_charge'] ?? 0),
                    'discount'      => (float)($r['discount'] ?? 0),
                    'tax_percent'   => (float)($r['tax_percent'] ?? 0),
                    'amount'        => (float)($r['amount'] ?? 0),
                ];
            }, $decoded);
        }

        // Existing additional charges
        $existingCharges = [];
        if (method_exists($invoice, 'additionalCharges')) {
            $existingCharges = $invoice->additionalCharges()
                ->get(['additional_charge_id as id','name','amount'])
                ->map(fn($r)=>[
                    'id'     => $r->id ? (int)$r->id : null,
                    'name'   => $r->name,
                    'amount' => (float)$r->amount,
                ])->values()->all();
        } elseif (!empty($invoice->charges_json)) {
            $decoded = json_decode($invoice->charges_json, true) ?: [];
            $existingCharges = array_map(function($r){
                return [
                    'id'     => isset($r['id']) ? (int)$r['id'] : null,
                    'name'   => $r['name'] ?? '',
                    'amount' => (float)($r['amount'] ?? 0),
                ];
            }, $decoded);
        }

        // Header/totals defaults (UI state)
        $hdr = [
            'date'  => optional($invoice->invoice_date)->toDateString() ?? now()->toDateString(),
            'terms' => (int)($invoice->payment_terms ?? 30),
            'due'   => optional($invoice->due_date)->toDateString(),
        ];

        $u = [
            'discount_total' => (float)($invoice->discount_total ?? 0),
            'tcs_percent'    => (float)($invoice->tcs_percent ?? 0),
            'received'       => (float)($invoice->received_amount ?? 0),
            'round_off'      => (float)abs($invoice->round_off ?? 0),
            'roundSign'      => ($invoice->round_off ?? 0) < 0 ? '-' : '+',
            'discountEnabled'=> (float)($invoice->discount_total ?? 0) > 0,
            'tcsEnabled'     => (float)($invoice->tcs_percent ?? 0) > 0,
            'autoRound'      => false,
        ];

        return view('invoices.edit', [
            'invoice'        => $invoice,
            'clientsJson'    => $clientsJson,
            'itemsJson'      => $itemsJson,
            'chargesJson'    => $chargesJson,
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



    public function update(Request $r, \App\Models\Invoice $invoice)
    {
        // 1) Basic validation (prefix/date editable per your UI)
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

            'items_json'      => ['required','string'],  // [{ item_id?, description, sac, qty, price, making_charge, discount, tax_percent, amount }]
            'charges_json'    => ['nullable','string'],  // [{ id?, name, amount }]
        ]);

        $invoiceDate  = \Carbon\Carbon::parse($data['invoice_date'])->toDateString();

        // Keep prefix/number; allow prefix change if UI sent (read-only in your UI though)
        $prefix       = trim($data['invoice_prefix'] ?? $invoice->invoice_prefix ?? '');
        if ($prefix === '') $prefix = $invoice->invoice_prefix ?? 'INV/';

        $paymentTerms = (int)($data['payment_terms'] ?? 0);
        $dueDate      = $data['due_date'] ?? \Carbon\Carbon::parse($invoiceDate)->addDays($paymentTerms)->toDateString();

        // 2) Decode rows
        $rows = json_decode($data['items_json'], true);
        if (!is_array($rows) || count($rows) < 1) {
            return back()->withErrors(['items' => 'कम से कम 1 line item जरूरी है.'])->withInput();
        }

        // 3) Decode charges and recompute total
        $chargesRaw = [];
        if (filled($data['charges_json'] ?? '')) {
            $decoded = json_decode($data['charges_json'], true);
            if (is_array($decoded)) {
                foreach ($decoded as $c) {
                    $name   = trim(\Illuminate\Support\Arr::get($c, 'name', ''));
                    $amount = (float)\Illuminate\Support\Arr::get($c, 'amount', 0);
                    if ($name !== '' && $amount >= 0) {
                        $chargesRaw[] = [
                            'id'     => \Illuminate\Support\Arr::get($c, 'id'),
                            'name'   => $name,
                            'amount' => round($amount, 2),
                        ];
                    }
                }
            }
        }
        $charge_total = round(array_sum(array_map(fn($c) => (float)$c['amount'], $chargesRaw)), 2);

        // 4) Recompute totals (authoritative)
        $subtotal  = 0.0;
        $taxTotal  = 0.0;
        $cleanRows = [];

        foreach ($rows as $i => $row) {
            $desc    = trim($row['description'] ?? '');
            $sac     = trim($row['sac'] ?? '');
            $qty     = (int)  ($row['qty'] ?? 0);
            $price   = (float)($row['price'] ?? 0);
            $making  = (float)($row['making_charge'] ?? 0);
            $disc    = (float)($row['discount'] ?? 0);
            $taxPct  = (float)($row['tax_percent'] ?? 0);
            $item_id = $row['item_id'] ?? null;

            if ($desc === '' || $qty < 1 || $price < 0 || $making < 0 || $disc < 0 || $taxPct < 0) {
                return back()->withErrors(['items' => "Row ".($i+1)." invalid है."])->withInput();
            }

            // base = (qty * price) + making - discount
            $lineBase = max(0, ($qty * $price) + $making - $disc);
            $lineTax  = $lineBase * ($taxPct / 100);
            $lineAmt  = round($lineBase + $lineTax, 2);

            $subtotal += $lineBase;
            $taxTotal += $lineTax;

            $clean = [
                'description'    => $desc,
                'sac_code'       => $sac ?: null,
                'quantity'       => $qty,
                'rate'           => $price,
                'making_charge'  => $making,
                'discount'       => $disc,
                'tax_percent'    => $taxPct,
                'amount'         => $lineAmt,
            ];
            if ($item_id) $clean['item_id'] = $item_id;
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

        // 5) Persist: replace items & charges; don't change invoice_number
        try {
            \DB::transaction(function () use (
                $invoice, $data, $invoiceDate, $paymentTerms, $dueDate,
                $prefix, $subtotal, $taxTotal, $discount_total, $charge_total,
                $tcs_percent, $tcs_amount, $round_off_in, $grand_total,
                $received, $balance, $cleanRows, $chargesRaw, $r
            ) {
                // Update header + totals
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

                    'items_json'      => $r->input('items_json'),
                    'charges_json'    => !empty($chargesRaw) ? json_encode($chargesRaw) : null,
                ]);

                // Replace items
                if (method_exists($invoice, 'items')) {
                    $invoice->items()->delete();
                    foreach ($cleanRows as $row) {
                        $row['invoice_id'] = $invoice->id;
                        \App\Models\InvoiceItem::create($row);
                    }
                }

                // Replace additional charges if relation exists
                if (class_exists(\App\Models\InvoiceCharge::class) && method_exists($invoice, 'additionalCharges')) {
                    $invoice->additionalCharges()->delete();
                    foreach ($chargesRaw as $c) {
                        \App\Models\InvoiceCharge::create([
                            'invoice_id' => $invoice->id,
                            'charge_id'  => $c['id'] ?? null,
                            'name'       => $c['name'],
                            'amount'     => $c['amount'],
                        ]);
                    }
                }
            });
        } catch (\Throwable $e) {
            report($e);
            return back()->withErrors(['invoice' => 'Invoice update करते समय error आया: '.$e->getMessage()])->withInput();
        }

        return redirect()->route('invoices.index')->with('success', 'Invoice updated successfully.');
    }


    public function destroy(Invoice $invoice)
    {
        $invoice->delete();
        return back()->with('success','Deleted.');
    }



//    public function download(\App\Models\Invoice $invoice)
//    {
//        $invoice->load(['client','items','business']);
//
//        // logo & signature ko data-uri banaye (agar path diya hai to)
//        [$logoDataUri, $signDataUri] = [$this->imageDataUri($invoice->business->logo), $this->imageDataUri($invoice->business->signature)];
//
//
//
//        $pdf = Pdf::loadView('invoices.pdf', [
//            'inv'   => $invoice,
//            'logo'  => $logoDataUri,   // null ya "data:image/..;base64,...."
//            'sign'  => $signDataUri,
//        ])->setPaper('a4');
//
//        // filename sanitize (slashes hatao)
//        $safeNumber = str_replace(['/', '\\'], '-', (string)$invoice->invoice_number);
//        return $pdf->download('Invoice-'.$safeNumber.'.pdf');
//    }
//
//
//    private function imageDataUri(?string $path): ?string
//    {
//        if (!$path) return null;
//        try {
//            // prefer public disk
//            if (Storage::disk('public')->exists($path)) {
//                $full = Storage::disk('public')->path($path);
//                $mime = mime_content_type($full) ?: 'image/png';
//                $data = base64_encode(file_get_contents($full));
//                return "data:{$mime};base64,{$data}";
//            }
//            // fallback for absolute public_path
//            $full = public_path($path);
//            if (is_file($full)) {
//                $mime = mime_content_type($full) ?: 'image/png';
//                $data = base64_encode(file_get_contents($full));
//                return "data:{$mime};base64,{$data}";
//            }
//        } catch (\Throwable $e) {
//            report($e);
//        }
//        return null;
//    }



    public function download(\App\Models\Invoice $invoice)
    {
        // DO NOT eager-load additionalCharges if relation doesn't exist
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

        // --- totals (server-side recompute) ---
        $subtotal = (float) $items->reduce(function ($sum, $it) {
            $qty   = (float) ($it->quantity ?? 0);
            $rate  = (float) ($it->rate ?? 0);
            $mk    = (float) ($it->making_charge ?? 0);
            $base  = max(0, $qty * ($rate + $mk));
            return $sum + $base;
        }, 0.0);

        $taxTotal = (float) $items->reduce(function ($sum, $it) {
            $qty   = (float) ($it->quantity ?? 0);
            $rate  = (float) ($it->rate ?? 0);
            $mk    = (float) ($it->making_charge ?? 0);
            $base  = max(0, $qty * ($rate + $mk));
            $tp    = (float) ($it->tax_percent ?? 0);
            return $sum + ($base * ($tp / 100));
        }, 0.0);

        $discountTotal = (float) ($invoice->discount_total ?? 0.0);
        $chargesTotal  = (float) $charges->reduce(fn($s, $r) => $s + (float) ($r->amount ?? 0), 0.0);
        $tcsPercent    = (float) ($invoice->tcs_percent ?? 0.0);
        $received      = (float) ($invoice->received_amount ?? 0.0);
        $roundOff      = (float) ($invoice->round_off ?? 0.0);

        $tcsAmount         = $tcsPercent > 0 ? max(0, ($subtotal + $taxTotal - $discountTotal)) * ($tcsPercent / 100) : 0.0;
        $totalBeforeRound  = $subtotal + $taxTotal - $discountTotal + $chargesTotal + $tcsAmount;
        $grandTotal        = (float) ($totalBeforeRound + $roundOff);
        $balance           = max(0, $grandTotal - $received);

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




}
