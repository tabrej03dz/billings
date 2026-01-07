<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Item;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $bid = $this->resolveBusinessId($request);

        $q           = trim((string) $request->get('q', ''));
        $category_id = $request->integer('category_id');
        $active      = $request->get('active'); // '1' | '0' | null
        $perPage     = (int) ($request->get('per_page', 15));
        $perPage     = ($perPage > 0 && $perPage <= 100) ? $perPage : 15;

        $items = Item::query()
            ->with('category:id,name')
            ->where('business_id', $bid)
            ->when($q !== '', function ($w) use ($q) {
                $w->where(function ($s) use ($q) {
                    $s->where('name', 'like', "%{$q}%")
                        ->orWhere('sku', 'like', "%{$q}%")
                        ->orWhere('description', 'like', "%{$q}%");
                });
            })
            ->when($category_id, fn ($w) => $w->where('category_id', $category_id))
            ->when($active !== null && $active !== '', fn ($w) => $w->where('is_active', (bool) $active))
            ->latest()
            ->paginate($perPage);

        return response()->json([
            'ok'   => true,
            'data' => $items,
        ]);
    }

    /**
     * GET /api/items/categories
     */
    public function categories(Request $request)
    {
        $bid = $this->resolveBusinessId($request);

        $categories = Category::query()
            ->where('business_id', $bid)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json([
            'ok' => true,
            'data' => $categories,
        ]);
    }

    /**
     * POST /api/items
     * NOTE: product => opening stock movement (stock_qty) records, item.stock_qty stays 0 initially
     */
    public function store(Request $request, StockService $stock)
    {
        $bid = $this->resolveBusinessId($request);

        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'sku'         => [
                'nullable', 'string', 'max:100',
                Rule::unique('items', 'sku')->where(fn ($q) => $q->where('business_id', $bid)),
            ],
            'category_id' => ['nullable', 'integer'],
            'type'        => ['required', Rule::in(['product', 'service'])],

            // service fields
            'sac'         => ['nullable', 'string', 'max:32', 'required_if:type,service'],

            'description' => ['nullable', 'string', 'max:2000'],

            // pricing
            'price'         => ['nullable', 'numeric', 'min:0'],
            'cost_price'    => ['nullable', 'numeric', 'min:0'],
            'making_charge' => ['nullable', 'numeric', 'min:0'],

            // stock (product only)
            'stock_qty'   => ['nullable', 'integer', 'min:0', 'required_if:type,product'],
            'unit'        => ['nullable', 'string', 'max:50'],

            'tax_rate'    => ['required', 'numeric', 'min:0', 'max:100'],
            'is_active'   => ['nullable'],

            // metals/weights
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

        // ✅ category business-scope check
        if (!empty($data['category_id'])) {
            $ok = Category::where('id', $data['category_id'])
                ->where('business_id', $bid)
                ->exists();
            abort_unless($ok, 422, 'Invalid category for this business.');
        }

        $type = $data['type'];

        $openingQty = ($type === 'product') ? (int) ($data['stock_qty'] ?? 0) : 0;

        $payload = Arr::except($data, ['stock_qty']);
        $payload['business_id'] = $bid;
        $payload['is_active']   = $request->boolean('is_active');
        $payload['stock_qty']   = 0; // always 0 initially

        // ✅ service => clear product-only fields
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

        if ($type === 'product' && $openingQty > 0) {
            $stock->recordOpening($item, $openingQty, 'Opening stock (item create)');
        }

        return response()->json([
            'ok'   => true,
            'msg'  => 'Item created successfully.',
            'item' => $item->load('category:id,name'),
        ], 201);
    }

    /**
     * GET /api/items/{item}
     */
    public function show(Request $request, Item $item)
    {
        $bid = $this->resolveBusinessId($request);
        abort_unless((int) $item->business_id === (int) $bid, 403, 'Unauthorized item.');

        return response()->json([
            'ok'   => true,
            'item' => $item->load('category:id,name'),
        ]);
    }

    /**
     * PUT/PATCH /api/items/{item}
     * NOTE: stock_qty is final stock; we adjust via StockService setStockTo()
     */
    public function update(Request $request, Item $item, StockService $stock)
    {
        $bid = $this->resolveBusinessId($request);
        abort_unless((int) $item->business_id === (int) $bid, 403, 'Unauthorized item.');

        $data = $request->validate([
            'name'          => ['required', 'string', 'max:255'],
            'sku'           => [
                'nullable', 'string', 'max:100',
                Rule::unique('items', 'sku')
                    ->ignore($item->id)
                    ->where(fn ($q) => $q->where('business_id', $bid)),
            ],
            'category_id'   => ['nullable', 'integer'],

            'type'          => ['nullable', Rule::in(['product', 'service'])], // optional update

            'sac'           => ['nullable', 'string', 'max:32'],
            'description'   => ['nullable', 'string', 'max:2000'],

            'price'         => ['nullable', 'numeric', 'min:0'],
            'cost_price'    => ['nullable', 'numeric', 'min:0'],
            'making_charge' => ['nullable', 'numeric', 'min:0'],

            'stock_qty'     => ['nullable', 'integer', 'min:0'], // final stock (optional)
            'unit'          => ['nullable', 'string', 'max:50'],

            'tax_rate'      => ['required', 'numeric', 'min:0', 'max:100'],
            'is_active'     => ['nullable'],

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
        ]);

        if (!empty($data['category_id'])) {
            $ok = Category::where('id', $data['category_id'])
                ->where('business_id', $bid)
                ->exists();
            abort_unless($ok, 422, 'Invalid category for this business.');
        }

        // stock set (only if provided)
        $finalQtyProvided = array_key_exists('stock_qty', $data);
        $finalQty         = (int) ($data['stock_qty'] ?? 0);

        $payload = Arr::except($data, ['stock_qty']);
        $payload['is_active'] = $request->boolean('is_active');

        // ✅ if type becomes service => clean product-only fields
        $type = $payload['type'] ?? $item->type;
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

        $item->update($payload);

        // ✅ adjust stock only when stock_qty passed
        if ($finalQtyProvided) {
            $stock->setStockTo($item, $finalQty, 'Stock updated from API item update');
        }

        return response()->json([
            'ok'   => true,
            'msg'  => 'Item updated successfully.',
            'item' => $item->fresh()->load('category:id,name'),
        ]);
    }

    /**
     * DELETE /api/items/{item}
     */
    public function destroy(Request $request, Item $item)
    {
        $bid = $this->resolveBusinessId($request);
        abort_unless((int) $item->business_id === (int) $bid, 403, 'Unauthorized item.');

        $item->delete();

        return response()->json([
            'ok'  => true,
            'msg' => 'Item deleted successfully.',
        ]);
    }

    /**
     * Business resolve (same style as your other controllers)
     */
    private function resolveBusinessId(Request $request): int
    {
        $user = $request->user();

        $bid = $user?->current_business_id ?? session('active_business_id');

        if (!$bid && $user) {
            $bid = $user->businesses()->pluck('businesses.id')->first();
        }

        abort_unless($bid, 422, 'Active business not found.');

        return (int) $bid;
    }
}
