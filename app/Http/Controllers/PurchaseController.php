<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Item;
use App\Models\Purchase;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    public function create()
    {
        $businessId = auth()->user()->business_id ?? null;

        $suppliers = Client::when($businessId, fn ($q) => $q->where('business_id', $businessId))
            ->orderBy('name')
            ->get();

        $items = Item::when($businessId, fn ($q) => $q->where('business_id', $businessId))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $purchase = new Purchase();

        return view('purchases.create', compact('purchase', 'suppliers', 'items'));
    }

    public function store(Request $request)
    {
        $businessId = auth()->user()->business_id ?? null;

        $data = $request->validate([
            'supplier_id'       => 'nullable|exists:clients,id',
            'invoice_no'        => 'nullable|string|max:50',
            'invoice_date'      => 'required|date',
            'items'             => 'required|array|min:1',
            'items.*.item_id'   => 'required|exists:items,id',
            'items.*.qty'       => 'required|integer|min:1',
            'items.*.rate'      => 'required|numeric|min:0',
            'items.*.amount'    => 'required|numeric|min:0',
            'items.*.gross_weight' => 'nullable|numeric',
            'items.*.metal_weight' => 'nullable|numeric',
            'items.*.stone_weight' => 'nullable|numeric',
        ]);

        return DB::transaction(function () use ($data, $businessId) {
            $total = collect($data['items'])->sum('amount');

            $purchase = Purchase::create([
                'business_id'  => $businessId,
                'supplier_id'  => $data['supplier_id'] ?? null,
                'invoice_no'   => $data['invoice_no'] ?? null,
                'invoice_date' => $data['invoice_date'],
                'total_amount' => $total,
            ]);

            foreach ($data['items'] as $row) {
                $purchase->items()->create($row);
            }

            $purchase->load('items.item');
            $this->stock->recordPurchase($purchase);

            return redirect()
                ->route('purchases.index')
                ->with('success', 'Purchase saved & stock increased.');
        });
    }

    public function edit(Purchase $purchase)
    {
        $this->authorizeBusiness($purchase);

        $businessId = $purchase->business_id;

        $suppliers = Client::when($businessId, fn ($q) => $q->where('business_id', $businessId))
            ->orderBy('name')
            ->get();

        $items = Item::when($businessId, fn ($q) => $q->where('business_id', $businessId))
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $purchase->load('items.item');

        return view('purchases.edit', compact('purchase', 'suppliers', 'items'));
    }

    public function update(Request $request, Purchase $purchase)
    {
        $this->authorizeBusiness($purchase);
        $businessId = $purchase->business_id;

        $data = $request->validate([
            'supplier_id'       => 'nullable|exists:clients,id',
            'invoice_no'        => 'nullable|string|max:50',
            'invoice_date'      => 'required|date',
            'items'             => 'required|array|min:1',
            'items.*.item_id'   => 'required|exists:items,id',
            'items.*.qty'       => 'required|integer|min:1',
            'items.*.rate'      => 'required|numeric|min:0',
            'items.*.amount'    => 'required|numeric|min:0',
            'items.*.gross_weight' => 'nullable|numeric',
            'items.*.metal_weight' => 'nullable|numeric',
            'items.*.stone_weight' => 'nullable|numeric',
        ]);

        return DB::transaction(function () use ($purchase, $data, $businessId) {
            $purchase->load('items.item');
            $this->stock->rollbackReference($purchase);

            $purchase->items()->delete();

            $total = collect($data['items'])->sum('amount');

            $purchase->update([
                'supplier_id'  => $data['supplier_id'] ?? null,
                'invoice_no'   => $data['invoice_no'] ?? null,
                'invoice_date' => $data['invoice_date'],
                'total_amount' => $total,
            ]);

            foreach ($data['items'] as $row) {
                $purchase->items()->create($row);
            }

            $purchase->load('items.item');
            $this->stock->recordPurchase($purchase);

            return redirect()
                ->route('purchases.index')
                ->with('success', 'Purchase updated & stock recalculated.');
        });
    }

    public function destroy(Purchase $purchase)
    {
        $this->authorizeBusiness($purchase);

        return DB::transaction(function () use ($purchase) {
            $purchase->load('items.item');
            $this->stock->rollbackReference($purchase);

            $purchase->items()->delete();
            $purchase->delete();

            return redirect()
                ->route('purchases.index')
                ->with('success', 'Purchase deleted & stock reverted.');
        });
    }

    protected function authorizeBusiness(Purchase $purchase): void
    {
        $current = auth()->user()->business_id ?? null;
        if ($current && $purchase->business_id !== $current) {
            abort(403);
        }
    }
}
