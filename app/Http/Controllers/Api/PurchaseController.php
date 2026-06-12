<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Item;
use App\Models\Purchase;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PurchaseController extends Controller
{
    protected StockService $stock;

    public function __construct(StockService $stock)
    {
        $this->stock = $stock;
    }

    // public function index(Request $request)
    // {
    //     $businessId = $request->business_id ?? $request->user()->business_id ?? null;

    //     $purchases = Purchase::with(['supplier', 'items.item'])
    //         ->when($businessId, fn ($q) => $q->where('business_id', $businessId))
    //         ->latest('invoice_date')
    //         ->paginate($request->get('per_page', 20));

    //     return response()->json([
    //         'status' => true,
    //         'message' => 'Purchases fetched successfully.',
    //         'data' => $purchases,
    //     ]);
    // }

    public function index(Request $request)
{
    $businessId = $request->business_id
        ?? $request->user()->business_id
        ?? null;

    $query = Purchase::withoutGlobalScopes()
        ->with([
            'supplier',
            'items.item',
        ]);

    if ($businessId) {
        $query->where('business_id', $businessId);
    }

    if ($request->filled('search')) {
        $search = $request->search;

        $query->where(function ($q) use ($search) {
            $q->where('invoice_no', 'like', "%{$search}%")
                ->orWhereHas('supplier', function ($supplierQuery) use ($search) {
                    $supplierQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('mobile', 'like', "%{$search}%");
                });
        });
    }

    if ($request->filled('supplier_id')) {
        $query->where('supplier_id', $request->supplier_id);
    }

    if ($request->filled('from_date')) {
        $query->whereDate('invoice_date', '>=', $request->from_date);
    }

    if ($request->filled('to_date')) {
        $query->whereDate('invoice_date', '<=', $request->to_date);
    }

    $purchases = $query
        ->orderByDesc('invoice_date')
        ->orderByDesc('id')
        ->paginate($request->get('per_page', 20));

    return response()->json([
        'status' => true,
        'message' => 'Purchases fetched successfully.',
        'data' => $purchases,
    ]);
}

    // public function formData(Request $request)
    // {
    //     $businessId = $request->user()->business_id ?? null;

    //     $suppliers = Client::when($businessId, fn ($q) => $q->where('business_id', $businessId))
    //         ->orderBy('name')
    //         ->get();

    //     $items = Item::when($businessId, fn ($q) => $q->where('business_id', $businessId))
    //         ->where('is_active', true)
    //         ->orderBy('name')
    //         ->get();

    //     return response()->json([
    //         'status' => true,
    //         'message' => 'Purchase form data fetched successfully.',
    //         'data' => [
    //             'suppliers' => $suppliers,
    //             'items' => $items,
    //         ],
    //     ]);
    // }


    public function formData(Request $request)
{
    $businessId = $request->business_id
        ?? $request->user()->business_id
        ?? null;

    $suppliers = Client::withoutGlobalScopes()
        ->when($businessId, fn ($q) => $q->where('business_id', $businessId))
        ->orderBy('name')
        ->get();

    $items = Item::withoutGlobalScopes()
        ->when($businessId, fn ($q) => $q->where('business_id', $businessId))
        ->where('is_active', true)
        ->orderBy('name')
        ->get();

    return response()->json([
        'status' => true,
        'message' => 'Purchase form data fetched successfully.',
        'data' => [
            'suppliers' => $suppliers,
            'items' => $items,
        ],
    ]);
}

    public function show(Request $request, Purchase $purchase)
    {
        $this->authorizeBusiness($request, $purchase);

        $purchase->load(['supplier', 'items.item']);

        return response()->json([
            'status' => true,
            'message' => 'Purchase fetched successfully.',
            'data' => $purchase,
        ]);
    }

    // public function store(Request $request)
    // {
    //     $businessId = $request->user()->business_id ?? null;

    //     $data = $request->validate([
    //         'supplier_id' => 'nullable|exists:clients,id',
    //         'invoice_no' => 'nullable|string|max:50',
    //         'invoice_date' => 'required|date',

    //         'items' => 'required|array|min:1',
    //         'items.*.item_id' => 'required|exists:items,id',
    //         'items.*.qty' => 'required|integer|min:1',
    //         'items.*.rate' => 'required|numeric|min:0',
    //         'items.*.amount' => 'required|numeric|min:0',
    //         'items.*.gross_weight' => 'nullable|numeric',
    //         'items.*.metal_weight' => 'nullable|numeric',
    //         'items.*.stone_weight' => 'nullable|numeric',
    //     ]);

    //     $purchase = DB::transaction(function () use ($data, $businessId) {
    //         $total = collect($data['items'])->sum('amount');

    //         $purchase = Purchase::create([
    //             'business_id' => $businessId,
    //             'supplier_id' => $data['supplier_id'] ?? null,
    //             'invoice_no' => $data['invoice_no'] ?? null,
    //             'invoice_date' => $data['invoice_date'],
    //             'total_amount' => $total,
    //         ]);

    //         foreach ($data['items'] as $row) {
    //             $purchase->items()->create($row);
    //         }

    //         $purchase->load('items.item');

    //         $this->stock->recordPurchase($purchase);

    //         return $purchase->load(['supplier', 'items.item']);
    //     });

    //     return response()->json([
    //         'status' => true,
    //         'message' => 'Purchase saved and stock increased successfully.',
    //         'data' => $purchase,
    //     ], 201);
    // }

    public function store(Request $request)
{
    $businessId = $request->user()->business_id ?? null;

    $data = $this->validatePurchase($request);

    $billFilePath = null;

    if ($request->hasFile('bill_file')) {
        $billFilePath = $request->file('bill_file')->store('purchase-bills', 'public');
    }

    try {

        $purchase = DB::transaction(function () use ($data, $businessId, $billFilePath) {

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

            return $purchase->load([
                'supplier',
                'items.item'
            ]);
        });

        return response()->json([
            'status' => true,
            'message' => 'Purchase saved successfully.',
            'data' => $purchase,
        ], 201);

    } catch (\Throwable $e) {

        if ($billFilePath) {
            Storage::disk('public')->delete($billFilePath);
        }

        throw $e;
    }
}

    // public function update(Request $request, Purchase $purchase)
    // {
    //     $this->authorizeBusiness($request, $purchase);

    //     $data = $request->validate([
    //         'supplier_id' => 'nullable|exists:clients,id',
    //         'invoice_no' => 'nullable|string|max:50',
    //         'invoice_date' => 'required|date',

    //         'items' => 'required|array|min:1',
    //         'items.*.item_id' => 'required|exists:items,id',
    //         'items.*.qty' => 'required|integer|min:1',
    //         'items.*.rate' => 'required|numeric|min:0',
    //         'items.*.amount' => 'required|numeric|min:0',
    //         'items.*.gross_weight' => 'nullable|numeric',
    //         'items.*.metal_weight' => 'nullable|numeric',
    //         'items.*.stone_weight' => 'nullable|numeric',
    //     ]);

    //     $purchase = DB::transaction(function () use ($purchase, $data) {
    //         $purchase->load('items.item');

    //         $this->stock->rollbackReference($purchase);

    //         $purchase->items()->delete();

    //         $total = collect($data['items'])->sum('amount');

    //         $purchase->update([
    //             'supplier_id' => $data['supplier_id'] ?? null,
    //             'invoice_no' => $data['invoice_no'] ?? null,
    //             'invoice_date' => $data['invoice_date'],
    //             'total_amount' => $total,
    //         ]);

    //         foreach ($data['items'] as $row) {
    //             $purchase->items()->create($row);
    //         }

    //         $purchase->load('items.item');

    //         $this->stock->recordPurchase($purchase);

    //         return $purchase->load(['supplier', 'items.item']);
    //     });

    //     return response()->json([
    //         'status' => true,
    //         'message' => 'Purchase updated and stock recalculated successfully.',
    //         'data' => $purchase,
    //     ]);
    // }

//     public function update(Request $request, Purchase $purchase)
// {
//     $this->authorizeBusiness($request, $purchase);

//     $data = $this->validatePurchase($request);

//     $oldBillPath = $purchase->bill_file;
//     $newBillPath = null;
//     $billFilePath = $oldBillPath;

//     if ($request->hasFile('bill_file')) {
//         $newBillPath = $request->file('bill_file')->store('purchase-bills', 'public');
//         $billFilePath = $newBillPath;
//     }

//     try {

//         $purchase = DB::transaction(function () use ($purchase, $data, $billFilePath) {

//             $purchase->load('items.item');

//             $this->stock->rollbackReference($purchase);

//             $purchase->items()->delete();

//             $calculated = $this->calculatePurchase($data);

//             $purchase->update([
//                 'supplier_id'       => $data['supplier_id'] ?? null,
//                 'invoice_no'        => $data['invoice_no'] ?? null,
//                 'invoice_date'      => $data['invoice_date'],
//                 'tax_type'          => $data['tax_type'] ?? 'intra_state',
//                 'bill_file'         => $billFilePath,

//                 'subtotal'          => $calculated['summary']['subtotal'],
//                 'discount_amount'   => $calculated['summary']['discount_amount'],
//                 'cgst_amount'       => $calculated['summary']['cgst_amount'],
//                 'sgst_amount'       => $calculated['summary']['sgst_amount'],
//                 'igst_amount'       => $calculated['summary']['igst_amount'],
//                 'round_off'         => $calculated['summary']['round_off'],
//                 'total_amount'      => $calculated['summary']['total_amount'],
//                 'paid_amount'       => $calculated['summary']['paid_amount'],
//                 'due_amount'        => $calculated['summary']['due_amount'],
//             ]);

//             foreach ($calculated['items'] as $row) {
//                 $purchase->items()->create($row);
//             }

//             $purchase->load('items.item');

//             $this->stock->recordPurchase($purchase);

//             return $purchase->load([
//                 'supplier',
//                 'items.item'
//             ]);
//         });

//         if ($newBillPath && $oldBillPath) {
//             Storage::disk('public')->delete($oldBillPath);
//         }

//         return response()->json([
//             'status' => true,
//             'message' => 'Purchase updated successfully.',
//             'data' => $purchase,
//         ]);

//     } catch (\Throwable $e) {

//         if ($newBillPath) {
//             Storage::disk('public')->delete($newBillPath);
//         }

//         throw $e;
//     }
// }

//     public function destroy(Request $request, Purchase $purchase)
//     {
//         $this->authorizeBusiness($request, $purchase);

//         DB::transaction(function () use ($purchase) {
//             $purchase->load('items.item');

//             $this->stock->rollbackReference($purchase);

//             $purchase->items()->delete();
//             $purchase->delete();
//         });

//         return response()->json([
//             'status' => true,
//             'message' => 'Purchase deleted and stock reverted successfully.',
//         ]);
//     }

//     protected function authorizeBusiness(Request $request, Purchase $purchase): void
//     {
//         $currentBusinessId = $request->user()->business_id ?? null;

//         if ($currentBusinessId && $purchase->business_id !== $currentBusinessId) {
//             abort(response()->json([
//                 'status' => false,
//                 'message' => 'Unauthorized business access.',
//             ], 403));
//         }
//     }


//     private function validatePurchase(Request $request): array
//     {
//         $businessId = $request->user()->business_id ?? null;

//         return $request->validate([
//             'supplier_id' => [
//                 'nullable',
//                 Rule::exists('clients', 'id')
//                     ->when($businessId, fn ($rule) => $rule->where('business_id', $businessId)),
//             ],

//             'invoice_no'      => 'nullable|string|max:50',
//             'invoice_date'    => 'required|date',

//             'tax_type'        => 'nullable|in:intra_state,inter_state',
//             'discount_amount' => 'nullable|numeric|min:0',
//             'round_off'       => 'nullable|numeric',
//             'paid_amount'     => 'nullable|numeric|min:0',

//             'items' => 'required|array|min:1',

//             'items.*.item_id' => [
//                 'required',
//                 Rule::exists('items', 'id')
//                     ->when($businessId, fn ($rule) => $rule->where('business_id', $businessId)),
//             ],

//             'items.*.qty'           => 'required|numeric|min:0.001',
//             'items.*.qty_unit'      => 'required|string|in:pcs,gram,kg,carat,pair,set,dozen',
//             'items.*.rate'          => 'required|numeric|min:0',
//             'items.*.gst_rate'      => 'nullable|numeric|min:0',

//             'items.*.gross_weight'  => 'nullable|numeric',
//             'items.*.metal_weight'  => 'nullable|numeric',
//             'items.*.stone_weight'  => 'nullable|numeric',

//             'bill_file'             => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
//         ]);
//     }




    public function update(Request $request, $purchase)
    {
        /*
        |--------------------------------------------------------------------------
        | IMPORTANT:
        | Route model binding/global scope issue avoid karne ke liye
        | Purchase $purchase ki jagah id se manually fetch kar rahe hain.
        |--------------------------------------------------------------------------
        */

        $purchase = Purchase::withoutGlobalScopes()
            ->with(['items.item'])
            ->findOrFail($purchase);

        $this->authorizeBusiness($request, $purchase);

        // update me validation purchase ke actual business_id ke hisab se hoga
        $data = $this->validatePurchase($request, $purchase->business_id);

        $oldBillPath = $purchase->bill_file;
        $newBillPath = null;
        $billFilePath = $oldBillPath;

        if ($request->hasFile('bill_file')) {
            $newBillPath = $request->file('bill_file')->store('purchase-bills', 'public');
            $billFilePath = $newBillPath;
        }

        try {
            $purchase = DB::transaction(function () use ($purchase, $data, $billFilePath) {

                $purchase->load(['items.item']);

                // old stock rollback
                $this->stock->rollbackReference($purchase);

                // old purchase items delete
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

                $purchase->unsetRelation('items');

                $purchase->load([
                    'supplier',
                    'items.item',
                ]);

                // new stock add
                $this->stock->recordPurchase($purchase);

                return $purchase;
            });

            if ($newBillPath && $oldBillPath) {
                Storage::disk('public')->delete($oldBillPath);
            }

            return response()->json([
                'status' => true,
                'message' => 'Purchase updated successfully.',
                'data' => $purchase,
            ]);

        } catch (\Throwable $e) {
            if ($newBillPath) {
                Storage::disk('public')->delete($newBillPath);
            }

            throw $e;
        }
    }

    public function destroy(Request $request, $purchase)
    {
        /*
        |--------------------------------------------------------------------------
        | Route model binding/global scope issue avoid
        |--------------------------------------------------------------------------
        */

        $purchase = Purchase::withoutGlobalScopes()
            ->with(['items.item'])
            ->findOrFail($purchase);

        $this->authorizeBusiness($request, $purchase);

        $oldBillPath = $purchase->bill_file;

        DB::transaction(function () use ($purchase) {
            $purchase->load(['items.item']);

            // stock revert
            $this->stock->rollbackReference($purchase);

            // child rows delete
            $purchase->items()->delete();

            // purchase delete
            $purchase->delete();
        });

        if ($oldBillPath) {
            Storage::disk('public')->delete($oldBillPath);
        }

        return response()->json([
            'status' => true,
            'message' => 'Purchase deleted and stock reverted successfully.',
        ]);
    }

    protected function authorizeBusiness(Request $request, Purchase $purchase): void
    {
        $currentBusinessId = $request->business_id
            ?? $request->user()->business_id
            ?? null;

        /*
        |--------------------------------------------------------------------------
        | Agar request me business_id aa rahi hai to purchase usi business ka hona chahiye.
        | Agar admin/user ka business_id null hai to allow rahega.
        |--------------------------------------------------------------------------
        */

        if ($currentBusinessId && (int) $purchase->business_id !== (int) $currentBusinessId) {
            abort(response()->json([
                'status' => false,
                'message' => 'Unauthorized business access.',
            ], 403));
        }
    }

    private function validatePurchase(Request $request, ?int $businessId = null): array
    {
        $businessId = $businessId
            ?? $request->business_id
            ?? $request->user()->business_id
            ?? null;

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
            'items.*.qty_unit'      => 'required|string|in:pcs,gram,kg,carat,pair,set,dozen',
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
}