<?php

namespace App\Http\Controllers;

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

    public function index()
    {
        $businessId = auth()->user()->business_id ?? null;

        $purchases = Purchase::with('supplier')
            ->when($businessId, fn ($q) => $q->where('business_id', $businessId))
            ->latest('invoice_date')
            ->paginate(20);

        return view('purchases.index', compact('purchases'));
    }

    // public function create()
    // {
    //     $businessId = auth()->user()->business_id ?? null;

    //     $suppliers = Client::when($businessId, fn ($q) => $q->where('business_id', $businessId))
    //         ->orderBy('name')
    //         ->get();

    //     $items = Item::when($businessId, fn ($q) => $q->where('business_id', $businessId))
    //         ->where('is_active', true)
    //         ->orderBy('name')
    //         ->get();

    //     $purchase = new Purchase();

    //     return view('purchases.create', compact('purchase', 'suppliers', 'items'));
    // }

    public function create()
    {
        $businessId = auth()->user()->business_id ?? null;

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

        $purchase = new Purchase();

        return view(
            'purchases.create',
            compact('purchase', 'suppliers', 'items')
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

    //     $suppliers = Client::when($businessId, fn ($q) => $q->where('business_id', $businessId))
    //         ->orderBy('name')
    //         ->get();

    //     $items = Item::when($businessId, fn ($q) => $q->where('business_id', $businessId))
    //         ->where('is_active', true)
    //         ->orderBy('name')
    //         ->get();

    //     $purchase->load('items.item');

    //     return view('purchases.edit', compact('purchase', 'suppliers', 'items'));
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

        $purchase->load('items.item');

        return view(
            'purchases.edit',
            compact('purchase', 'suppliers', 'items')
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

    public function storeSupplier(Request $request)
    {
        $businessId = auth()->user()->business_id ?? null;

        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'phone' => [
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

        $supplier->phone = $data['phone'] ?? null;
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
                'phone' => $supplier->phone,
            ],
        ]);
    }
}