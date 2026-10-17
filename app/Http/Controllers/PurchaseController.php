<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Item;
use App\Models\Purchase;
use App\Models\Unit;
use App\Services\StockService;
use App\Services\PurchaseBillAiService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class PurchaseController extends Controller
{
    protected StockService $stock;
    protected PurchaseBillAiService $billAi;

    public function __construct(
        StockService $stock,
        PurchaseBillAiService $billAi
    ) {
        $this->stock = $stock;
        $this->billAi = $billAi;
    }

public function index()
{
    $businessId = auth()->user()->business_id ?? null;

    $baseQuery = Purchase::query()
        ->when(
            $businessId,
            fn ($q) => $q->where('business_id', $businessId)
        );

    /*
    |--------------------------------------------------------------------------
    | Summary
    |--------------------------------------------------------------------------
    */
    $summary = [
        'total_purchases' => (clone $baseQuery)->count(),
        'total_amount'    => (clone $baseQuery)->sum('total_amount'),
        'paid_amount'     => (clone $baseQuery)->sum('paid_amount'),
        'due_amount'      => (clone $baseQuery)->sum('due_amount'),
    ];

    /*
    |--------------------------------------------------------------------------
    | Purchases
    |--------------------------------------------------------------------------
    */
    $purchases = $baseQuery
        ->with('supplier')
        ->withCount('items')
        ->latest('invoice_date')
        ->latest('id')
        ->paginate(20);

    return view('purchases.index', compact(
        'purchases',
        'summary'
    ));
}

    // public function create()
    // {
    //     $businessId = auth()->user()->business_id ?? null;

    //     $suppliers = Client::query()
    //         ->when(
    //             $businessId,
    //             fn ($q) => $q->where('business_id', $businessId)
    //         )
    //         ->whereIn('party_type', ['supplier', 'both'])
    //         ->orderBy('name')
    //         ->get();

    //     $items = Item::query()
    //         ->when(
    //             $businessId,
    //             fn ($q) => $q->where('business_id', $businessId)
    //         )
    //         ->where('is_active', true)
    //         ->orderBy('name')
    //         ->get();

    //     $purchase = new Purchase();

    //     return view(
    //         'purchases.create',
    //         compact('purchase', 'suppliers', 'items')
    //     );
    // }


    public function create()
{
    $businessId = auth()->user()->current_business_id
        ?? session('active_business_id')
        ?? auth()->user()->business_id
        ?? null;

    $suppliers = Client::query()
        ->when(
            $businessId,
            fn ($q) => $q->where('business_id', $businessId)
        )
        ->whereIn('party_type', ['supplier', 'both'])
        ->orderBy('name')
        ->get();

    $items = Item::query()
        ->when(
            $businessId,
            fn ($q) => $q->where('business_id', $businessId)
        )
        ->where('is_active', true)
        ->orderBy('name')
        ->get();

    /*
    |--------------------------------------------------------------------------
    | Dynamic Units
    |--------------------------------------------------------------------------
    | business_id = null      => global unit
    | business_id = current   => current business unit
    */
    $units = Unit::query()
        ->withoutGlobalScope('business')
        ->where(function ($query) use ($businessId) {

            $query->whereNull('business_id');

            if ($businessId) {
                $query->orWhere(
                    'business_id',
                    (int) $businessId
                );
            }
        })
        ->orderBy('name')
        ->get([
            'id',
            'business_id',
            'name',
            'description',
        ]);

    $purchase = new Purchase();

    return view(
        'purchases.create',
        compact(
            'purchase',
            'suppliers',
            'items',
            'units'
        )
    );
}

    public function store(Request $request)
    {
        $businessId = auth()->user()->business_id ?? null;

        $data = $this->validatePurchase($request);

        $billFilePath = null;

        if ($request->hasFile('bill_file')) {
            $billFilePath = $request->file('bill_file')->store('purchase-bills', 'public');
        }

        try {
            return DB::transaction(function () use ($data, $businessId, $billFilePath) {
                $calculated = $this->calculatePurchase($data);

                $purchase = Purchase::create([
                    'business_id'       => $businessId,
                    'supplier_id'       => $data['supplier_id'] ?? null,
                    'invoice_no'        => $data['invoice_no'] ?? null,
                    'invoice_date'      => $data['invoice_date'],
                    'tax_type'          => $data['tax_type'] ?? 'intra_state',
                    'bill_file'         => $billFilePath,

                    'subtotal'          => $calculated['summary']['subtotal'],
                    'discount_amount'   => $calculated['summary']['discount_amount'],
                    'cgst_amount'       => $calculated['summary']['cgst_amount'],
                    'sgst_amount'       => $calculated['summary']['sgst_amount'],
                    'igst_amount'       => $calculated['summary']['igst_amount'],
                    'round_off'         => $calculated['summary']['round_off'],
                    'total_amount'      => $calculated['summary']['total_amount'],
                    'paid_amount'       => $calculated['summary']['paid_amount'],
                    'due_amount'        => $calculated['summary']['due_amount'],
                ]);

                foreach ($calculated['items'] as $row) {
                    $purchase->items()->create($row);
                }

                $purchase->load('items.item');
                $this->stock->recordPurchase($purchase);

                return redirect()
                    ->route('purchases.index')
                    ->with('success', 'Purchase saved with GST & stock increased.');
            });
        } catch (\Throwable $e) {
            if ($billFilePath) {
                Storage::disk('public')->delete($billFilePath);
            }

            throw $e;
        }
    }


    // public function edit(Purchase $purchase)
    // {
    //     $this->authorizeBusiness($purchase);

    //     $businessId = $purchase->business_id;

    //     $suppliers = Client::query()
    //         ->when(
    //             $businessId,
    //             fn ($q) => $q->where('business_id', $businessId)
    //         )
    //         ->whereIn('party_type', ['supplier', 'both'])
    //         ->orderBy('name')
    //         ->get();

    //     $items = Item::query()
    //         ->when(
    //             $businessId,
    //             fn ($q) => $q->where('business_id', $businessId)
    //         )
    //         ->where('is_active', true)
    //         ->orderBy('name')
    //         ->get();

    //     $purchase->load('items.item');

    //     return view(
    //         'purchases.edit',
    //         compact('purchase', 'suppliers', 'items')
    //     );
    // }


    public function edit(Purchase $purchase)
{
    $this->authorizeBusiness($purchase);

    $businessId = $purchase->business_id;

    $suppliers = Client::query()
        ->when(
            $businessId,
            fn ($q) => $q->where('business_id', $businessId)
        )
        ->whereIn('party_type', ['supplier', 'both'])
        ->orderBy('name')
        ->get();

    $items = Item::query()
        ->when(
            $businessId,
            fn ($q) => $q->where('business_id', $businessId)
        )
        ->where('is_active', true)
        ->orderBy('name')
        ->get();

    /*
    |--------------------------------------------------------------------------
    | Dynamic Units
    |--------------------------------------------------------------------------
    */
    $units = Unit::query()
        ->withoutGlobalScope('business')
        ->where(function ($query) use ($businessId) {

            $query->whereNull('business_id');

            if ($businessId) {
                $query->orWhere(
                    'business_id',
                    (int) $businessId
                );
            }
        })
        ->orderBy('name')
        ->get([
            'id',
            'business_id',
            'name',
            'description',
        ]);

    $purchase->load('items.item');

    return view(
        'purchases.edit',
        compact(
            'purchase',
            'suppliers',
            'items',
            'units'
        )
    );
}

    public function update(Request $request, Purchase $purchase)
    {
        $this->authorizeBusiness($purchase);

        $data = $this->validatePurchase($request);

        $oldBillPath = $purchase->bill_file;
        $newBillPath = null;
        $billFilePath = $oldBillPath;

        if ($request->hasFile('bill_file')) {
            $newBillPath = $request->file('bill_file')->store('purchase-bills', 'public');
            $billFilePath = $newBillPath;
        }

        try {
            $response = DB::transaction(function () use ($purchase, $data, $billFilePath) {
                $purchase->load('items.item');

                $this->stock->rollbackReference($purchase);

                $purchase->items()->delete();

                $calculated = $this->calculatePurchase($data);

                $purchase->update([
                    'supplier_id'       => $data['supplier_id'] ?? null,
                    'invoice_no'        => $data['invoice_no'] ?? null,
                    'invoice_date'      => $data['invoice_date'],
                    'tax_type'          => $data['tax_type'] ?? 'intra_state',
                    'bill_file'         => $billFilePath,

                    'subtotal'          => $calculated['summary']['subtotal'],
                    'discount_amount'   => $calculated['summary']['discount_amount'],
                    'cgst_amount'       => $calculated['summary']['cgst_amount'],
                    'sgst_amount'       => $calculated['summary']['sgst_amount'],
                    'igst_amount'       => $calculated['summary']['igst_amount'],
                    'round_off'         => $calculated['summary']['round_off'],
                    'total_amount'      => $calculated['summary']['total_amount'],
                    'paid_amount'       => $calculated['summary']['paid_amount'],
                    'due_amount'        => $calculated['summary']['due_amount'],
                ]);

                foreach ($calculated['items'] as $row) {
                    $purchase->items()->create($row);
                }

                $purchase->load('items.item');
                $this->stock->recordPurchase($purchase);

                return redirect()
                    ->route('purchases.index')
                    ->with('success', 'Purchase updated with GST & stock recalculated.');
            });

            if ($newBillPath && $oldBillPath) {
                Storage::disk('public')->delete($oldBillPath);
            }

            return $response;
        } catch (\Throwable $e) {
            if ($newBillPath) {
                Storage::disk('public')->delete($newBillPath);
            }

            throw $e;
        }
    }

    public function destroy(Purchase $purchase)
    {
        $this->authorizeBusiness($purchase);

        return DB::transaction(function () use ($purchase) {
            $purchase->load('items.item');

            $this->stock->rollbackReference($purchase);

            $billFile = $purchase->bill_file;

            $purchase->items()->delete();
            $purchase->delete();

            if ($billFile) {
                Storage::disk('public')->delete($billFile);
            }

            return redirect()
                ->route('purchases.index')
                ->with('success', 'Purchase deleted & stock reverted.');
        });
    }

    private function validatePurchase(Request $request): array
    {
        $businessId = auth()->user()->business_id ?? null;

        return $request->validate([
            'supplier_id' => [
                'nullable',
                Rule::exists('clients', 'id')
                    ->when($businessId, fn ($rule) => $rule->where('business_id', $businessId)),
            ],

            'invoice_no'      => 'nullable|string|max:50',
            'invoice_date'    => 'required|date',

            'tax_type'        => 'nullable|in:intra_state,inter_state',
            'discount_amount' => 'nullable|numeric|min:0',
            'round_off'       => 'nullable|numeric',
            'paid_amount'     => 'nullable|numeric|min:0',

            'items' => 'required|array|min:1',

            'items.*.item_id' => [
                'required',
                Rule::exists('items', 'id')
                    ->when($businessId, fn ($rule) => $rule->where('business_id', $businessId)),
            ],

            'items.*.qty'           => 'required|numeric|min:0.001',
            'items.*.qty_unit' => [
                'required',
                'string',
                'max:50',
            ],
            'items.*.rate'          => 'required|numeric|min:0',
            'items.*.gst_rate'      => 'nullable|numeric|min:0',

            'items.*.gross_weight'  => 'nullable|numeric',
            'items.*.metal_weight'  => 'nullable|numeric',
            'items.*.stone_weight'  => 'nullable|numeric',

            'bill_file'             => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);
    }

    private function calculatePurchase(array $data): array
    {
        $taxType = $data['tax_type'] ?? 'intra_state';

        $subtotal = 0;
        $cgstTotal = 0;
        $sgstTotal = 0;
        $igstTotal = 0;

        $items = [];

        foreach ($data['items'] as $row) {
            $qty = (float) $row['qty'];
            $qtyUnit = $row['qty_unit'] ?? 'pcs';
            $rate = (float) $row['rate'];
            $gstRate = (float) ($row['gst_rate'] ?? 0);

            $taxableAmount = round($qty * $rate, 2);

            $cgstRate = 0;
            $sgstRate = 0;
            $igstRate = 0;

            $cgstAmount = 0;
            $sgstAmount = 0;
            $igstAmount = 0;

            if ($taxType === 'intra_state') {
                $cgstRate = $gstRate / 2;
                $sgstRate = $gstRate / 2;

                $cgstAmount = round(($taxableAmount * $cgstRate) / 100, 2);
                $sgstAmount = round(($taxableAmount * $sgstRate) / 100, 2);
            }

            if ($taxType === 'inter_state') {
                $igstRate = $gstRate;
                $igstAmount = round(($taxableAmount * $igstRate) / 100, 2);
            }

            $lineTotal = round($taxableAmount + $cgstAmount + $sgstAmount + $igstAmount, 2);

            $subtotal += $taxableAmount;
            $cgstTotal += $cgstAmount;
            $sgstTotal += $sgstAmount;
            $igstTotal += $igstAmount;

            $items[] = [
                'item_id'         => $row['item_id'],
                'qty'             => $qty,
                'qty_unit'        => $qtyUnit,

                'gross_weight'    => $row['gross_weight'] ?? null,
                'metal_weight'    => $row['metal_weight'] ?? null,
                'stone_weight'    => $row['stone_weight'] ?? null,

                'rate'            => $rate,
                'amount'          => $taxableAmount,
                'taxable_amount'  => $taxableAmount,

                'gst_rate'        => $gstRate,

                'cgst_rate'       => $cgstRate,
                'sgst_rate'       => $sgstRate,
                'igst_rate'       => $igstRate,

                'cgst_amount'     => $cgstAmount,
                'sgst_amount'     => $sgstAmount,
                'igst_amount'     => $igstAmount,

                'total_amount'    => $lineTotal,
            ];
        }

        $discountAmount = (float) ($data['discount_amount'] ?? 0);
        $roundOff = (float) ($data['round_off'] ?? 0);
        $paidAmount = (float) ($data['paid_amount'] ?? 0);

        $totalAmount = round(
            $subtotal + $cgstTotal + $sgstTotal + $igstTotal - $discountAmount + $roundOff,
            2
        );

        $dueAmount = round($totalAmount - $paidAmount, 2);

        return [
            'items' => $items,
            'summary' => [
                'subtotal'        => round($subtotal, 2),
                'discount_amount' => round($discountAmount, 2),
                'cgst_amount'     => round($cgstTotal, 2),
                'sgst_amount'     => round($sgstTotal, 2),
                'igst_amount'     => round($igstTotal, 2),
                'round_off'       => round($roundOff, 2),
                'total_amount'    => $totalAmount,
                'paid_amount'     => round($paidAmount, 2),
                'due_amount'      => $dueAmount,
            ],
        ];
    }

    protected function authorizeBusiness(Purchase $purchase): void
    {
        $current = auth()->user()->business_id ?? null;

        if ($current && $purchase->business_id !== $current) {
            abort(403);
        }
    }


    public function show(Purchase $purchase)
    {
        $this->authorizeBusiness($purchase);

        $purchase->load([
            'supplier',
            'items.item',
        ]);

        return view('purchases.show', compact('purchase'));
    }

    /**
     * Scan purchase bill and return extracted + matched data.
     * Existing purchase save flow is not changed by this method.
     */
    public function scanBill(Request $request)
    {
        $request->validate([
            'bill_file' => [
                'required',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:5120',
            ],
        ]);

        $businessId = auth()->user()->current_business_id
            ?? session('active_business_id')
            ?? auth()->user()->business_id
            ?? null;

        try {
            $extracted = $this->billAi->extract(
                $request->file('bill_file')
            );

            $items = Item::query()
                ->when(
                    $businessId,
                    fn ($q) => $q->where('business_id', $businessId)
                )
                ->where('is_active', true)
                ->orderBy('name')
                ->get();

            $suppliers = Client::query()
                ->when(
                    $businessId,
                    fn ($q) => $q->where('business_id', $businessId)
                )
                ->whereIn('party_type', ['supplier', 'both'])
                ->orderBy('name')
                ->get();

            $supplierMatch = $this->matchSupplier(
                $extracted,
                $suppliers
            );

            $matchedRows = collect($extracted['items'] ?? [])
                ->map(function (array $row) use ($items) {
                    $match = $this->matchItem($row, $items);

                    return [
                        'bill_name' => $row['name'] ?? '',
                        'bill_sku' => $row['sku'] ?? null,
                        'hsn_sac' => $row['hsn_sac'] ?? null,
                        'qty' => (float) ($row['qty'] ?? 1),
                        'unit' => $row['unit'] ?? null,
                        'rate' => $row['rate'] ?? null,
                        'gst_rate' => $row['gst_rate'] ?? null,
                        'taxable_amount' => $row['taxable_amount'] ?? null,
                        'line_total' => $row['line_total'] ?? null,
                        'matched_item' => $match,
                        'needs_item_creation' => $match === null,
                    ];
                })
                ->values();

            return response()->json([
                'success' => true,
                'message' => 'Bill scanned successfully.',
                'purchase' => [
                    'invoice_no' => $extracted['invoice_no'] ?? null,
                    'invoice_date' => $extracted['invoice_date'] ?? null,
                    'tax_type' => $extracted['tax_type'] ?? 'intra_state',
                    'discount_amount' => $extracted['discount_amount'] ?? 0,
                    'round_off' => $extracted['round_off'] ?? 0,
                    'paid_amount' => $extracted['paid_amount'] ?? 0,
                    'grand_total' => $extracted['grand_total'] ?? null,
                ],
                'supplier' => [
                    'extracted_name' => $extracted['supplier_name'] ?? null,
                    'extracted_gstin' => $extracted['supplier_gstin'] ?? null,
                    'extracted_mobile' => $extracted['supplier_mobile'] ?? null,
                    'match' => $supplierMatch,
                    'needs_creation' => $supplierMatch === null
                        && filled($extracted['supplier_name'] ?? null),
                ],
                'items' => $matchedRows,
                'missing_items_count' => $matchedRows
                    ->where('needs_item_creation', true)
                    ->count(),
            ]);

        } catch (\Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
                    ?: 'Unable to scan the purchase bill.',
            ], 422);
        }
    }

    /**
     * Match bill supplier to existing supplier master.
     * GSTIN exact match gets highest priority, then fuzzy name match.
     */
    private function matchSupplier(array $extracted, $suppliers): ?array
    {
        $gstin = strtoupper(
            preg_replace(
                '/\s+/',
                '',
                (string) ($extracted['supplier_gstin'] ?? '')
            )
        );

        if ($gstin !== '') {
            foreach ($suppliers as $supplier) {
                $existingGstin = strtoupper(
                    preg_replace(
                        '/\s+/',
                        '',
                        (string) ($supplier->gstin ?? '')
                    )
                );

                if ($existingGstin !== '' && $existingGstin === $gstin) {
                    return [
                        'id' => $supplier->id,
                        'name' => $supplier->name,
                        'mobile' => $supplier->mobile,
                        'gstin' => $supplier->gstin,
                        'confidence' => 100,
                        'matched_by' => 'gstin',
                    ];
                }
            }
        }

        $needle = $this->normalizeForMatch(
            $extracted['supplier_name'] ?? null
        );

        if ($needle === '') {
            return null;
        }

        $best = null;
        $bestScore = 0.0;

        foreach ($suppliers as $supplier) {
            $candidate = $this->normalizeForMatch($supplier->name);

            if ($candidate === '') {
                continue;
            }

            if ($candidate === $needle) {
                $score = 100.0;
            } else {
                similar_text($needle, $candidate, $score);
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $supplier;
            }
        }

        if (!$best || $bestScore < 78) {
            return null;
        }

        return [
            'id' => $best->id,
            'name' => $best->name,
            'mobile' => $best->mobile,
            'gstin' => $best->gstin,
            'confidence' => round($bestScore, 1),
            'matched_by' => 'name',
        ];
    }

    /**
     * Match bill line to item master.
     * Priority: SKU exact -> name exact -> fuzzy name.
     */
    private function matchItem(array $row, $items): ?array
    {
        $billSku = $this->normalizeForMatch($row['sku'] ?? null);
        $billName = $this->normalizeForMatch($row['name'] ?? null);

        if ($billSku !== '') {
            foreach ($items as $item) {
                $itemSku = $this->normalizeForMatch($item->sku ?? null);

                if ($itemSku !== '' && $itemSku === $billSku) {
                    return $this->itemMatchPayload(
                        $item,
                        100,
                        'sku'
                    );
                }
            }
        }

        if ($billName === '') {
            return null;
        }

        foreach ($items as $item) {
            if ($this->normalizeForMatch($item->name) === $billName) {
                return $this->itemMatchPayload(
                    $item,
                    100,
                    'name'
                );
            }
        }

        $best = null;
        $bestScore = 0.0;

        foreach ($items as $item) {
            $candidate = $this->normalizeForMatch($item->name);

            if ($candidate === '') {
                continue;
            }

            similar_text($billName, $candidate, $score);

            // Small boost when one normalized name contains the other.
            if (
                str_contains($candidate, $billName)
                || str_contains($billName, $candidate)
            ) {
                $score = min(100, $score + 8);
            }

            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $item;
            }
        }

        if (!$best || $bestScore < 72) {
            return null;
        }

        return $this->itemMatchPayload(
            $best,
            $bestScore,
            'fuzzy_name'
        );
    }

    private function itemMatchPayload(
        Item $item,
        float $confidence,
        string $matchedBy
    ): array {
        return [
            'id' => $item->id,
            'name' => $item->name,
            'sku' => $item->sku,
            'unit' => $item->unit,
            'rate' => $item->cost_price
                ?? $item->price
                ?? 0,
            'gst_rate' => $item->tax_rate ?? 0,
            'confidence' => round($confidence, 1),
            'matched_by' => $matchedBy,
        ];
    }

    private function normalizeForMatch(?string $value): string
    {
        $value = Str::ascii(
            Str::lower(trim((string) $value))
        );

        $value = preg_replace(
            '/[^a-z0-9]+/',
            ' ',
            $value
        );

        return trim(
            preg_replace('/\s+/', ' ', $value)
        );
    }

    public function storeSupplier(Request $request)
    {
        $businessId = auth()->user()->business_id ?? null;

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'mobile' => [
                'nullable',
                'string',
                'max:20',
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
            ],

            'gstin' => [
                'nullable',
                'string',
                'max:30',
            ],

            'address' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ]);

        $supplier = new Client();

        $supplier->business_id = $businessId;
        $supplier->party_type = 'supplier';
        $supplier->name = $data['name'];

        $supplier->mobile = $data['mobile'] ?? null;
        $supplier->email = $data['email'] ?? null;
        $supplier->gstin = $data['gstin'] ?? null;
        $supplier->address = $data['address'] ?? null;

        $supplier->save();

        return response()->json([
            'success' => true,

            'message' => 'Supplier created successfully.',

            'supplier' => [
                'id' => $supplier->id,
                'name' => $supplier->name,
                'mobile' => $supplier->mobile,
            ],
        ]);
    }
}