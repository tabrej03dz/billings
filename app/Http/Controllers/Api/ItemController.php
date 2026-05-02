<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Item;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ItemController extends Controller
{
    // public function index(Request $request)
    // {
    //     $bid = $this->resolveBusinessId($request);

    //     $q           = trim((string) $request->get('q', ''));
    //     $category_id = $request->integer('category_id');
    //     $active      = $request->get('active'); // '1' | '0' | null
    //     $perPage     = (int) ($request->get('per_page', 15));
    //     $perPage     = ($perPage > 0 && $perPage <= 100) ? $perPage : 15;

    //     $items = Item::query()
    //         ->with('category:id,name')
    //         ->where('business_id', $bid)
    //         ->when($q !== '', function ($w) use ($q) {
    //             $w->where(function ($s) use ($q) {
    //                 $s->where('name', 'like', "%{$q}%")
    //                     ->orWhere('sku', 'like', "%{$q}%")
    //                     ->orWhere('description', 'like', "%{$q}%");
    //             });
    //         })
    //         ->when($category_id, fn ($w) => $w->where('category_id', $category_id))
    //         ->when($active !== null && $active !== '', fn ($w) => $w->where('is_active', (bool) $active))
    //         ->latest()
    //         ->paginate($perPage);

    //     return response()->json([
    //         'ok'   => true,
    //         'data' => $items,
    //     ]);
    // }

    public function index(Request $request)
    {
        $bid = DB::table('business_user')->where('user_id', 1)->first()?->business_id;

        $q           = trim((string) $request->get('q', ''));
        $category_id = $request->integer('category_id');
        $active      = $request->get('active'); // '1' | '0' | null
        $perPage     = (int) ($request->get('per_page', 15));
        $perPage     = ($perPage > 0 && $perPage <= 100) ? $perPage : 15;

        // ✅ sold summary subquery
        $soldSub = DB::table('invoice_items')
            ->selectRaw('item_id, SUM(quantity) as total_sold')
            ->groupBy('item_id');

        $items = Item::query()
            ->with('category:id,name')
            ->where('items.business_id', $bid)

            // ✅ join with subquery
            ->leftJoinSub($soldSub, 'sold', function ($join) {
                $join->on('sold.item_id', '=', 'items.id');
            })
            ->addSelect('items.*')
            ->addSelect(DB::raw('COALESCE(sold.total_sold,0) as total_sold'))

            ->when($q !== '', function ($w) use ($q) {
                $w->where(function ($s) use ($q) {
                    $s->where('items.name', 'like', "%{$q}%")
                    ->orWhere('items.sku', 'like', "%{$q}%")
                    ->orWhere('items.description', 'like', "%{$q}%");
                });
            })
            ->when($category_id, fn ($w) => $w->where('items.category_id', $category_id))
            ->when($active !== null && $active !== '', fn ($w) => $w->where('items.is_active', (bool) $active))

            ->orderByDesc('total_sold')
            ->orderByDesc('items.id')
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
        $bid = (int) $request->input('business_id');

        if ($bid <= 0) {
            return response()->json([
                'ok' => false,
                'msg' => 'Validation failed',
                'errors' => ['business_id' => ['business_id is required.']],
            ], 422);
        }

        try {
            // ✅ validate (same as web, + business_id)
            $data = $request->validate([
                'business_id' => ['required', 'integer', 'min:1'],

                'name'        => ['required', 'string', 'max:255'],
                'sku'         => [
                    'nullable', 'string', 'max:100',
                    Rule::unique('items', 'sku')->where(fn($q) => $q->where('business_id', $bid)),
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
//            if (!empty($data['category_id'])) {
//                $ok = Category::where('id', $data['category_id'])
//                    ->where('business_id', $bid)
//                    ->exists();
//
//                if (!$ok) {
//                    throw ValidationException::withMessages([
//                        'category_id' => ['Invalid category for this business.'],
//                    ]);
//                }
//            }

            return DB::transaction(function () use ($data, $request, $stock, $bid) {

                $type = $data['type'];
                $openingQty = ($type === 'product') ? (int) ($data['stock_qty'] ?? 0) : 0;

                // ❗ we don't save stock_qty directly
                $payload = Arr::except($data, ['stock_qty']);
                $payload['business_id'] = $bid;
                $payload['is_active']   = $request->boolean('is_active');
                $payload['stock_qty']   = 0;

                // ✅ if service => nullify product-only fields
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

                $item->load('category:id,name');

                return response()->json([
                    'ok'   => true,
                    'msg'  => 'Item created successfully.',
                    'item' => $item,
                ], 201);
            });

        } catch (ValidationException $e) {
            return response()->json([
                'ok' => false,
                'msg' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'msg' => 'Server error while creating item.',
                // dev debug:
                // 'error' => $e->getMessage(),
            ], 500);
        }
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
    public function update(Request $request, StockService $stock, $id)
    {
        $bid = (int) $request->input('business_id');

        if ($bid <= 0) {
            return response()->json([
                'ok' => false,
                'msg' => 'Validation failed',
                'errors' => ['business_id' => ['business_id is required.']],
            ], 422);
        }

        // ✅ business-scope item fetch (important)
        $item = Item::where('id', $id)->where('business_id', $bid)->first();

        if (!$item) {
            return response()->json([
                'ok' => false,
                'msg' => 'Item not found for this business.',
            ], 404);
        }

        try {
            $data = $request->validate([
                'business_id' => ['required', 'integer', 'min:1'],

                'name'        => ['required', 'string', 'max:255'],
                'sku' => [
                    'sometimes', 'nullable', 'string', 'max:100',
                    Rule::unique('items', 'sku')
                        ->where(fn ($q) => $q->where('business_id', $bid))
                        ->ignore($item->id),
                ],
                'category_id' => ['nullable', 'integer'],
                'type'        => ['required', Rule::in(['product', 'service'])],

                'sac'         => ['nullable', 'string', 'max:32', 'required_if:type,service'],
                'description' => ['nullable', 'string', 'max:2000'],

                'price'         => ['nullable', 'numeric', 'min:0'],
                'cost_price'    => ['nullable', 'numeric', 'min:0'],
                'making_charge' => ['nullable', 'numeric', 'min:0'],

                // ✅ UPDATE: stock_qty optional (adjustment) — not required
                'stock_qty'    => ['nullable', 'integer', 'min:0'],
                'unit'         => ['nullable', 'string', 'max:50'],

                'tax_rate'     => ['required', 'numeric', 'min:0', 'max:100'],
                'is_active'    => ['nullable'],

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
                'sac.required_if' => 'SAC Code is required for Service.',
            ]);

            return DB::transaction(function () use ($data, $request, $stock, $bid, $item) {

                $newType = $data['type'];

                // ✅ if client sends stock_qty in update, treat as "set stock to X" adjustment
                // current stock is in stock system, but item->stock_qty is 0 always in your design.
                // so we only run an adjustment if you want:
                $targetQty = $request->has('stock_qty') ? (int) ($data['stock_qty'] ?? 0) : null;

                // ❗ remove stock_qty from payload (stock service will handle)
                $payload = Arr::except($data, ['stock_qty']);
                $payload['business_id'] = $bid;
                $payload['is_active']   = $request->boolean('is_active');

                // ✅ if service => nullify product-only fields
                if ($newType === 'service') {
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

                // ✅ item table stock_qty stays 0 (same as store)
                $payload['stock_qty'] = 0;

                $item->update($payload);

                /**
                 * ✅ Optional stock adjustment on update:
                 * If you want "stock_qty" to mean opening/adjust stock to EXACT qty:
                 * You need a method in StockService like setOnHand($item, $targetQty, $note)
                 *
                 * If you DON'T have it, comment this block.
                 */
                if ($newType === 'product' && $targetQty !== null) {
                    // Implement in StockService:
                    // $stock->setOnHand($item, $targetQty, 'Stock adjusted (item update)');
                    //
                    // OR if you only have recordOpening(), you can use it only when there is no stock history.
                }

                $item->load('category:id,name');

                return response()->json([
                    'ok'   => true,
                    'msg'  => 'Item updated successfully.',
                    'item' => $item,
                ], 200);
            });

        } catch (ValidationException $e) {
            return response()->json([
                'ok' => false,
                'msg' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'msg' => 'Server error while updating item.',
                // 'error' => $e->getMessage(),
            ], 500);
        }
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




    public function index1(Request $request)
    {
        $items = Item::query()
            ->with('category:id,name')
            ->where('items.business_id', 1)
            ->orderByDesc('items.id')
            ->get();

        return response()->json([
            'ok'   => true,
            'data' => $items,
        ]);
    }
}
