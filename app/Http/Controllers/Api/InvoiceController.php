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
use Mpdf\Mpdf;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Illuminate\Http\Response;
use App\Models\Item;

use App\Models\BankAccount;
use App\Models\HospitalBed;
use App\Models\InvoiceAdditionalCharge;
use App\Models\PatientVisit;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class InvoiceController extends Controller
{
    public function __construct(protected StockService $stock) {}

    // ------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------
    protected function activeBusinessId(Request $request): int
    {
        $me = $request->user();
        $bid = (int)($me->current_business_id ?? session('active_business_id') ?? 0);
        if (!$bid) {
            $bid = (int)($me->businesses()->pluck('businesses.id')->first() ?? 0);
        }
        if (!$bid) abort(422, 'Active business not found.');
        return $bid;
    }

    protected function activeBusinessId1(Request $request): int
    { $bid = (int) $request->input('business_id');

        abort_unless($bid > 0, 422, 'business_id is required.');

        $user = $request->user();

        $hasBusiness = $user->businesses()
            ->where('businesses.id', $bid)
            ->exists();

        abort_unless($hasBusiness, 403, 'You do not have access to this business.');

        return $bid;
    }


    protected function requestBusinessId(Request $request): int
    {
        $user = $request->user();

        $bid = (int) (
            $request->input('business_id')
            ?? $request->query('business_id')
            ?? $request->header('X-Business-Id')
            ?? $user->current_business_id
            ?? session('active_business_id')
            ?? 0
        );

        if (!$bid) {
            $bid = (int) ($user->businesses()->pluck('businesses.id')->first() ?? 0);
        }

        abort_unless($bid > 0, 422, 'business_id is required.');

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


    public function index(Request $request, $type = 'tax')
    {
        $me = $request->user();
        $bid = $this->activeBusinessId1($request);

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

    // public function store(Request $request, $type = 'tax')
    // {
    //     $me = $request->user();

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Resolve Business
    //     |--------------------------------------------------------------------------
    //     */

    //     $bid = $this->selectedBusinessId($request);

    //     $docType = $this->normalizeDocType((string) $type);

    //     if (!$me->can($this->requiredPerm($docType))) {
    //         return response()->json([
    //             'ok' => false,
    //             'message' => 'Permission denied',
    //         ], 403);
    //     }

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Base Validation
    //     |--------------------------------------------------------------------------
    //     */

    //     $data = $request->validate([
    //         'client_id' => [
    //             'required',
    //             'integer',
    //         ],

    //         'invoice_date' => [
    //             'required',
    //             'date',
    //         ],

    //         'invoice_prefix' => [
    //             'nullable',
    //             'string',
    //             'max:255',
    //         ],

    //         'invoice_number' => [
    //             'nullable',
    //             'string',
    //             'max:255',
    //         ],

    //         'transport_mode' => [
    //             'nullable',
    //             'string',
    //             'max:255',
    //         ],

    //         'reverse_charge' => [
    //             'nullable',
    //         ],

    //         'notes' => [
    //             'nullable',
    //             'string',
    //             'max:5000',
    //         ],

    //         'terms' => [
    //             'nullable',
    //             'string',
    //             'max:5000',
    //         ],

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Item input
    //         |--------------------------------------------------------------------------
    //         */

    //         'items_json' => [
    //             'nullable',
    //         ],

    //         'items' => [
    //             'nullable',
    //             'array',
    //             'min:1',
    //         ],

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Totals
    //         |--------------------------------------------------------------------------
    //         */

    //         'charges_json' => [
    //             'nullable',
    //         ],

    //         'discount_total' => [
    //             'nullable',
    //             'numeric',
    //             'min:0',
    //         ],

    //         'charge_total' => [
    //             'nullable',
    //             'numeric',
    //             'min:0',
    //         ],

    //         'tcs_percent' => [
    //             'nullable',
    //             'numeric',
    //             'min:0',
    //             'max:100',
    //         ],

    //         'tcs_amount' => [
    //             'nullable',
    //             'numeric',
    //             'min:0',
    //         ],

    //         'round_off' => [
    //             'nullable',
    //             'numeric',
    //         ],

    //         'less_amount' => [
    //             'nullable',
    //             'numeric',
    //             'min:0',
    //         ],

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Payment / Bank
    //         |--------------------------------------------------------------------------
    //         */

    //         'payment_method' => [
    //             'nullable',
    //             'string',
    //             'max:255',
    //         ],

    //         'bank_account_id' => [
    //             'nullable',
    //             'integer',
    //         ],
    //     ]);

    //     $toNumber = static function (
    //         $value,
    //         float $default = 0.0
    //     ): ?float {
    //         if ($value === null || $value === '') {
    //             return $default;
    //         }

    //         if (is_int($value) || is_float($value)) {
    //             return (float) $value;
    //         }

    //         if (is_string($value)) {
    //             $value = trim($value);

    //             if ($value === '') {
    //                 return $default;
    //             }

    //             $value = str_replace(',', '', $value);
    //         }

    //         if (!is_numeric($value)) {
    //             return null;
    //         }

    //         return (float) $value;
    //     };

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Items Input Normalize
    //     |--------------------------------------------------------------------------
    //     */

    //     $rows = null;

    //     $itemsJsonInput = $request->input('items_json');

    //     if (is_string($itemsJsonInput)) {
    //         $itemsJsonInput = trim($itemsJsonInput);

    //         if ($itemsJsonInput !== '') {
    //             $rows = json_decode(
    //                 $itemsJsonInput,
    //                 true
    //             );

    //             if (
    //                 json_last_error() !== JSON_ERROR_NONE
    //                 || !is_array($rows)
    //             ) {
    //                 return response()->json([
    //                     'ok' => false,
    //                     'message' => 'items_json invalid JSON hai.',
    //                     'error' => json_last_error_msg(),
    //                 ], 422);
    //             }
    //         }
    //     } elseif (is_array($itemsJsonInput)) {
    //         $rows = $itemsJsonInput;
    //     }

    //     /*
    //     |----------------------------------------------------------------------
    //     | "items" fallback
    //     |----------------------------------------------------------------------
    //     */

    //     if (
    //         (!$rows || count($rows) < 1)
    //         && !empty($data['items'])
    //         && is_array($data['items'])
    //     ) {
    //         $rows = $data['items'];
    //     }

    //     if (!is_array($rows) || count($rows) < 1) {
    //         return response()->json([
    //             'ok' => false,
    //             'message' => 'items_json or items is required',
    //         ], 422);
    //     }

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Single associative item support
    //     |--------------------------------------------------------------------------
    //     |
    //     | Agar frontend:
    //     |
    //     | { item_id: 1, ... }
    //     |
    //     | bhej de instead of:
    //     |
    //     | [{ item_id: 1, ... }]
    //     |
    //     */

    //     if (!array_is_list($rows)) {
    //         $rows = [$rows];
    //     }

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Charges JSON Normalize
    //     |--------------------------------------------------------------------------
    //     */

    //     $chargesJson = null;

    //     $chargesJsonInput = $request->input(
    //         'charges_json'
    //     );

    //     if (is_string($chargesJsonInput)) {
    //         $chargesJsonInput = trim(
    //             $chargesJsonInput
    //         );

    //         if ($chargesJsonInput !== '') {
    //             $decodedCharges = json_decode(
    //                 $chargesJsonInput,
    //                 true
    //             );

    //             if (
    //                 json_last_error() !== JSON_ERROR_NONE
    //                 || !is_array($decodedCharges)
    //             ) {
    //                 return response()->json([
    //                     'ok' => false,
    //                     'message' => 'charges_json invalid JSON hai.',
    //                     'error' => json_last_error_msg(),
    //                 ], 422);
    //             }

    //             $chargesJson = json_encode(
    //                 $decodedCharges,
    //                 JSON_UNESCAPED_UNICODE
    //             );
    //         }
    //     } elseif (is_array($chargesJsonInput)) {
    //         $chargesJson = json_encode(
    //             $chargesJsonInput,
    //             JSON_UNESCAPED_UNICODE
    //         );
    //     }

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Payment Validation
    //     |--------------------------------------------------------------------------
    //     */

    //     $pay = [];

    //     if ($docType === 'tax') {
    //         $pay = $request->validate([
    //             'pay_cash' => [
    //                 'nullable',
    //                 'numeric',
    //                 'min:0',
    //             ],

    //             'pay_upi' => [
    //                 'nullable',
    //                 'numeric',
    //                 'min:0',
    //             ],

    //             'pay_card' => [
    //                 'nullable',
    //                 'numeric',
    //                 'min:0',
    //             ],

    //             'pay_cheque' => [
    //                 'nullable',
    //                 'numeric',
    //                 'min:0',
    //             ],

    //             'credit_sales_excess' => [
    //                 'nullable',
    //                 'numeric',
    //                 'min:0',
    //             ],

    //             'advance_amount' => [
    //                 'nullable',
    //                 'numeric',
    //                 'min:0',
    //             ],

    //             'online_mode' => [
    //                 'nullable',
    //                 'string',
    //                 'max:30',
    //             ],

    //             'online_ref' => [
    //                 'nullable',
    //                 'string',
    //                 'max:100',
    //             ],

    //             'upi_id' => [
    //                 'nullable',
    //                 'string',
    //                 'max:100',
    //             ],

    //             'card_last4' => [
    //                 'nullable',
    //                 'string',
    //                 'max:4',
    //             ],

    //             'card_ref' => [
    //                 'nullable',
    //                 'string',
    //                 'max:100',
    //             ],

    //             'cheque_no' => [
    //                 'nullable',
    //                 'string',
    //                 'max:50',
    //             ],

    //             'bank_name' => [
    //                 'nullable',
    //                 'string',
    //                 'max:100',
    //             ],

    //             /*
    //             | Web + old API dono naming support
    //             */

    //             'pay_notes' => [
    //                 'nullable',
    //                 'string',
    //                 'max:2000',
    //             ],

    //             'payment_notes' => [
    //                 'nullable',
    //                 'string',
    //                 'max:2000',
    //             ],
    //         ]);
    //     }

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Business
    //     |--------------------------------------------------------------------------
    //     */

    //     $biz = Business::withoutGlobalScopes()
    //         ->find($bid);

    //     if (!$biz) {
    //         return response()->json([
    //             'ok' => false,
    //             'message' => 'Business not found',
    //         ], 404);
    //     }

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Client
    //     |--------------------------------------------------------------------------
    //     */

    //     $client = Client::withoutGlobalScopes()
    //         ->where('business_id', $bid)
    //         ->where(
    //             'id',
    //             (int) $data['client_id']
    //         )
    //         ->first();

    //     if (!$client) {
    //         return response()->json([
    //             'ok' => false,
    //             'message' => 'Client not found for this business',
    //         ], 404);
    //     }

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Invoice Date
    //     |--------------------------------------------------------------------------
    //     */

    //     $invoiceDate = \Carbon\Carbon::parse(
    //         $data['invoice_date']
    //     )->toDateString();

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Invoice Prefix
    //     |--------------------------------------------------------------------------
    //     */

    //     $prefix = trim(
    //         (string) ($data['invoice_prefix'] ?? '')
    //     );

    //     if ($prefix === '') {
    //         $base = $docType === 'proforma'
    //             ? 'PF'
    //             : (
    //                 $docType === 'quotation'
    //                     ? 'QT'
    //                     : (
    //                         $biz->invoice_base_prefix
    //                         ?? 'INV'
    //                     )
    //             );

    //         $prefix = InvoiceNumber::previewPrefix(
    //             $invoiceDate,
    //             $base
    //         );

    //         if (!$prefix) {
    //             $prefix = $this->computePrefix(
    //                 $invoiceDate,
    //                 $base
    //             );
    //         }
    //     }

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Invoice Number
    //     |--------------------------------------------------------------------------
    //     */

    //     $reqInvoiceNo = trim(
    //         (string) ($data['invoice_number'] ?? '')
    //     );

    //     if ($reqInvoiceNo === '') {
    //         $alloc = InvoiceNumber::next(
    //             (int) $bid,
    //             $invoiceDate,
    //             $prefix,
    //             3,
    //             $docType
    //         );

    //         $reqInvoiceNo = $alloc['full']
    //             ?? null;
    //     }

    //     if (!$reqInvoiceNo) {
    //         return response()->json([
    //             'ok' => false,
    //             'message' => 'Invoice number generate failed',
    //         ], 422);
    //     }

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Duplicate Invoice Number
    //     |--------------------------------------------------------------------------
    //     */

    //     if (
    //         Invoice::withoutGlobalScopes()
    //             ->where('business_id', $bid)
    //             ->where(
    //                 'invoice_number',
    //                 $reqInvoiceNo
    //             )
    //             ->exists()
    //     ) {
    //         return response()->json([
    //             'ok' => false,
    //             'message' => 'Invoice number already exists',
    //             'invoice_number' => $reqInvoiceNo,
    //         ], 409);
    //     }

    //     /*
    //     |--------------------------------------------------------------------------
    //     | ITEM CALCULATION
    //     |--------------------------------------------------------------------------
    //     |
    //     | IMPORTANT:
    //     |
    //     | Calculation web store() ke same pattern par hai.
    //     |
    //     | Base Price:
    //     |
    //     | fixed_price
    //     | -> service_rate
    //     | -> price
    //     | -> unit_rate
    //     | -> gold/silver metal value
    //     |
    //     */

    //     $subtotal = 0.0;
    //     $weightedTax = 0.0;
    //     $itemsTaxTotal = 0.0;

    //     $cleanRows = [];

    //     foreach ($rows as $index => $row) {
    //         $rowNo = $index + 1;

    //         if (!is_array($row)) {
    //             return response()->json([
    //                 'ok' => false,
    //                 'message' => "Row {$rowNo} invalid hai.",
    //             ], 422);
    //         }

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Item ID
    //         |--------------------------------------------------------------------------
    //         */

    //         $itemId = $row['item_id']
    //             ?? null;

    //         if (!$itemId) {
    //             return response()->json([
    //                 'ok' => false,
    //                 'message' => "Row {$rowNo} item_id missing",
    //             ], 422);
    //         }

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Verify Item
    //         |--------------------------------------------------------------------------
    //         */

    //         $item = Item::withoutGlobalScopes()
    //             ->where(
    //                 'business_id',
    //                 $bid
    //             )
    //             ->where(
    //                 'id',
    //                 $itemId
    //             )
    //             ->where(
    //                 'is_active',
    //                 true
    //             )
    //             ->first();

    //         if (!$item) {
    //             return response()->json([
    //                 'ok' => false,
    //                 'message' => "Row {$rowNo} ka item invalid/inactive hai.",
    //             ], 422);
    //         }

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Description
    //         |--------------------------------------------------------------------------
    //         */

    //         $description = trim(
    //             (string) (
    //                 $row['description']
    //                 ?? $item->description
    //                 ?? $item->name
    //                 ?? ''
    //             )
    //         );

    //         if ($description === '') {
    //             return response()->json([
    //                 'ok' => false,
    //                 'message' => "Row {$rowNo} description missing",
    //             ], 422);
    //         }

    //         /*
    //         |--------------------------------------------------------------------------
    //         | HSN / SAC
    //         |--------------------------------------------------------------------------
    //         */

    //         $hsn = trim(
    //             (string) (
    //                 $row['hsn']
    //                 ?? $row['sac']
    //                 ?? $item->sac
    //                 ?? $item->hsn
    //                 ?? ''
    //             )
    //         );

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Item Type
    //         |--------------------------------------------------------------------------
    //         */

    //         $itemType = strtolower(
    //             trim(
    //                 (string) (
    //                     $row['item_type']
    //                     ?? $item->type
    //                     ?? 'product'
    //                 )
    //             )
    //         );

    //         if (
    //             !in_array(
    //                 $itemType,
    //                 [
    //                     'product',
    //                     'service',
    //                 ],
    //                 true
    //             )
    //         ) {
    //             $itemType = 'product';
    //         }

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Quantity
    //         |--------------------------------------------------------------------------
    //         |
    //         | IMPORTANT:
    //         | int cast nahi karna.
    //         |
    //         | 1.5 quantity ko 1 banana calculation error tha.
    //         |
    //         */

    //         $quantity = $toNumber(
    //             $row['qty']
    //                 ?? $row['quantity']
    //                 ?? 1,
    //             1
    //         );

    //         if (
    //             $quantity === null
    //             || $quantity <= 0
    //         ) {
    //             return response()->json([
    //                 'ok' => false,
    //                 'message' => "Row {$rowNo} quantity invalid hai.",
    //             ], 422);
    //         }

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Tax %
    //         |--------------------------------------------------------------------------
    //         */

    //         $taxPercent = $toNumber(
    //             $row['tax_percent']
    //                 ?? 0
    //         );

    //         if (
    //             $taxPercent === null
    //             || $taxPercent < 0
    //             || $taxPercent > 100
    //         ) {
    //             return response()->json([
    //                 'ok' => false,
    //                 'message' => "Row {$rowNo} tax percentage invalid hai.",
    //             ], 422);
    //         }

    //         $taxPercent = round(
    //             $taxPercent,
    //             2
    //         );

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Fixed / Service / Normal Price
    //         |--------------------------------------------------------------------------
    //         */

    //         $priceValue = null;

    //         $priceCandidates = [
    //             $row['fixed_price'] ?? null,
    //             $row['service_rate'] ?? null,
    //             $row['price'] ?? null,
    //             $row['unit_rate'] ?? null,
    //         ];

    //         foreach ($priceCandidates as $candidate) {
    //             if (
    //                 $candidate !== null
    //                 && $candidate !== ''
    //             ) {
    //                 $priceValue = $candidate;
    //                 break;
    //             }
    //         }

    //         $fixedPrice = $toNumber(
    //             $priceValue,
    //             0
    //         );

    //         if (
    //             $fixedPrice === null
    //             || $fixedPrice < 0
    //         ) {
    //             return response()->json([
    //                 'ok' => false,
    //                 'message' => "Row {$rowNo} fixed/service price invalid hai.",
    //             ], 422);
    //         }

    //         $fixedPrice = round(
    //             $fixedPrice,
    //             2
    //         );

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Gold Weight
    //         |--------------------------------------------------------------------------
    //         */

    //         $goldWeight = $toNumber(
    //             $row['gold_wt']
    //                 ?? 0
    //         );

    //         if (
    //             $goldWeight === null
    //             || $goldWeight < 0
    //         ) {
    //             return response()->json([
    //                 'ok' => false,
    //                 'message' => "Row {$rowNo} gold weight invalid hai.",
    //             ], 422);
    //         }

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Silver Weight
    //         |--------------------------------------------------------------------------
    //         */

    //         $silverWeight = $toNumber(
    //             $row['silver_wt']
    //                 ?? 0
    //         );

    //         if (
    //             $silverWeight === null
    //             || $silverWeight < 0
    //         ) {
    //             return response()->json([
    //                 'ok' => false,
    //                 'message' => "Row {$rowNo} silver weight invalid hai.",
    //             ], 422);
    //         }

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Gold Rate
    //         |--------------------------------------------------------------------------
    //         */

    //         $goldRate = $toNumber(
    //             $row['gold_rate']
    //                 ?? 0
    //         );

    //         if (
    //             $goldRate === null
    //             || $goldRate < 0
    //         ) {
    //             return response()->json([
    //                 'ok' => false,
    //                 'message' => "Row {$rowNo} gold rate invalid hai.",
    //             ], 422);
    //         }

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Silver Rate
    //         |--------------------------------------------------------------------------
    //         */

    //         $silverRate = $toNumber(
    //             $row['silver_rate']
    //                 ?? 0
    //         );

    //         if (
    //             $silverRate === null
    //             || $silverRate < 0
    //         ) {
    //             return response()->json([
    //                 'ok' => false,
    //                 'message' => "Row {$rowNo} silver rate invalid hai.",
    //             ], 422);
    //         }

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Making Rate
    //         |--------------------------------------------------------------------------
    //         */

    //         $makingRate = $toNumber(
    //             $row['making_rate']
    //                 ?? 0
    //         );

    //         if (
    //             $makingRate === null
    //             || $makingRate < 0
    //         ) {
    //             return response()->json([
    //                 'ok' => false,
    //                 'message' => "Row {$rowNo} making rate invalid hai.",
    //             ], 422);
    //         }

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Gemstone Weight
    //         |--------------------------------------------------------------------------
    //         */

    //         $gemstoneWeight = $toNumber(
    //             $row['gemstone_wt']
    //                 ?? 0
    //         );

    //         if (
    //             $gemstoneWeight === null
    //             || $gemstoneWeight < 0
    //         ) {
    //             return response()->json([
    //                 'ok' => false,
    //                 'message' => "Row {$rowNo} gemstone weight invalid hai.",
    //             ], 422);
    //         }

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Diamond Weight
    //         |--------------------------------------------------------------------------
    //         */

    //         $diamondWeight = $toNumber(
    //             $row['diamond_wt']
    //                 ?? 0
    //         );

    //         if (
    //             $diamondWeight === null
    //             || $diamondWeight < 0
    //         ) {
    //             return response()->json([
    //                 'ok' => false,
    //                 'message' => "Row {$rowNo} diamond weight invalid hai.",
    //             ], 422);
    //         }

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Stone Charges
    //         |--------------------------------------------------------------------------
    //         */

    //         $stoneCharges = $toNumber(
    //             $row['gemstone_charge']
    //                 ?? $row['stone_charges']
    //                 ?? 0
    //         );

    //         if (
    //             $stoneCharges === null
    //             || $stoneCharges < 0
    //         ) {
    //             return response()->json([
    //                 'ok' => false,
    //                 'message' => "Row {$rowNo} stone charges invalid hai.",
    //             ], 422);
    //         }

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Diamond Charges
    //         |--------------------------------------------------------------------------
    //         */

    //         $diamondCharges = $toNumber(
    //             $row['diamond_charge']
    //                 ?? $row['diamond_charges']
    //                 ?? 0
    //         );

    //         if (
    //             $diamondCharges === null
    //             || $diamondCharges < 0
    //         ) {
    //             return response()->json([
    //                 'ok' => false,
    //                 'message' => "Row {$rowNo} diamond charges invalid hai.",
    //             ], 422);
    //         }

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Making Charge Type
    //         |--------------------------------------------------------------------------
    //         */

    //         $makingChargeType = strtolower(
    //             trim(
    //                 (string) (
    //                     $row['making_charge_type']
    //                     ?? 'percentage'
    //                 )
    //             )
    //         );

    //         $makingChargeType = str_replace(
    //             [
    //                 ' ',
    //                 '-',
    //             ],
    //             '_',
    //             $makingChargeType
    //         );

    //         /*
    //         | Small aliases
    //         */

    //         if (
    //             in_array(
    //                 $makingChargeType,
    //                 [
    //                     'percent',
    //                     'percentage_based',
    //                 ],
    //                 true
    //             )
    //         ) {
    //             $makingChargeType = 'percentage';
    //         }

    //         if (
    //             in_array(
    //                 $makingChargeType,
    //                 [
    //                     'pergram',
    //                     'gram',
    //                 ],
    //                 true
    //             )
    //         ) {
    //             $makingChargeType = 'per_gram';
    //         }

    //         if (
    //             in_array(
    //                 $makingChargeType,
    //                 [
    //                     'perproduct',
    //                     'product',
    //                 ],
    //                 true
    //             )
    //         ) {
    //             $makingChargeType = 'per_product';
    //         }

    //         $allowedMakingTypes = [
    //             'percentage',
    //             'fixed',
    //             'per_gram',
    //             'per_product',
    //         ];

    //         if (
    //             !in_array(
    //                 $makingChargeType,
    //                 $allowedMakingTypes,
    //                 true
    //             )
    //         ) {
    //             return response()->json([
    //                 'ok' => false,
    //                 'message' => "Row {$rowNo} making_charge_type invalid hai.",
    //                 'allowed' => $allowedMakingTypes,
    //             ], 422);
    //         }

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Round Raw Values
    //         |--------------------------------------------------------------------------
    //         */

    //         $goldWeight = round(
    //             $goldWeight,
    //             3
    //         );

    //         $silverWeight = round(
    //             $silverWeight,
    //             3
    //         );

    //         $goldRate = round(
    //             $goldRate,
    //             2
    //         );

    //         $silverRate = round(
    //             $silverRate,
    //             2
    //         );

    //         $makingRate = round(
    //             $makingRate,
    //             2
    //         );

    //         $gemstoneWeight = round(
    //             $gemstoneWeight,
    //             3
    //         );

    //         $diamondWeight = round(
    //             $diamondWeight,
    //             3
    //         );

    //         $stoneCharges = round(
    //             $stoneCharges,
    //             2
    //         );

    //         $diamondCharges = round(
    //             $diamondCharges,
    //             2
    //         );

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Metal Base
    //         |--------------------------------------------------------------------------
    //         |
    //         | Gold value:
    //         | gold weight × gold rate
    //         |
    //         | Silver value:
    //         | silver weight × silver rate
    //         |
    //         */

    //         $goldValue = round(
    //             $goldWeight * $goldRate,
    //             2
    //         );

    //         $silverValue = round(
    //             $silverWeight * $silverRate,
    //             2
    //         );

    //         $metalBase = round(
    //             $goldValue + $silverValue,
    //             2
    //         );

    //         $basePrice = $fixedPrice > 0
    //             ? $fixedPrice
    //             : $metalBase;

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Making Charge Calculation
    //         |--------------------------------------------------------------------------
    //         */

    //         $makingAmount = match ($makingChargeType) {
    //             /*
    //             | Example:
    //             | base = 100000
    //             | making = 10%
    //             | result = 10000
    //             */
    //             'percentage' => round(
    //                 $basePrice
    //                 * ($makingRate / 100),
    //                 2
    //             ),

    //             /*
    //             | Fixed amount per line unit
    //             */
    //             'fixed' => round(
    //                 $makingRate,
    //                 2
    //             ),

    //             /*
    //             | Gold + silver total weight × rate
    //             */
    //             'per_gram' => round(
    //                 (
    //                     $goldWeight
    //                     + $silverWeight
    //                 ) * $makingRate,
    //                 2
    //             ),

    //             /*
    //             | Fixed amount per product
    //             */
    //             'per_product' => round(
    //                 $makingRate,
    //                 2
    //             ),

    //             default => 0.0,
    //         };

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Unit Base
    //         |--------------------------------------------------------------------------
    //         */

    //         $unitBase = round(
    //             $basePrice
    //             + $makingAmount
    //             + $stoneCharges
    //             + $diamondCharges,
    //             2
    //         );

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Line Base
    //         |--------------------------------------------------------------------------
    //         |
    //         | Quantity LAST me multiply hogi.
    //         |
    //         */

    //         $lineBase = round(
    //             $unitBase * $quantity,
    //             2
    //         );

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Line Tax
    //         |--------------------------------------------------------------------------
    //         */

    //         $lineTax = round(
    //             $lineBase
    //             * ($taxPercent / 100),
    //             2
    //         );

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Final Line Amount
    //         |--------------------------------------------------------------------------
    //         */

    //         $lineTotal = round(
    //             $lineBase + $lineTax,
    //             2
    //         );

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Add Totals
    //         |--------------------------------------------------------------------------
    //         */

    //         $subtotal += $lineBase;

    //         $weightedTax += (
    //             $lineBase
    //             * $taxPercent
    //         );

    //         $itemsTaxTotal += $lineTax;

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Clean Item Data
    //         |--------------------------------------------------------------------------
    //         */

    //         $cleanRows[] = [
    //             'item_id' => (int) $itemId,

    //             'item_type' => $itemType,

    //             'description' => $description,

    //             'hsn' => $hsn,

    //             /*
    //             | DO NOT int cast quantity
    //             */
    //             'qty' => $quantity,

    //             'tax_percent' => $taxPercent,

    //             /*
    //             | Unit price
    //             */
    //             'fixed_price' => $fixedPrice,
    //             'service_rate' => $fixedPrice,
    //             'unit_rate' => $fixedPrice,

    //             /*
    //             | Metal
    //             */
    //             'gold_wt' => $goldWeight,
    //             'silver_wt' => $silverWeight,

    //             'gold_rate' => $goldRate,
    //             'silver_rate' => $silverRate,

    //             /*
    //             | Gem / diamond
    //             */
    //             'gemstone_wt' => $gemstoneWeight,
    //             'diamond_wt' => $diamondWeight,

    //             'stone_charges' => $stoneCharges,
    //             'diamond_charges' => $diamondCharges,

    //             /*
    //             | Making
    //             */
    //             'making_charge_type' => $makingChargeType,
    //             'making_rate' => $makingRate,
    //             'making_charge' => $makingAmount,

    //             /*
    //             | Calculated
    //             */
    //             'base_price' => round(
    //                 $basePrice,
    //                 2
    //             ),

    //             'unit_base' => $unitBase,

    //             /*
    //             | rate = taxable line amount before GST
    //             */
    //             'rate' => $lineBase,

    //             'tax_amount' => $lineTax,

    //             /*
    //             | amount = line amount including GST
    //             */
    //             'amount' => $lineTotal,
    //         ];
    //     }

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Invoice Totals
    //     |--------------------------------------------------------------------------
    //     */

    //     $subtotal = round(
    //         $subtotal,
    //         2
    //     );

    //     $itemsTaxTotal = round(
    //         $itemsTaxTotal,
    //         2
    //     );


    //     $averageTaxPercent = $subtotal > 0
    //         ? round(
    //             $weightedTax / $subtotal,
    //             2
    //         )
    //         : 0.0;

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Discount
    //     |--------------------------------------------------------------------------
    //     */

    //     $discountTotal = round(
    //         (float) (
    //             $data['discount_total']
    //             ?? 0
    //         ),
    //         2
    //     );

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Additional Charge Total
    //     |--------------------------------------------------------------------------
    //     */

    //     $chargeTotal = round(
    //         (float) (
    //             $data['charge_total']
    //             ?? 0
    //         ),
    //         2
    //     );

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Taxable Amount
    //     |--------------------------------------------------------------------------
    //     */

    //     $taxableAmount = round(
    //         max(
    //             0,
    //             $subtotal
    //             - $discountTotal
    //             + $chargeTotal
    //         ),
    //         2
    //     );


    //     $taxAmount = $itemsTaxTotal;

    //     /*
    //     |--------------------------------------------------------------------------
    //     | TCS
    //     |--------------------------------------------------------------------------
    //     */

    //     $tcsPercent = round(
    //         (float) (
    //             $data['tcs_percent']
    //             ?? 0
    //         ),
    //         2
    //     );

    //     $tcsAmount = round(
    //         (float) (
    //             $data['tcs_amount']
    //             ?? 0
    //         ),
    //         2
    //     );

    //     /*
    //     | Percentage diya hai to calculated value authoritative hogi.
    //     */

    //     if ($tcsPercent > 0) {
    //         $tcsAmount = round(
    //             $taxableAmount
    //             * ($tcsPercent / 100),
    //             2
    //         );
    //     }

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Round Off
    //     |--------------------------------------------------------------------------
    //     */

    //     $roundOff = round(
    //         (float) (
    //             $data['round_off']
    //             ?? 0
    //         ),
    //         2
    //     );

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Less Amount
    //     |--------------------------------------------------------------------------
    //     */

    //     $lessAmount = round(
    //         (float) (
    //             $data['less_amount']
    //             ?? $discountTotal
    //         ),
    //         2
    //     );

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Grand Total
    //     |--------------------------------------------------------------------------
    //     */

    //     $grandTotal = round(
    //         $taxableAmount
    //         + $taxAmount
    //         + $tcsAmount
    //         + $roundOff,
    //         2
    //     );

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Payment Calculation
    //     |--------------------------------------------------------------------------
    //     */

    //     $cash = 0.0;
    //     $online = 0.0;
    //     $card = 0.0;
    //     $cheque = 0.0;
    //     $credit = 0.0;
    //     $advance = 0.0;

    //     $receivedTotal = 0.0;

    //     $balance = $grandTotal;

    //     if ($docType === 'tax') {
    //         $cash = (float) (
    //             $pay['pay_cash']
    //             ?? 0
    //         );

    //         $online = (float) (
    //             $pay['pay_upi']
    //             ?? 0
    //         );

    //         $card = (float) (
    //             $pay['pay_card']
    //             ?? 0
    //         );

    //         $cheque = (float) (
    //             $pay['pay_cheque']
    //             ?? 0
    //         );

    //         $credit = (float) (
    //             $pay['credit_sales_excess']
    //             ?? 0
    //         );

    //         $advance = (float) (
    //             $pay['advance_amount']
    //             ?? 0
    //         );

    //         $receivedTotal = round(
    //             $cash
    //             + $online
    //             + $card
    //             + $cheque,
    //             2
    //         );

    //         $balance = round(
    //             max(
    //                 0,
    //                 $grandTotal
    //                 - $receivedTotal
    //                 - $advance
    //                 - $credit
    //             ),
    //             2
    //         );
    //     }

    //     /*
    //     |--------------------------------------------------------------------------
    //     | GST State Calculation
    //     |--------------------------------------------------------------------------
    //     |
    //     | Web logic ke same state code normalization.
    //     |
    //     */

    //     $normalizeStateCode = static function (
    //         $value
    //     ): string {
    //         $code = trim(
    //             (string) $value
    //         );

    //         $code = preg_replace(
    //             '/\D+/',
    //             '',
    //             $code
    //         );

    //         return ltrim(
    //             $code,
    //             '0'
    //         );
    //     };

    //     $businessStateCode = $normalizeStateCode(
    //         $biz->state_code
    //         ?? ''
    //     );

    //     $clientStateCode = $normalizeStateCode(
    //         $client->state_code
    //         ?? ''
    //     );

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Same state = CGST + SGST
    //     | Different state = IGST
    //     |--------------------------------------------------------------------------
    //     */

    //     $isIntraState = (
    //         $businessStateCode !== ''
    //         && $clientStateCode !== ''
    //     )
    //         ? (
    //             $businessStateCode
    //             === $clientStateCode
    //         )
    //         : false;

    //     $cgstPercent = $isIntraState
    //         ? round(
    //             $averageTaxPercent / 2,
    //             2
    //         )
    //         : 0.0;

    //     $sgstPercent = $isIntraState
    //         ? round(
    //             $averageTaxPercent / 2,
    //             2
    //         )
    //         : 0.0;

    //     $igstPercent = $isIntraState
    //         ? 0.0
    //         : round(
    //             $averageTaxPercent,
    //             2
    //         );

    //     /*
    //     |--------------------------------------------------------------------------
    //     | GST Amount Split
    //     |--------------------------------------------------------------------------
    //     */

    //     $cgst = $isIntraState
    //         ? round(
    //             $taxAmount / 2,
    //             2
    //         )
    //         : 0.0;

    //     $sgst = $isIntraState
    //         ? round(
    //             $taxAmount / 2,
    //             2
    //         )
    //         : 0.0;

    //     $igst = $isIntraState
    //         ? 0.0
    //         : round(
    //             $taxAmount,
    //             2
    //         );

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Save Invoice
    //     |--------------------------------------------------------------------------
    //     */

    //     try {
    //         $createdInvoice = DB::transaction(
    //             function () use (
    //                 $bid,
    //                 $data,
    //                 $client,
    //                 $invoiceDate,
    //                 $prefix,
    //                 $docType,
    //                 $reqInvoiceNo,
    //                 $subtotal,
    //                 $discountTotal,
    //                 $chargeTotal,
    //                 $lessAmount,
    //                 $taxAmount,
    //                 $cgstPercent,
    //                 $sgstPercent,
    //                 $igstPercent,
    //                 $cgst,
    //                 $sgst,
    //                 $igst,
    //                 $tcsPercent,
    //                 $tcsAmount,
    //                 $roundOff,
    //                 $grandTotal,
    //                 $receivedTotal,
    //                 $balance,
    //                 $cleanRows,
    //                 $cash,
    //                 $online,
    //                 $card,
    //                 $cheque,
    //                 $credit,
    //                 $advance,
    //                 $pay,
    //                 $chargesJson
    //             ) {
    //                 /*
    //                 |--------------------------------------------------------------------------
    //                 | Invoice
    //                 |--------------------------------------------------------------------------
    //                 */

    //                 $invoice = Invoice::withoutGlobalScopes()
    //                     ->create([
    //                         'business_id' => $bid,

    //                         'client_id' => (int) $data['client_id'],

    //                         'invoice_date' => $invoiceDate,

    //                         'invoice_prefix' => $prefix,

    //                         'invoice_number' => $reqInvoiceNo,

    //                         'invoice_type' => $docType,

    //                         /*
    //                         |--------------------------------------------------------------------------
    //                         | Amounts
    //                         |--------------------------------------------------------------------------
    //                         */

    //                         'subtotal' => $subtotal,

    //                         'discount_total' => $discountTotal,

    //                         'charge_total' => $chargeTotal,

    //                         'less_amount' => $lessAmount,

    //                         'tax_amount' => $taxAmount,

    //                         /*
    //                         |--------------------------------------------------------------------------
    //                         | GST
    //                         |--------------------------------------------------------------------------
    //                         */

    //                         'cgst_percent' => $cgstPercent,

    //                         'cgst_amount' => $cgst,

    //                         'sgst_percent' => $sgstPercent,

    //                         'sgst_amount' => $sgst,

    //                         'igst_percent' => $igstPercent,

    //                         'igst_amount' => $igst,

    //                         /*
    //                         |--------------------------------------------------------------------------
    //                         | TCS / Round
    //                         |--------------------------------------------------------------------------
    //                         */

    //                         'tcs_percent' => $tcsPercent,

    //                         'tcs_amount' => $tcsAmount,

    //                         'round_off' => $roundOff,

    //                         /*
    //                         |--------------------------------------------------------------------------
    //                         | Final
    //                         |--------------------------------------------------------------------------
    //                         */

    //                         'total' => $grandTotal,

    //                         'received_amount' => $docType === 'tax'
    //                             ? $receivedTotal
    //                             : 0,

    //                         'balance' => $docType === 'tax'
    //                             ? $balance
    //                             : $grandTotal,

    //                         /*
    //                         |--------------------------------------------------------------------------
    //                         | Other
    //                         |--------------------------------------------------------------------------
    //                         */

    //                         'payment_method' => $data['payment_method']
    //                             ?? null,

    //                         'transport_mode' => !empty(
    //                             $data['transport_mode']
    //                         )
    //                             ? $data['transport_mode']
    //                             : null,

    //                         'reverse_charge' => !empty(
    //                             $data['reverse_charge']
    //                         )
    //                             ? 1
    //                             : 0,

    //                         'place_of_supply_state' => $client->state
    //                             ?? null,

    //                         'place_of_supply_code' => $client->state_code
    //                             ?? null,

    //                         'notes' => $data['notes']
    //                             ?? null,

    //                         'terms' => $data['terms']
    //                             ?? null,

    //                         'charges_json' => $chargesJson,

    //                         'items_json' => json_encode(
    //                             $cleanRows,
    //                             JSON_UNESCAPED_UNICODE
    //                         ),

    //                         'amount_in_words' => '',
    //                     ]);

    //                 /*
    //                 |--------------------------------------------------------------------------
    //                 | Invoice Items
    //                 |--------------------------------------------------------------------------
    //                 */

    //                 foreach ($cleanRows as $row) {
    //                     InvoiceItem::withoutGlobalScopes()
    //                         ->create([
    //                             'invoice_id' => $invoice->id,

    //                             'item_id' => $row['item_id'],

    //                             'description' => $row['description'],

    //                             /*
    //                             | Web ke same
    //                             */

    //                             'sac_code' => $row['hsn']
    //                                 ?: null,

    //                             'hsn_code' => $row['hsn']
    //                                 ?: null,

    //                             /*
    //                             | IMPORTANT:
    //                             | Quantity decimal preserve hogi.
    //                             */

    //                             'quantity' => $row['qty'],

    //                             /*
    //                             |--------------------------------------------------------------------------
    //                             | Metal
    //                             |--------------------------------------------------------------------------
    //                             */

    //                             'gold_wt' => (float) (
    //                                 $row['gold_wt']
    //                                 ?? 0
    //                             ),

    //                             'silver_wt' => (float) (
    //                                 $row['silver_wt']
    //                                 ?? 0
    //                             ),

    //                             'gold_rate' => (float) (
    //                                 $row['gold_rate']
    //                                 ?? 0
    //                             ),

    //                             'silver_rate' => (float) (
    //                                 $row['silver_rate']
    //                                 ?? 0
    //                             ),

    //                             /*
    //                             |--------------------------------------------------------------------------
    //                             | Diamond / Gem
    //                             |--------------------------------------------------------------------------
    //                             */

    //                             'gemstone_wt_ct' => (float) (
    //                                 $row['gemstone_wt']
    //                                 ?? 0
    //                             ),

    //                             'diamond_wt_ct' => (float) (
    //                                 $row['diamond_wt']
    //                                 ?? 0
    //                             ),

    //                             'stone_charges' => (float) (
    //                                 $row['stone_charges']
    //                                 ?? 0
    //                             ),

    //                             'diamond_charges' => (float) (
    //                                 $row['diamond_charges']
    //                                 ?? 0
    //                             ),

    //                             /*
    //                             |--------------------------------------------------------------------------
    //                             | Making
    //                             |--------------------------------------------------------------------------
    //                             */

    //                             'making_charge' => (float) (
    //                                 $row['making_charge']
    //                                 ?? 0
    //                             ),

    //                             'making_rate' => (float) (
    //                                 $row['making_rate']
    //                                 ?? 0
    //                             ),

    //                             'making_charge_type' => $row['making_charge_type']
    //                                 ?? 'percentage',

    //                             /*
    //                             |--------------------------------------------------------------------------
    //                             | Discount
    //                             |--------------------------------------------------------------------------
    //                             */

    //                             'discount' => 0,

    //                             /*
    //                             |--------------------------------------------------------------------------
    //                             | Tax
    //                             |--------------------------------------------------------------------------
    //                             */

    //                             'tax_percent' => (float) (
    //                                 $row['tax_percent']
    //                                 ?? 0
    //                             ),

    //                             /*
    //                             |--------------------------------------------------------------------------
    //                             | Rate / Amount
    //                             |--------------------------------------------------------------------------
    //                             |
    //                             | rate:
    //                             | taxable line amount BEFORE tax
    //                             |
    //                             | amount:
    //                             | line amount INCLUDING tax
    //                             |
    //                             */

    //                             'rate' => round(
    //                                 (float) (
    //                                     $row['rate']
    //                                     ?? 0
    //                                 ),
    //                                 2
    //                             ),

    //                             'amount' => round(
    //                                 (float) (
    //                                     $row['amount']
    //                                     ?? 0
    //                                 ),
    //                                 2
    //                             ),
    //                         ]);
    //                 }

    //                 /*
    //                 |--------------------------------------------------------------------------
    //                 | Payment Record
    //                 |--------------------------------------------------------------------------
    //                 */

    //                 if ($docType === 'tax') {
    //                     $payRow = new InvoicePayment();

    //                     $payRow->fill([
    //                         'business_id' => $bid,

    //                         'invoice_id' => $invoice->id,

    //                         'client_id' => (int) $data['client_id'],

    //                         'total_value' => $grandTotal,

    //                         /*
    //                         |--------------------------------------------------------------------------
    //                         | Payment amounts
    //                         |--------------------------------------------------------------------------
    //                         */

    //                         'cash_amount' => $cash,

    //                         'online_amount' => $online,

    //                         'card_amount' => $card,

    //                         'cheque_amount' => $cheque,

    //                         /*
    //                         |--------------------------------------------------------------------------
    //                         | Online
    //                         |--------------------------------------------------------------------------
    //                         */

    //                         'online_mode' => $pay['online_mode']
    //                             ?? null,

    //                         'online_ref' => $pay['online_ref']
    //                             ?? null,

    //                         'upi_id' => $pay['upi_id']
    //                             ?? null,

    //                         /*
    //                         |--------------------------------------------------------------------------
    //                         | Card
    //                         |--------------------------------------------------------------------------
    //                         */

    //                         'card_last4' => $pay['card_last4']
    //                             ?? null,

    //                         'card_ref' => $pay['card_ref']
    //                             ?? null,

    //                         /*
    //                         |--------------------------------------------------------------------------
    //                         | Cheque
    //                         |--------------------------------------------------------------------------
    //                         */

    //                         'cheque_no' => $pay['cheque_no']
    //                             ?? null,

    //                         'bank_name' => $pay['bank_name']
    //                             ?? null,

    //                         /*
    //                         |--------------------------------------------------------------------------
    //                         | Credit / Advance
    //                         |--------------------------------------------------------------------------
    //                         */

    //                         'credit_sales_excess_amount' => $credit,

    //                         'advance_amount' => $advance,

    //                         /*
    //                         |--------------------------------------------------------------------------
    //                         | Received
    //                         |--------------------------------------------------------------------------
    //                         */

    //                         'received_total' => $receivedTotal,

    //                         /*
    //                         | Old API + Web naming support
    //                         */

    //                         'notes' => $pay['payment_notes']
    //                             ?? $pay['pay_notes']
    //                             ?? null,

    //                         'paid_at' => $receivedTotal > 0
    //                             ? now()
    //                             : null,
    //                     ]);

    //                     $payRow->save();
    //                 }

    //                 return $invoice;
    //             }
    //         );

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Stock Record
    //         |--------------------------------------------------------------------------
    //         */

    //         if ($docType === 'tax') {
    //             $freshInvoice = Invoice::withoutGlobalScopes()
    //                 ->with([
    //                     'items',
    //                 ])
    //                 ->where(
    //                     'id',
    //                     $createdInvoice->id
    //                 )
    //                 ->first();

    //             if ($freshInvoice) {
    //                 $this->stock->recordSale(
    //                     $freshInvoice
    //                 );
    //             }
    //         }

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Sync Invoice Number Sequence
    //         |--------------------------------------------------------------------------
    //         */

    //         InvoiceNumber::syncNextSeqIfMatches(
    //             (int) $bid,
    //             $invoiceDate,
    //             $reqInvoiceNo,
    //             3,
    //             $docType
    //         );

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Response
    //         |--------------------------------------------------------------------------
    //         */

    //         return response()->json([
    //             'ok' => true,

    //             'message' => ucfirst(
    //                 $docType
    //             ) . ' created',

    //             'invoice' => Invoice::withoutGlobalScopes()
    //                 ->with([
    //                     'client',
    //                     'items',
    //                     'business',
    //                 ])
    //                 ->where(
    //                     'id',
    //                     $createdInvoice->id
    //                 )
    //                 ->first(),
    //         ], 201);
    //     } catch (\Throwable $e) {
    //         Log::error(
    //             'API Invoice create failed',
    //             [
    //                 'business_id' => $bid,

    //                 'error' => $e->getMessage(),

    //                 'line' => $e->getLine(),

    //                 'file' => $e->getFile(),
    //             ]
    //         );

    //         return response()->json([
    //             'ok' => false,

    //             'message' => 'Invoice create failed',

    //             'error' => $e->getMessage(),
    //         ], 500);
    //     }
    // }



    public function store(Request $request, $type = 'tax')
    {
        $user = $request->user();
        $bid = $this->selectedBusinessId($request);
        $docType = $this->normalizeDocType((string) $type);

        if (!$user->can($this->requiredPerm($docType))) {
            return response()->json([
                'ok' => false,
                'message' => 'Permission denied',
            ], 403);
        }

        $business = Business::withoutGlobalScopes()
            ->with('businessType')
            ->whereKey($bid)
            ->first();

        if (!$business) {
            return response()->json([
                'ok' => false,
                'message' => 'Business not found',
            ], 404);
        }

        $businessType = strtolower(trim((string) (
            $business->businessType?->slug
            ?? $business->businessType?->name
            ?? ''
        )));

        $isHospitalBusiness = method_exists($business, 'isHospitalBusiness')
            ? $business->isHospitalBusiness()
            : in_array($businessType, [
                'hospital',
                'clinic',
                'nursing home',
                'nursing_home',
                'diagnostic center',
                'diagnostic_center',
                'pathology lab',
                'pathology_lab',
            ], true);

        if (!$user->hasAnyRole(['super_admin', 'admin'])) {
            $activePlan = UserPlan::withoutGlobalScopes()
                ->where('business_id', $bid)
                ->where(function ($q) {
                    $q->where('status', 'active')->orWhere('status', 1);
                })
                ->whereDate('start_date', '<=', today())
                ->where(function ($q) {
                    $q->whereNull('expiry_date')->orWhereDate('expiry_date', '>=', today());
                })
                ->latest('id')
                ->first();

            if (!$activePlan) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Is business ka active plan available nahi hai ya plan expire ho chuka hai.',
                ], 422);
            }
        }

        $gstInvoiceAllowed = (bool) $business->gst_enabled
            && filled(trim((string) $business->gstin));

        if (in_array($docType, ['tax', 'proforma'], true) && !$gstInvoiceAllowed) {
            return response()->json([
                'ok' => false,
                'message' => 'GST Enabled aur GSTIN ke bina Tax/Proforma invoice nahi ban sakta. Sirf quotation bana sakte hain.',
            ], 422);
        }

        $rules = [
            'client_id' => [
                'required',
                'integer',
                Rule::exists('clients', 'id')->where('business_id', $bid),
            ],
            'invoice_date' => ['required', 'date'],
            'invoice_prefix' => ['nullable', 'string', 'max:255'],
            'invoice_number' => ['nullable', 'string', 'max:255'],
            'transport_mode' => ['nullable', 'string', 'max:255'],
            'gst_no' => ['nullable', 'string', 'max:50'],
            'reverse_charge' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'terms' => ['nullable', 'string', 'max:5000'],

            // API can send either items_json or items[]
            'items_json' => ['nullable'],
            'items' => ['nullable', 'array', 'min:1'],

            'charges_json' => ['nullable'],
            'discount_total' => ['nullable', 'numeric', 'min:0'],
            'charge_total' => ['nullable', 'numeric', 'min:0'],
            'tcs_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'tcs_amount' => ['nullable', 'numeric', 'min:0'],
            'round_off' => ['nullable', 'numeric'],
            'less_amount' => ['nullable', 'numeric', 'min:0'],

            'payment_method' => ['nullable', 'string', 'max:255'],
            'bank_account_id' => [
                'nullable',
                'integer',
                Rule::exists('bank_accounts', 'id')->where('business_id', $bid),
            ],
        ];

        if ($isHospitalBusiness) {
            $rules = array_merge($rules, [
                'patient_uhid' => ['nullable', 'string', 'max:100'],
                'patient_age' => ['nullable', 'integer', 'min:0', 'max:150'],
                'patient_gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
                'blood_group' => ['nullable', Rule::in(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'])],
                'guardian_name' => ['nullable', 'string', 'max:255'],

                'visit_type' => ['required', Rule::in([
                    'opd', 'ipd', 'emergency', 'day_care', 'diagnostic', 'pharmacy',
                ])],
                'visit_number' => ['nullable', 'string', 'max:100'],
                'visit_at' => ['required', 'date'],

                'doctor_id' => [
                    'nullable', 'integer',
                    Rule::exists('doctors', 'id')->where('business_id', $bid),
                ],
                'department_id' => [
                    'nullable', 'integer',
                    Rule::exists('hospital_departments', 'id')->where('business_id', $bid),
                ],
                'referred_by' => ['nullable', 'string', 'max:255'],

                'billing_category' => ['required', Rule::in([
                    'cash', 'credit', 'insurance', 'corporate', 'government_scheme', 'charity',
                ])],

                'ward_id' => [
                    'nullable', 'integer',
                    Rule::exists('hospital_wards', 'id')->where('business_id', $bid),
                ],
                'room_id' => [
                    'nullable', 'integer',
                    Rule::exists('hospital_rooms', 'id')->where('business_id', $bid),
                ],
                'bed_id' => [
                    'nullable', 'integer',
                    Rule::exists('hospital_beds', 'id')->where('business_id', $bid),
                ],

                'admitted_at' => ['nullable', 'date'],
                'discharged_at' => ['nullable', 'date', 'after_or_equal:admitted_at'],

                'insurance_provider' => ['nullable', 'string', 'max:255'],
                'insurance_policy_number' => ['nullable', 'string', 'max:255'],
                'chief_complaint' => ['nullable', 'string', 'max:5000'],
                'diagnosis' => ['nullable', 'string', 'max:5000'],
                'hospital_notes' => ['nullable', 'string', 'max:5000'],
            ]);
        }

        $data = $request->validate($rules);

        $pay = [];
        if ($docType === 'tax') {
            $pay = $request->validate([
                'pay_cash' => ['nullable', 'numeric', 'min:0'],
                'pay_upi' => ['nullable', 'numeric', 'min:0'],
                'pay_card' => ['nullable', 'numeric', 'min:0'],
                'pay_cheque' => ['nullable', 'numeric', 'min:0'],
                'credit_sales_excess' => ['nullable', 'numeric', 'min:0'],
                'advance_amount' => ['nullable', 'numeric', 'min:0'],
                'online_mode' => ['nullable', 'string', 'max:30'],
                'online_ref' => ['nullable', 'string', 'max:100'],
                'upi_id' => ['nullable', 'string', 'max:100'],
                'card_last4' => ['nullable', 'digits:4'],
                'card_ref' => ['nullable', 'string', 'max:100'],
                'cheque_no' => ['nullable', 'string', 'max:50'],
                'bank_name' => ['nullable', 'string', 'max:100'],
                'pay_notes' => ['nullable', 'string', 'max:2000'],
                'payment_notes' => ['nullable', 'string', 'max:2000'],
            ]);
        }

        $client = Client::withoutGlobalScopes()
            ->where('business_id', $bid)
            ->whereKey((int) $data['client_id'])
            ->first();

        if (!$client) {
            return response()->json([
                'ok' => false,
                'message' => 'Client/Patient not found for this business',
            ], 404);
        }

        $toNumber = static function ($value, float $default = 0.0): ?float {
            if ($value === null || $value === '') return $default;
            if (is_int($value) || is_float($value)) return (float) $value;
            if (is_string($value)) {
                $value = str_replace(',', '', trim($value));
                if ($value === '') return $default;
            }
            return is_numeric($value) ? (float) $value : null;
        };

        $decodeArrayInput = static function ($value, string $fieldName): array {
            if ($value === null || $value === '') return [];
            if (is_array($value)) return $value;
            if (is_string($value)) {
                $decoded = json_decode($value, true);
                if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
                    throw new \InvalidArgumentException("{$fieldName} invalid JSON hai: " . json_last_error_msg());
                }
                return $decoded;
            }
            throw new \InvalidArgumentException("{$fieldName} array ya JSON string hona chahiye.");
        };

        try {
            $rows = $decodeArrayInput($request->input('items_json'), 'items_json');
        } catch (\InvalidArgumentException $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 422);
        }

        if (!$rows && is_array($request->input('items'))) {
            $rows = $request->input('items');
        }

        if ($rows && !array_is_list($rows)) {
            $rows = [$rows];
        }

        if (!$rows) {
            return response()->json([
                'ok' => false,
                'message' => $isHospitalBusiness
                    ? 'Kam se kam 1 hospital service/charge zaroori hai.'
                    : 'items_json or items is required.',
            ], 422);
        }

        $subtotal = 0.0;
        $weightedTax = 0.0;
        $itemsTaxTotal = 0.0;
        $cleanRows = [];

        foreach ($rows as $index => $row) {
            $rowNo = $index + 1;

            if (!is_array($row)) {
                return response()->json(['ok' => false, 'message' => "Row {$rowNo} invalid hai."], 422);
            }

            $itemId = (int) ($row['item_id'] ?? 0);
            if ($itemId <= 0) {
                return response()->json([
                    'ok' => false,
                    'message' => $isHospitalBusiness
                        ? "Row {$rowNo} me service select nahi hai."
                        : "Row {$rowNo} item_id missing hai.",
                ], 422);
            }

            $item = Item::withoutGlobalScopes()
                ->where('business_id', $bid)
                ->whereKey($itemId)
                ->where('is_active', true)
                ->first();

            if (!$item) {
                return response()->json([
                    'ok' => false,
                    'message' => "Row {$rowNo} ka item/service invalid ya inactive hai.",
                ], 422);
            }

            $description = trim((string) (
                $row['description'] ?? $item->description ?? $item->name ?? ''
            ));

            if ($description === '') {
                return response()->json(['ok' => false, 'message' => "Row {$rowNo} description missing hai."], 422);
            }

            $hsn = trim((string) (
                $row['hsn'] ?? $row['sac'] ?? $item->sac ?? $item->hsn ?? ''
            ));

            $quantity = $toNumber($row['qty'] ?? $row['quantity'] ?? 1, 1);
            if ($quantity === null || $quantity <= 0) {
                return response()->json(['ok' => false, 'message' => "Row {$rowNo} quantity invalid hai."], 422);
            }

            $taxPercent = $toNumber($row['tax_percent'] ?? $item->tax_rate ?? 0, 0);
            if ($taxPercent === null || $taxPercent < 0 || $taxPercent > 100) {
                return response()->json(['ok' => false, 'message' => "Row {$rowNo} tax percentage invalid hai."], 422);
            }
            $taxPercent = round($taxPercent, 2);

            $fixedPrice = $toNumber(
                $row['fixed_price']
                ?? $row['service_rate']
                ?? $row['price']
                ?? $row['unit_rate']
                ?? 0,
                0
            );

            if ($fixedPrice === null || $fixedPrice < 0) {
                return response()->json(['ok' => false, 'message' => "Row {$rowNo} rate invalid hai."], 422);
            }
            $fixedPrice = round($fixedPrice, 2);

            $goldWeight = $isHospitalBusiness ? 0.0 : $toNumber($row['gold_wt'] ?? $row['gold_weight'] ?? 0, 0);
            $silverWeight = $isHospitalBusiness ? 0.0 : $toNumber($row['silver_wt'] ?? $row['silver_weight'] ?? 0, 0);
            $goldRate = $isHospitalBusiness ? 0.0 : $toNumber($row['gold_rate'] ?? 0, 0);
            $silverRate = $isHospitalBusiness ? 0.0 : $toNumber($row['silver_rate'] ?? 0, 0);
            $makingRate = $isHospitalBusiness ? 0.0 : $toNumber($row['making_rate'] ?? 0, 0);
            $gemstoneWeight = $isHospitalBusiness ? 0.0 : $toNumber($row['gemstone_wt'] ?? $row['gemstone_wt_ct'] ?? 0, 0);
            $diamondWeight = $isHospitalBusiness ? 0.0 : $toNumber($row['diamond_wt'] ?? $row['diamond_wt_ct'] ?? 0, 0);
            $stoneCharges = $isHospitalBusiness ? 0.0 : $toNumber($row['gemstone_charge'] ?? $row['stone_charges'] ?? 0, 0);
            $diamondCharges = $isHospitalBusiness ? 0.0 : $toNumber($row['diamond_charge'] ?? $row['diamond_charges'] ?? 0, 0);

            foreach ([
                $goldWeight, $silverWeight, $goldRate, $silverRate, $makingRate,
                $gemstoneWeight, $diamondWeight, $stoneCharges, $diamondCharges,
            ] as $numericValue) {
                if ($numericValue === null || $numericValue < 0) {
                    return response()->json(['ok' => false, 'message' => "Row {$rowNo} me invalid numeric value hai."], 422);
                }
            }

            $makingChargeType = strtolower(trim((string) ($row['making_charge_type'] ?? 'percentage')));
            $makingChargeType = str_replace([' ', '-'], '_', $makingChargeType);

            if (in_array($makingChargeType, ['percent', 'percentage_based'], true)) $makingChargeType = 'percentage';
            if (in_array($makingChargeType, ['pergram', 'gram'], true)) $makingChargeType = 'per_gram';

            if ($isHospitalBusiness || !in_array($makingChargeType, ['percentage', 'fixed', 'per_gram', 'per_product'], true)) {
                $makingChargeType = 'percentage';
            }

            $metalBase = ($goldWeight * $goldRate) + ($silverWeight * $silverRate);
            $basePrice = $fixedPrice > 0 ? $fixedPrice : $metalBase;

            $makingAmount = 0.0;
            if (!$isHospitalBusiness) {
                $makingAmount = match ($makingChargeType) {
                    'percentage' => round($basePrice * ($makingRate / 100), 2),
                    'fixed' => round($makingRate, 2),
                    'per_gram' => round(($goldWeight + $silverWeight) * $makingRate, 2),
                    'per_product' => round($makingRate, 2),
                    default => 0.0,
                };
            }

            $lineBase = round((
                $basePrice + $makingAmount + $stoneCharges + $diamondCharges
            ) * $quantity, 2);

            $lineTax = round($lineBase * ($taxPercent / 100), 2);
            $lineTotal = round($lineBase + $lineTax, 2);

            $subtotal += $lineBase;
            $weightedTax += $lineBase * $taxPercent;
            $itemsTaxTotal += $lineTax;

            $rowItemType = strtolower(trim((string) ($row['item_type'] ?? $item->type ?? '')));
            if ($isHospitalBusiness) $rowItemType = 'service';
            if (!in_array($rowItemType, ['product', 'service'], true)) $rowItemType = 'product';

            $cleanRows[] = [
                'item_id' => $itemId,
                'item_type' => $rowItemType,
                'description' => $description,
                'hsn' => $hsn,
                'qty' => round($quantity, 3),
                'tax_percent' => $taxPercent,
                'fixed_price' => $fixedPrice,
                'service_rate' => $fixedPrice,
                'gold_wt' => round($goldWeight, 3),
                'silver_wt' => round($silverWeight, 3),
                'gold_rate' => round($goldRate, 2),
                'silver_rate' => round($silverRate, 2),
                'gemstone_wt' => round($gemstoneWeight, 3),
                'diamond_wt' => round($diamondWeight, 3),
                'making_charge_type' => $makingChargeType,
                'making_rate' => round($makingRate, 2),
                'making_charge' => round($makingAmount, 2),
                'stone_charges' => round($stoneCharges, 2),
                'diamond_charges' => round($diamondCharges, 2),
                'rate' => $lineBase,
                'unit_rate' => $fixedPrice,
                'tax_amount' => $lineTax,
                'amount' => $lineTotal,
            ];
        }

        $subtotal = round($subtotal, 2);
        $itemsTaxTotal = round($itemsTaxTotal, 2);
        $averageTaxPercent = $subtotal > 0 ? round($weightedTax / $subtotal, 2) : 0.0;

        $discountTotal = round((float) ($data['discount_total'] ?? 0), 2);
        $chargeTotal = round((float) ($data['charge_total'] ?? 0), 2);
        $taxableAmount = round(max(0, $subtotal - $discountTotal + $chargeTotal), 2);
        $taxAmount = $itemsTaxTotal;

        $tcsPercent = round((float) ($data['tcs_percent'] ?? 0), 2);
        $tcsAmount = round((float) ($data['tcs_amount'] ?? 0), 2);
        if ($tcsPercent > 0) {
            $tcsAmount = round($taxableAmount * ($tcsPercent / 100), 2);
        }

        $roundOff = round((float) ($data['round_off'] ?? 0), 2);
        $lessAmount = round((float) ($data['less_amount'] ?? $discountTotal), 2);
        $grandTotal = round($taxableAmount + $taxAmount + $tcsAmount + $roundOff, 2);

        $cash = $online = $card = $cheque = $credit = $advance = 0.0;
        $receivedTotal = 0.0;
        $balance = $grandTotal;

        if ($docType === 'tax') {
            $cash = (float) ($pay['pay_cash'] ?? 0);
            $online = (float) ($pay['pay_upi'] ?? 0);
            $card = (float) ($pay['pay_card'] ?? 0);
            $cheque = (float) ($pay['pay_cheque'] ?? 0);
            $credit = (float) ($pay['credit_sales_excess'] ?? 0);
            $advance = (float) ($pay['advance_amount'] ?? 0);

            $receivedTotal = round($cash + $online + $card + $cheque, 2);
            $balance = round(max(0, $grandTotal - $receivedTotal - $advance - $credit), 2);
        }

        try {
            $chargesArr = $decodeArrayInput($request->input('charges_json'), 'charges_json');
        } catch (\InvalidArgumentException $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 422);
        }

        if ($chargesArr && !array_is_list($chargesArr)) $chargesArr = [$chargesArr];

        $additionalCharges = [];
        foreach ($chargesArr as $charge) {
            if (!is_array($charge)) continue;
            $name = trim((string) ($charge['name'] ?? ''));
            $amount = round((float) ($charge['amount'] ?? 0), 2);
            if ($name !== '' && $amount != 0) {
                $additionalCharges[] = ['name' => $name, 'amount' => $amount];
            }
        }

        $invoiceDate = Carbon::parse($data['invoice_date'])->toDateString();

        $prefix = trim((string) ($data['invoice_prefix'] ?? ''));
        if ($prefix === '') {
            $defaultBase = match ($docType) {
                'proforma' => 'PF',
                'quotation' => 'QT',
                default => $isHospitalBusiness
                    ? 'HSP'
                    : ($business->invoice_base_prefix ?: 'INV'),
            };

            $prefix = InvoiceNumber::previewPrefix($invoiceDate, $defaultBase)
                ?: $this->computePrefix($invoiceDate, $defaultBase);
        }

        $invoiceNumber = trim((string) ($data['invoice_number'] ?? ''));
        if ($invoiceNumber === '') {
            $allocation = InvoiceNumber::next($bid, $invoiceDate, $prefix, 3, $docType);
            $invoiceNumber = $allocation['full'] ?? '';
        }

        if ($invoiceNumber === '') {
            return response()->json(['ok' => false, 'message' => 'Invoice number generate failed'], 422);
        }

        if (Invoice::withoutGlobalScopes()
            ->where('business_id', $bid)
            ->where('invoice_number', $invoiceNumber)
            ->exists()) {
            return response()->json([
                'ok' => false,
                'message' => 'Invoice number already exists',
                'invoice_number' => $invoiceNumber,
            ], 409);
        }

        $normalizeStateCode = static function ($value): string {
            $code = preg_replace('/\D+/', '', trim((string) $value));
            return ltrim($code, '0');
        };

        $businessStateCode = $normalizeStateCode($business->state_code ?? '');
        $clientStateCode = $normalizeStateCode($client->state_code ?? '');
        $isIntraState = $businessStateCode !== '' && $clientStateCode !== ''
            ? $businessStateCode === $clientStateCode
            : false;

        $cgstPercent = $isIntraState ? round($averageTaxPercent / 2, 2) : 0;
        $sgstPercent = $isIntraState ? round($averageTaxPercent / 2, 2) : 0;
        $igstPercent = $isIntraState ? 0 : round($averageTaxPercent, 2);
        $cgstAmount = $isIntraState ? round($taxAmount / 2, 2) : 0;
        $sgstAmount = $isIntraState ? round($taxAmount / 2, 2) : 0;
        $igstAmount = $isIntraState ? 0 : round($taxAmount, 2);

        $hospitalSnapshot = null;
        if ($isHospitalBusiness) {
            $hospitalSnapshot = [
                'patient_uhid' => $data['patient_uhid'] ?? null,
                'patient_age' => $data['patient_age'] ?? null,
                'patient_gender' => $data['patient_gender'] ?? null,
                'blood_group' => $data['blood_group'] ?? null,
                'guardian_name' => $data['guardian_name'] ?? null,
                'visit_type' => $data['visit_type'],
                'visit_number' => $data['visit_number'] ?? null,
                'visit_at' => $data['visit_at'],
                'doctor_id' => $data['doctor_id'] ?? null,
                'department_id' => $data['department_id'] ?? null,
                'referred_by' => $data['referred_by'] ?? null,
                'billing_category' => $data['billing_category'],
                'ward_id' => $data['ward_id'] ?? null,
                'room_id' => $data['room_id'] ?? null,
                'bed_id' => $data['bed_id'] ?? null,
                'admitted_at' => $data['admitted_at'] ?? null,
                'discharged_at' => $data['discharged_at'] ?? null,
                'insurance_provider' => $data['insurance_provider'] ?? null,
                'insurance_policy_number' => $data['insurance_policy_number'] ?? null,
                'chief_complaint' => $data['chief_complaint'] ?? null,
                'diagnosis' => $data['diagnosis'] ?? null,
                'notes' => $data['hospital_notes'] ?? $data['notes'] ?? null,
            ];
        }

        try {
            $createdInvoice = DB::transaction(function () use (
                $request, $user, $bid, $business, $client, $isHospitalBusiness,
                $data, $docType, $invoiceDate, $prefix, $invoiceNumber,
                $subtotal, $discountTotal, $chargeTotal, $lessAmount,
                $taxAmount, $cgstPercent, $cgstAmount, $sgstPercent, $sgstAmount,
                $igstPercent, $igstAmount, $tcsPercent, $tcsAmount, $roundOff,
                $grandTotal, $receivedTotal, $balance, $cleanRows,
                $cash, $online, $card, $cheque, $credit, $advance, $pay,
                $additionalCharges, $hospitalSnapshot
            ) {
                $patientVisit = null;

                if ($isHospitalBusiness) {
                    $visitNumber = trim((string) ($data['visit_number'] ?? ''));

                    if ($visitNumber === '') {
                        $visitPrefix = match ($data['visit_type']) {
                            'ipd' => 'IPD',
                            'emergency' => 'EMR',
                            'day_care' => 'DAY',
                            'diagnostic' => 'DIA',
                            'pharmacy' => 'PHA',
                            default => 'OPD',
                        };

                        $nextVisitSequence = PatientVisit::withoutGlobalScopes()
                            ->where('business_id', $bid)
                            ->lockForUpdate()
                            ->count() + 1;

                        $visitNumber = sprintf(
                            '%s-%s-%05d',
                            $visitPrefix,
                            Carbon::parse($data['visit_at'])->format('Y'),
                            $nextVisitSequence
                        );
                    }

                    $visitStatus = match ($data['visit_type']) {
                        'ipd' => 'admitted',
                        'emergency' => !empty($data['admitted_at']) ? 'admitted' : 'registered',
                        default => 'registered',
                    };

                    if (!empty($data['discharged_at'])) {
                        $visitStatus = 'discharged';
                    }

                    $patientVisit = PatientVisit::withoutGlobalScopes()->create([
                        'business_id' => $bid,
                        'client_id' => $client->id,
                        'doctor_id' => $data['doctor_id'] ?? null,
                        'department_id' => $data['department_id'] ?? null,
                        'visit_number' => $visitNumber,
                        'visit_type' => $data['visit_type'],
                        'visit_at' => Carbon::parse($data['visit_at']),
                        'chief_complaint' => $data['chief_complaint'] ?? null,
                        'diagnosis' => $data['diagnosis'] ?? null,
                        'remarks' => $data['hospital_notes'] ?? $data['notes'] ?? null,
                        'ward_id' => $data['ward_id'] ?? null,
                        'room_id' => $data['room_id'] ?? null,
                        'bed_id' => $data['bed_id'] ?? null,
                        'admitted_at' => !empty($data['admitted_at']) ? Carbon::parse($data['admitted_at']) : null,
                        'discharged_at' => !empty($data['discharged_at']) ? Carbon::parse($data['discharged_at']) : null,
                        'status' => $visitStatus,
                    ]);

                    if (!empty($data['bed_id']) && Schema::hasColumn('hospital_beds', 'status')) {
                        HospitalBed::withoutGlobalScopes()
                            ->where('business_id', $bid)
                            ->whereKey($data['bed_id'])
                            ->update([
                                'status' => !empty($data['discharged_at']) ? 'available' : 'occupied',
                            ]);
                    }
                }

                $payload = [
                    'business_id' => $bid,
                    'client_id' => $client->id,
                    'invoice_date' => $invoiceDate,
                    'invoice_prefix' => $prefix,
                    'invoice_number' => $invoiceNumber,
                    'invoice_type' => $docType,
                    'subtotal' => $subtotal,
                    'discount_total' => $discountTotal,
                    'charge_total' => $chargeTotal,
                    'less_amount' => $lessAmount,
                    'tax_amount' => $taxAmount,
                    'cgst_percent' => $cgstPercent,
                    'cgst_amount' => $cgstAmount,
                    'sgst_percent' => $sgstPercent,
                    'sgst_amount' => $sgstAmount,
                    'igst_percent' => $igstPercent,
                    'igst_amount' => $igstAmount,
                    'tcs_percent' => $tcsPercent,
                    'tcs_amount' => $tcsAmount,
                    'round_off' => $roundOff,
                    'total' => $grandTotal,
                    'received_amount' => $docType === 'tax' ? $receivedTotal : 0,
                    'balance' => $docType === 'tax' ? $balance : $grandTotal,
                    'payment_method' => $data['payment_method'] ?? null,
                    'gst_no' => $data['gst_no'] ?? null,
                    'transport_mode' => $data['transport_mode'] ?? null,
                    'reverse_charge' => !empty($data['reverse_charge']) ? 1 : 0,
                    'place_of_supply_state' => $client->state ?? null,
                    'place_of_supply_code' => $client->state_code ?? null,
                    'notes' => $data['notes'] ?? null,
                    'terms' => $data['terms'] ?? null,
                    'charges_json' => json_encode($additionalCharges, JSON_UNESCAPED_UNICODE),
                    'items_json' => json_encode($cleanRows, JSON_UNESCAPED_UNICODE),
                    'amount_in_words' => '',
                    'created_by' => $user->id,
                    'updated_by' => $user->id,
                ];

                if ($isHospitalBusiness) {
                    $payload['patient_visit_id'] = $patientVisit?->id;
                    $payload['doctor_id'] = $data['doctor_id'] ?? null;
                    $payload['billing_category'] = $data['billing_category'];
                    $payload['hospital_bill_type'] = $data['visit_type'];
                    $payload['hospital_details_json'] = json_encode(array_merge(
                        $hospitalSnapshot ?? [],
                        ['visit_number' => $patientVisit?->visit_number]
                    ), JSON_UNESCAPED_UNICODE);
                }

                $invoice = Invoice::withoutGlobalScopes()->create($payload);

                foreach ($additionalCharges as $charge) {
                    InvoiceAdditionalCharge::withoutGlobalScopes()->create([
                        'invoice_id' => $invoice->id,
                        'additional_charge_id' => null,
                        'name' => $charge['name'],
                        'amount' => $charge['amount'],
                    ]);
                }

                foreach ($cleanRows as $row) {
                    InvoiceItem::withoutGlobalScopes()->create([
                        'invoice_id' => $invoice->id,
                        'item_id' => $row['item_id'],
                        'description' => $row['description'],
                        'sac_code' => $row['hsn'] ?: null,
                        'hsn_code' => $row['hsn'] ?: null,
                        'quantity' => $row['qty'],
                        'gold_wt' => (float) ($row['gold_wt'] ?? 0),
                        'silver_wt' => (float) ($row['silver_wt'] ?? 0),
                        'gold_rate' => (float) ($row['gold_rate'] ?? 0),
                        'silver_rate' => (float) ($row['silver_rate'] ?? 0),
                        'gemstone_wt_ct' => (float) ($row['gemstone_wt'] ?? 0),
                        'diamond_wt_ct' => (float) ($row['diamond_wt'] ?? 0),
                        'stone_charges' => (float) ($row['stone_charges'] ?? 0),
                        'diamond_charges' => (float) ($row['diamond_charges'] ?? 0),
                        'making_charge' => (float) ($row['making_charge'] ?? 0),
                        'making_rate' => (float) ($row['making_rate'] ?? 0),
                        'making_charge_type' => $row['making_charge_type'] ?? 'percentage',
                        'discount' => 0,
                        'tax_percent' => (float) ($row['tax_percent'] ?? 0),
                        'rate' => round((float) ($row['rate'] ?? 0), 2),
                        'amount' => round((float) ($row['amount'] ?? 0), 2),
                    ]);
                }

                if ($docType === 'tax') {
                    InvoicePayment::withoutGlobalScopes()->create([
                        'business_id' => $bid,
                        'invoice_id' => $invoice->id,
                        'client_id' => $client->id,
                        'total_value' => $grandTotal,
                        'cash_amount' => $cash,
                        'online_amount' => $online,
                        'card_amount' => $card,
                        'cheque_amount' => $cheque,
                        'online_mode' => $pay['online_mode'] ?? null,
                        'online_ref' => $pay['online_ref'] ?? null,
                        'upi_id' => $pay['upi_id'] ?? null,
                        'card_last4' => $pay['card_last4'] ?? null,
                        'card_ref' => $pay['card_ref'] ?? null,
                        'cheque_no' => $pay['cheque_no'] ?? null,
                        'bank_name' => $pay['bank_name'] ?? null,
                        'credit_sales_excess_amount' => $credit,
                        'advance_amount' => $advance,
                        'received_total' => $receivedTotal,
                        'notes' => $pay['payment_notes'] ?? $pay['pay_notes'] ?? null,
                        'meta' => $isHospitalBusiness ? json_encode([
                            'patient_visit_id' => $patientVisit?->id,
                            'visit_type' => $data['visit_type'] ?? null,
                        ]) : null,
                        'paid_at' => $receivedTotal > 0 ? now() : null,
                    ]);

                    $invoice->load('items');
                    $this->stock->recordSale($invoice);

                    $bankAccountId = $data['bank_account_id'] ?? null;
                    $paymentMode = strtolower(trim((string) ($data['payment_method'] ?? '')));

                    if ($bankAccountId
                        && in_array($paymentMode, ['upi', 'bank', 'card', 'cheque'], true)
                        && $receivedTotal > 0) {
                        $bankAccount = BankAccount::withoutGlobalScopes()
                            ->where('business_id', $bid)
                            ->whereKey($bankAccountId)
                            ->lockForUpdate()
                            ->first();

                        if ($bankAccount) {
                            $bankAccount->balance = round((float) $bankAccount->balance + $receivedTotal, 2);
                            $bankAccount->save();
                        }
                    }
                }

                return $invoice;
            });

            InvoiceNumber::syncNextSeqIfMatches($bid, $invoiceDate, $invoiceNumber, 3, $docType);

            return response()->json([
                'ok' => true,
                'message' => $isHospitalBusiness
                    ? 'Hospital bill created successfully.'
                    : ucfirst($docType) . ' created successfully.',
                'invoice' => Invoice::withoutGlobalScopes()
                    ->with(['client', 'items', 'business'])
                    ->whereKey($createdInvoice->id)
                    ->first(),
            ], 201);
        } catch (\Throwable $e) {
            Log::error('API Invoice create failed', [
                'business_id' => $bid,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return response()->json([
                'ok' => false,
                'message' => 'Invoice create failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // public function update(Request $request, $invoice)
    // {
    //     $invoice = $this->findInvoiceForUser($request, $invoice);
    //     $bid = (int) $invoice->business_id;


    //     $docType = $this->normalizeDocType((string) ($invoice->invoice_type ?? 'tax'));

    //     $data = $request->validate([
    //         'client_id'      => ['required', 'integer'],
    //         'invoice_date'   => ['required', 'date'],
    //         'invoice_prefix' => ['nullable', 'string', 'max:255'],
    //         'invoice_number' => ['required', 'string', 'max:255'],

    //         'transport_mode' => ['nullable', 'string', 'max:255'],
    //         // 'gst_no'         => ['nullable', 'string', 'max:50'],
    //         'reverse_charge' => ['nullable'],

    //         'notes'          => ['nullable', 'string', 'max:2000'],
    //         'terms'          => ['nullable', 'string', 'max:2000'],

    //         'items_json'     => ['nullable'],
    //         'items'          => ['nullable', 'array', 'min:1'],

    //         'charges_json'   => ['nullable'],
    //         'discount_total' => ['nullable', 'numeric', 'min:0'],
    //         'charge_total'   => ['nullable', 'numeric', 'min:0'],
    //         'tcs_percent'    => ['nullable', 'numeric', 'min:0'],
    //         'tcs_amount'     => ['nullable', 'numeric', 'min:0'],
    //         'round_off'      => ['nullable', 'numeric'],
    //         'less_amount'    => ['nullable', 'numeric', 'min:0'],

    //         'payment_method'  => ['nullable', 'string', 'max:255'],
    //         'bank_account_id' => ['nullable', 'integer'],
    //     ]);

    //     // Handle items_json from Flutter / Postman / web
    //     $itemsJsonInput = $request->input('items_json');
    //     $itemsJson = null;

    //     if (is_string($itemsJsonInput)) {
    //         $itemsJson = $itemsJsonInput;
    //     } elseif (is_array($itemsJsonInput)) {
    //         $isAssoc = array_keys($itemsJsonInput) !== range(0, count($itemsJsonInput) - 1);
    //         $rowsToEncode = $isAssoc ? [$itemsJsonInput] : $itemsJsonInput;
    //         $itemsJson = json_encode($rowsToEncode, JSON_UNESCAPED_UNICODE);
    //     }

    //     if (!$itemsJson && !empty($data['items']) && is_array($data['items'])) {
    //         $itemsJson = json_encode($data['items'], JSON_UNESCAPED_UNICODE);
    //     }

    //     if (!$itemsJson) {
    //         return response()->json([
    //             'ok' => false,
    //             'message' => 'items_json or items is required',
    //         ], 422);
    //     }

    //     // Handle charges_json from string / array
    //     $chargesJsonInput = $request->input('charges_json');
    //     $chargesJson = null;

    //     if (is_string($chargesJsonInput)) {
    //         $chargesJson = $chargesJsonInput;
    //     } elseif (is_array($chargesJsonInput)) {
    //         $chargesJson = json_encode($chargesJsonInput, JSON_UNESCAPED_UNICODE);
    //     }

    //     // payments only for tax
    //     $pay = [];
    //     if ($docType === 'tax') {
    //         $pay = $request->validate([
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
    //             'pay_notes'           => ['nullable', 'string', 'max:2000'],
    //         ]);
    //     }

    //     $biz = Business::withoutGlobalScopes()->find($bid);
    //     if (!$biz) {
    //         return response()->json([
    //             'ok' => false,
    //             'message' => 'Business not found',
    //         ], 404);
    //     }

    //     $client = Client::withoutGlobalScopes()
    //         ->where('business_id', $bid)
    //         ->where('id', (int) $data['client_id'])
    //         ->first();
    //     if (!$client) {
    //         return response()->json([
    //             'ok' => false,
    //             'message' => 'Client not found for this business',
    //         ], 404);
    //     }

    //     $invoiceDate = \Carbon\Carbon::parse($data['invoice_date'])->toDateString();

    //     $prefix = trim((string) ($data['invoice_prefix'] ?? ''));
    //     if ($prefix === '') {
    //         $base = $docType === 'proforma'
    //             ? 'PF'
    //             : ($docType === 'quotation'
    //                 ? 'QT'
    //                 : ($biz->invoice_base_prefix ?? 'INV'));

    //         $prefix = InvoiceNumber::previewPrefix($invoiceDate, $base);
    //         if (!$prefix) {
    //             $prefix = $this->computePrefix($invoiceDate, $base);
    //         }
    //     }

    //     $rows = json_decode($itemsJson, true);
    //     if (!is_array($rows) || count($rows) < 1) {
    //         return response()->json([
    //             'ok' => false,
    //             'message' => 'Items invalid',
    //         ], 422);
    //     }

    //     $subtotal = 0.0;
    //     $weightedTax = 0.0;
    //     $itemsTaxTotal = 0.0;
    //     $cleanRows = [];

    //     foreach ($rows as $i => $row) {
    //         $rowNo = $i + 1;

    //         $itemId = $row['item_id'] ?? null;
    //         if (!$itemId) {
    //             return response()->json([
    //                 'ok' => false,
    //                 'message' => "Row {$rowNo} item_id missing",
    //             ], 422);
    //         }

    //         $itemType = strtolower(trim((string) ($row['item_type'] ?? 'product')));
    //         if (!in_array($itemType, ['product', 'service'], true)) {
    //             $itemType = 'product';
    //         }

    //         $desc = trim((string) ($row['description'] ?? ''));
    //         if ($desc === '') {
    //             return response()->json([
    //                 'ok' => false,
    //                 'message' => "Row {$rowNo} description missing",
    //             ], 422);
    //         }

    //         $hsn = trim((string) ($row['hsn'] ?? ''));
    //         $qty = (int) ($row['qty'] ?? $row['quantity'] ?? 1);
    //         $qty = $qty < 1 ? 1 : $qty;

    //         $taxPct = round((float) ($row['tax_percent'] ?? 0), 2);
    //         if ($taxPct < 0 || $taxPct > 100) {
    //             return response()->json([
    //                 'ok' => false,
    //                 'message' => "Row {$rowNo} tax_percent invalid",
    //             ], 422);
    //         }

    //         if ($itemType === 'service') {
    //             $serviceRate = round((float) ($row['service_rate'] ?? 0), 2);
    //             if ($serviceRate < 0) {
    //                 return response()->json([
    //                     'ok' => false,
    //                     'message' => "Row {$rowNo} service_rate invalid",
    //                 ], 422);
    //             }

    //             $lineBase = round($serviceRate * $qty, 2);
    //             $lineTax  = round($lineBase * ($taxPct / 100), 2);

    //             $subtotal += $lineBase;
    //             $weightedTax += ($lineBase * $taxPct);
    //             $itemsTaxTotal += $lineTax;

    //             $cleanRows[] = [
    //                 'item_id'       => (int) $itemId,
    //                 'item_type'     => 'service',
    //                 'description'   => $desc,
    //                 'hsn'           => $hsn,
    //                 'qty'           => $qty,
    //                 'tax_percent'   => $taxPct,
    //                 'service_rate'  => $serviceRate,
    //                 'rate'          => $lineBase,
    //                 'tax_amount'    => $lineTax,
    //                 'amount'        => round($lineBase + $lineTax, 2),
    //                 'gold_wt'       => 0,
    //                 'silver_wt'     => 0,
    //                 'gold_rate'     => 0,
    //                 'silver_rate'   => 0,
    //                 'gemstone_wt'   => 0,
    //                 'diamond_wt'    => 0,
    //                 'making_rate'   => 0,
    //             ];

    //             continue;
    //         }

    //         $goldWt      = round((float) ($row['gold_wt'] ?? 0), 3);
    //         $silverWt    = round((float) ($row['silver_wt'] ?? 0), 3);
    //         $goldRate    = round((float) ($row['gold_rate'] ?? 0), 2);
    //         $silverRate  = round((float) ($row['silver_rate'] ?? 0), 2);
    //         $makingRate  = round((float) ($row['making_rate'] ?? 0), 2);
    //         $gemstoneWt  = round((float) ($row['gemstone_wt'] ?? 0), 3);
    //         $diamondWt   = round((float) ($row['diamond_wt'] ?? 0), 3);

    //         $lineBase = round((($goldWt * $goldRate) + ($silverWt * $silverRate) + $makingRate) * $qty, 2);
    //         $lineTax  = round($lineBase * ($taxPct / 100), 2);

    //         $subtotal += $lineBase;
    //         $weightedTax += ($lineBase * $taxPct);
    //         $itemsTaxTotal += $lineTax;

    //         $cleanRows[] = [
    //             'item_id'      => (int) $itemId,
    //             'item_type'    => 'product',
    //             'description'  => $desc,
    //             'hsn'          => $hsn,
    //             'qty'          => $qty,
    //             'tax_percent'  => $taxPct,
    //             'gold_wt'      => $goldWt,
    //             'silver_wt'    => $silverWt,
    //             'gold_rate'    => $goldRate,
    //             'silver_rate'  => $silverRate,
    //             'gemstone_wt'  => $gemstoneWt,
    //             'diamond_wt'   => $diamondWt,
    //             'making_rate'  => $makingRate,
    //             'rate'         => $lineBase,
    //             'tax_amount'   => $lineTax,
    //             'amount'       => round($lineBase + $lineTax, 2),
    //         ];
    //     }

    //     $subtotal = round($subtotal, 2);
    //     $itemsTaxTotal = round($itemsTaxTotal, 2);

    //     $avgTaxPercent = $subtotal > 0
    //         ? round($weightedTax / $subtotal, 2)
    //         : 0.00;

    //     $discountTotal = round((float) ($data['discount_total'] ?? 0), 2);
    //     $chargeTotal   = round((float) ($data['charge_total'] ?? 0), 2);
    //     $roundOff      = round((float) ($data['round_off'] ?? 0), 2);

    //     $taxableAmount = round(max(0, $subtotal - $discountTotal + $chargeTotal), 2);

    //     $taxAmount = $avgTaxPercent > 0
    //         ? round($taxableAmount * ($avgTaxPercent / 100), 2)
    //         : 0.00;

    //     $tcsPercent = round((float) ($data['tcs_percent'] ?? 0), 2);
    //     $tcsAmount  = round((float) ($data['tcs_amount'] ?? 0), 2);

    //     if ($tcsAmount <= 0 && $tcsPercent > 0) {
    //         $tcsAmount = round($taxableAmount * ($tcsPercent / 100), 2);
    //     }

    //     $lessAmount = round((float) ($data['less_amount'] ?? $discountTotal), 2);

    //     $grandTotal = round($taxableAmount + $taxAmount + $tcsAmount + $roundOff, 2);

    //     $cash = $online = $card = $cheque = $credit = $advance = 0.0;
    //     $receivedTotal = 0.0;
    //     $balance = $grandTotal;

    //     if ($docType === 'tax') {
    //         $cash    = round((float) ($pay['pay_cash'] ?? 0), 2);
    //         $online  = round((float) ($pay['pay_upi'] ?? 0), 2);
    //         $card    = round((float) ($pay['pay_card'] ?? 0), 2);
    //         $cheque  = round((float) ($pay['pay_cheque'] ?? 0), 2);
    //         $credit  = round((float) ($pay['credit_sales_excess'] ?? 0), 2);
    //         $advance = round((float) ($pay['advance_amount'] ?? 0), 2);

    //         $receivedTotal = round($cash + $online + $card + $cheque, 2);
    //         $balance = round(max(0, $grandTotal - $receivedTotal - $advance - $credit), 2);
    //     }

    //     $bizCode   = $this->normCode($biz->state_code ?? '');
    //     $partyCode = $this->normCode($client->state_code ?? '');

    //     $isIntra = false;

    //     if ($bizCode !== '' && $partyCode !== '') {
    //         $isIntra = ($bizCode === $partyCode);
    //     } else {
    //         $bizState   = strtolower(trim((string) ($biz->state ?? '')));
    //         $partyState = strtolower(trim((string) ($client->state ?? '')));
    //         if ($bizState !== '' && $partyState !== '') {
    //             $isIntra = ($bizState === $partyState);
    //         }
    //     }

    //     $cgstPercent = $isIntra ? round($avgTaxPercent / 2, 2) : 0.00;
    //     $sgstPercent = $isIntra ? round($avgTaxPercent / 2, 2) : 0.00;
    //     $igstPercent = $isIntra ? 0.00 : round($avgTaxPercent, 2);

    //     $cgst = $isIntra ? round($taxAmount / 2, 2) : 0.00;
    //     $sgst = $isIntra ? round($taxAmount / 2, 2) : 0.00;
    //     $igst = $isIntra ? 0.00 : round($taxAmount, 2);

    //     $reqInvoiceNo = trim((string) ($data['invoice_number'] ?? ''));
    //     if ($reqInvoiceNo === '') {
    //         return response()->json([
    //             'ok' => false,
    //             'message' => 'invoice_number is required',
    //         ], 422);
    //     }

    //     if (
    //         Invoice::withoutGlobalScopes()
    //             ->where('business_id', $bid)
    //             ->where('invoice_number', $reqInvoiceNo)
    //             ->where('id', '!=', $invoice->id)
    //             ->exists()
    //     ) {
    //         return response()->json([
    //             'ok' => false,
    //             'message' => 'Invoice number already exists',
    //             'invoice_number' => $reqInvoiceNo,
    //         ], 409);
    //     }

    //     InvoiceNumber::syncNextSeqIfMatches((int) $bid, $invoiceDate, $reqInvoiceNo, 3, $docType);

    //     DB::transaction(function () use (
    //         $invoice,
    //         $bid,
    //         $data,
    //         $client,
    //         $invoiceDate,
    //         $prefix,
    //         $docType,
    //         $reqInvoiceNo,
    //         $subtotal,
    //         $discountTotal,
    //         $chargeTotal,
    //         $lessAmount,
    //         $taxAmount,
    //         $cgstPercent,
    //         $sgstPercent,
    //         $igstPercent,
    //         $cgst,
    //         $sgst,
    //         $igst,
    //         $tcsPercent,
    //         $tcsAmount,
    //         $roundOff,
    //         $grandTotal,
    //         $receivedTotal,
    //         $balance,
    //         $cleanRows,
    //         $cash,
    //         $online,
    //         $card,
    //         $cheque,
    //         $credit,
    //         $advance,
    //         $pay,
    //         $chargesJson
    //     ) {
    //         Invoice::withoutGlobalScopes()
    //             ->where('id', $invoice->id)
    //             ->update([
    //             'client_id'             => (int) $data['client_id'],
    //             'invoice_date'          => $invoiceDate,

    //             'invoice_prefix'        => $prefix,
    //             'invoice_number'        => $reqInvoiceNo,
    //             'invoice_type'          => $docType,

    //             'subtotal'              => $subtotal,
    //             'discount_total'        => $discountTotal,
    //             'charge_total'          => $chargeTotal,
    //             'less_amount'           => $lessAmount,

    //             'tax_amount'            => $taxAmount,

    //             'cgst_percent'          => $cgstPercent,
    //             'cgst_amount'           => $cgst,
    //             'sgst_percent'          => $sgstPercent,
    //             'sgst_amount'           => $sgst,
    //             'igst_percent'          => $igstPercent,
    //             'igst_amount'           => $igst,

    //             'tcs_percent'           => $tcsPercent,
    //             'tcs_amount'            => $tcsAmount,
    //             'round_off'             => $roundOff,

    //             'total'                 => $grandTotal,
    //             'received_amount'       => $docType === 'tax' ? $receivedTotal : 0,
    //             'balance'               => $docType === 'tax' ? $balance : $grandTotal,

    //             'payment_method'        => $data['payment_method'] ?? null,
    //             // 'gst_no'                => $data['gst_no'] ?: null,
    //             'transport_mode'        => !empty($data['transport_mode']) ? $data['transport_mode'] : null,
    //             'reverse_charge'        => !empty($data['reverse_charge']) ? 1 : 0,

    //             'place_of_supply_state' => $client->state ?? null,
    //             'place_of_supply_code'  => $client->state_code ?? null,

    //             'notes'                 => $data['notes'] ?? null,
    //             'terms'                 => $data['terms'] ?? null,

    //             'charges_json'          => $chargesJson,
    //             'items_json'            => json_encode($cleanRows, JSON_UNESCAPED_UNICODE),
    //             'amount_in_words'       => '',
    //         ]);

    //         InvoiceItem::withoutGlobalScopes()->where('invoice_id', $invoice->id)->delete();

    //         foreach ($cleanRows as $row) {
    //             InvoiceItem::withoutGlobalScopes()->create([
    //                 'invoice_id'      => $invoice->id,
    //                 'item_id'         => $row['item_id'],
    //                 'description'     => $row['description'],
    //                 'hsn_code'        => $row['hsn'] ?: null,
    //                 'quantity'        => (int) $row['qty'],

    //                 'gold_wt'         => (float) ($row['gold_wt'] ?? 0),
    //                 'silver_wt'       => (float) ($row['silver_wt'] ?? 0),
    //                 'gold_rate'       => (float) ($row['gold_rate'] ?? 0),
    //                 'silver_rate'     => (float) ($row['silver_rate'] ?? 0),
    //                 'gemstone_wt_ct'  => (float) ($row['gemstone_wt'] ?? 0),
    //                 'diamond_wt_ct'   => (float) ($row['diamond_wt'] ?? 0),

    //                 'making_rate'     => $row['item_type'] === 'product'
    //                     ? (float) ($row['making_rate'] ?? 0)
    //                     : null,

    //                 'making_charge'   => $row['item_type'] === 'service'
    //                     ? (float) ($row['service_rate'] ?? 0)
    //                     : null,

    //                 'tax_percent'     => (float) ($row['tax_percent'] ?? 0),
    //                 'rate'            => (float) ($row['rate'] ?? 0),
    //                 'amount'          => (float) ($row['amount'] ?? 0),
    //             ]);
    //         }

    //         if ($docType === 'tax') {
    //             $payRow = InvoicePayment::withoutGlobalScopes()->where('invoice_id', $invoice->id)->latest('id')->first();

    //             if (!$payRow) {
    //                 $payRow = new InvoicePayment();
    //             }

    //             $payRow->fill([
    //                 'business_id'                 => $bid,
    //                 'invoice_id'                  => $invoice->id,
    //                 'client_id'                   => (int) $data['client_id'],
    //                 'total_value'                 => $grandTotal,

    //                 'cash_amount'                 => $cash,
    //                 'online_amount'               => $online,
    //                 'card_amount'                 => $card,
    //                 'cheque_amount'               => $cheque,

    //                 'online_mode'                 => $pay['online_mode'] ?? null,
    //                 'online_ref'                  => $pay['online_ref'] ?? null,
    //                 'upi_id'                      => $pay['upi_id'] ?? null,
    //                 'card_last4'                  => $pay['card_last4'] ?? null,
    //                 'card_ref'                    => $pay['card_ref'] ?? null,
    //                 'cheque_no'                   => $pay['cheque_no'] ?? null,
    //                 'bank_name'                   => $pay['bank_name'] ?? null,

    //                 'credit_sales_excess_amount' => $credit,
    //                 'advance_amount'              => $advance,
    //                 'received_total'              => $receivedTotal,
    //                 'notes'                       => $pay['pay_notes'] ?? null,
    //                 'paid_at'                     => $receivedTotal > 0 ? now() : null,
    //             ]);

    //             $payRow->save();
    //         }
    //     });

    //     if ($docType === 'tax') {
    //         if (method_exists($this->stock, 'rollbackSale')) {
    //             $this->stock->rollbackSale($invoice);
    //         }

    //         $invoice->load('items');
    //         $this->stock->recordSale($invoice);
    //     }

    //     return response()->json([
    //         'ok'      => true,
    //         'message' => ucfirst($docType) . ' updated',
    //         'invoice' => $invoice->fresh(['client', 'items', 'business']),]);
    // }


    public function update(Request $request, $invoice)
    {
        $invoice = $this->findInvoiceForUser($request, $invoice);
        $user = $request->user();
        $bid = (int) $invoice->business_id;
        $docType = $this->normalizeDocType((string) ($invoice->invoice_type ?? 'tax'));

        $business = Business::withoutGlobalScopes()
            ->with('businessType')
            ->whereKey($bid)
            ->firstOrFail();

        $businessType = strtolower(trim((string) (
            $business->businessType?->slug
            ?? $business->businessType?->name
            ?? ''
        )));

        $isHospitalBusiness = method_exists($business, 'isHospitalBusiness')
            ? $business->isHospitalBusiness()
            : in_array($businessType, [
                'hospital', 'clinic', 'nursing home', 'nursing_home',
                'diagnostic center', 'diagnostic_center',
                'pathology lab', 'pathology_lab',
            ], true);

        $rules = [
            'client_id' => [
                'required', 'integer',
                Rule::exists('clients', 'id')->where('business_id', $bid),
            ],
            'invoice_date' => ['required', 'date'],
            'invoice_prefix' => ['nullable', 'string', 'max:255'],
            'invoice_number' => ['nullable', 'string', 'max:255'],
            'transport_mode' => ['nullable', 'string', 'max:255'],
            'gst_no' => ['nullable', 'string', 'max:50'],
            'reverse_charge' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'terms' => ['nullable', 'string', 'max:5000'],
            'items_json' => ['nullable'],
            'items' => ['nullable', 'array', 'min:1'],
            'charges_json' => ['nullable'],
            'discount_total' => ['nullable', 'numeric', 'min:0'],
            'charge_total' => ['nullable', 'numeric', 'min:0'],
            'tcs_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'tcs_amount' => ['nullable', 'numeric', 'min:0'],
            'round_off' => ['nullable', 'numeric'],
            'less_amount' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['nullable', 'string', 'max:255'],
            'bank_account_id' => [
                'nullable', 'integer',
                Rule::exists('bank_accounts', 'id')->where('business_id', $bid),
            ],
        ];

        if ($isHospitalBusiness) {
            $rules = array_merge($rules, [
                'patient_uhid' => ['nullable', 'string', 'max:100'],
                'patient_age' => ['nullable', 'integer', 'min:0', 'max:150'],
                'patient_gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
                'blood_group' => ['nullable', Rule::in(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'])],
                'guardian_name' => ['nullable', 'string', 'max:255'],
                'visit_type' => ['required', Rule::in(['opd', 'ipd', 'emergency', 'day_care', 'diagnostic', 'pharmacy'])],
                'visit_number' => ['nullable', 'string', 'max:100'],
                'visit_at' => ['required', 'date'],
                'doctor_id' => ['nullable', 'integer', Rule::exists('doctors', 'id')->where('business_id', $bid)],
                'department_id' => ['nullable', 'integer', Rule::exists('hospital_departments', 'id')->where('business_id', $bid)],
                'referred_by' => ['nullable', 'string', 'max:255'],
                'billing_category' => ['required', Rule::in(['cash', 'credit', 'insurance', 'corporate', 'government_scheme', 'charity'])],
                'ward_id' => ['nullable', 'integer', Rule::exists('hospital_wards', 'id')->where('business_id', $bid)],
                'room_id' => ['nullable', 'integer', Rule::exists('hospital_rooms', 'id')->where('business_id', $bid)],
                'bed_id' => ['nullable', 'integer', Rule::exists('hospital_beds', 'id')->where('business_id', $bid)],
                'admitted_at' => ['nullable', 'date'],
                'discharged_at' => ['nullable', 'date', 'after_or_equal:admitted_at'],
                'insurance_provider' => ['nullable', 'string', 'max:255'],
                'insurance_policy_number' => ['nullable', 'string', 'max:255'],
                'chief_complaint' => ['nullable', 'string', 'max:5000'],
                'diagnosis' => ['nullable', 'string', 'max:5000'],
                'hospital_notes' => ['nullable', 'string', 'max:5000'],
            ]);
        }

        $data = $request->validate($rules);

        $pay = [];
        if ($docType === 'tax') {
            $pay = $request->validate([
                'pay_cash' => ['nullable', 'numeric', 'min:0'],
                'pay_upi' => ['nullable', 'numeric', 'min:0'],
                'pay_card' => ['nullable', 'numeric', 'min:0'],
                'pay_cheque' => ['nullable', 'numeric', 'min:0'],
                'credit_sales_excess' => ['nullable', 'numeric', 'min:0'],
                'advance_amount' => ['nullable', 'numeric', 'min:0'],
                'online_mode' => ['nullable', 'string', 'max:30'],
                'online_ref' => ['nullable', 'string', 'max:100'],
                'upi_id' => ['nullable', 'string', 'max:100'],
                'card_last4' => ['nullable', 'digits:4'],
                'card_ref' => ['nullable', 'string', 'max:100'],
                'cheque_no' => ['nullable', 'string', 'max:50'],
                'bank_name' => ['nullable', 'string', 'max:100'],
                'pay_notes' => ['nullable', 'string', 'max:2000'],
                'payment_notes' => ['nullable', 'string', 'max:2000'],
            ]);
        }

        $client = Client::withoutGlobalScopes()
            ->where('business_id', $bid)
            ->whereKey((int) $data['client_id'])
            ->firstOrFail();

        $toNumber = static function ($value, float $default = 0.0): ?float {
            if ($value === null || $value === '') return $default;
            if (is_int($value) || is_float($value)) return (float) $value;
            if (is_string($value)) {
                $value = str_replace(',', '', trim($value));
                if ($value === '') return $default;
            }
            return is_numeric($value) ? (float) $value : null;
        };

        $decodeArrayInput = static function ($value, string $fieldName): array {
            if ($value === null || $value === '') return [];
            if (is_array($value)) return $value;
            if (is_string($value)) {
                $decoded = json_decode($value, true);
                if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
                    throw new \InvalidArgumentException("{$fieldName} invalid JSON hai: " . json_last_error_msg());
                }
                return $decoded;
            }
            throw new \InvalidArgumentException("{$fieldName} array ya JSON string hona chahiye.");
        };

        try {
            $rows = $decodeArrayInput($request->input('items_json'), 'items_json');
            if (!$rows && is_array($request->input('items'))) $rows = $request->input('items');
            if ($rows && !array_is_list($rows)) $rows = [$rows];

            $chargesArr = $decodeArrayInput($request->input('charges_json'), 'charges_json');
            if ($chargesArr && !array_is_list($chargesArr)) $chargesArr = [$chargesArr];
        } catch (\InvalidArgumentException $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 422);
        }

        if (!$rows) {
            return response()->json([
                'ok' => false,
                'message' => $isHospitalBusiness
                    ? 'Kam se kam 1 hospital service/charge zaroori hai.'
                    : 'items_json or items is required.',
            ], 422);
        }

        $subtotal = 0.0;
        $weightedTax = 0.0;
        $itemsTaxTotal = 0.0;
        $cleanRows = [];

        foreach ($rows as $index => $row) {
            $rowNo = $index + 1;
            if (!is_array($row)) {
                return response()->json(['ok' => false, 'message' => "Row {$rowNo} invalid hai."], 422);
            }

            $itemId = (int) ($row['item_id'] ?? 0);
            $item = Item::withoutGlobalScopes()
                ->where('business_id', $bid)
                ->whereKey($itemId)
                ->where('is_active', true)
                ->first();

            if (!$item) {
                return response()->json(['ok' => false, 'message' => "Row {$rowNo} item/service invalid hai."], 422);
            }

            $description = trim((string) ($row['description'] ?? $item->description ?? $item->name ?? ''));
            if ($description === '') {
                return response()->json(['ok' => false, 'message' => "Row {$rowNo} description missing hai."], 422);
            }

            $hsn = trim((string) ($row['hsn'] ?? $row['sac'] ?? $item->sac ?? $item->hsn ?? ''));

            $quantity = $toNumber($row['qty'] ?? $row['quantity'] ?? 1, 1);
            $taxPercent = $toNumber($row['tax_percent'] ?? $item->tax_rate ?? 0, 0);
            $fixedPrice = $toNumber($row['fixed_price'] ?? $row['service_rate'] ?? $row['price'] ?? $row['unit_rate'] ?? 0, 0);

            if ($quantity === null || $quantity <= 0 || $taxPercent === null || $taxPercent < 0 || $taxPercent > 100 || $fixedPrice === null || $fixedPrice < 0) {
                return response()->json(['ok' => false, 'message' => "Row {$rowNo} quantity/tax/rate invalid hai."], 422);
            }

            $goldWeight = $isHospitalBusiness ? 0.0 : ($toNumber($row['gold_wt'] ?? $row['gold_weight'] ?? 0, 0) ?? 0);
            $silverWeight = $isHospitalBusiness ? 0.0 : ($toNumber($row['silver_wt'] ?? $row['silver_weight'] ?? 0, 0) ?? 0);
            $goldRate = $isHospitalBusiness ? 0.0 : ($toNumber($row['gold_rate'] ?? 0, 0) ?? 0);
            $silverRate = $isHospitalBusiness ? 0.0 : ($toNumber($row['silver_rate'] ?? 0, 0) ?? 0);
            $makingRate = $isHospitalBusiness ? 0.0 : ($toNumber($row['making_rate'] ?? 0, 0) ?? 0);
            $gemstoneWeight = $isHospitalBusiness ? 0.0 : ($toNumber($row['gemstone_wt'] ?? $row['gemstone_wt_ct'] ?? 0, 0) ?? 0);
            $diamondWeight = $isHospitalBusiness ? 0.0 : ($toNumber($row['diamond_wt'] ?? $row['diamond_wt_ct'] ?? 0, 0) ?? 0);
            $stoneCharges = $isHospitalBusiness ? 0.0 : ($toNumber($row['gemstone_charge'] ?? $row['stone_charges'] ?? 0, 0) ?? 0);
            $diamondCharges = $isHospitalBusiness ? 0.0 : ($toNumber($row['diamond_charge'] ?? $row['diamond_charges'] ?? 0, 0) ?? 0);

            $makingChargeType = strtolower(trim((string) ($row['making_charge_type'] ?? 'percentage')));
            $makingChargeType = str_replace([' ', '-'], '_', $makingChargeType);
            if (in_array($makingChargeType, ['percent', 'percentage_based'], true)) $makingChargeType = 'percentage';
            if (in_array($makingChargeType, ['pergram', 'gram'], true)) $makingChargeType = 'per_gram';
            if ($isHospitalBusiness || !in_array($makingChargeType, ['percentage', 'fixed', 'per_gram', 'per_product'], true)) {
                $makingChargeType = 'percentage';
            }

            $metalBase = ($goldWeight * $goldRate) + ($silverWeight * $silverRate);
            $basePrice = $fixedPrice > 0 ? $fixedPrice : $metalBase;

            $makingAmount = 0.0;
            if (!$isHospitalBusiness) {
                $makingAmount = match ($makingChargeType) {
                    'percentage' => round($basePrice * ($makingRate / 100), 2),
                    'fixed' => round($makingRate, 2),
                    'per_gram' => round(($goldWeight + $silverWeight) * $makingRate, 2),
                    'per_product' => round($makingRate, 2),
                    default => 0.0,
                };
            }

            $lineBase = round(($basePrice + $makingAmount + $stoneCharges + $diamondCharges) * $quantity, 2);
            $lineTax = round($lineBase * ($taxPercent / 100), 2);
            $lineTotal = round($lineBase + $lineTax, 2);

            $subtotal += $lineBase;
            $weightedTax += $lineBase * $taxPercent;
            $itemsTaxTotal += $lineTax;

            $cleanRows[] = [
                'item_id' => $itemId,
                'item_type' => $isHospitalBusiness ? 'service' : (strtolower((string) ($row['item_type'] ?? $item->type ?? 'product'))),
                'description' => $description,
                'hsn' => $hsn,
                'qty' => round($quantity, 3),
                'tax_percent' => round($taxPercent, 2),
                'fixed_price' => round($fixedPrice, 2),
                'service_rate' => round($fixedPrice, 2),
                'gold_wt' => round($goldWeight, 3),
                'silver_wt' => round($silverWeight, 3),
                'gold_rate' => round($goldRate, 2),
                'silver_rate' => round($silverRate, 2),
                'gemstone_wt' => round($gemstoneWeight, 3),
                'diamond_wt' => round($diamondWeight, 3),
                'making_charge_type' => $makingChargeType,
                'making_rate' => round($makingRate, 2),
                'making_charge' => round($makingAmount, 2),
                'stone_charges' => round($stoneCharges, 2),
                'diamond_charges' => round($diamondCharges, 2),
                'rate' => $lineBase,
                'unit_rate' => round($fixedPrice, 2),
                'tax_amount' => $lineTax,
                'amount' => $lineTotal,
            ];
        }

        $subtotal = round($subtotal, 2);
        $itemsTaxTotal = round($itemsTaxTotal, 2);
        $averageTaxPercent = $subtotal > 0 ? round($weightedTax / $subtotal, 2) : 0;

        $discountTotal = round((float) ($data['discount_total'] ?? 0), 2);
        $chargeTotal = round((float) ($data['charge_total'] ?? 0), 2);
        $taxableAmount = round(max(0, $subtotal - $discountTotal + $chargeTotal), 2);
        $taxAmount = $itemsTaxTotal;
        $tcsPercent = round((float) ($data['tcs_percent'] ?? 0), 2);
        $tcsAmount = $tcsPercent > 0
            ? round($taxableAmount * ($tcsPercent / 100), 2)
            : round((float) ($data['tcs_amount'] ?? 0), 2);
        $roundOff = round((float) ($data['round_off'] ?? 0), 2);
        $lessAmount = round((float) ($data['less_amount'] ?? $discountTotal), 2);
        $grandTotal = round($taxableAmount + $taxAmount + $tcsAmount + $roundOff, 2);

        $cash = $online = $card = $cheque = $credit = $advance = 0.0;
        $receivedTotal = 0.0;
        $balance = $grandTotal;

        if ($docType === 'tax') {
            $cash = (float) ($pay['pay_cash'] ?? 0);
            $online = (float) ($pay['pay_upi'] ?? 0);
            $card = (float) ($pay['pay_card'] ?? 0);
            $cheque = (float) ($pay['pay_cheque'] ?? 0);
            $credit = (float) ($pay['credit_sales_excess'] ?? 0);
            $advance = (float) ($pay['advance_amount'] ?? 0);
            $receivedTotal = round($cash + $online + $card + $cheque, 2);
            $balance = round(max(0, $grandTotal - $receivedTotal - $advance - $credit), 2);
        }

        $additionalCharges = [];
        foreach ($chargesArr as $charge) {
            if (!is_array($charge)) continue;
            $name = trim((string) ($charge['name'] ?? ''));
            $amount = round((float) ($charge['amount'] ?? 0), 2);
            if ($name !== '' && $amount != 0) $additionalCharges[] = ['name' => $name, 'amount' => $amount];
        }

        $invoiceDate = Carbon::parse($data['invoice_date'])->toDateString();
        $prefix = trim((string) ($data['invoice_prefix'] ?? '')) ?: $invoice->invoice_prefix;
        $invoiceNumber = trim((string) ($data['invoice_number'] ?? '')) ?: $invoice->invoice_number;

        if (Invoice::withoutGlobalScopes()
            ->where('business_id', $bid)
            ->where('invoice_number', $invoiceNumber)
            ->where('id', '!=', $invoice->id)
            ->exists()) {
            return response()->json(['ok' => false, 'message' => 'Invoice number already exists'], 409);
        }

        $normalizeStateCode = static function ($value): string {
            $code = preg_replace('/\D+/', '', trim((string) $value));
            return ltrim($code, '0');
        };

        $bizCode = $normalizeStateCode($business->state_code ?? '');
        $clientCode = $normalizeStateCode($client->state_code ?? '');
        $isIntraState = $bizCode !== '' && $clientCode !== '' ? $bizCode === $clientCode : false;

        $cgstPercent = $isIntraState ? round($averageTaxPercent / 2, 2) : 0;
        $sgstPercent = $isIntraState ? round($averageTaxPercent / 2, 2) : 0;
        $igstPercent = $isIntraState ? 0 : round($averageTaxPercent, 2);
        $cgstAmount = $isIntraState ? round($taxAmount / 2, 2) : 0;
        $sgstAmount = $isIntraState ? round($taxAmount / 2, 2) : 0;
        $igstAmount = $isIntraState ? 0 : round($taxAmount, 2);

        $hospitalSnapshot = null;
        if ($isHospitalBusiness) {
            $hospitalSnapshot = [
                'patient_uhid' => $data['patient_uhid'] ?? null,
                'patient_age' => $data['patient_age'] ?? null,
                'patient_gender' => $data['patient_gender'] ?? null,
                'blood_group' => $data['blood_group'] ?? null,
                'guardian_name' => $data['guardian_name'] ?? null,
                'visit_type' => $data['visit_type'],
                'visit_number' => $data['visit_number'] ?? null,
                'visit_at' => $data['visit_at'],
                'doctor_id' => $data['doctor_id'] ?? null,
                'department_id' => $data['department_id'] ?? null,
                'referred_by' => $data['referred_by'] ?? null,
                'billing_category' => $data['billing_category'],
                'ward_id' => $data['ward_id'] ?? null,
                'room_id' => $data['room_id'] ?? null,
                'bed_id' => $data['bed_id'] ?? null,
                'admitted_at' => $data['admitted_at'] ?? null,
                'discharged_at' => $data['discharged_at'] ?? null,
                'insurance_provider' => $data['insurance_provider'] ?? null,
                'insurance_policy_number' => $data['insurance_policy_number'] ?? null,
                'chief_complaint' => $data['chief_complaint'] ?? null,
                'diagnosis' => $data['diagnosis'] ?? null,
                'notes' => $data['hospital_notes'] ?? $data['notes'] ?? null,
            ];
        }

        try {
            DB::transaction(function () use (
                $request, $user, $invoice, $business, $client, $bid, $docType,
                $isHospitalBusiness, $data, $invoiceDate, $prefix, $invoiceNumber,
                $subtotal, $discountTotal, $chargeTotal, $lessAmount,
                $taxAmount, $cgstPercent, $cgstAmount, $sgstPercent, $sgstAmount,
                $igstPercent, $igstAmount, $tcsPercent, $tcsAmount, $roundOff,
                $grandTotal, $receivedTotal, $balance, $cleanRows,
                $cash, $online, $card, $cheque, $credit, $advance, $pay,
                $additionalCharges, $hospitalSnapshot
            ) {
                $patientVisit = null;

                if ($isHospitalBusiness) {
                    $patientVisit = !empty($invoice->patient_visit_id)
                        ? PatientVisit::withoutGlobalScopes()
                            ->where('business_id', $bid)
                            ->whereKey($invoice->patient_visit_id)
                            ->first()
                        : null;

                    $oldBedId = $patientVisit?->bed_id;
                    $visitNumber = trim((string) ($data['visit_number'] ?? $patientVisit?->visit_number ?? ''));

                    if ($visitNumber === '') {
                        $visitPrefix = match ($data['visit_type']) {
                            'ipd' => 'IPD',
                            'emergency' => 'EMR',
                            'day_care' => 'DAY',
                            'diagnostic' => 'DIA',
                            'pharmacy' => 'PHA',
                            default => 'OPD',
                        };

                        $nextVisitSequence = PatientVisit::withoutGlobalScopes()
                            ->where('business_id', $bid)
                            ->lockForUpdate()
                            ->count() + 1;

                        $visitNumber = sprintf('%s-%s-%05d',
                            $visitPrefix,
                            Carbon::parse($data['visit_at'])->format('Y'),
                            $nextVisitSequence
                        );
                    }

                    $visitStatus = match ($data['visit_type']) {
                        'ipd' => 'admitted',
                        'emergency' => !empty($data['admitted_at']) ? 'admitted' : 'registered',
                        default => 'registered',
                    };
                    if (!empty($data['discharged_at'])) $visitStatus = 'discharged';

                    $visitPayload = [
                        'business_id' => $bid,
                        'client_id' => $client->id,
                        'doctor_id' => $data['doctor_id'] ?? null,
                        'department_id' => $data['department_id'] ?? null,
                        'visit_number' => $visitNumber,
                        'visit_type' => $data['visit_type'],
                        'visit_at' => Carbon::parse($data['visit_at']),
                        'chief_complaint' => $data['chief_complaint'] ?? null,
                        'diagnosis' => $data['diagnosis'] ?? null,
                        'remarks' => $data['hospital_notes'] ?? $data['notes'] ?? null,
                        'ward_id' => $data['ward_id'] ?? null,
                        'room_id' => $data['room_id'] ?? null,
                        'bed_id' => $data['bed_id'] ?? null,
                        'admitted_at' => !empty($data['admitted_at']) ? Carbon::parse($data['admitted_at']) : null,
                        'discharged_at' => !empty($data['discharged_at']) ? Carbon::parse($data['discharged_at']) : null,
                        'status' => $visitStatus,
                    ];

                    if ($patientVisit) {
                        $patientVisit->update($visitPayload);
                    } else {
                        $patientVisit = PatientVisit::withoutGlobalScopes()->create($visitPayload);
                    }

                    if (Schema::hasColumn('hospital_beds', 'status')) {
                        $newBedId = $data['bed_id'] ?? null;

                        if ($oldBedId && (int) $oldBedId !== (int) $newBedId) {
                            HospitalBed::withoutGlobalScopes()
                                ->where('business_id', $bid)
                                ->whereKey($oldBedId)
                                ->update(['status' => 'available']);
                        }

                        if ($newBedId) {
                            HospitalBed::withoutGlobalScopes()
                                ->where('business_id', $bid)
                                ->whereKey($newBedId)
                                ->update([
                                    'status' => !empty($data['discharged_at']) ? 'available' : 'occupied',
                                ]);
                        }
                    }
                }

                $payload = [
                    'client_id' => $client->id,
                    'invoice_date' => $invoiceDate,
                    'invoice_prefix' => $prefix,
                    'invoice_number' => $invoiceNumber,
                    'subtotal' => $subtotal,
                    'discount_total' => $discountTotal,
                    'charge_total' => $chargeTotal,
                    'less_amount' => $lessAmount,
                    'tax_amount' => $taxAmount,
                    'cgst_percent' => $cgstPercent,
                    'cgst_amount' => $cgstAmount,
                    'sgst_percent' => $sgstPercent,
                    'sgst_amount' => $sgstAmount,
                    'igst_percent' => $igstPercent,
                    'igst_amount' => $igstAmount,
                    'tcs_percent' => $tcsPercent,
                    'tcs_amount' => $tcsAmount,
                    'round_off' => $roundOff,
                    'total' => $grandTotal,
                    'received_amount' => $docType === 'tax' ? $receivedTotal : 0,
                    'balance' => $docType === 'tax' ? $balance : $grandTotal,
                    'payment_method' => $data['payment_method'] ?? null,
                    'gst_no' => $data['gst_no'] ?? null,
                    'transport_mode' => $data['transport_mode'] ?? null,
                    'reverse_charge' => !empty($data['reverse_charge']) ? 1 : 0,
                    'place_of_supply_state' => $client->state ?? null,
                    'place_of_supply_code' => $client->state_code ?? null,
                    'notes' => $data['notes'] ?? null,
                    'terms' => $data['terms'] ?? null,
                    'charges_json' => json_encode($additionalCharges, JSON_UNESCAPED_UNICODE),
                    'items_json' => json_encode($cleanRows, JSON_UNESCAPED_UNICODE),
                    'updated_by' => $user->id,
                ];

                if ($isHospitalBusiness) {
                    $payload['patient_visit_id'] = $patientVisit?->id;
                    $payload['doctor_id'] = $data['doctor_id'] ?? null;
                    $payload['billing_category'] = $data['billing_category'];
                    $payload['hospital_bill_type'] = $data['visit_type'];
                    $payload['hospital_details_json'] = json_encode(array_merge(
                        $hospitalSnapshot ?? [],
                        ['visit_number' => $patientVisit?->visit_number]
                    ), JSON_UNESCAPED_UNICODE);
                }

                $invoice->update($payload);

                InvoiceAdditionalCharge::withoutGlobalScopes()
                    ->where('invoice_id', $invoice->id)
                    ->delete();

                foreach ($additionalCharges as $charge) {
                    InvoiceAdditionalCharge::withoutGlobalScopes()->create([
                        'invoice_id' => $invoice->id,
                        'additional_charge_id' => null,
                        'name' => $charge['name'],
                        'amount' => $charge['amount'],
                    ]);
                }

                InvoiceItem::withoutGlobalScopes()
                    ->where('invoice_id', $invoice->id)
                    ->delete();

                foreach ($cleanRows as $row) {
                    InvoiceItem::withoutGlobalScopes()->create([
                        'invoice_id' => $invoice->id,
                        'item_id' => $row['item_id'],
                        'description' => $row['description'],
                        'sac_code' => $row['hsn'] ?: null,
                        'hsn_code' => $row['hsn'] ?: null,
                        'quantity' => $row['qty'],
                        'gold_wt' => (float) ($row['gold_wt'] ?? 0),
                        'silver_wt' => (float) ($row['silver_wt'] ?? 0),
                        'gold_rate' => (float) ($row['gold_rate'] ?? 0),
                        'silver_rate' => (float) ($row['silver_rate'] ?? 0),
                        'gemstone_wt_ct' => (float) ($row['gemstone_wt'] ?? 0),
                        'diamond_wt_ct' => (float) ($row['diamond_wt'] ?? 0),
                        'stone_charges' => (float) ($row['stone_charges'] ?? 0),
                        'diamond_charges' => (float) ($row['diamond_charges'] ?? 0),
                        'making_charge' => (float) ($row['making_charge'] ?? 0),
                        'making_rate' => (float) ($row['making_rate'] ?? 0),
                        'making_charge_type' => $row['making_charge_type'] ?? 'percentage',
                        'discount' => 0,
                        'tax_percent' => (float) ($row['tax_percent'] ?? 0),
                        'rate' => round((float) ($row['rate'] ?? 0), 2),
                        'amount' => round((float) ($row['amount'] ?? 0), 2),
                    ]);
                }

                if ($docType === 'tax') {
                    $payment = InvoicePayment::withoutGlobalScopes()
                        ->where('invoice_id', $invoice->id)
                        ->latest('id')
                        ->first() ?: new InvoicePayment();

                    $payment->fill([
                        'business_id' => $bid,
                        'invoice_id' => $invoice->id,
                        'client_id' => $client->id,
                        'total_value' => $grandTotal,
                        'cash_amount' => $cash,
                        'online_amount' => $online,
                        'card_amount' => $card,
                        'cheque_amount' => $cheque,
                        'online_mode' => $pay['online_mode'] ?? null,
                        'online_ref' => $pay['online_ref'] ?? null,
                        'upi_id' => $pay['upi_id'] ?? null,
                        'card_last4' => $pay['card_last4'] ?? null,
                        'card_ref' => $pay['card_ref'] ?? null,
                        'cheque_no' => $pay['cheque_no'] ?? null,
                        'bank_name' => $pay['bank_name'] ?? null,
                        'credit_sales_excess_amount' => $credit,
                        'advance_amount' => $advance,
                        'received_total' => $receivedTotal,
                        'notes' => $pay['payment_notes'] ?? $pay['pay_notes'] ?? null,
                        'meta' => $isHospitalBusiness ? json_encode([
                            'patient_visit_id' => $patientVisit?->id,
                            'visit_type' => $data['visit_type'] ?? null,
                        ]) : null,
                        'paid_at' => $receivedTotal > 0 ? now() : null,
                    ]);
                    $payment->save();

                    $invoice->load('items');
                    $this->stock->recordSale($invoice);
                }
            });

            return response()->json([
                'ok' => true,
                'message' => $isHospitalBusiness
                    ? 'Hospital bill updated successfully.'
                    : ucfirst($docType) . ' updated successfully.',
                'invoice' => Invoice::withoutGlobalScopes()
                    ->with(['client', 'items', 'business'])
                    ->whereKey($invoice->id)
                    ->first(),
            ]);
        } catch (\Throwable $e) {
            Log::error('API Invoice update failed', [
                'invoice_id' => $invoice->id,
                'business_id' => $bid,
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
            ]);

            return response()->json([
                'ok' => false,
                'message' => 'Invoice update failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // ------------------------------------------------------------
    // DELETE /api/invoices/{invoice}
    // ------------------------------------------------------------
    public function destroy(Request $request, $invoice)
    {
        $invoice = $this->findInvoiceForUser($request, $invoice);

        try {
            DB::transaction(function () use ($invoice) {
                // ✅ tax invoice hai to stock rollback pehle
                if (($invoice->invoice_type ?? 'tax') === 'tax') {
                    if (method_exists($this->stock, 'rollbackSale')) {
                        $invoiceForStock = Invoice::withoutGlobalScopes()
                            ->with(['items'])
                            ->where('id', $invoice->id)
                            ->first();

                        if ($invoiceForStock) {
                            $this->stock->rollbackSale($invoiceForStock);
                        }
                    }
                }

                // ✅ invoice payments delete
                InvoicePayment::withoutGlobalScopes()
                    ->where('invoice_id', $invoice->id)
                    ->delete();

                // ✅ invoice items delete
                InvoiceItem::withoutGlobalScopes()
                    ->where('invoice_id', $invoice->id)
                    ->delete();

                // ✅ pdf file delete if exists
                if (!empty($invoice->pdf_url)) {
                    $path = $this->normalizePdfPath($invoice->pdf_url);

                    if ($path && Storage::disk('public')->exists($path)) {
                        Storage::disk('public')->delete($path);
                    }
                }

                // ✅ invoice delete without global scopes
                Invoice::withoutGlobalScopes()
                    ->where('id', $invoice->id)
                    ->delete();
            });

            return response()->json([
                'ok' => true,
                'message' => 'Invoice deleted successfully',
            ]);

        } catch (\Throwable $e) {
            Log::error('API Invoice delete failed', [
                'invoice_id' => $invoice->id ?? null,
                'error'      => $e->getMessage(),
                'line'       => $e->getLine(),
                'file'       => $e->getFile(),
            ]);

            return response()->json([
                'ok'      => false,
                'message' => 'Invoice delete failed',
                'error'   => $e->getMessage(),
            ], 500);
        }
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


    public function pdf(Request $request, $invoice)
    {
        $invoice = $this->findInvoiceForUser($request, $invoice);

        $invoice = Invoice::withoutGlobalScopes()
            ->with([
                'client',
                'items.item',
                'business',
                'payments',
            ])
            ->where('id', $invoice->id)
            ->firstOrFail();

        $safeNumber = str_replace(
            ['/', '\\'],
            '-',
            (string) ($invoice->invoice_number ?? 'INV')
        );

        try {
            $output = $this->simplePdfBuild($invoice);

            return response($output, 200, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'inline; filename="Invoice-' . $safeNumber . '.pdf"',
                'Cache-Control'       => 'no-store, no-cache, must-revalidate, max-age=0',
                'Pragma'              => 'no-cache',
                'Expires'             => '0',
            ]);

        } catch (\Throwable $e) {
            Log::error('API Invoice PDF failed', [
                'invoice_id'  => $invoice->id ?? null,
                'business_id' => $invoice->business_id ?? null,
                'error'       => $e->getMessage(),
                'line'        => $e->getLine(),
                'file'        => $e->getFile(),
            ]);

            return response()->json([
                'ok'      => false,
                'message' => 'PDF generate failed',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function pdfUrl(Request $request, $invoice)
    {
        $invoice = $this->findInvoiceForUser($request, $invoice);

        if (!empty($invoice->pdf_url)) {
            $path = $this->normalizePdfPath($invoice->pdf_url);

            if ($path && Storage::disk('public')->exists($path)) {
                return response()->json([
                    'ok'  => true,
                    'url' => Storage::disk('public')->url($path),
                ]);
            }
        }

        return response()->json([
            'ok' => false,
            'message' => 'PDF not found',
        ], 404);
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


    public function show(Request $request, $invoice)
    {
        $invoice = $this->findInvoiceForUser($request, $invoice);

        // हर request पर fresh invoice data और relations load होंगी
        $invoice = Invoice::withoutGlobalScopes()
            ->with([
                'client',
                'items.item',
                'business',
                'payments',
            ])
            ->where('id', $invoice->id)
            ->firstOrFail();

        $safeNumber = str_replace(
            ['/', '\\'],
            '-',
            (string) ($invoice->invoice_number ?? 'INV')
        );

        try {
            /*
            |--------------------------------------------------------------------------
            | हमेशा fresh PDF generate करें
            |--------------------------------------------------------------------------
            | pdf_url check नहीं होगा
            | पुरानी PDF storage से return नहीं होगी
            | selected business template से नई PDF बनेगी
            */
            $output = $this->simplePdfBuild($invoice);

            return response($output, 200, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'inline; filename="Invoice-' . $safeNumber . '.pdf"',
                'Cache-Control'       => 'no-store, no-cache, must-revalidate, max-age=0',
                'Pragma'              => 'no-cache',
                'Expires'             => '0',
            ]);

        } catch (\Throwable $e) {
            Log::error('API Invoice PDF generation failed', [
                'invoice_id'  => $invoice->id ?? null,
                'business_id' => $invoice->business_id ?? null,
                'error'       => $e->getMessage(),
                'line'        => $e->getLine(),
                'file'        => $e->getFile(),
            ]);

            return response()->json([
                'ok'      => false,
                'message' => 'PDF generate failed',
                'error'   => $e->getMessage(),
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

    protected function simplePdfBuild(Invoice $invoice): string
    {
        /*
        |--------------------------------------------------------------------------
        | Safe relations loading without global scope issue
        |--------------------------------------------------------------------------
        | client, items, business teeno PDF ke liye required hain.
        | Isliye yaha normal $invoice->load(['client','items','business'])
        | ki jagah manual safe loading use kar rahe hain.
        */

        $inv = $invoice;

        // ✅ Business required - without global scopes
        $biz = Business::withoutGlobalScopes()
            ->where('id', $invoice->business_id)
            ->first();

        // ✅ Client required - without global scopes if Client model has scope
        $client = null;

        if (!empty($invoice->client_id)) {
            $client = Client::withoutGlobalScopes()
                ->where('id', $invoice->client_id)
                ->first();
        }

        // ✅ Items required - without global scopes if InvoiceItem model has scope
        // $items = InvoiceItem::withoutGlobalScopes()
        //     ->where('invoice_id', $invoice->id)
        //     ->orderBy('id')
        //     ->get();

        $items = InvoiceItem::withoutGlobalScopes()
            ->with('item')
            ->where('invoice_id', $invoice->id)
            ->orderBy('id')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Payment row
        |--------------------------------------------------------------------------
        */
        $payRow = InvoicePayment::withoutGlobalScopes()
            ->where('invoice_id', $inv->id)
            ->latest('id')
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Additional charges
        |--------------------------------------------------------------------------
        */
        if (method_exists($invoice, 'additionalCharges')) {
            try {
                $charges = $invoice->additionalCharges()
                    ->withoutGlobalScopes()
                    ->get(['name', 'amount']);
            } catch (\Throwable $e) {
                $charges = collect();
            }
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

        /*
        |--------------------------------------------------------------------------
        | Totals
        |--------------------------------------------------------------------------
        */
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

        // ✅ Correct total tax amount
        $taxAmount = (float) ($cgst_amount + $sgst_amount + $igst_amount);

        /*
        |--------------------------------------------------------------------------
        | Logo and signature
        |--------------------------------------------------------------------------
        */
        $logoDataUri = $this->imageDataUri($biz?->logo);
        $signDataUri = $this->imageDataUri($biz?->signature);

        /*
        |--------------------------------------------------------------------------
        | Invoice type
        |--------------------------------------------------------------------------
        */
        $type = strtolower((string) ($invoice->invoice_type ?? 'tax'));

        /*
        |--------------------------------------------------------------------------
        | View data
        |--------------------------------------------------------------------------
        */
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
            'payRow'
        );

        // ✅ aliases for old blade templates
        $vm['logo'] = $logoDataUri;
        $vm['sign'] = $signDataUri;

        /*
        |--------------------------------------------------------------------------
        | Template resolve
        |--------------------------------------------------------------------------
        | billTemplate relation direct use nahi karenge kyuki usme bhi global scope
        | issue aa sakta hai. Isliye business ko manually load kiya hai.
        */

        $templatePage = 'pdf_simple';

        try {
            if ($biz && method_exists($biz, 'billTemplate')) {
                $billTemplate = $biz->billTemplate()->first();

                if ($billTemplate && !empty($billTemplate->page_name)) {
                    $templatePage = $billTemplate->page_name;
                }
            }
        } catch (\Throwable $e) {
            $templatePage = 'pdf_simple';
        }

        $view = 'invoices.' . $templatePage;

        if (!view()->exists($view)) {
            $view = 'invoices.pdf_simple';
        }

        /*
        |--------------------------------------------------------------------------
        | Generate PDF
        |--------------------------------------------------------------------------
        */
        // return Pdf::loadView($view, $vm)
        //     ->setPaper('a4');

        return $this->renderMpdfOutput(
            $view,
            $vm
        );
    }


    protected function ensureInvoiceAccess(Request $request, Invoice $invoice): int
    {
        $user = $request->user();

        $invoiceBusinessId = (int) $invoice->business_id;

        abort_unless($invoiceBusinessId > 0, 422, 'Invoice business not found.');

        $hasAccess = $user->businesses()
            ->where('businesses.id', $invoiceBusinessId)
            ->exists();

        abort_unless($hasAccess, 403, 'You do not have access to this invoice business.');

        return $invoiceBusinessId;
    }



    protected function selectedBusinessId(Request $request): int
    {
        $user = $request->user();

        $bid = (int) (
            $request->input('business_id')
            ?? $request->query('business_id')
            ?? $request->header('X-Business-Id')
            ?? 0
        );

        abort_unless($bid > 0, 422, 'business_id is required.');

        $hasAccess = $user->businesses()
            ->where('businesses.id', $bid)
            ->exists();

        abort_unless($hasAccess, 403, 'You do not have access to this business.');

        return $bid;
    }

    protected function findInvoiceForUser(Request $request, $invoiceId): Invoice
    {
        $user = $request->user();

        $invoice = Invoice::withoutGlobalScopes()
            ->with([
                'client',
                'items',
                'business',
            ])
            ->where('id', $invoiceId)
            ->firstOrFail();

        $invoiceBusinessId = (int) $invoice->business_id;

        abort_unless($invoiceBusinessId > 0, 422, 'Invoice business not found.');

        $hasAccess = $user->businesses()
            ->where('businesses.id', $invoiceBusinessId)
            ->exists();

        abort_unless($hasAccess, 403, 'You do not have access to this invoice business.');

        return $invoice;
    }

    // protected function renderMpdfOutput(string $view, array $vm): string
    // {
    //     $tempDir = storage_path('app/mpdf-temp');

    //     if (!is_dir($tempDir)) {
    //         mkdir($tempDir, 0775, true);
    //     }

    //     if (!is_writable($tempDir)) {
    //         @chmod($tempDir, 0775);
    //     }

    //     $mpdf = new Mpdf([
    //         'mode'   => 'utf-8',
    //         'format' => 'A4',

    //         'margin_left'   => 8,
    //         'margin_right'  => 8,
    //         'margin_top'    => 8,
    //         'margin_bottom' => 8,

    //         'tempDir' => $tempDir,

    //         // Custom NotoSansDevanagari font remove किया गया
    //         'default_font' => 'freeserif',

    //         'autoScriptToLang' => true,
    //         'autoLangToFont'   => true,
    //     ]);

    //     $html = view($view, $vm)->render();

    //     $mpdf->WriteHTML($html);

    //     return $mpdf->Output('', 'S');
    // }

    protected function renderMpdfOutput(string $view, array $vm): string
{
    /*
    |--------------------------------------------------------------------------
    | Temp directory
    |--------------------------------------------------------------------------
    */
    $tempDir = storage_path('app/mpdf-temp');

    if (!is_dir($tempDir)) {
        mkdir($tempDir, 0775, true);
    }

    if (!is_writable($tempDir)) {
        @chmod($tempDir, 0775);
    }

    /*
    |--------------------------------------------------------------------------
    | mPDF
    |--------------------------------------------------------------------------
    */
    $mpdf = new \Mpdf\Mpdf([
        'mode'   => 'utf-8',
        'format' => 'A4',

        'margin_left'   => 8,
        'margin_right'  => 8,
        'margin_top'    => 8,
        'margin_bottom' => 8,

        'tempDir' => $tempDir,

        'default_font' => 'freeserif',

        'autoScriptToLang' => true,
        'autoLangToFont'   => true,
    ]);

    /*
    |--------------------------------------------------------------------------
    | Render Blade HTML
    |--------------------------------------------------------------------------
    */
    $html = view($view, $vm)->render();

    /*
    |--------------------------------------------------------------------------
    | Important:
    | mPDF poora huge HTML ek WriteHTML() me lene par
    | pcre.backtrack_limit error de raha tha.
    |--------------------------------------------------------------------------
    */

    // <style>...</style> ko alag extract karo.
    $css = '';

    if (
        preg_match(
            '/<style\b[^>]*>(.*?)<\/style>/is',
            $html,
            $matches
        )
    ) {
        $css = $matches[1] ?? '';

        // Original HTML se style tag remove
        $html = preg_replace(
            '/<style\b[^>]*>.*?<\/style>/is',
            '',
            $html
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CSS first
    |--------------------------------------------------------------------------
    */
    if (trim($css) !== '') {
        $mpdf->WriteHTML(
            $css,
            \Mpdf\HTMLParserMode::HEADER_CSS
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Body HTML in smaller chunks
    |--------------------------------------------------------------------------
    |
    | 50 KB chunks generally safe hain.
    | Direct str_split HTML tag ke beech cut kar sakta hai,
    | isliye pehle logical tags par split karenge.
    |--------------------------------------------------------------------------
    */

    $parts = preg_split(
        '/(?=<\/?(?:div|table|thead|tbody|tfoot|tr|section|header|footer)\b)/i',
        $html,
        -1,
        PREG_SPLIT_NO_EMPTY
    );

    $buffer = '';
    $maxChunkSize = 50000; // 50 KB

    foreach ($parts as $part) {

        /*
        |--------------------------------------------------------------------------
        | Agar current buffer limit cross karne wala hai
        |--------------------------------------------------------------------------
        */
        if (
            $buffer !== ''
            && strlen($buffer) + strlen($part) > $maxChunkSize
        ) {
            $mpdf->WriteHTML(
                $buffer,
                \Mpdf\HTMLParserMode::HTML_BODY
            );

            $buffer = '';
        }

        /*
        |--------------------------------------------------------------------------
        | Agar ek individual part hi bahut bada hai
        |--------------------------------------------------------------------------
        */
        if (strlen($part) > $maxChunkSize) {

            if ($buffer !== '') {
                $mpdf->WriteHTML(
                    $buffer,
                    \Mpdf\HTMLParserMode::HTML_BODY
                );

                $buffer = '';
            }

            /*
            | Large part ko paragraphs / lines ke around split karo.
            */
            $subParts = preg_split(
                '/(?=<\/?(?:p|span|br|td|th|li)\b)/i',
                $part,
                -1,
                PREG_SPLIT_NO_EMPTY
            );

            $subBuffer = '';

            foreach ($subParts as $subPart) {

                if (
                    $subBuffer !== ''
                    && strlen($subBuffer) + strlen($subPart) > $maxChunkSize
                ) {
                    $mpdf->WriteHTML(
                        $subBuffer,
                        \Mpdf\HTMLParserMode::HTML_BODY
                    );

                    $subBuffer = '';
                }

                $subBuffer .= $subPart;
            }

            if ($subBuffer !== '') {
                $mpdf->WriteHTML(
                    $subBuffer,
                    \Mpdf\HTMLParserMode::HTML_BODY
                );
            }

            continue;
        }

        $buffer .= $part;
    }

    /*
    |--------------------------------------------------------------------------
    | Remaining HTML
    |--------------------------------------------------------------------------
    */
    if ($buffer !== '') {
        $mpdf->WriteHTML(
            $buffer,
            \Mpdf\HTMLParserMode::HTML_BODY
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Return PDF binary
    |--------------------------------------------------------------------------
    */
    return $mpdf->Output('', 'S');
}

}
