<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Category;
use App\Models\Item;
use App\Models\Unit;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class ItemController extends Controller
{

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
        $q = trim((string) $request->query('q', ''));

        $categoryId = $request->integer('category_id');

        $active = $request->query('active');

        $stockStatus = strtolower(
            trim((string) $request->query('stock_status', ''))
        );

        $allowedStockStatuses = [
            'in_stock',
            'low_stock',
            'out_of_stock',
        ];

        if (!in_array($stockStatus, $allowedStockStatuses, true)) {
            $stockStatus = '';
        }

        /*
        |--------------------------------------------------------------------------
        | Common low-stock limit
        |--------------------------------------------------------------------------
        */
        $lowStockLimit = 5;

        /*
        |--------------------------------------------------------------------------
        | Items query
        |--------------------------------------------------------------------------
        */
        $itemsQuery = Item::query()
            ->where('business_id', $businessId)
            ->with('category:id,name')

            /*
            |--------------------------------------------------------------------------
            | Search
            |--------------------------------------------------------------------------
            */
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($searchQuery) use ($q) {
                    $searchQuery
                        ->where('name', 'like', "%{$q}%")
                        ->orWhere('sku', 'like', "%{$q}%")
                        ->orWhere('barcode', 'like', "%{$q}%")
                        ->orWhere('description', 'like', "%{$q}%");
                });
            })

            /*
            |--------------------------------------------------------------------------
            | Category
            |--------------------------------------------------------------------------
            */
            ->when(
                $categoryId > 0,
                fn ($query) => $query->where(
                    'category_id',
                    $categoryId
                )
            )

            /*
            |--------------------------------------------------------------------------
            | Active status
            |--------------------------------------------------------------------------
            */
            ->when(
                $active !== null && $active !== '',
                fn ($query) => $query->where(
                    'is_active',
                    (int) $active
                )
            )

            /*
            |--------------------------------------------------------------------------
            | Healthy stock
            |--------------------------------------------------------------------------
            */
            ->when(
                $stockStatus === 'in_stock',
                fn ($query) => $query
                    ->where('stock_qty', '>', $lowStockLimit)
            )

            /*
            |--------------------------------------------------------------------------
            | Low stock
            |--------------------------------------------------------------------------
            */
            ->when(
                $stockStatus === 'low_stock',
                fn ($query) => $query
                    ->where('stock_qty', '>', 0)
                    ->where('stock_qty', '<=', $lowStockLimit)
            )

            /*
            |--------------------------------------------------------------------------
            | Out of stock
            |--------------------------------------------------------------------------
            */
            ->when(
                $stockStatus === 'out_of_stock',
                fn ($query) => $query
                    ->where('stock_qty', '<=', 0)
            );

        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */
        $items = $itemsQuery
            ->orderByDesc('id')
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
            'stockStatus'        => $stockStatus,
            'lowStockLimit'      => $lowStockLimit,
            'currentItemCount'   => $currentItemCount,
            'showItemSuggestion' => $showItemSuggestion,
            'activeBusinessId'   => $businessId,
        ]);
    }

    public function create(Request $request)
    {
        $businessId = session('active_business_id')
            ?? $request->user()?->business_id;

        $categories = Category::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        /*
        * Business global scope ko remove karke:
        *
        * business_id = null       → sabhi businesses me
        * business_id = current ID → sirf current business me
        */
        $units = Unit::query()
            ->withoutGlobalScope('business')
            ->where(function ($query) use ($businessId) {
                $query->whereNull('business_id');

                if ($businessId !== null) {
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

        $business = Business::with('businessType.itemFields')
            ->find($businessId);

        $allowedFields = [];

        if ($business?->businessType) {
            $allowedFields = $business->businessType->itemFields
                ->pluck('field_name')
                ->toArray();
        }

        return view('items.create', compact(
            'categories',
            'units',
            'allowedFields'
        ));
    }


    // public function store(Request $request, StockService $stock)
    // {
    //     $bid = $request->user()->current_business_id
    //         ?? session('active_business_id');

    //     if (!$bid) {
    //         $bid = $request->user()
    //             ->businesses()
    //             ->pluck('businesses.id')
    //             ->first();
    //     }

    //     abort_unless($bid, 422, 'Active business not found.');

    //     $business = Business::with('businessType.itemFields')
    //         ->find($bid);

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

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Default Item Fields
    //     |--------------------------------------------------------------------------
    //     */
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

    //     $isAllowed = fn ($field) => in_array($field, $allowedFields, true);

    //     $isRequired = fn ($field) =>
    //         in_array($field, $requiredFields, true)
    //             ? 'required'
    //             : 'nullable';

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Validation Rules
    //     |--------------------------------------------------------------------------
    //     */
    //     $rules = [];

    //     if ($isAllowed('name')) {
    //         $rules['name'] = [
    //             $isRequired('name'),
    //             'string',
    //             'max:255',
    //         ];
    //     }

    //     if ($isAllowed('sku')) {
    //         $rules['sku'] = [
    //             'nullable',
    //             'string',
    //             'max:100',
    //             Rule::unique('items', 'sku')
    //                 ->where(
    //                     fn ($query) =>
    //                     $query->where('business_id', $bid)
    //                 ),
    //         ];
    //     }

    //     if ($isAllowed('category_id')) {
    //         $rules['category_id'] = [
    //             $isRequired('category_id'),
    //             'integer',
    //         ];
    //     }

    //     if ($isAllowed('type')) {
    //         $rules['type'] = [
    //             $isRequired('type'),
    //             Rule::in([
    //                 'product',
    //                 'service',
    //             ]),
    //         ];
    //     }

    //     if ($isAllowed('sac')) {
    //         $rules['sac'] = [
    //             $isRequired('sac'),
    //             'string',
    //             'max:32',
    //         ];
    //     }

    //     if ($isAllowed('description')) {
    //         $rules['description'] = [
    //             'nullable',
    //             'string',
    //             'max:2000',
    //         ];
    //     }

    //     if ($isAllowed('price')) {
    //         $rules['price'] = [
    //             $isRequired('price'),
    //             'numeric',
    //             'min:0',
    //         ];
    //     }

    //     if ($isAllowed('cost_price')) {
    //         $rules['cost_price'] = [
    //             'nullable',
    //             'numeric',
    //             'min:0',
    //         ];
    //     }

    //     if ($isAllowed('making_charge')) {
    //         $rules['making_charge_type'] = [
    //             'nullable',
    //             Rule::in([
    //                 'percentage',
    //                 'fixed',
    //                 'per_gram',
    //                 'per_product',
    //             ]),
    //         ];

    //         $rules['making_charge'] = [
    //             'nullable',
    //             'numeric',
    //             'min:0',

    //             Rule::when(
    //                 $request->input(
    //                     'making_charge_type',
    //                     'percentage'
    //                 ) === 'percentage',
    //                 ['max:100']
    //             ),
    //         ];
    //     }

    //     if ($isAllowed('stock_qty')) {
    //         $rules['stock_qty'] = [
    //             $isRequired('stock_qty'),
    //             'integer',
    //             'min:0',
    //         ];
    //     }

    //     if ($isAllowed('unit')) {
    //         $rules['unit'] = [
    //             'nullable',
    //             'string',
    //             'max:50',
    //         ];
    //     }

    //     if ($isAllowed('tax_rate')) {
    //         $rules['tax_rate'] = [
    //             $isRequired('tax_rate'),
    //             'numeric',
    //             'min:0',
    //             'max:100',
    //         ];
    //     }

    //     if ($isAllowed('is_active')) {
    //         $rules['is_active'] = [
    //             'nullable',
    //         ];
    //     }

    //     if ($isAllowed('metal_type')) {
    //         $rules['metal_type'] = [
    //             'nullable',
    //             Rule::in([
    //                 'gold',
    //                 'silver',
    //                 'other',
    //             ]),
    //         ];
    //     }

    //     if ($isAllowed('purity')) {
    //         $rules['purity'] = [
    //             'nullable',
    //             'string',
    //             'max:50',
    //         ];
    //     }

    //     if ($isAllowed('gross_weight')) {
    //         $rules['gross_weight'] = [
    //             'nullable',
    //             'numeric',
    //             'min:0',
    //         ];
    //     }

    //     if ($isAllowed('metal_weight')) {
    //         $rules['metal_weight'] = [
    //             'nullable',
    //             'numeric',
    //             'min:0',
    //         ];
    //     }

    //     if ($isAllowed('stone_weight')) {
    //         $rules['stone_weight'] = [
    //             'nullable',
    //             'numeric',
    //             'min:0',
    //         ];
    //     }

    //     if ($isAllowed('stone_charges')) {
    //         $rules['stone_charges'] = [
    //             'nullable',
    //             'numeric',
    //             'min:0',
    //         ];
    //     }

    //     if ($isAllowed('gold_weight')) {
    //         $rules['gold_weight'] = [
    //             'nullable',
    //             'numeric',
    //             'min:0',
    //         ];
    //     }

    //     if ($isAllowed('gold_purity')) {
    //         $rules['gold_purity'] = [
    //             'nullable',
    //             'string',
    //             'max:50',
    //         ];
    //     }

    //     if ($isAllowed('silver_weight')) {
    //         $rules['silver_weight'] = [
    //             'nullable',
    //             'numeric',
    //             'min:0',
    //         ];
    //     }

    //     if ($isAllowed('silver_purity')) {
    //         $rules['silver_purity'] = [
    //             'nullable',
    //             'string',
    //             'max:50',
    //         ];
    //     }

    //     if ($isAllowed('diamond_weight')) {
    //         $rules['diamond_weight'] = [
    //             'nullable',
    //             'numeric',
    //             'min:0',
    //         ];
    //     }

    //     if ($isAllowed('diamond_charges')) {
    //         $rules['diamond_charges'] = [
    //             'nullable',
    //             'numeric',
    //             'min:0',
    //         ];
    //     }

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Image Validation
    //     |--------------------------------------------------------------------------
    //     |
    //     | Image ko business type item fields se independent rakha hai.
    //     | Isliye har item me optional image upload ho sakegi.
    //     |
    //     */
    //     $rules['image'] = [
    //         'nullable',
    //         'image',
    //         'mimes:jpg,jpeg,png,webp',
    //         'max:2048',
    //     ];

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Validate
    //     |--------------------------------------------------------------------------
    //     */
    //     $data = $request->validate($rules);

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Validate Category Business
    //     |--------------------------------------------------------------------------
    //     */
    //     if (!empty($data['category_id'])) {
    //         $categoryBelongsToBusiness = Category::query()
    //             ->where('id', $data['category_id'])
    //             ->where('business_id', $bid)
    //             ->exists();

    //         if (!$categoryBelongsToBusiness) {
    //             return back()
    //                 ->withErrors([
    //                     'category_id' =>
    //                         'Selected category does not belong to active business.',
    //                 ])
    //                 ->withInput();
    //         }
    //     }

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Opening Stock
    //     |--------------------------------------------------------------------------
    //     */
    //     $openingQty = (int) ($data['stock_qty'] ?? 0);

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Remove File + Stock From Normal Payload
    //     |--------------------------------------------------------------------------
    //     */
    //     $payload = Arr::except(
    //         $data,
    //         [
    //             'stock_qty',
    //             'image',
    //         ]
    //     );

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Making Charge Type
    //     |--------------------------------------------------------------------------
    //     */
    //     if ($isAllowed('making_charge')) {
    //         $payload['making_charge_type'] =
    //             $request->input(
    //                 'making_charge_type',
    //                 'percentage'
    //             );
    //     }

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Business
    //     |--------------------------------------------------------------------------
    //     */
    //     $payload['business_id'] = $bid;

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Active Status
    //     |--------------------------------------------------------------------------
    //     */
    //     $payload['is_active'] =
    //         $request->has('is_active')
    //             ? $request->boolean('is_active')
    //             : true;

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Initial Stock Zero
    //     |--------------------------------------------------------------------------
    //     |
    //     | StockService opening stock record karega.
    //     |
    //     */
    //     $payload['stock_qty'] = 0;

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Clear Non Allowed Fields
    //     |--------------------------------------------------------------------------
    //     */
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
    //             !in_array(
    //                 $field,
    //                 $allowedFields,
    //                 true
    //             )
    //             && $field !== 'stock_qty'
    //             && $field !== 'is_active'
    //             && $field !== 'making_charge_type'
    //         ) {
    //             $payload[$field] = null;
    //         }
    //     }

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Image Upload
    //     |--------------------------------------------------------------------------
    //     */
    //     $uploadedImagePath = null;

    //     if ($request->hasFile('image')) {
    //         $uploadedImagePath =
    //             $request->file('image')
    //                 ->store(
    //                     'items',
    //                     'public'
    //                 );

    //         $payload['image'] =
    //             $uploadedImagePath;
    //     }

    //     DB::beginTransaction();

    //     try {

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Create Item
    //         |--------------------------------------------------------------------------
    //         */
    //         $item = Item::create($payload);

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Opening Stock
    //         |--------------------------------------------------------------------------
    //         */
    //         if ($openingQty > 0) {
    //             $stock->recordOpening(
    //                 $item,
    //                 $openingQty,
    //                 'Opening stock (item create)'
    //             );
    //         }

    //         DB::commit();

    //         return redirect()
    //             ->route('items.index')
    //             ->with(
    //                 'success',
    //                 'Item created successfully.'
    //             );

    //     } catch (\Throwable $e) {

    //         DB::rollBack();

    //         /*
    //         |--------------------------------------------------------------------------
    //         | Delete Uploaded Image If DB Failed
    //         |--------------------------------------------------------------------------
    //         */
    //         if (
    //             $uploadedImagePath
    //             && Storage::disk('public')
    //                 ->exists($uploadedImagePath)
    //         ) {
    //             Storage::disk('public')
    //                 ->delete($uploadedImagePath);
    //         }

    //         Log::error(
    //             'Item create failed',
    //             [
    //                 'business_id' => $bid,
    //                 'user_id'     => auth()->id(),
    //                 'message'     => $e->getMessage(),
    //                 'line'        => $e->getLine(),
    //                 'file'        => $e->getFile(),
    //             ]
    //         );

    //         return back()
    //             ->withErrors([
    //                 'general' =>
    //                     'Item create failed: '
    //                     . $e->getMessage(),
    //             ])
    //             ->withInput();
    //     }
    // }


    public function store(Request $request)
{
    $validated = $request->validate([
        'name' => [
            'required',
            'string',
            'max:255',
        ],

        'office_id' => [
            'required',
            'exists:offices,id',
        ],

        // baaki validation...
    ]);

    DB::transaction(function () use (
        $request,
        $validated
    ) {

        /*
        |--------------------------------------------------------------------------
        | Lock office row
        |--------------------------------------------------------------------------
        */

        $office = Office::query()
            ->where('id', $validated['office_id'])
            ->lockForUpdate()
            ->firstOrFail();

        /*
        |--------------------------------------------------------------------------
        | Generate next sequence
        |--------------------------------------------------------------------------
        */

        $nextSequence =
            (int) $office->employee_sequence + 1;

        $prefix =
            $office->employee_prefix
            ?: 'OFF' . $office->id;

        $employeeId =
            strtoupper($prefix)
            . '-'
            . str_pad(
                $nextSequence,
                4,
                '0',
                STR_PAD_LEFT
            );

        /*
        |--------------------------------------------------------------------------
        | Create employee
        |--------------------------------------------------------------------------
        */

        $employee = User::create([
            'name' => $validated['name'],

            'office_id' =>
                $validated['office_id'],

            'employee_id' =>
                $employeeId,

            // other fields...
        ]);

        /*
        |--------------------------------------------------------------------------
        | Update office sequence
        |--------------------------------------------------------------------------
        */

        $office->employee_sequence =
            $nextSequence;

        $office->save();
    });

    return redirect()
        ->route('employee.index')
        ->with(
            'success',
            'Employee created successfully.'
        );
}


    // public function edit(Item $item)
    // {
    //     $categories = Category::orderBy('name')->get(['id', 'name']);

    //     $businessId = session('active_business_id');

    //     if (!$businessId) {
    //         $businessId = auth()->user()->current_business_id
    //             ?? auth()->user()->businesses()->pluck('businesses.id')->first();
    //     }

    //     abort_unless($businessId, 422, 'Active business not found.');

    //     abort_unless((int) $item->business_id === (int) $businessId, 403, 'Unauthorized item.');

    //     $business = \App\Models\Business::with('businessType.itemFields')
    //         ->find($businessId);

    //     $allowedFields = [];

    //     if ($business && $business->businessType) {
    //         $allowedFields = $business->businessType->itemFields
    //             ->pluck('field_name')
    //             ->toArray();
    //     }

    //     return view('items.edit', compact('item', 'categories', 'allowedFields'));
    // }

    public function edit(Request $request, Item $item)
{
    $user = $request->user();

    $businessId = $user->current_business_id
        ?? session('active_business_id');

    if (!$businessId) {
        $businessId = $user->businesses()
            ->pluck('businesses.id')
            ->first();
    }

    abort_unless($businessId, 422, 'Active business not found.');

    abort_unless(
        (int) $item->business_id === (int) $businessId,
        403,
        'Unauthorized item.'
    );

    /*
    |--------------------------------------------------------------------------
    | Categories - only active business
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
    | Units
    |--------------------------------------------------------------------------
    |
    | Global units:
    | business_id = null
    |
    | Current business units:
    | business_id = current business
    |
    */
    $units = Unit::query()
        ->withoutGlobalScope('business')
        ->where(function ($query) use ($businessId) {

            $query->whereNull('business_id');

            $query->orWhere(
                'business_id',
                (int) $businessId
            );
        })
        ->orderBy('name')
        ->get([
            'id',
            'business_id',
            'name',
            'description',
        ]);

    /*
    |--------------------------------------------------------------------------
    | Business Item Fields
    |--------------------------------------------------------------------------
    */
    $business = Business::with('businessType.itemFields')
        ->find($businessId);

    $allowedFields = [];

    if ($business?->businessType) {

        $allowedFields = $business
            ->businessType
            ->itemFields
            ->pluck('field_name')
            ->toArray();
    }

    return view('items.edit', [
        'item'          => $item,
        'categories'    => $categories,
        'units'         => $units,
        'allowedFields' => $allowedFields,
    ]);
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

    //             'sac'         => ['nullable', 'string', 'max:32'],
    //             'description' => ['nullable', 'string', 'max:2000'],

    //             'price'      => ['nullable', 'numeric', 'min:0'],
    //             'cost_price' => ['nullable', 'numeric', 'min:0'],

    //             'making_charge_type' => [
    //                 'nullable',
    //                 Rule::in([
    //                     'percentage',
    //                     'fixed',
    //                     'per_gram',
    //                     'per_product',
    //                 ]),
    //             ],

    //             'making_charge' => [
    //                 'nullable',
    //                 'numeric',
    //                 'min:0',
    //                 Rule::when(
    //                     $request->input('making_charge_type', 'percentage') === 'percentage',
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


    public function update(
    Request $request,
    Item $item,
    StockService $stock
) {
    $newUploadedImage = null;

    try {

        /*
        |--------------------------------------------------------------------------
        | Active Business
        |--------------------------------------------------------------------------
        */
        $bid =
            $request->user()->current_business_id
            ?? session('active_business_id');

        if (!$bid) {
            $bid = $request->user()
                ->businesses()
                ->pluck('businesses.id')
                ->first();
        }

        abort_unless(
            $bid,
            422,
            'Active business not found.'
        );

        /*
        |--------------------------------------------------------------------------
        | Item Authorization
        |--------------------------------------------------------------------------
        */
        abort_unless(
            (int) $item->business_id === (int) $bid,
            403,
            'Unauthorized item.'
        );

        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */
        $data = $request->validate([

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'sku' => [
                'nullable',
                'string',
                'max:100',

                Rule::unique(
                    'items',
                    'sku'
                )
                    ->ignore($item->id)
                    ->where(
                        fn ($query) =>
                        $query->where(
                            'business_id',
                            $bid
                        )
                    ),
            ],

            'category_id' => [
                'nullable',
                'integer',
            ],

            'type' => [
                'required',
                Rule::in([
                    'product',
                    'service',
                ]),
            ],

            'sac' => [
                'nullable',
                'string',
                'max:32',
            ],

            'description' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'price' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'cost_price' => [
                'nullable',
                'numeric',
                'min:0',
            ],

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
                    $request->input(
                        'making_charge_type',
                        'percentage'
                    ) === 'percentage',
                    [
                        'max:100',
                    ]
                ),
            ],

            'stock_qty' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'unit' => [
                'nullable',
                'string',
                'max:50',
            ],

            'tax_rate' => [
                'required',
                'numeric',
                'min:0',
                'max:100',
            ],

            'is_active' => [
                'nullable',
            ],

            'metal_type' => [
                'nullable',
                Rule::in([
                    'gold',
                    'silver',
                    'other',
                ]),
            ],

            'purity' => [
                'nullable',
                'string',
                'max:50',
            ],

            'gross_weight' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'metal_weight' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'stone_weight' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'stone_charges' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'gold_weight' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'gold_purity' => [
                'nullable',
                'string',
                'max:50',
            ],

            'silver_weight' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'silver_purity' => [
                'nullable',
                'string',
                'max:50',
            ],

            'diamond_weight' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'diamond_charges' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            /*
            |--------------------------------------------------------------------------
            | Image
            |--------------------------------------------------------------------------
            */
            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],
        ]);

        /*
        |--------------------------------------------------------------------------
        | Category Business Validation
        |--------------------------------------------------------------------------
        */
        if (!empty($data['category_id'])) {

            $categoryBelongsToBusiness =
                Category::query()
                    ->where(
                        'id',
                        $data['category_id']
                    )
                    ->where(
                        'business_id',
                        $bid
                    )
                    ->exists();

            if (!$categoryBelongsToBusiness) {

                return back()
                    ->withErrors([
                        'category_id' =>
                            'Selected category does not belong to active business.',
                    ])
                    ->withInput();
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Final Stock Qty
        |--------------------------------------------------------------------------
        */
        $finalQty =
            (int) (
                $data['stock_qty']
                ?? $item->stock_qty
                ?? 0
            );

        /*
        |--------------------------------------------------------------------------
        | Remove Stock + Image File Object
        |--------------------------------------------------------------------------
        */
        $payload = Arr::except(
            $data,
            [
                'stock_qty',
                'image',
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Checkbox
        |--------------------------------------------------------------------------
        */
        $payload['is_active'] =
            $request->has('is_active')
                ? $request->boolean('is_active')
                : false;

        /*
        |--------------------------------------------------------------------------
        | Making Charge
        |--------------------------------------------------------------------------
        */
        $payload['making_charge_type'] =
            $request->input(
                'making_charge_type',
                'percentage'
            );

        /*
        |--------------------------------------------------------------------------
        | Old Image
        |--------------------------------------------------------------------------
        */
        $oldImage = $item->image;

        /*
        |--------------------------------------------------------------------------
        | New Image Upload
        |--------------------------------------------------------------------------
        */
        if ($request->hasFile('image')) {

            $newUploadedImage =
                $request->file('image')
                    ->store(
                        'items',
                        'public'
                    );

            $payload['image'] =
                $newUploadedImage;
        }

        DB::beginTransaction();

        /*
        |--------------------------------------------------------------------------
        | Update Item
        |--------------------------------------------------------------------------
        */
        $item->update($payload);

        /*
        |--------------------------------------------------------------------------
        | Update Stock
        |--------------------------------------------------------------------------
        */
        $stock->setStockTo(
            $item,
            $finalQty,
            'Stock updated from item edit'
        );

        DB::commit();

        /*
        |--------------------------------------------------------------------------
        | Delete Old Image
        |--------------------------------------------------------------------------
        |
        | DB successful hone ke baad hi old image delete kar rahe hain.
        |
        */
        if (
            $newUploadedImage
            && $oldImage
            && $oldImage !== $newUploadedImage
            && Storage::disk('public')
                ->exists($oldImage)
        ) {
            Storage::disk('public')
                ->delete($oldImage);
        }

        return redirect()
            ->route('items.index')
            ->with(
                'success',
                'Item updated successfully.'
            );

    } catch (\Throwable $e) {

        /*
        |--------------------------------------------------------------------------
        | Rollback Only If Transaction Active
        |--------------------------------------------------------------------------
        */
        if (DB::transactionLevel() > 0) {
            DB::rollBack();
        }

        /*
        |--------------------------------------------------------------------------
        | Remove New Uploaded Image On Failure
        |--------------------------------------------------------------------------
        |
        | Purani image ko touch nahi karenge.
        |
        */
        if (
            $newUploadedImage
            && Storage::disk('public')
                ->exists($newUploadedImage)
        ) {
            Storage::disk('public')
                ->delete($newUploadedImage);
        }

        Log::error(
            'Item update failed',
            [
                'item_id' => $item->id ?? null,
                'user_id' => auth()->id(),
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile(),
            ]
        );

        return back()
            ->withErrors([
                'general' =>
                    'Update failed: '
                    . $e->getMessage(),
            ])
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
