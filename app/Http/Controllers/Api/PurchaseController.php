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

class PurchaseController extends Controller
{
    protected StockService $stock;

    public function __construct(StockService $stock)
    {
        $this->stock = $stock;
    }

    public function index(Request $request)
    {
        $businessId = $request->business_id ?? $request->user()->business_id ?? null;

        $purchases = Purchase::with(['supplier', 'items.item'])
            ->when($businessId, fn ($q) => $q->where('business_id', $businessId))
            ->latest('invoice_date')
            ->paginate($request->get('per_page', 20));

        return response()->json([
            'status' => true,
            'message' => 'Purchases fetched successfully.',
            'data' => $purchases,
        ]);
    }

    public function formData(Request $request)
    {
        $businessId = $request->user()->business_id ?? null;

        $suppliers = Client::when($businessId, fn ($q) => $q->where('business_id', $businessId))
            ->orderBy('name')
            ->get();

        $items = Item::when($businessId, fn ($q) => $q->where('business_id', $businessId))
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

    public function update(Request $request, Purchase $purchase)
{
    $this->authorizeBusiness($request, $purchase);

    $data = $this->validatePurchase($request);

    $oldBillPath = $purchase->bill_file;
    $newBillPath = null;
    $billFilePath = $oldBillPath;

    if ($request->hasFile('bill_file')) {
        $newBillPath = $request->file('bill_file')->store('purchase-bills', 'public');
        $billFilePath = $newBillPath;
    }

    try {

        $purchase = DB::transaction(function () use ($purchase, $data, $billFilePath) {

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

            return $purchase->load([
                'supplier',
                'items.item'
            ]);
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

    public function destroy(Request $request, Purchase $purchase)
    {
        $this->authorizeBusiness($request, $purchase);

        DB::transaction(function () use ($purchase) {
            $purchase->load('items.item');

            $this->stock->rollbackReference($purchase);

            $purchase->items()->delete();
            $purchase->delete();
        });

        return response()->json([
            'status' => true,
            'message' => 'Purchase deleted and stock reverted successfully.',
        ]);
    }

    protected function authorizeBusiness(Request $request, Purchase $purchase): void
    {
        $currentBusinessId = $request->user()->business_id ?? null;

        if ($currentBusinessId && $purchase->business_id !== $currentBusinessId) {
            abort(response()->json([
                'status' => false,
                'message' => 'Unauthorized business access.',
            ], 403));
        }
    }
}