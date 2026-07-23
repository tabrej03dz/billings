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
    // public function index(Request $request)
    // {
    //     $q           = trim($request->get('q', ''));
    //     $category_id = $request->integer('category_id');
    //     $active      = $request->get('active'); // '1' | '0' | null

    //     $items = Item::query()
    //         ->with('category:id,name') // eager-load for table
    //         ->when($q !== '', function ($w) use ($q) {
    //             $w->where(function ($s) use ($q) {
    //                 // $s->where('name', 'like', "%{$q}%")
    //                 //     ->orWhere('sku', 'like', "%{$q}%")
    //                 //     ->orWhere('description', 'like', "%{$q}%");
    //                 $s->where('name', 'like', "%{$q}%")
    //                 ->orWhere('sku', 'like', "%{$q}%")
    //                 ->orWhere('barcode', 'like', "%{$q}%")
    //                 ->orWhere('description', 'like', "%{$q}%");
    //             });
    //         })
    //         ->when($category_id, fn($w) => $w->where('category_id', $category_id))
    //         ->when($active !== null && $active !== '', fn($w) => $w->where('is_active', (bool)$active))
    //         ->latest()
    //         ->paginate(15)
    //         ->withQueryString();

    //     // current business ki categories (BelongsToBusiness scope ke sath)
    //     $categories = Category::orderBy('name')->get(['id','name']);

    //     return view('items.index', compact('items', 'categories', 'q', 'category_id', 'active'));
    // }


    public function index(Request $request)
    {
        $user = $request->user();

        /*
        |--------------------------------------------------------------------------
        | Active business resolve
        |--------------------------------------------------------------------------
        */
        $businessId = $user->current_business_id
            ?? session('active_business_id');

        if (!$businessId) {
            $businessId = $user->businesses()
                ->pluck('businesses.id')
                ->first();
        }

        if (!$businessId) {
            return back()->withErrors([
                'business' => 'Active business select/attach नहीं है.',
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | Filters
        |--------------------------------------------------------------------------
        */
        $q = trim((string) $request->get('q', ''));

        $categoryId = $request->integer('category_id');

        $active = $request->get('active');

        /*
        |--------------------------------------------------------------------------
        | Items query
        |--------------------------------------------------------------------------
        */
        $itemsQuery = Item::query()
            ->where('business_id', $businessId)
            ->with('category:id,name')
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($searchQuery) use ($q) {
                    $searchQuery
                        ->where('name', 'like', "%{$q}%")
                        ->orWhere('sku', 'like', "%{$q}%")
                        ->orWhere('barcode', 'like', "%{$q}%")
                        ->orWhere('description', 'like', "%{$q}%");
                });
            })
            ->when(
                $categoryId,
                fn ($query) => $query->where(
                    'category_id',
                    $categoryId
                )
            )
            ->when(
                $active !== null && $active !== '',
                fn ($query) => $query->where(
                    'is_active',
                    (int) $active
                )
            );

        $items = $itemsQuery
            ->latest()
            ->paginate(15)
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Categories
        |--------------------------------------------------------------------------
        */
        $categories = Category::query()
            ->where('business_id', $businessId)
            ->orderBy('name')
            ->get([
                'id',
                'name',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Suggestion visibility
        |--------------------------------------------------------------------------
        | Current business me 5 se kam items hone tak guide dikhega.
        */
        $currentItemCount = Item::query()
            ->where('business_id', $businessId)
            ->count();

        $showItemSuggestion = $currentItemCount < 5;

        return view('items.index', [
            'items'              => $items,
            'categories'         => $categories,
            'q'                  => $q,
            'category_id'        => $categoryId,
            'active'             => $active,

            'currentItemCount'   => $currentItemCount,
            'showItemSuggestion' => $showItemSuggestion,
            'activeBusinessId'   => $businessId,
        ]);
    }

    // public function create()
    // {
    //     $categories = Category::orderBy('name')->get(['id','name']);
    //     return view('items.create', compact('categories'));
    // }

    public function create()
    {
        $categories = Category::orderBy('name')->get(['id', 'name']);

        $businessId = session('active_business_id');

        $business = \App\Models\Business::with('businessType.itemFields')
            ->find($businessId);

        $allowedFields = [];

        if ($business && $business->businessType) {
            $allowedFields = $business->businessType->itemFields
                ->pluck('field_name')
                ->toArray();
        }
        return view('items.create', compact('categories', 'allowedFields'));
    }


    // public function store(Request $request, StockService $stock)
    // {
    //     $bid = $request->user()->current_business_id ?? session('active_business_id');

    //     if (!$bid) {
    //         $bid = $request->user()->businesses()->pluck('businesses.id')->first();
    //     }

    //     abort_unless($bid, 422, 'Active business not found.');

    //     $business = \App\Models\Business::with('businessType.itemFields')->find($bid);

    //     $allowedFields = [];
    //     $requiredFields = [];

    //     if ($business && $business->businessType) {
    //         $allowedFields = $business->businessType->itemFields
    //             ->pluck('field_name')
    //             ->toArray();

    //         $requiredFields = $business->businessType->itemFields
    //             ->where('is_required', 1)
    //             ->pluck('field_name')
    //             ->toArray();
    //     }

    //     if (empty($allowedFields)) {
    //         $allowedFields = [
    //             'name',
    //             'sku',
    //             'category_id',
    //             'type',
    //             'sac',
    //             'description',
    //             'price',
    //             'cost_price',
    //             'making_charge_type',
    //             'making_charge',
    //             'stock_qty',
    //             'unit',
    //             'tax_rate',
    //             'is_active',
    //             'metal_type',
    //             'purity',
    //             'gross_weight',
    //             'metal_weight',
    //             'stone_weight',
    //             'stone_charges',
    //             'gold_weight',
    //             'gold_purity',
    //             'silver_weight',
    //             'silver_purity',
    //             'diamond_weight',
    //             'diamond_charges',
    //         ];
    //     }

    //     $isAllowed = fn ($field) => in_array($field, $allowedFields);
    //     $isRequired = fn ($field) => in_array($field, $requiredFields) ? 'required' : 'nullable';

    //     $rules = [];

    //     if ($isAllowed('name')) {
    //         $rules['name'] = [$isRequired('name'), 'string', 'max:255'];
    //     }

    //     if ($isAllowed('sku')) {
    //         $rules['sku'] = [
    //             'nullable',
    //             'string',
    //             'max:100',
    //             Rule::unique('items', 'sku')->where(fn ($q) => $q->where('business_id', $bid)),
    //         ];
    //     }

    //     if ($isAllowed('category_id')) {
    //         $rules['category_id'] = [$isRequired('category_id'), 'integer'];
    //     }

    //     if ($isAllowed('type')) {
    //         $rules['type'] = [$isRequired('type'), Rule::in(['product', 'service'])];
    //     }

    //     if ($isAllowed('sac')) {
    //         $rules['sac'] = [$isRequired('sac'), 'string', 'max:32'];
    //     }

    //     if ($isAllowed('description')) {
    //         $rules['description'] = ['nullable', 'string', 'max:2000'];
    //     }

    //     if ($isAllowed('price')) {
    //         $rules['price'] = [$isRequired('price'), 'numeric', 'min:0'];
    //     }

    //     if ($isAllowed('cost_price')) {
    //         $rules['cost_price'] = ['nullable', 'numeric', 'min:0'];
    //     }

    //     if ($isAllowed('making_charge')) {
    //         $rules['making_charge_type'] = ['nullable', Rule::in(['fixed', 'percent'])];

    //         $rules['making_charge'] = [
    //             'nullable',
    //             'numeric',
    //             'min:0',
    //             Rule::when(
    //                 $request->input('making_charge_type', 'percent') === 'percent',
    //                 ['max:100']
    //             ),
    //         ];
    //     }

    //     if ($isAllowed('stock_qty')) {
    //         $rules['stock_qty'] = [$isRequired('stock_qty'), 'integer', 'min:0'];
    //     }

    //     if ($isAllowed('unit')) {
    //         $rules['unit'] = ['nullable', 'string', 'max:50'];
    //     }

    //     if ($isAllowed('tax_rate')) {
    //         $rules['tax_rate'] = [$isRequired('tax_rate'), 'numeric', 'min:0', 'max:100'];
    //     }

    //     if ($isAllowed('is_active')) {
    //         $rules['is_active'] = ['nullable'];
    //     }

    //     if ($isAllowed('metal_type')) {
    //         $rules['metal_type'] = ['nullable', Rule::in(['gold', 'silver', 'other'])];
    //     }

    //     if ($isAllowed('purity')) {
    //         $rules['purity'] = ['nullable', 'string', 'max:50'];
    //     }

    //     if ($isAllowed('gross_weight')) {
    //         $rules['gross_weight'] = ['nullable', 'numeric', 'min:0'];
    //     }

    //     if ($isAllowed('metal_weight')) {
    //         $rules['metal_weight'] = ['nullable', 'numeric', 'min:0'];
    //     }

    //     if ($isAllowed('stone_weight')) {
    //         $rules['stone_weight'] = ['nullable', 'numeric', 'min:0'];
    //     }

    //     if ($isAllowed('stone_charges')) {
    //         $rules['stone_charges'] = ['nullable', 'numeric', 'min:0'];
    //     }

    //     if ($isAllowed('gold_weight')) {
    //         $rules['gold_weight'] = ['nullable', 'numeric', 'min:0'];
    //     }

    //     if ($isAllowed('gold_purity')) {
    //         $rules['gold_purity'] = ['nullable', 'string', 'max:50'];
    //     }

    //     if ($isAllowed('silver_weight')) {
    //         $rules['silver_weight'] = ['nullable', 'numeric', 'min:0'];
    //     }

    //     if ($isAllowed('silver_purity')) {
    //         $rules['silver_purity'] = ['nullable', 'string', 'max:50'];
    //     }

    //     if ($isAllowed('diamond_weight')) {
    //         $rules['diamond_weight'] = ['nullable', 'numeric', 'min:0'];
    //     }

    //     if ($isAllowed('diamond_charges')) {
    //         $rules['diamond_charges'] = ['nullable', 'numeric', 'min:0'];
    //     }

    //     $data = $request->validate($rules);

    //     if (!empty($data['category_id'])) {
    //         $ok = Category::where('id', $data['category_id'])
    //             ->where('business_id', $bid)
    //             ->exists();

    //         abort_unless($ok, 422, 'Invalid category for this business.');
    //     }

    //     $openingQty = (int) ($data['stock_qty'] ?? 0);

    //     $payload = Arr::except($data, ['stock_qty']);

    //     $payload['making_charge_type'] = $request->input('making_charge_type', 'percent');

    //     $payload['business_id'] = $bid;

    //     $payload['is_active'] = $request->has('is_active')
    //         ? $request->boolean('is_active')
    //         : true;

    //     $payload['stock_qty'] = 0;

    //     $allItemFields = [
    //         'name',
    //         'sku',
    //         'category_id',
    //         'type',
    //         'sac',
    //         'description',
    //         'price',
    //         'cost_price',
    //         'making_charge_type',
    //         'making_charge',
    //         'stock_qty',
    //         'unit',
    //         'tax_rate',
    //         'is_active',
    //         'metal_type',
    //         'purity',
    //         'gross_weight',
    //         'metal_weight',
    //         'stone_weight',
    //         'stone_charges',
    //         'gold_weight',
    //         'gold_purity',
    //         'silver_weight',
    //         'silver_purity',
    //         'diamond_weight',
    //         'diamond_charges',
    //     ];

    //     foreach ($allItemFields as $field) {
    //         if (
    //             !in_array($field, $allowedFields)
    //             && $field !== 'stock_qty'
    //             && $field !== 'is_active'
    //             && $field !== 'making_charge_type'
    //         ) {
    //             $payload[$field] = null;
    //         }
    //     }

    //     $item = Item::create($payload);

    //     if ($openingQty > 0) {
    //         $stock->recordOpening($item, $openingQty, 'Opening stock (item create)');
    //     }

    //     return redirect()
    //         ->route('items.index')
    //         ->with('success', 'Item created successfully.');
    // }

    public function store(Request $request, StockService $stock)
    {
        $bid = $request->user()->current_business_id ?? session('active_business_id');

        if (!$bid) {
            $bid = $request->user()->businesses()->pluck('businesses.id')->first();
        }

        abort_unless($bid, 422, 'Active business not found.');

        $business = \App\Models\Business::with('businessType.itemFields')->find($bid);

        $allowedFields = [];
        $requiredFields = [];

        if ($business && $business->businessType) {
            $allowedFields = $business->businessType->itemFields
                ->pluck('field_name')
                ->toArray();

            $requiredFields = $business->businessType->itemFields
                ->where('is_required', 1)
                ->pluck('field_name')
                ->toArray();
        }

        if (empty($allowedFields)) {
            $allowedFields = [
                'name',
                'sku',
                'category_id',
                'type',
                'sac',
                'description',
                'price',
                'cost_price',
                'making_charge_type',
                'making_charge',
                'stock_qty',
                'unit',
                'tax_rate',
                'is_active',
                'metal_type',
                'purity',
                'gross_weight',
                'metal_weight',
                'stone_weight',
                'stone_charges',
                'gold_weight',
                'gold_purity',
                'silver_weight',
                'silver_purity',
                'diamond_weight',
                'diamond_charges',
            ];
        }

        $isAllowed = fn ($field) => in_array($field, $allowedFields);
        $isRequired = fn ($field) => in_array($field, $requiredFields) ? 'required' : 'nullable';

        $rules = [];

        if ($isAllowed('name')) {
            $rules['name'] = [$isRequired('name'), 'string', 'max:255'];
        }

        if ($isAllowed('sku')) {
            $rules['sku'] = [
                'nullable',
                'string',
                'max:100',
                Rule::unique('items', 'sku')->where(fn ($q) => $q->where('business_id', $bid)),
            ];
        }

        if ($isAllowed('category_id')) {
            $rules['category_id'] = [$isRequired('category_id'), 'integer'];
        }

        if ($isAllowed('type')) {
            $rules['type'] = [$isRequired('type'), Rule::in(['product', 'service'])];
        }

        if ($isAllowed('sac')) {
            $rules['sac'] = [$isRequired('sac'), 'string', 'max:32'];
        }

        if ($isAllowed('description')) {
            $rules['description'] = ['nullable', 'string', 'max:2000'];
        }

        if ($isAllowed('price')) {
            $rules['price'] = [$isRequired('price'), 'numeric', 'min:0'];
        }

        if ($isAllowed('cost_price')) {
            $rules['cost_price'] = ['nullable', 'numeric', 'min:0'];
        }

        if ($isAllowed('making_charge')) {
            $rules['making_charge_type'] = [
                'nullable',
                Rule::in([
                    'percentage',
                    'fixed',
                    'per_gram',
                    'per_product',
                ]),
            ];

            $rules['making_charge'] = [
                'nullable',
                'numeric',
                'min:0',
                Rule::when(
                    $request->input('making_charge_type', 'percentage') === 'percentage',
                    ['max:100']
                ),
            ];
        }

        if ($isAllowed('stock_qty')) {
            $rules['stock_qty'] = [$isRequired('stock_qty'), 'integer', 'min:0'];
        }

        if ($isAllowed('unit')) {
            $rules['unit'] = ['nullable', 'string', 'max:50'];
        }

        if ($isAllowed('tax_rate')) {
            $rules['tax_rate'] = [$isRequired('tax_rate'), 'numeric', 'min:0', 'max:100'];
        }

        if ($isAllowed('is_active')) {
            $rules['is_active'] = ['nullable'];
        }

        if ($isAllowed('metal_type')) {
            $rules['metal_type'] = ['nullable', Rule::in(['gold', 'silver', 'other'])];
        }

        if ($isAllowed('purity')) {
            $rules['purity'] = ['nullable', 'string', 'max:50'];
        }

        if ($isAllowed('gross_weight')) {
            $rules['gross_weight'] = ['nullable', 'numeric', 'min:0'];
        }

        if ($isAllowed('metal_weight')) {
            $rules['metal_weight'] = ['nullable', 'numeric', 'min:0'];
        }

        if ($isAllowed('stone_weight')) {
            $rules['stone_weight'] = ['nullable', 'numeric', 'min:0'];
        }

        if ($isAllowed('stone_charges')) {
            $rules['stone_charges'] = ['nullable', 'numeric', 'min:0'];
        }

        if ($isAllowed('gold_weight')) {
            $rules['gold_weight'] = ['nullable', 'numeric', 'min:0'];
        }

        if ($isAllowed('gold_purity')) {
            $rules['gold_purity'] = ['nullable', 'string', 'max:50'];
        }

        if ($isAllowed('silver_weight')) {
            $rules['silver_weight'] = ['nullable', 'numeric', 'min:0'];
        }

        if ($isAllowed('silver_purity')) {
            $rules['silver_purity'] = ['nullable', 'string', 'max:50'];
        }

        if ($isAllowed('diamond_weight')) {
            $rules['diamond_weight'] = ['nullable', 'numeric', 'min:0'];
        }

        if ($isAllowed('diamond_charges')) {
            $rules['diamond_charges'] = ['nullable', 'numeric', 'min:0'];
        }

        $data = $request->validate($rules);

        if (!empty($data['category_id'])) {
            $ok = Category::where('id', $data['category_id'])
                ->where('business_id', $bid)
                ->exists();

            abort_unless($ok, 422, 'Invalid category for this business.');
        }

        $openingQty = (int) ($data['stock_qty'] ?? 0);

        $payload = Arr::except($data, ['stock_qty']);

        $payload['making_charge_type'] = $request->input('making_charge_type', 'percentage');

        $payload['business_id'] = $bid;

        $payload['is_active'] = $request->has('is_active')
            ? $request->boolean('is_active')
            : true;

        $payload['stock_qty'] = 0;

        $allItemFields = [
            'name',
            'sku',
            'category_id',
            'type',
            'sac',
            'description',
            'price',
            'cost_price',
            'making_charge_type',
            'making_charge',
            'stock_qty',
            'unit',
            'tax_rate',
            'is_active',
            'metal_type',
            'purity',
            'gross_weight',
            'metal_weight',
            'stone_weight',
            'stone_charges',
            'gold_weight',
            'gold_purity',
            'silver_weight',
            'silver_purity',
            'diamond_weight',
            'diamond_charges',
        ];

        foreach ($allItemFields as $field) {
            if (
                !in_array($field, $allowedFields)
                && $field !== 'stock_qty'
                && $field !== 'is_active'
                && $field !== 'making_charge_type'
            ) {
                $payload[$field] = null;
            }
        }

        $item = Item::create($payload);

        if ($openingQty > 0) {
            $stock->recordOpening($item, $openingQty, 'Opening stock (item create)');
        }

        return redirect()
            ->route('items.index')
            ->with('success', 'Item created successfully.');
    }


    // public function edit(Item $item)
    // {
    //     // Global scope ensures this $item belongs to active business
    //     $categories = Category::orderBy('name')->get(['id','name']);
    //     return view('items.edit', compact('item','categories'));
    // }

    public function edit(Item $item)
    {
        $categories = Category::orderBy('name')->get(['id', 'name']);

        $businessId = session('active_business_id');

        if (!$businessId) {
            $businessId = auth()->user()->current_business_id
                ?? auth()->user()->businesses()->pluck('businesses.id')->first();
        }

        abort_unless($businessId, 422, 'Active business not found.');

        abort_unless((int) $item->business_id === (int) $businessId, 403, 'Unauthorized item.');

        $business = \App\Models\Business::with('businessType.itemFields')
            ->find($businessId);

        $allowedFields = [];

        if ($business && $business->businessType) {
            $allowedFields = $business->businessType->itemFields
                ->pluck('field_name')
                ->toArray();
        }

        return view('items.edit', compact('item', 'categories', 'allowedFields'));
    }

    // public function update(Request $request, Item $item, StockService $stock)
    // {
    //     try {
    //         $bid = $request->user()->current_business_id ?? session('active_business_id');

    //         if (!$bid) {
    //             $bid = $request->user()->businesses()->pluck('businesses.id')->first();
    //         }

    //         abort_unless($bid, 422, 'Active business not found.');
    //         abort_unless((int) $item->business_id === (int) $bid, 403, 'Unauthorized item.');

    //         $data = $request->validate([
    //             'name' => ['required', 'string', 'max:255'],

    //             'sku' => [
    //                 'nullable',
    //                 'string',
    //                 'max:100',
    //                 Rule::unique('items', 'sku')
    //                     ->ignore($item->id)
    //                     ->where(fn ($q) => $q->where('business_id', $bid)),
    //             ],

    //             'category_id' => ['nullable', 'integer'],
    //             'type'        => ['required', Rule::in(['product', 'service'])],

    //             'sac'         => ['nullable', 'string', 'max:32', 'required_if:type,service'],
    //             'description' => ['nullable', 'string', 'max:2000'],

    //             'price'         => ['nullable', 'numeric', 'min:0'],
    //             'cost_price'    => ['nullable', 'numeric', 'min:0'],
    //             'making_charge' => ['nullable', 'numeric', 'min:0', 'max:100'],

    //             'stock_qty' => ['nullable', 'integer', 'min:0', 'required_if:type,product'],
    //             'unit'      => ['nullable', 'string', 'max:50'],

    //             'tax_rate'  => ['required', 'numeric', 'min:0', 'max:100'],
    //             'is_active' => ['nullable'],

    //             'metal_type'    => ['nullable', Rule::in(['gold', 'silver', 'other'])],
    //             'purity'        => ['nullable', 'string', 'max:50'],

    //             'gross_weight'  => ['nullable', 'numeric', 'min:0'],
    //             'metal_weight'  => ['nullable', 'numeric', 'min:0'],
    //             'stone_weight'  => ['nullable', 'numeric', 'min:0'],
    //             'stone_charges' => ['nullable', 'numeric', 'min:0'],

    //             'gold_weight'     => ['nullable', 'numeric', 'min:0'],
    //             'gold_purity'     => ['nullable', 'string', 'max:50'],
    //             'silver_weight'   => ['nullable', 'numeric', 'min:0'],
    //             'silver_purity'   => ['nullable', 'string', 'max:50'],
    //             'diamond_weight'  => ['nullable', 'numeric', 'min:0'],
    //             'diamond_charges' => ['nullable', 'numeric', 'min:0'],
    //         ], [
    //             'sac.required_if'       => 'SAC Code is required for Service.',
    //             'stock_qty.required_if' => 'Stock Qty is required for Product.',
    //         ]);

    //         if (!empty($data['category_id'])) {
    //             $categoryBelongsToBusiness = Category::where('id', $data['category_id'])
    //                 ->where('business_id', $bid)
    //                 ->exists();

    //             if (! $categoryBelongsToBusiness) {
    //                 return back()
    //                     ->withErrors(['category_id' => 'Selected category does not belong to active business.'])
    //                     ->withInput();
    //             }
    //         }

    //         DB::beginTransaction();

    //         $type = $data['type'];
    //         $finalQty = $type === 'product' ? (int) ($data['stock_qty'] ?? 0) : 0;

    //         $payload = Arr::except($data, ['stock_qty']);
    //         $payload['is_active'] = $request->boolean('is_active');

    //         if ($type === 'service') {
    //             $payload['making_charge'] = null;
    //             $payload['unit']          = null;

    //             $payload['metal_type']    = null;
    //             $payload['purity']        = null;
    //             $payload['gross_weight']  = null;
    //             $payload['metal_weight']  = null;
    //             $payload['stone_weight']  = null;
    //             $payload['stone_charges'] = null;
    //             $payload['gold_weight']   = null;
    //             $payload['gold_purity']   = null;
    //             $payload['silver_weight'] = null;
    //             $payload['silver_purity'] = null;
    //             $payload['diamond_weight'] = null;
    //             $payload['diamond_charges'] = null;
    //         } else {
    //             $payload['sac'] = null;
    //         }

    //         $item->update($payload);

    //         if ($type === 'product') {
    //             $stock->setStockTo($item, $finalQty, 'Stock updated from item edit');
    //         }

    //         DB::commit();

    //         return redirect()
    //             ->route('items.index')
    //             ->with('success', 'Item updated successfully.');
    //     } catch (\Throwable $e) {
    //         DB::rollBack();

    //         Log::error('Item update failed', [
    //             'item_id' => $item->id ?? null,
    //             'user_id' => auth()->id(),
    //             'message' => $e->getMessage(),
    //             'line'    => $e->getLine(),
    //             'file'    => $e->getFile(),
    //         ]);

    //         return back()
    //             ->withErrors(['general' => 'Update failed: ' . $e->getMessage()])
    //             ->withInput();
    //     }
    // }


    // public function update(Request $request, Item $item, StockService $stock)
    // {
    //     try {
    //         $bid = $request->user()->current_business_id ?? session('active_business_id');

    //         if (!$bid) {
    //             $bid = $request->user()->businesses()->pluck('businesses.id')->first();
    //         }

    //         abort_unless($bid, 422, 'Active business not found.');
    //         abort_unless((int) $item->business_id === (int) $bid, 403, 'Unauthorized item.');

    //         $data = $request->validate([
    //             'name' => ['required', 'string', 'max:255'],

    //             'sku' => [
    //                 'nullable',
    //                 'string',
    //                 'max:100',
    //                 Rule::unique('items', 'sku')
    //                     ->ignore($item->id)
    //                     ->where(fn ($q) => $q->where('business_id', $bid)),
    //             ],

    //             'category_id' => ['nullable', 'integer'],
    //             'type'        => ['required', Rule::in(['product', 'service'])],

    //             'sac'         => ['nullable', 'string', 'max:32'],
    //             'description' => ['nullable', 'string', 'max:2000'],

    //             'price'      => ['nullable', 'numeric', 'min:0'],
    //             'cost_price' => ['nullable', 'numeric', 'min:0'],

    //             'making_charge_type' => ['nullable', Rule::in(['fixed', 'percentage'])],
    //             'making_charge' => [
    //                 'nullable',
    //                 'numeric',
    //                 'min:0',
    //                 Rule::when(
    //                     $request->input('making_charge_type', 'percent') === 'percent',
    //                     ['max:100']
    //                 ),
    //             ],

    //             'stock_qty' => ['nullable', 'integer', 'min:0'],
    //             'unit'      => ['nullable', 'string', 'max:50'],

    //             'tax_rate'  => ['required', 'numeric', 'min:0', 'max:100'],
    //             'is_active' => ['nullable'],

    //             'metal_type' => ['nullable', Rule::in(['gold', 'silver', 'other'])],
    //             'purity'     => ['nullable', 'string', 'max:50'],

    //             'gross_weight'  => ['nullable', 'numeric', 'min:0'],
    //             'metal_weight'  => ['nullable', 'numeric', 'min:0'],
    //             'stone_weight'  => ['nullable', 'numeric', 'min:0'],
    //             'stone_charges' => ['nullable', 'numeric', 'min:0'],

    //             'gold_weight'     => ['nullable', 'numeric', 'min:0'],
    //             'gold_purity'     => ['nullable', 'string', 'max:50'],
    //             'silver_weight'   => ['nullable', 'numeric', 'min:0'],
    //             'silver_purity'   => ['nullable', 'string', 'max:50'],
    //             'diamond_weight'  => ['nullable', 'numeric', 'min:0'],
    //             'diamond_charges' => ['nullable', 'numeric', 'min:0'],
    //         ]);

    //         if (!empty($data['category_id'])) {
    //             $categoryBelongsToBusiness = Category::where('id', $data['category_id'])
    //                 ->where('business_id', $bid)
    //                 ->exists();

    //             if (!$categoryBelongsToBusiness) {
    //                 return back()
    //                     ->withErrors(['category_id' => 'Selected category does not belong to active business.'])
    //                     ->withInput();
    //             }
    //         }

    //         DB::beginTransaction();

    //         $finalQty = (int) ($data['stock_qty'] ?? 0);

    //         $payload = Arr::except($data, ['stock_qty']);

    //         $payload['making_charge_type'] = $request->input('making_charge_type', 'percentage');

    //         $payload['is_active'] = $request->has('is_active')
    //             ? $request->boolean('is_active')
    //             : false;

    //         $item->update($payload);

    //         $stock->setStockTo($item, $finalQty, 'Stock updated from item edit');

    //         DB::commit();

    //         return redirect()
    //             ->route('items.index')
    //             ->with('success', 'Item updated successfully.');

    //     } catch (\Throwable $e) {
    //         DB::rollBack();

    //         Log::error('Item update failed', [
    //             'item_id' => $item->id ?? null,
    //             'user_id' => auth()->id(),
    //             'message' => $e->getMessage(),
    //             'line'    => $e->getLine(),
    //             'file'    => $e->getFile(),
    //         ]);

    //         return back()
    //             ->withErrors(['general' => 'Update failed: ' . $e->getMessage()])
    //             ->withInput();
    //     }
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

                'sac'         => ['nullable', 'string', 'max:32'],
                'description' => ['nullable', 'string', 'max:2000'],

                'price'      => ['nullable', 'numeric', 'min:0'],
                'cost_price' => ['nullable', 'numeric', 'min:0'],

                'making_charge_type' => [
                    'nullable',
                    Rule::in([
                        'percentage',
                        'fixed',
                        'per_gram',
                        'per_product',
                    ]),
                ],

                'making_charge' => [
                    'nullable',
                    'numeric',
                    'min:0',
                    Rule::when(
                        $request->input('making_charge_type', 'percentage') === 'percentage',
                        ['max:100']
                    ),
                ],

                'stock_qty' => ['nullable', 'integer', 'min:0'],
                'unit'      => ['nullable', 'string', 'max:50'],

                'tax_rate'  => ['required', 'numeric', 'min:0', 'max:100'],
                'is_active' => ['nullable'],

                'metal_type' => ['nullable', Rule::in(['gold', 'silver', 'other'])],
                'purity'     => ['nullable', 'string', 'max:50'],

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
                $categoryBelongsToBusiness = Category::where('id', $data['category_id'])
                    ->where('business_id', $bid)
                    ->exists();

                if (!$categoryBelongsToBusiness) {
                    return back()
                        ->withErrors(['category_id' => 'Selected category does not belong to active business.'])
                        ->withInput();
                }
            }

            DB::beginTransaction();

            $finalQty = (int) ($data['stock_qty'] ?? 0);

            $payload = Arr::except($data, ['stock_qty']);

            $payload['making_charge_type'] = $request->input('making_charge_type', 'percentage');

            $payload['is_active'] = $request->has('is_active')
                ? $request->boolean('is_active')
                : false;

            $item->update($payload);

            $stock->setStockTo($item, $finalQty, 'Stock updated from item edit');

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


    // public function storeAjax(\Illuminate\Http\Request $r)
    // {
    //     $data = $r->validate([
    //         'type' => ['required','in:product,service'],
    //         'name' => ['required','string','max:255'],
    //         'sku'  => ['nullable','string','max:100'],
    //         'description' => ['nullable','string','max:500'],

    //         'tax_rate' => ['nullable','numeric','min:0','max:100'],
    //         'hsn' => ['nullable','string','max:50'],
    //         'sac' => ['nullable','string','max:50'],

    //         // service
    //         'price' => ['nullable','numeric','min:0'],

    //         // product
    //         'making_charge' => ['nullable','numeric','min:0'],
    //         'gold_weight' => ['nullable','numeric','min:0'],
    //         'gold_purity' => ['nullable','string','max:50'],
    //         'silver_weight' => ['nullable','numeric','min:0'],
    //         'silver_purity' => ['nullable','string','max:50'],
    //         'stone_weight' => ['nullable','numeric','min:0'],
    //         'diamond_weight' => ['nullable','numeric','min:0'],
    //     ]);

    //     $bid = $r->user()->current_business_id ?? session('active_business_id');

    //     $item = \App\Models\Item::create([
    //         'business_id' => $bid,
    //         'type' => $data['type'],
    //         'name' => $data['name'],
    //         'sku'  => $data['sku'] ?? null,
    //         'description' => $data['description'] ?? null,

    //         'tax_rate' => (float)($data['tax_rate'] ?? 0),
    //         'hsn' => $data['hsn'] ?? null,
    //         'sac' => $data['sac'] ?? null,

    //         // service price (used in your pickItem for service_rate)
    //         'price' => (float)($data['price'] ?? 0),

    //         // product fields
    //         'making_charge' => (float)($data['making_charge'] ?? 0),
    //         'gold_weight' => (float)($data['gold_weight'] ?? 0),
    //         'gold_purity' => $data['gold_purity'] ?? null,
    //         'silver_weight' => (float)($data['silver_weight'] ?? 0),
    //         'silver_purity' => $data['silver_purity'] ?? null,
    //         'stone_weight' => (float)($data['stone_weight'] ?? 0),
    //         'diamond_weight' => (float)($data['diamond_weight'] ?? 0),
    //     ]);

    //     // ✅ return in same shape your itemsJson expects
    //     return response()->json([
    //         'item' => $item->only([
    //             'id','type','name','sku','description','tax_rate','hsn','sac','price',
    //             'making_charge','gold_weight','gold_purity','silver_weight','silver_purity',
    //             'stone_weight','diamond_weight'
    //         ])
    //     ]);
    // }

    public function storeAjax(Request $request)
    {
        $bid = $request->user()->current_business_id ?? session('active_business_id');

        if (!$bid) {
            $bid = $request->user()->businesses()->pluck('businesses.id')->first();
        }

        abort_unless($bid, 422, 'Active business not found.');

        $data = $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'sku'             => ['nullable', 'string', 'max:100'],
            'category_id'     => ['required', 'integer', Rule::exists('categories', 'id')->where(fn ($q) => $q->where('business_id', $bid))],
            'type'            => ['required', Rule::in(['product', 'service'])],
            'description'     => ['nullable', 'string'],
            'tax_rate'        => ['nullable', 'numeric', 'min:0', 'max:100'],
            'hsn'             => ['nullable', 'string', 'max:50'],
            'sac'             => ['nullable', 'string', 'max:50'],
            'price'           => ['nullable', 'numeric', 'min:0'],
            'making_charge'   => ['nullable', 'numeric', 'min:0'],
            'gold_weight'     => ['nullable', 'numeric', 'min:0'],
            'gold_purity'     => ['nullable', 'string', 'max:50'],
            'silver_weight'   => ['nullable', 'numeric', 'min:0'],
            'silver_purity'   => ['nullable', 'string', 'max:50'],
            'stone_weight'    => ['nullable', 'numeric', 'min:0'],
            'diamond_weight'  => ['nullable', 'numeric', 'min:0'],
            'is_save'         => ['nullable', 'boolean'],
        ]);

        if ($data['type'] === 'service' && (float) ($data['price'] ?? 0) <= 0) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors'  => ['price' => ['Service price is required.']],
            ], 422);
        }

        $item = Item::create([
            'business_id'      => $bid,
            'category_id'      => $data['category_id'],
            'name'             => $data['name'],
            'sku'              => $data['sku'] ?? null,
            'type'             => $data['type'],
            'description'      => $data['description'] ?? null,
            'tax_rate'         => $data['tax_rate'] ?? 0,
            'hsn'              => $data['hsn'] ?? null,
            'sac'              => $data['sac'] ?? null,
            'price'            => $data['price'] ?? 0,
            'making_charge'    => $data['making_charge'] ?? 0,
            'gold_weight'      => $data['gold_weight'] ?? 0,
            'gold_purity'      => $data['gold_purity'] ?? null,
            'silver_weight'    => $data['silver_weight'] ?? 0,
            'silver_purity'    => $data['silver_purity'] ?? null,
            'stone_weight'     => $data['stone_weight'] ?? 0,
            'diamond_weight'   => $data['diamond_weight'] ?? 0,
            'is_active'        => true,
            'is_save'          => (bool) ($data['is_save'] ?? false),
        ]);

        return response()->json([
            'message' => 'Item created successfully.',
            'item'    => $item,
        ]);
    }

}
