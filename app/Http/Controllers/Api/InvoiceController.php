<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoicePayment;
use App\Models\UserPlan;
use App\Services\InvoiceNumber;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Services\StockService;
use Illuminate\Support\Facades\Log;

class InvoiceController extends Controller
{
    public function __construct(protected StockService $stock) {}

    // ------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------
    // protected function activeBusinessId(Request $request): int
    // {
    //     $me = $request->user();
    //     $bid = (int)($me->current_business_id ?? session('active_business_id') ?? 0);
    //     if (!$bid) {
    //         $bid = (int)($me->businesses()->pluck('businesses.id')->first() ?? 0);
    //     }
    //     if (!$bid) abort(422, 'Active business not found.');
    //     return $bid;
    // }

    protected function activeBusinessId(Request $request): int
    { $bid = (int) $request->input('business_id');

        abort_unless($bid > 0, 422, 'business_id is required.');

        $user = $request->user();

        $hasBusiness = $user->businesses()
            ->where('businesses.id', $bid)
            ->exists();

        abort_unless($hasBusiness, 403, 'You do not have access to this business.');

        return $bid;
    }

    protected function normalizeDocType(string $docType): string
    {
        $docType = strtolower(trim($docType));
        return in_array($docType, ['tax','proforma','quotation'], true) ? $docType : 'tax';
    }

    protected function requiredPerm(string $docType): string
    {
        return match ($docType) {
            'tax'       => 'show invoices',
            'proforma'  => 'show proformas',
            'quotation' => 'show quotations',
            default     => 'show invoices',
        };
    }

    protected function computePrefix(string $date, string $base = 'INV'): string
    {
        $ts = strtotime($date);
        $y  = (int)date('Y', $ts);
        $m  = (int)date('n', $ts);
        $startYY = ($m >= 4) ? ($y % 100) : (($y - 1) % 100);
        $a = str_pad((string)$startYY, 2, '0', STR_PAD_LEFT);
        $b = str_pad((string)(($startYY + 1) % 100), 2, '0', STR_PAD_LEFT);
        $fy = "{$a}-{$b}";
        $base = rtrim($base, '/');
        return "{$base}/{$fy}/";
    }

    protected function normCode($v): string
    {
        $s = trim((string)$v);
        $s = preg_replace('/\D+/', '', $s);
        $s = ltrim($s, '0');
        return (string)$s;
    }

    // ------------------------------------------------------------
    // GET /api/invoices?type=tax&search=&from_date=&to_date=&status=
    // ------------------------------------------------------------
    // public function index(Request $request, $type = 'tax')
    // {
    //     $me  = $request->user();
    //     $bid = $this->activeBusinessId($request);

    //     $type = $this->normalizeDocType((string)$type);
    //     if (!$me->can($this->requiredPerm($type))) {
    //         return response()->json(['ok'=>false,'message'=>'Permission denied'], 403);
    //     }

    //     $search   = trim((string)$request->get('search', ''));
    //     $fromDate = $request->get('from_date');
    //     $toDate   = $request->get('to_date');
    //     $status   = $request->get('status');

    //     $q = Invoice::withoutGlobalScope('business')
    //         ->with(['client:id,name'])
    //         ->where('business_id', $bid)
    //         ->where('invoice_type', $type);


    //     if ($search !== '') {
    //         $q->where(function ($w) use ($search) {

    //             // ✅ invoice number
    //             $w->where('invoice_number', 'like', "%{$search}%")

    //             // ✅ amount search
    //             ->orWhere('total', 'like', "%{$search}%")
    //             ->orWhere('balance', 'like', "%{$search}%")
    //             ->orWhere('received_amount', 'like', "%{$search}%")

    //             // ✅ client fields
    //             ->orWhereHas('client', function ($c) use ($search) {
    //                 $c->where(function ($cc) use ($search) {
    //                     $cc->where('name', 'like', "%{$search}%")
    //                     ->orWhere('mobile', 'like', "%{$search}%")
    //                     ->orWhere('gstin', 'like', "%{$search}%")
    //                     ->orWhere('pan', 'like', "%{$search}%")
    //                     ->orWhere('address', 'like', "%{$search}%");
    //                 });
    //             });

    //         });
    //     }

    //     if (!empty($fromDate)) $q->whereDate('invoice_date', '>=', $fromDate);
    //     if (!empty($toDate))   $q->whereDate('invoice_date', '<=', $toDate);

    //     if (!empty($status)) {
    //         if ($status === 'paid') {
    //             $q->where('balance', '<=', 0);
    //         } elseif ($status === 'unpaid') {
    //             $q->where('received_amount', '<=', 0);
    //         } elseif ($status === 'partial') {
    //             $q->where('received_amount', '>', 0)->where('balance', '>', 0);
    //         }
    //     }

    //     $perPage = (int)($request->get('per_page', 20));
    //     $invoices = $q->orderByDesc('invoice_date')->orderByDesc('id')->paginate($perPage);

    //     return response()->json([
    //         'ok' => true,
    //         'data' => $invoices,
    //     ]);
    // }

// public function index(Request $request, $type = 'tax')
// {
//     $me = $request->user();
//     $bid = $this->activeBusinessId($request);

//     $type = $this->normalizeDocType((string) $type);

//     return response()->json([
//         'business_id' => $bid,
//         'type' => $type,
//         'count' => Invoice::withoutGlobalScopes()
//             ->where('business_id', $bid)
//             ->where('invoice_type', $type)
//             ->count(),
//         'invoices' => Invoice::withoutGlobalScopes()
//             ->where('business_id', $bid)
//             ->where('invoice_type', $type)
//             ->get([
//                 'id',
//                 'invoice_number',
//                 'invoice_type',
//                 'business_id'
//             ]),
//     ]);
// }


public function index(Request $request, $type = 'tax')
{
    $me = $request->user();
    $bid = $this->activeBusinessId($request);

    $type = $this->normalizeDocType((string) $type);

    if (!$me->can($this->requiredPerm($type))) {
        return response()->json([
            'ok' => false,
            'message' => 'Permission denied'
        ], 403);
    }

    $search   = trim((string) $request->input('search', ''));
    $fromDate = $request->input('from_date');
    $toDate   = $request->input('to_date');
    $status   = $request->input('status');
    $perPage  = (int) $request->input('per_page', 20);

    $q = Invoice::withoutGlobalScopes()
        ->with('client:id,name')
        ->where('business_id', $bid)
        ->where('invoice_type', $type);

    if ($search !== '') {
        $q->where(function ($query) use ($search) {
            $query->where('invoice_number', 'like', "%{$search}%")
                ->orWhere('total', 'like', "%{$search}%")
                ->orWhere('balance', 'like', "%{$search}%")
                ->orWhere('received_amount', 'like', "%{$search}%")
                ->orWhereHas('client', function ($client) use ($search) {
                    $client->where('name', 'like', "%{$search}%")
                        ->orWhere('mobile', 'like', "%{$search}%")
                        ->orWhere('gstin', 'like', "%{$search}%")
                        ->orWhere('pan', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%");
                });
        });
    }

    if ($fromDate) {
        $q->whereDate('invoice_date', '>=', $fromDate);
    }

    if ($toDate) {
        $q->whereDate('invoice_date', '<=', $toDate);
    }

    if ($status === 'paid') {
        $q->where('balance', '<=', 0);
    } elseif ($status === 'unpaid') {
        $q->where('received_amount', '<=', 0);
    } elseif ($status === 'partial') {
        $q->where('received_amount', '>', 0)
          ->where('balance', '>', 0);
    }

    $invoices = $q->latest('invoice_date')
        ->latest('id')
        ->paginate($perPage);

    return response()->json([
        'ok' => true,
        'business_id' => $bid,
        'type' => $type,
        'data' => $invoices,
    ]);
}


    public function update(Request $request, Invoice $invoice)
    {
        $bid = $this->activeBusinessId($request);

        if ((int) $invoice->business_id !== (int) $bid) {
            return response()->json([
                'ok' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $docType = $this->normalizeDocType((string) ($invoice->invoice_type ?? 'tax'));

        $data = $request->validate([
            'client_id'      => ['required', 'integer'],
            'invoice_date'   => ['required', 'date'],
            'invoice_prefix' => ['nullable', 'string', 'max:255'],
            'invoice_number' => ['required', 'string', 'max:255'],

            'transport_mode' => ['nullable', 'string', 'max:255'],
            'gst_no'         => ['nullable', 'string', 'max:50'],
            'reverse_charge' => ['nullable'],

            'notes'          => ['nullable', 'string', 'max:2000'],
            'terms'          => ['nullable', 'string', 'max:2000'],

            'items_json'     => ['nullable'],
            'items'          => ['nullable', 'array', 'min:1'],

            'charges_json'   => ['nullable'],
            'discount_total' => ['nullable', 'numeric', 'min:0'],
            'charge_total'   => ['nullable', 'numeric', 'min:0'],
            'tcs_percent'    => ['nullable', 'numeric', 'min:0'],
            'tcs_amount'     => ['nullable', 'numeric', 'min:0'],
            'round_off'      => ['nullable', 'numeric'],
            'less_amount'    => ['nullable', 'numeric', 'min:0'],

            'payment_method'  => ['nullable', 'string', 'max:255'],
            'bank_account_id' => ['nullable', 'integer'],
        ]);

        // Handle items_json from Flutter / Postman / web
        $itemsJsonInput = $request->input('items_json');
        $itemsJson = null;

        if (is_string($itemsJsonInput)) {
            $itemsJson = $itemsJsonInput;
        } elseif (is_array($itemsJsonInput)) {
            $isAssoc = array_keys($itemsJsonInput) !== range(0, count($itemsJsonInput) - 1);
            $rowsToEncode = $isAssoc ? [$itemsJsonInput] : $itemsJsonInput;
            $itemsJson = json_encode($rowsToEncode, JSON_UNESCAPED_UNICODE);
        }

        if (!$itemsJson && !empty($data['items']) && is_array($data['items'])) {
            $itemsJson = json_encode($data['items'], JSON_UNESCAPED_UNICODE);
        }

        if (!$itemsJson) {
            return response()->json([
                'ok' => false,
                'message' => 'items_json or items is required',
            ], 422);
        }

        // Handle charges_json from string / array
        $chargesJsonInput = $request->input('charges_json');
        $chargesJson = null;

        if (is_string($chargesJsonInput)) {
            $chargesJson = $chargesJsonInput;
        } elseif (is_array($chargesJsonInput)) {
            $chargesJson = json_encode($chargesJsonInput, JSON_UNESCAPED_UNICODE);
        }

        // payments only for tax
        $pay = [];
        if ($docType === 'tax') {
            $pay = $request->validate([
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
                'pay_notes'           => ['nullable', 'string', 'max:2000'],
            ]);
        }

        $biz = Business::find($bid);
        if (!$biz) {
            return response()->json([
                'ok' => false,
                'message' => 'Business not found',
            ], 404);
        }

        $client = Client::where('business_id', $bid)->find((int) $data['client_id']);
        if (!$client) {
            return response()->json([
                'ok' => false,
                'message' => 'Client not found for this business',
            ], 404);
        }

        $invoiceDate = \Carbon\Carbon::parse($data['invoice_date'])->toDateString();

        $prefix = trim((string) ($data['invoice_prefix'] ?? ''));
        if ($prefix === '') {
            $base = $docType === 'proforma'
                ? 'PF'
                : ($docType === 'quotation'
                    ? 'QT'
                    : ($biz->invoice_base_prefix ?? 'INV'));

            $prefix = InvoiceNumber::previewPrefix($invoiceDate, $base);
            if (!$prefix) {
                $prefix = $this->computePrefix($invoiceDate, $base);
            }
        }

        $rows = json_decode($itemsJson, true);
        if (!is_array($rows) || count($rows) < 1) {
            return response()->json([
                'ok' => false,
                'message' => 'Items invalid',
            ], 422);
        }

        $subtotal = 0.0;
        $weightedTax = 0.0;
        $itemsTaxTotal = 0.0;
        $cleanRows = [];

        foreach ($rows as $i => $row) {
            $rowNo = $i + 1;

            $itemId = $row['item_id'] ?? null;
            if (!$itemId) {
                return response()->json([
                    'ok' => false,
                    'message' => "Row {$rowNo} item_id missing",
                ], 422);
            }

            $itemType = strtolower(trim((string) ($row['item_type'] ?? 'product')));
            if (!in_array($itemType, ['product', 'service'], true)) {
                $itemType = 'product';
            }

            $desc = trim((string) ($row['description'] ?? ''));
            if ($desc === '') {
                return response()->json([
                    'ok' => false,
                    'message' => "Row {$rowNo} description missing",
                ], 422);
            }

            $hsn = trim((string) ($row['hsn'] ?? ''));
            $qty = (int) ($row['qty'] ?? $row['quantity'] ?? 1);
            $qty = $qty < 1 ? 1 : $qty;

            $taxPct = round((float) ($row['tax_percent'] ?? 0), 2);
            if ($taxPct < 0 || $taxPct > 100) {
                return response()->json([
                    'ok' => false,
                    'message' => "Row {$rowNo} tax_percent invalid",
                ], 422);
            }

            if ($itemType === 'service') {
                $serviceRate = round((float) ($row['service_rate'] ?? 0), 2);
                if ($serviceRate < 0) {
                    return response()->json([
                        'ok' => false,
                        'message' => "Row {$rowNo} service_rate invalid",
                    ], 422);
                }

                $lineBase = round($serviceRate * $qty, 2);
                $lineTax  = round($lineBase * ($taxPct / 100), 2);

                $subtotal += $lineBase;
                $weightedTax += ($lineBase * $taxPct);
                $itemsTaxTotal += $lineTax;

                $cleanRows[] = [
                    'item_id'       => (int) $itemId,
                    'item_type'     => 'service',
                    'description'   => $desc,
                    'hsn'           => $hsn,
                    'qty'           => $qty,
                    'tax_percent'   => $taxPct,
                    'service_rate'  => $serviceRate,
                    'rate'          => $lineBase,
                    'tax_amount'    => $lineTax,
                    'amount'        => round($lineBase + $lineTax, 2),
                    'gold_wt'       => 0,
                    'silver_wt'     => 0,
                    'gold_rate'     => 0,
                    'silver_rate'   => 0,
                    'gemstone_wt'   => 0,
                    'diamond_wt'    => 0,
                    'making_rate'   => 0,
                ];

                continue;
            }

            $goldWt      = round((float) ($row['gold_wt'] ?? 0), 3);
            $silverWt    = round((float) ($row['silver_wt'] ?? 0), 3);
            $goldRate    = round((float) ($row['gold_rate'] ?? 0), 2);
            $silverRate  = round((float) ($row['silver_rate'] ?? 0), 2);
            $makingRate  = round((float) ($row['making_rate'] ?? 0), 2);
            $gemstoneWt  = round((float) ($row['gemstone_wt'] ?? 0), 3);
            $diamondWt   = round((float) ($row['diamond_wt'] ?? 0), 3);

            $lineBase = round((($goldWt * $goldRate) + ($silverWt * $silverRate) + $makingRate) * $qty, 2);
            $lineTax  = round($lineBase * ($taxPct / 100), 2);

            $subtotal += $lineBase;
            $weightedTax += ($lineBase * $taxPct);
            $itemsTaxTotal += $lineTax;

            $cleanRows[] = [
                'item_id'      => (int) $itemId,
                'item_type'    => 'product',
                'description'  => $desc,
                'hsn'          => $hsn,
                'qty'          => $qty,
                'tax_percent'  => $taxPct,
                'gold_wt'      => $goldWt,
                'silver_wt'    => $silverWt,
                'gold_rate'    => $goldRate,
                'silver_rate'  => $silverRate,
                'gemstone_wt'  => $gemstoneWt,
                'diamond_wt'   => $diamondWt,
                'making_rate'  => $makingRate,
                'rate'         => $lineBase,
                'tax_amount'   => $lineTax,
                'amount'       => round($lineBase + $lineTax, 2),
            ];
        }

        $subtotal = round($subtotal, 2);
        $itemsTaxTotal = round($itemsTaxTotal, 2);

        $avgTaxPercent = $subtotal > 0
            ? round($weightedTax / $subtotal, 2)
            : 0.00;

        $discountTotal = round((float) ($data['discount_total'] ?? 0), 2);
        $chargeTotal   = round((float) ($data['charge_total'] ?? 0), 2);
        $roundOff      = round((float) ($data['round_off'] ?? 0), 2);

        $taxableAmount = round(max(0, $subtotal - $discountTotal + $chargeTotal), 2);

        $taxAmount = $avgTaxPercent > 0
            ? round($taxableAmount * ($avgTaxPercent / 100), 2)
            : 0.00;

        $tcsPercent = round((float) ($data['tcs_percent'] ?? 0), 2);
        $tcsAmount  = round((float) ($data['tcs_amount'] ?? 0), 2);

        if ($tcsAmount <= 0 && $tcsPercent > 0) {
            $tcsAmount = round($taxableAmount * ($tcsPercent / 100), 2);
        }

        $lessAmount = round((float) ($data['less_amount'] ?? $discountTotal), 2);

        $grandTotal = round($taxableAmount + $taxAmount + $tcsAmount + $roundOff, 2);

        $cash = $online = $card = $cheque = $credit = $advance = 0.0;
        $receivedTotal = 0.0;
        $balance = $grandTotal;

        if ($docType === 'tax') {
            $cash    = round((float) ($pay['pay_cash'] ?? 0), 2);
            $online  = round((float) ($pay['pay_upi'] ?? 0), 2);
            $card    = round((float) ($pay['pay_card'] ?? 0), 2);
            $cheque  = round((float) ($pay['pay_cheque'] ?? 0), 2);
            $credit  = round((float) ($pay['credit_sales_excess'] ?? 0), 2);
            $advance = round((float) ($pay['advance_amount'] ?? 0), 2);

            $receivedTotal = round($cash + $online + $card + $cheque, 2);
            $balance = round(max(0, $grandTotal - $receivedTotal - $advance - $credit), 2);
        }

        $bizCode   = $this->normCode($biz->state_code ?? '');
        $partyCode = $this->normCode($client->state_code ?? '');

        $isIntra = false;

        if ($bizCode !== '' && $partyCode !== '') {
            $isIntra = ($bizCode === $partyCode);
        } else {
            $bizState   = strtolower(trim((string) ($biz->state ?? '')));
            $partyState = strtolower(trim((string) ($client->state ?? '')));
            if ($bizState !== '' && $partyState !== '') {
                $isIntra = ($bizState === $partyState);
            }
        }

        $cgstPercent = $isIntra ? round($avgTaxPercent / 2, 2) : 0.00;
        $sgstPercent = $isIntra ? round($avgTaxPercent / 2, 2) : 0.00;
        $igstPercent = $isIntra ? 0.00 : round($avgTaxPercent, 2);

        $cgst = $isIntra ? round($taxAmount / 2, 2) : 0.00;
        $sgst = $isIntra ? round($taxAmount / 2, 2) : 0.00;
        $igst = $isIntra ? 0.00 : round($taxAmount, 2);

        $reqInvoiceNo = trim((string) ($data['invoice_number'] ?? ''));
        if ($reqInvoiceNo === '') {
            return response()->json([
                'ok' => false,
                'message' => 'invoice_number is required',
            ], 422);
        }

        if (
            Invoice::where('business_id', $bid)
                ->where('invoice_number', $reqInvoiceNo)
                ->where('id', '!=', $invoice->id)
                ->exists()
        ) {
            return response()->json([
                'ok' => false,
                'message' => 'Invoice number already exists',
                'invoice_number' => $reqInvoiceNo,
            ], 409);
        }

        InvoiceNumber::syncNextSeqIfMatches((int) $bid, $invoiceDate, $reqInvoiceNo, 3, $docType);

        DB::transaction(function () use (
            $invoice,
            $bid,
            $data,
            $client,
            $invoiceDate,
            $prefix,
            $docType,
            $reqInvoiceNo,
            $subtotal,
            $discountTotal,
            $chargeTotal,
            $lessAmount,
            $taxAmount,
            $cgstPercent,
            $sgstPercent,
            $igstPercent,
            $cgst,
            $sgst,
            $igst,
            $tcsPercent,
            $tcsAmount,
            $roundOff,
            $grandTotal,
            $receivedTotal,
            $balance,
            $cleanRows,
            $cash,
            $online,
            $card,
            $cheque,
            $credit,
            $advance,
            $pay,
            $chargesJson
        ) {
            $invoice->update([
                'client_id'             => (int) $data['client_id'],
                'invoice_date'          => $invoiceDate,

                'invoice_prefix'        => $prefix,
                'invoice_number'        => $reqInvoiceNo,
                'invoice_type'          => $docType,

                'subtotal'              => $subtotal,
                'discount_total'        => $discountTotal,
                'charge_total'          => $chargeTotal,
                'less_amount'           => $lessAmount,

                'tax_amount'            => $taxAmount,

                'cgst_percent'          => $cgstPercent,
                'cgst_amount'           => $cgst,
                'sgst_percent'          => $sgstPercent,
                'sgst_amount'           => $sgst,
                'igst_percent'          => $igstPercent,
                'igst_amount'           => $igst,

                'tcs_percent'           => $tcsPercent,
                'tcs_amount'            => $tcsAmount,
                'round_off'             => $roundOff,

                'total'                 => $grandTotal,
                'received_amount'       => $docType === 'tax' ? $receivedTotal : 0,
                'balance'               => $docType === 'tax' ? $balance : $grandTotal,

                'payment_method'        => $data['payment_method'] ?? null,
                'gst_no'                => $data['gst_no'] ?: null,
                'transport_mode'        => !empty($data['transport_mode']) ? $data['transport_mode'] : null,
                'reverse_charge'        => !empty($data['reverse_charge']) ? 1 : 0,

                'place_of_supply_state' => $client->state ?? null,
                'place_of_supply_code'  => $client->state_code ?? null,

                'notes'                 => $data['notes'] ?? null,
                'terms'                 => $data['terms'] ?? null,

                'charges_json'          => $chargesJson,
                'items_json'            => json_encode($cleanRows, JSON_UNESCAPED_UNICODE),
                'amount_in_words'       => '',
            ]);

            InvoiceItem::where('invoice_id', $invoice->id)->delete();

            foreach ($cleanRows as $row) {
                InvoiceItem::create([
                    'invoice_id'      => $invoice->id,
                    'item_id'         => $row['item_id'],
                    'description'     => $row['description'],
                    'hsn_code'        => $row['hsn'] ?: null,
                    'quantity'        => (int) $row['qty'],

                    'gold_wt'         => (float) ($row['gold_wt'] ?? 0),
                    'silver_wt'       => (float) ($row['silver_wt'] ?? 0),
                    'gold_rate'       => (float) ($row['gold_rate'] ?? 0),
                    'silver_rate'     => (float) ($row['silver_rate'] ?? 0),
                    'gemstone_wt_ct'  => (float) ($row['gemstone_wt'] ?? 0),
                    'diamond_wt_ct'   => (float) ($row['diamond_wt'] ?? 0),

                    'making_rate'     => $row['item_type'] === 'product'
                        ? (float) ($row['making_rate'] ?? 0)
                        : null,

                    'making_charge'   => $row['item_type'] === 'service'
                        ? (float) ($row['service_rate'] ?? 0)
                        : null,

                    'tax_percent'     => (float) ($row['tax_percent'] ?? 0),
                    'rate'            => (float) ($row['rate'] ?? 0),
                    'amount'          => (float) ($row['amount'] ?? 0),
                ]);
            }

            if ($docType === 'tax') {
                $payRow = InvoicePayment::where('invoice_id', $invoice->id)->latest('id')->first();

                if (!$payRow) {
                    $payRow = new InvoicePayment();
                }

                $payRow->fill([
                    'business_id'                 => $bid,
                    'invoice_id'                  => $invoice->id,
                    'client_id'                   => (int) $data['client_id'],
                    'total_value'                 => $grandTotal,

                    'cash_amount'                 => $cash,
                    'online_amount'               => $online,
                    'card_amount'                 => $card,
                    'cheque_amount'               => $cheque,

                    'online_mode'                 => $pay['online_mode'] ?? null,
                    'online_ref'                  => $pay['online_ref'] ?? null,
                    'upi_id'                      => $pay['upi_id'] ?? null,
                    'card_last4'                  => $pay['card_last4'] ?? null,
                    'card_ref'                    => $pay['card_ref'] ?? null,
                    'cheque_no'                   => $pay['cheque_no'] ?? null,
                    'bank_name'                   => $pay['bank_name'] ?? null,

                    'credit_sales_excess_amount' => $credit,
                    'advance_amount'              => $advance,
                    'received_total'              => $receivedTotal,
                    'notes'                       => $pay['pay_notes'] ?? null,
                    'paid_at'                     => $receivedTotal > 0 ? now() : null,
                ]);

                $payRow->save();
            }
        });

        if ($docType === 'tax') {
            if (method_exists($this->stock, 'rollbackSale')) {
                $this->stock->rollbackSale($invoice);
            }

            $invoice->load('items');
            $this->stock->recordSale($invoice);
        }

        return response()->json([
            'ok'      => true,
            'message' => ucfirst($docType) . ' updated',
            'invoice' => $invoice->fresh(['client', 'items', 'business']),
        ]);
    }

    // ------------------------------------------------------------
    // DELETE /api/invoices/{invoice}
    // ------------------------------------------------------------
    public function destroy(Request $request, Invoice $invoice)
    {
        $bid = $this->activeBusinessId($request);
        if ((int)$invoice->business_id !== (int)$bid) {
            return response()->json(['ok'=>false,'message'=>'Unauthorized'], 403);
        }

        $invoice->delete();

        return response()->json(['ok'=>true,'message'=>'Deleted']);
    }


    public function preview(Request $request)
    {
        $user = $request->user();

        // ✅ body (form-data/json) OR query param
        $type = strtolower(trim((string) $request->input('type', $request->query('type', 'proforma'))));
        if (!in_array($type, ['tax','proforma','quotation'], true)) {
            $type = 'proforma';
        }

        $date = $request->input('date', $request->query('date'));
        $date = $date ? \Carbon\Carbon::parse($date)->toDateString() : now()->toDateString();

        $digits = (int) $request->input('digits', $request->query('digits', 3));
        if ($digits < 1 || $digits > 6) $digits = 3;

        $bid = DB::table('business_user')->where('user_id', $user->id)->first()?->business_id;
        
        $business = Business::find($bid);
        if (!$business) {
            return response()->json(['ok' => false, 'msg' => 'Business not found.'], 404);
        }

        $taxBase = optional(
                $user->businesses()->where('businesses.id', $bid)->first()
            )->invoice_base_prefix ?? 'RV/SL';

        $base = ($type === 'proforma') ? 'PF' : (($type === 'quotation') ? 'QT' : $taxBase);

        $suggestedPrefix = \App\Services\InvoiceNumber::previewPrefix($date, $base);
        $preview         = \App\Services\InvoiceNumber::peek($bid, $date, $suggestedPrefix, $digits, $type);

        return response()->json([
            'ok' => true,
            'data' => [
                'business_id'      => $bid,
                'type'             => $type,
                'date'             => $date,
                'digits'           => $digits,
                'base_prefix'      => $base,
                'suggested_prefix' => $suggestedPrefix,
                'invoice_number'   => $preview['full'] ?? null,
                'next_sequence'    => $preview['seq'] ?? null,
            ],
        ]);
    }


    // ------------------------------------------------------------
    // GET /api/invoices/{invoice}/pdf  (stream inline)
    // ------------------------------------------------------------
    public function pdf(Request $request, Invoice $invoice)
    {
        $bid = $this->activeBusinessId($request);
        if ((int)$invoice->business_id !== (int)$bid) {
            return response()->json(['ok'=>false,'message'=>'Unauthorized'], 403);
        }

        // If already saved
        if (!empty($invoice->pdf_url) && Storage::disk('public')->exists($invoice->pdf_url)) {
            $path = Storage::disk('public')->path($invoice->pdf_url);
            return response()->file($path, ['Content-Type'=>'application/pdf']);
        }

        // else generate on fly (simple template)
        $invoice->load(['client','items','business']);
        $pdf = Pdf::loadView('invoices.pdf_simple', [
            'invoice'=>$invoice,
            'inv'=>$invoice,
            'biz'=>$invoice->business,
            'client'=>$invoice->client,
            'items'=>$invoice->items,
        ])->setPaper('a4');

        return response($pdf->output(), 200, ['Content-Type'=>'application/pdf']);
    }

    // ------------------------------------------------------------
    // GET /api/invoices/{invoice}/pdf-url
    // ------------------------------------------------------------
    public function pdfUrl(Request $request, Invoice $invoice)
    {
        $bid = $this->activeBusinessId($request);
        if ((int)$invoice->business_id !== (int)$bid) {
            return response()->json(['ok'=>false,'message'=>'Unauthorized'], 403);
        }

        if (!empty($invoice->pdf_url) && Storage::disk('public')->exists($invoice->pdf_url)) {
            return response()->json([
                'ok'=>true,
                'url'=> Storage::disk('public')->url($invoice->pdf_url),
            ]);
        }

        return response()->json(['ok'=>false,'message'=>'PDF not found'], 404);
    }

    // ------------------------------------------------------------
    // POST /api/invoices/{invoice}/convert-to-tax
    // ------------------------------------------------------------
    public function convertToTax(Request $request, Invoice $invoice)
    {
        $bid = $this->activeBusinessId($request);
        if ((int)$invoice->business_id !== (int)$bid) {
            return response()->json(['ok'=>false,'message'=>'Unauthorized'], 403);
        }

        $fromType = strtolower((string)$invoice->invoice_type);
        if (!in_array($fromType, ['quotation','proforma'], true)) {
            return response()->json(['ok'=>false,'message'=>'Only quotation/proforma can be converted'], 422);
        }

        $biz = Business::findOrFail($bid);
        $invoiceDate = now()->toDateString();

        $taxBase = $biz->invoice_base_prefix ?? 'RV/SL';
        $taxSeries = InvoiceNumber::previewPrefix($invoiceDate, $taxBase);
        $alloc = InvoiceNumber::next((int)$bid, $invoiceDate, $taxSeries, 3, 'tax');

        DB::transaction(function () use ($invoice, $invoiceDate, $taxSeries, $alloc) {
            $invoice->update([
                'invoice_type'=>'tax',
                'invoice_date'=>$invoiceDate,
                'invoice_prefix'=>$taxSeries,
                'invoice_number'=>$alloc['full'],
                'received_amount'=>0,
                'balance'=>(float)($invoice->total ?? 0),
                'converted_at'=>now(), // if column exists
            ]);
        });

        $invoice->load('items');
        $this->stock->recordSale($invoice);

        return response()->json([
            'ok'=>true,
            'message'=>'Converted to tax',
            'invoice'=>$invoice->fresh(['client','items','business']),
        ]);
    }




public function show(Request $request, Invoice $invoice)
{
   
    $safeNumber = str_replace(['/', '\\'], '-', (string)($invoice->invoice_number ?? 'INV'));
    $disk = Storage::disk('public');

    // Always use fresh relations for PDF
    $invoice = $invoice->fresh(['client', 'items', 'business']);

    try {
        // ✅ 1) If DB has pdf_url and file exists => return file content
        if (!empty($invoice->pdf_url)) {
            $path = $this->normalizePdfPath($invoice->pdf_url);

            if ($path && $disk->exists($path)) {
                $content = $disk->get($path);

                return response($content, 200, [
                    'Content-Type'        => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="Invoice-' . $safeNumber . '.pdf"',
                ]);
            }

            // file missing but db has path -> reset
            $invoice->update(['pdf_url' => null]);
        }

        // ✅ 2) Generate fresh pdf
        $pdf = $this->simplePdfBuild($invoice);

        $fileName = 'invoices/Invoice-' . $safeNumber . '.pdf';

        // ensure folder exists
        if (!$disk->exists('invoices')) {
            $disk->makeDirectory('invoices');
        }

        // save
        $disk->put($fileName, $pdf->output());

        // update db
        $invoice->update(['pdf_url' => $fileName]);

        // return inline
        return response($pdf->output(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="Invoice-' . $safeNumber . '.pdf"',
        ]);
    } catch (\Throwable $e) {
        Log::error('API Invoice PDF failed', [
            'invoice_id' => $invoice->id ?? null,
            'error'      => $e->getMessage(),
        ]);

        return response()->json([
            'status'  => false,
            'message' => 'PDF generate failed',
            'error'   => $e->getMessage(), // production me remove
        ], 500);
    }
}

/**
 * Normalize stored path (handles "storage/..", leading slash, full url cases)
 */
protected function normalizePdfPath(?string $pdfUrl): ?string
{
    if (!$pdfUrl) return null;

    $p = trim($pdfUrl);

    // if full url => extract path after /storage/
    if (preg_match('~^https?://~i', $p)) {
        $pos = stripos($p, '/storage/');
        if ($pos !== false) $p = substr($p, $pos + strlen('/storage/'));
    }

    $p = str_replace('\\', '/', $p);
    $p = ltrim($p, '/');

    // if starts with storage/ => remove it for public disk
    if (str_starts_with($p, 'storage/')) {
        $p = substr($p, strlen('storage/'));
    }

    return $p ?: null;
}


protected function imageDataUri(?string $pathOrUrl): ?string
{
    if (!$pathOrUrl) return null;

    $value = trim((string) $pathOrUrl);

    // Already data URI
    if (str_starts_with($value, 'data:image/')) {
        return $value;
    }

    // If full URL -> try to convert to relative storage path
    if (preg_match('~^https?://~i', $value)) {
        $pos = stripos($value, '/storage/');
        if ($pos !== false) {
            $value = substr($value, $pos + strlen('/storage/')); // after /storage/
        } else {
            // Remote URL -> don't fetch (dompdf often fails). Just skip.
            return null;
        }
    }

    $value = str_replace('\\', '/', $value);
    $value = ltrim($value, '/');

    // normalize for public disk
    if (str_starts_with($value, 'storage/')) {
        $value = substr($value, strlen('storage/'));
    }

    $disk = Storage::disk('public');

    if (!$disk->exists($value)) {
        return null;
    }

    $absPath = $disk->path($value);
    $ext = strtolower(pathinfo($absPath, PATHINFO_EXTENSION));

    $mime = match ($ext) {
        'png'  => 'image/png',
        'jpg', 'jpeg' => 'image/jpeg',
        'webp' => 'image/webp',
        'svg'  => 'image/svg+xml',
        default => null,
    };

    if (!$mime) return null;

    $data = file_get_contents($absPath);
    if ($data === false) return null;

    return "data:{$mime};base64," . base64_encode($data);
}

    
    /**
     * Build invoice PDF (same controller)
     */
    protected function simplePdfBuild(Invoice $invoice): \Barryvdh\DomPDF\PDF
    {
        $invoice->load(['client', 'items', 'business']);

        $inv    = $invoice;
        $biz    = $invoice->business;
        $client = $invoice->client;
        $items  = $invoice->items ?? collect();

        // ✅ payment row
        $payRow = InvoicePayment::where('invoice_id', $inv->id)
            ->latest('id')
            ->first();

        // ✅ charges
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

        // ✅ totals
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

        // ✅ FIXED: correct taxAmount
        $taxAmount = (float)($cgst_amount + $sgst_amount + $igst_amount);

        // ✅ data URIs (logo/sign)
        $logoDataUri = $this->imageDataUri($biz?->logo);
        $signDataUri = $this->imageDataUri($biz?->signature);

        $type = $invoice->invoice_type;

        $vm = compact(
            'inv','invoice','biz','client','items','charges','type','taxAmount',
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

        // ✅ FIXED: safe view resolve + fallback if missing
        $view = 'invoices.' . (($biz?->billTemplate->page_name) ?: 'pdf_simple');
        if (!view()->exists($view)) {
            $view = 'invoices.pdf_simple';
        }

        return Pdf::loadView($view, $vm)->setPaper('a4');
    }
}
