<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $q           = trim($request->get('q', ''));
        $category_id = $request->integer('category_id');
        $active      = $request->get('active'); // '1' | '0' | null

        $items = Item::query()
            ->with('category:id,name') // eager-load for table
            ->when($q !== '', function ($w) use ($q) {
                $w->where(function ($s) use ($q) {
                    $s->where('name', 'like', "%{$q}%")
                        ->orWhere('sku', 'like', "%{$q}%")
                        ->orWhere('description', 'like', "%{$q}%");
                });
            })
            ->when($category_id, fn($w) => $w->where('category_id', $category_id))
            ->when($active !== null && $active !== '', fn($w) => $w->where('is_active', (bool)$active))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        // current business ki categories (BelongsToBusiness scope ke sath)
        $categories = Category::orderBy('name')->get(['id','name']);

        return view('items.index', compact('items', 'categories', 'q', 'category_id', 'active'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get(['id','name']);
        return view('items.create', compact('categories'));
    }


    public function store(Request $request, StockService $stock)
    {
        $bid = $request->user()->current_business_id ?? session('active_business_id');

        if (!$bid) {
            $bid = $request->user()->businesses()->pluck('businesses.id')->first();
        }

        abort_unless($bid, 422, 'Active business not found.');

        // ✅ validate
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'sku'         => [
                'nullable', 'string', 'max:100',
                Rule::unique('items', 'sku')->where(fn($q) => $q->where('business_id', $bid)),
            ],
            'category_id' => ['required', 'integer'],
            'type'        => ['required', Rule::in(['product', 'service'])],

            // service fields
            'sac'         => ['nullable', 'string', 'max:32', 'required_if:type,service'],

            'description' => ['nullable', 'string', 'max:2000'],

            // pricing
            'price'         => ['nullable', 'numeric', 'min:0'],
            'cost_price'    => ['nullable', 'numeric', 'min:0'],
            'making_charge' => ['nullable', 'numeric', 'min:0'], // ✅ product only (we'll nullify on service)

            // stock (product only)
            'stock_qty'   => ['nullable', 'integer', 'min:0', 'required_if:type,product'],
            'unit'        => ['nullable', 'string', 'max:50'],

            'tax_rate'    => ['required', 'numeric', 'min:0', 'max:100'],
            'is_active'   => ['nullable'],

            // metals/weights (product only - we'll nullify on service)
            'metal_type'    => ['nullable', Rule::in(['gold','silver','other'])],
            'purity'        => ['nullable', 'string', 'max:50'],

            'gross_weight'  => ['nullable', 'numeric', 'min:0'],
            'metal_weight'  => ['nullable', 'numeric', 'min:0'],
            'stone_weight'  => ['nullable', 'numeric', 'min:0'],
            'stone_charges' => ['nullable', 'numeric', 'min:0'],

            'gold_weight'     => ['nullable', 'numeric', 'min:0'],
            'gold_purity'     => ['nullable', 'string', 'max:50'],
            'silver_weight'   => ['nullable', 'numeric', 'min:0'],
            'silver_purity'   => ['nullable', 'string', 'max:50'],
            'diamond_weight'  => ['nullable', 'numeric', 'min:0'],
            'diamond_charges' => ['nullable', 'numeric', 'min:0'],
        ], [
            'sac.required_if'       => 'SAC Code is required for Service.',
            'stock_qty.required_if' => 'Stock Qty is required for Product.',
        ]);


        
        // ✅ category business-scope check
        if (!empty($data['category_id'])) {
            $ok = Category::where('id', $data['category_id'])
                ->where('business_id', $bid)
                ->exists();
            abort_unless($ok, 422, 'Invalid category for this business.');
        }

        $type = $data['type'];

        // ✅ opening qty only for product
        $openingQty = ($type === 'product') ? (int)($data['stock_qty'] ?? 0) : 0;

        // ❗ we don't save stock_qty directly (opening movement handles it)
        $payload = Arr::except($data, ['stock_qty']);

        $payload['business_id'] = $bid;
        $payload['is_active']   = $request->boolean('is_active');
        $payload['stock_qty']   = 0; // always 0 initially

        // ✅ if service => clear product-only fields (safe + clean DB)
        if ($type === 'service') {
            $payload['making_charge'] = null;
            $payload['unit']          = null;

            $payload['metal_type']    = null;
            $payload['purity']        = null;

            $payload['gross_weight']  = null;
            $payload['metal_weight']  = null;
            $payload['stone_weight']  = null;
            $payload['stone_charges'] = null;

            $payload['gold_weight']     = null;
            $payload['gold_purity']     = null;
            $payload['silver_weight']   = null;
            $payload['silver_purity']   = null;
            $payload['diamond_weight']  = null;
            $payload['diamond_charges'] = null;
        }

        $item = Item::create($payload);

        // ✅ opening stock movement only for product
        if ($type === 'product' && $openingQty > 0) {
            $stock->recordOpening($item, $openingQty, 'Opening stock (item create)');
        }

        return redirect()
            ->route('items.index')
            ->with('success', 'Item created successfully.');
    }


    public function edit(Item $item)
    {
        // Global scope ensures this $item belongs to active business
        $categories = Category::orderBy('name')->get(['id','name']);
        return view('items.edit', compact('item','categories'));
    }

    // public function update(Request $request, Item $item, StockService $stock)
    // {
    //     $bid = $request->user()->current_business_id ?? session('active_business_id');
    //     if (!$bid) {
    //         $bid = $request->user()->businesses()->pluck('businesses.id')->first();
    //     }
    //     abort_unless($bid, 422, 'Active business not found.');
    //     abort_unless((int)$item->business_id === (int)$bid, 403, 'Unauthorized item.');

    //     $data = $request->validate([
    //         'name'          => ['required','string','max:255'],
    //         'sku'           => [
    //             'nullable','string','max:100',
    //             Rule::unique('items','sku')
    //                 ->ignore($item->id)
    //                 ->where(fn($q) => $q->where('business_id', $bid)),
    //         ],
    //         'category_id'   => ['nullable','integer'],
    //         'sac'           => ['nullable','string','max:32'],
    //         'description'   => ['nullable','string','max:2000'],

    //         'price'         => ['nullable','numeric','min:0'],
    //         'cost_price'    => ['nullable','numeric','min:0'],
    //         'making_charge' => ['nullable','numeric','min:0'],

    //         'stock_qty'     => ['required','integer','min:0'], // 👈 final stock
    //         'unit'          => ['nullable','string','max:50'],
    //         'weight'        => ['nullable','numeric','min:0'],

    //         'tax_rate'      => ['required','numeric','min:0','max:100'],
    //         'is_active'     => ['nullable'],

    //         'metal_type'    => ['nullable', Rule::in(['gold','silver','other'])],
    //         'purity'        => ['nullable','string','max:50'],

    //         'gross_weight'  => ['nullable','numeric','min:0'],
    //         'metal_weight'  => ['nullable','numeric','min:0'],
    //         'stone_weight'  => ['nullable','numeric','min:0'],
    //         'stone_charges' => ['nullable','numeric','min:0'],

    //         'gold_weight'     => ['nullable','numeric','min:0'],
    //         'gold_purity'     => ['nullable','string','max:50'],
    //         'silver_weight'   => ['nullable','numeric','min:0'],
    //         'silver_purity'   => ['nullable','string','max:50'],
    //         'diamond_weight'  => ['nullable','numeric','min:0'],
    //         'diamond_charges' => ['nullable','numeric','min:0'],
    //     ]);

    //     if (!empty($data['category_id'])) {
    //         $ok = Category::where('id', $data['category_id'])
    //             ->where('business_id', $bid)
    //             ->exists();
    //         abort_unless($ok, 422, 'Invalid category for this business.');
    //     }

    //     $finalQty = (int) $data['stock_qty'];

    //     // ❗ stock_qty directly update nahi hoga
    //     $payload = Arr::except($data, ['stock_qty']);
    //     $payload['is_active'] = $request->boolean('is_active');

    //     $item->update($payload);

    //     // ✅ stock difference adjustment
    //     $stock->setStockTo($item, $finalQty, 'Stock updated from item edit');

    //     return redirect()
    //         ->route('items.index')
    //         ->with('success', 'Item updated successfully and stock adjusted.');
    // }

public function update(Request $request, Item $item, StockService $stock)
{
    try {
        $bid = $request->user()->current_business_id ?? session('active_business_id');

        if (!$bid) {
            $bid = $request->user()->businesses()->pluck('businesses.id')->first();
        }

        abort_unless($bid, 422, 'Active business not found.');
        abort_unless((int) $item->business_id === (int) $bid, 403, 'Unauthorized item.');

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],

            'sku' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('items', 'sku')
                    ->ignore($item->id)
                    ->where(fn ($q) => $q->where('business_id', $bid)),
            ],

            'category_id' => ['nullable', 'integer'],
            'type'        => ['required', Rule::in(['product', 'service'])],

            'sac'         => ['nullable', 'string', 'max:32', 'required_if:type,service'],
            'description' => ['nullable', 'string', 'max:2000'],

            'price'         => ['nullable', 'numeric', 'min:0'],
            'cost_price'    => ['nullable', 'numeric', 'min:0'],
            'making_charge' => ['nullable', 'numeric', 'min:0'],

            'stock_qty' => ['nullable', 'integer', 'min:0', 'required_if:type,product'],
            'unit'      => ['nullable', 'string', 'max:50'],

            'tax_rate'  => ['required', 'numeric', 'min:0', 'max:100'],
            'is_active' => ['nullable'],

            'metal_type'    => ['nullable', Rule::in(['gold', 'silver', 'other'])],
            'purity'        => ['nullable', 'string', 'max:50'],

            'gross_weight'  => ['nullable', 'numeric', 'min:0'],
            'metal_weight'  => ['nullable', 'numeric', 'min:0'],
            'stone_weight'  => ['nullable', 'numeric', 'min:0'],
            'stone_charges' => ['nullable', 'numeric', 'min:0'],

            'gold_weight'     => ['nullable', 'numeric', 'min:0'],
            'gold_purity'     => ['nullable', 'string', 'max:50'],
            'silver_weight'   => ['nullable', 'numeric', 'min:0'],
            'silver_purity'   => ['nullable', 'string', 'max:50'],
            'diamond_weight'  => ['nullable', 'numeric', 'min:0'],
            'diamond_charges' => ['nullable', 'numeric', 'min:0'],
        ], [
            'sac.required_if'       => 'SAC Code is required for Service.',
            'stock_qty.required_if' => 'Stock Qty is required for Product.',
        ]);

        if (!empty($data['category_id'])) {
            $categoryBelongsToBusiness = Category::where('id', $data['category_id'])
                ->where('business_id', $bid)
                ->exists();

            if (! $categoryBelongsToBusiness) {
                return back()
                    ->withErrors(['category_id' => 'Selected category does not belong to active business.'])
                    ->withInput();
            }
        }

        DB::beginTransaction();

        $type = $data['type'];
        $finalQty = $type === 'product' ? (int) ($data['stock_qty'] ?? 0) : 0;

        $payload = Arr::except($data, ['stock_qty']);
        $payload['is_active'] = $request->boolean('is_active');

        if ($type === 'service') {
            $payload['making_charge'] = null;
            $payload['unit']          = null;

            $payload['metal_type']    = null;
            $payload['purity']        = null;
            $payload['gross_weight']  = null;
            $payload['metal_weight']  = null;
            $payload['stone_weight']  = null;
            $payload['stone_charges'] = null;
            $payload['gold_weight']   = null;
            $payload['gold_purity']   = null;
            $payload['silver_weight'] = null;
            $payload['silver_purity'] = null;
            $payload['diamond_weight'] = null;
            $payload['diamond_charges'] = null;
        } else {
            $payload['sac'] = null;
        }

        $item->update($payload);

        if ($type === 'product') {
            $stock->setStockTo($item, $finalQty, 'Stock updated from item edit');
        }

        DB::commit();

        return redirect()
            ->route('items.index')
            ->with('success', 'Item updated successfully.');
    } catch (\Throwable $e) {
        DB::rollBack();

        Log::error('Item update failed', [
            'item_id' => $item->id ?? null,
            'user_id' => auth()->id(),
            'message' => $e->getMessage(),
            'line'    => $e->getLine(),
            'file'    => $e->getFile(),
        ]);

        return back()
            ->withErrors(['general' => 'Update failed: ' . $e->getMessage()])
            ->withInput();
    }
}


    public function destroy(Item $item)
    {
        $item->delete();
        return redirect()->route('items.index')->with('success','Item deleted successfully.');
    }

    public function show(\App\Models\Item $item)
    {
        // BelongsToBusiness scope ensure same business
        return response()->json([
            'id' => $item->id,
            'name' => $item->name,
            'sku' => $item->sku,
            'price' => (float)$item->price,
            'tax_rate' => (float)$item->tax_rate,
            'description' => $item->description,
        ]);
    }


    public function storeAjax(\Illuminate\Http\Request $r)
    {
        $data = $r->validate([
            'type' => ['required','in:product,service'],
            'name' => ['required','string','max:255'],
            'sku'  => ['nullable','string','max:100'],
            'description' => ['nullable','string','max:500'],

            'tax_rate' => ['nullable','numeric','min:0','max:100'],
            'hsn' => ['nullable','string','max:50'],
            'sac' => ['nullable','string','max:50'],

            // service
            'price' => ['nullable','numeric','min:0'],

            // product
            'making_charge' => ['nullable','numeric','min:0'],
            'gold_weight' => ['nullable','numeric','min:0'],
            'gold_purity' => ['nullable','string','max:50'],
            'silver_weight' => ['nullable','numeric','min:0'],
            'silver_purity' => ['nullable','string','max:50'],
            'stone_weight' => ['nullable','numeric','min:0'],
            'diamond_weight' => ['nullable','numeric','min:0'],
        ]);

        $bid = $r->user()->current_business_id ?? session('active_business_id');

        $item = \App\Models\Item::create([
            'business_id' => $bid,
            'type' => $data['type'],
            'name' => $data['name'],
            'sku'  => $data['sku'] ?? null,
            'description' => $data['description'] ?? null,

            'tax_rate' => (float)($data['tax_rate'] ?? 0),
            'hsn' => $data['hsn'] ?? null,
            'sac' => $data['sac'] ?? null,

            // service price (used in your pickItem for service_rate)
            'price' => (float)($data['price'] ?? 0),

            // product fields
            'making_charge' => (float)($data['making_charge'] ?? 0),
            'gold_weight' => (float)($data['gold_weight'] ?? 0),
            'gold_purity' => $data['gold_purity'] ?? null,
            'silver_weight' => (float)($data['silver_weight'] ?? 0),
            'silver_purity' => $data['silver_purity'] ?? null,
            'stone_weight' => (float)($data['stone_weight'] ?? 0),
            'diamond_weight' => (float)($data['diamond_weight'] ?? 0),
        ]);

        // ✅ return in same shape your itemsJson expects
        return response()->json([
            'item' => $item->only([
                'id','type','name','sku','description','tax_rate','hsn','sac','price',
                'making_charge','gold_weight','gold_purity','silver_weight','silver_purity',
                'stone_weight','diamond_weight'
            ])
        ]);
    }

}
