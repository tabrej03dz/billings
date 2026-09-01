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
use App\Imports\ItemsImport;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;

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
                        ->orWhere('huid', 'like', "%{$q}%")
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

        $generatedBarcode = $this->generateUniqueBarcode();

        return view('items.create', compact(
            'categories',
            'units',
            'allowedFields',
            'generatedBarcode'
        ));
    }


    public function store(Request $request, StockService $stock)
    {
        $bid = $request->user()->current_business_id
            ?? session('active_business_id');

        if (!$bid) {
            $bid = $request->user()
                ->businesses()
                ->pluck('businesses.id')
                ->first();
        }

        abort_unless($bid, 422, 'Active business not found.');

        $business = Business::with('businessType.itemFields')
            ->find($bid);

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

        /*
        |--------------------------------------------------------------------------
        | Default Item Fields
        |--------------------------------------------------------------------------
        */
        if (empty($allowedFields)) {
            $allowedFields = [
                'name',
                'huid',
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

        $isAllowed = fn ($field) => in_array($field, $allowedFields, true);

        $isRequired = fn ($field) =>
            in_array($field, $requiredFields, true)
                ? 'required'
                : 'nullable';

        /*
        |--------------------------------------------------------------------------
        | Validation Rules
        |--------------------------------------------------------------------------
        */
        $rules = [];

        if ($isAllowed('name')) {
            $rules['name'] = [
                $isRequired('name'),
                'string',
                'max:255',
            ];
        }

        if ($isAllowed('sku')) {
            $rules['sku'] = [
                'nullable',
                'string',
                'max:100',
                Rule::unique('items', 'sku')
                    ->where(
                        fn ($query) =>
                        $query->where('business_id', $bid)
                    ),
            ];
        }

        if ($isAllowed('category_id')) {
            $rules['category_id'] = [
                $isRequired('category_id'),
                'integer',
            ];
        }

        if ($isAllowed('type')) {
            $rules['type'] = [
                $isRequired('type'),
                Rule::in([
                    'product',
                    'service',
                ]),
            ];
        }

        if ($isAllowed('sac')) {
            $rules['sac'] = [
                $isRequired('sac'),
                'string',
                'max:32',
            ];
        }

        if ($isAllowed('description')) {
            $rules['description'] = [
                'nullable',
                'string',
                'max:2000',
            ];
        }

        if ($isAllowed('price')) {
            $rules['price'] = [
                $isRequired('price'),
                'numeric',
                'min:0',
            ];
        }

        if ($isAllowed('cost_price')) {
            $rules['cost_price'] = [
                'nullable',
                'numeric',
                'min:0',
            ];
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
                    $request->input(
                        'making_charge_type',
                        'percentage'
                    ) === 'percentage',
                    ['max:100']
                ),
            ];
        }

        if ($isAllowed('stock_qty')) {
            $rules['stock_qty'] = [
                $isRequired('stock_qty'),
                'integer',
                'min:0',
            ];
        }

        if ($isAllowed('unit')) {
            $rules['unit'] = [
                'nullable',
                'string',
                'max:50',
            ];
        }

        if ($isAllowed('tax_rate')) {
            $rules['tax_rate'] = [
                $isRequired('tax_rate'),
                'numeric',
                'min:0',
                'max:100',
            ];
        }

        if ($isAllowed('is_active')) {
            $rules['is_active'] = [
                'nullable',
            ];
        }

        if ($isAllowed('metal_type')) {
            $rules['metal_type'] = [
                'nullable',
                Rule::in([
                    'gold',
                    'silver',
                    'other',
                ]),
            ];
        }

        if ($isAllowed('purity')) {
            $rules['purity'] = [
                'nullable',
                'string',
                'max:50',
            ];
        }

        if ($isAllowed('gross_weight')) {
            $rules['gross_weight'] = [
                'nullable',
                'numeric',
                'min:0',
            ];
        }

        if ($isAllowed('metal_weight')) {
            $rules['metal_weight'] = [
                'nullable',
                'numeric',
                'min:0',
            ];
        }

        if ($isAllowed('stone_weight')) {
            $rules['stone_weight'] = [
                'nullable',
                'numeric',
                'min:0',
            ];
        }

        if ($isAllowed('stone_charges')) {
            $rules['stone_charges'] = [
                'nullable',
                'numeric',
                'min:0',
            ];
        }

        if ($isAllowed('gold_weight')) {
            $rules['gold_weight'] = [
                'nullable',
                'numeric',
                'min:0',
            ];
        }

        if ($isAllowed('gold_purity')) {
            $rules['gold_purity'] = [
                'nullable',
                'string',
                'max:50',
            ];
        }

        if ($isAllowed('silver_weight')) {
            $rules['silver_weight'] = [
                'nullable',
                'numeric',
                'min:0',
            ];
        }

        if ($isAllowed('silver_purity')) {
            $rules['silver_purity'] = [
                'nullable',
                'string',
                'max:50',
            ];
        }

        if ($isAllowed('diamond_weight')) {
            $rules['diamond_weight'] = [
                'nullable',
                'numeric',
                'min:0',
            ];
        }

        if ($isAllowed('diamond_charges')) {
            $rules['diamond_charges'] = [
                'nullable',
                'numeric',
                'min:0',
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Image Validation
        |--------------------------------------------------------------------------
        |
        | Image ko business type item fields se independent rakha hai.
        | Isliye har item me optional image upload ho sakegi.
        |
        */
        $rules['image'] = [
            'nullable',
            'image',
            'mimes:jpg,jpeg,png,webp',
            'max:2048',
        ];

        $rules['barcode'] = [
            'nullable',
            'string',
            'max:100',
            Rule::unique('items', 'barcode')
                ->where(
                    fn ($query) =>
                    $query->where('business_id', $bid)
                ),
        ];

        if ($isAllowed('huid')) {
            $rules['huid'] = [
                $isRequired('huid'),
                'string',
                'max:50',

                Rule::unique('items', 'huid')
                    ->where(
                        fn ($query) =>
                        $query->where('business_id', $bid)
                    ),
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Validate
        |--------------------------------------------------------------------------
        */
        $data = $request->validate($rules);


        if (!empty($data['huid'])) {
            $data['huid'] = strtoupper(
                trim($data['huid'])
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Validate Category Business
        |--------------------------------------------------------------------------
        */
        if (!empty($data['category_id'])) {
            $categoryBelongsToBusiness = Category::query()
                ->where('id', $data['category_id'])
                ->where('business_id', $bid)
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
        | Opening Stock
        |--------------------------------------------------------------------------
        */
        $openingQty = (int) ($data['stock_qty'] ?? 0);

        /*
        |--------------------------------------------------------------------------
        | Remove File + Stock From Normal Payload
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
        | Making Charge Type
        |--------------------------------------------------------------------------
        */
        if ($isAllowed('making_charge')) {
            $payload['making_charge_type'] =
                $request->input(
                    'making_charge_type',
                    'percentage'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | Business
        |--------------------------------------------------------------------------
        */
        $payload['business_id'] = $bid;

        /*
        |--------------------------------------------------------------------------
        | Active Status
        |--------------------------------------------------------------------------
        */
        $payload['is_active'] =
            $request->has('is_active')
                ? $request->boolean('is_active')
                : true;

        /*
        |--------------------------------------------------------------------------
        | Initial Stock Zero
        |--------------------------------------------------------------------------
        |
        | StockService opening stock record karega.
        |
        */
        $payload['stock_qty'] = 0;

        /*
        |--------------------------------------------------------------------------
        | Clear Non Allowed Fields
        |--------------------------------------------------------------------------
        */
        $allItemFields = [
            'name',
            'huid',
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
                !in_array(
                    $field,
                    $allowedFields,
                    true
                )
                && $field !== 'stock_qty'
                && $field !== 'is_active'
                && $field !== 'making_charge_type'
            ) {
                $payload[$field] = null;
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Image Upload
        |--------------------------------------------------------------------------
        */
        $uploadedImagePath = null;

        if ($request->hasFile('image')) {
            $uploadedImagePath =
                $request->file('image')
                    ->store(
                        'items',
                        'public'
                    );

            $payload['image'] =
                $uploadedImagePath;
        }

        DB::beginTransaction();

        try {

            /*
            |--------------------------------------------------------------------------
            | Create Item
            |--------------------------------------------------------------------------
            */
            $item = Item::create($payload);

            /*
            |--------------------------------------------------------------------------
            | Opening Stock
            |--------------------------------------------------------------------------
            */
            if ($openingQty > 0) {
                $stock->recordOpening(
                    $item,
                    $openingQty,
                    'Opening stock (item create)'
                );
            }

            DB::commit();

            return redirect()
                ->route('items.index')
                ->with(
                    'success',
                    'Item created successfully.'
                );

        } catch (\Throwable $e) {

            DB::rollBack();

            /*
            |--------------------------------------------------------------------------
            | Delete Uploaded Image If DB Failed
            |--------------------------------------------------------------------------
            */
            if (
                $uploadedImagePath
                && Storage::disk('public')
                    ->exists($uploadedImagePath)
            ) {
                Storage::disk('public')
                    ->delete($uploadedImagePath);
            }

            Log::error(
                'Item create failed',
                [
                    'business_id' => $bid,
                    'user_id'     => auth()->id(),
                    'message'     => $e->getMessage(),
                    'line'        => $e->getLine(),
                    'file'        => $e->getFile(),
                ]
            );

            return back()
                ->withErrors([
                    'general' =>
                        'Item create failed: '
                        . $e->getMessage(),
                ])
                ->withInput();
        }
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


                'barcode' => [
                    'nullable',
                    'string',
                    'max:100',

                    Rule::unique('items', 'barcode')
                        ->ignore($item->id)
                        ->where(
                            fn ($query) =>
                            $query->where(
                                'business_id',
                                $bid
                            )
                        ),
                ],

                'huid' => [
                    'nullable',
                    'string',
                    'max:50',

                    Rule::unique('items', 'huid')
                        ->ignore($item->id)
                        ->where(
                            fn ($query) =>
                            $query->where(
                                'business_id',
                                $bid
                            )
                        ),
                ],

            ]);


            if (!empty($data['huid'])) {
                $data['huid'] = strtoupper(
                    trim($data['huid'])
                );
            }

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
            'huid'        => $item->huid,
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
            'huid' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('items', 'huid')
                    ->where(
                        fn ($q) =>
                        $q->where('business_id', $bid)
                    ),
            ],
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
            'huid' => !empty($data['huid'])
                ? strtoupper(trim($data['huid']))
                : null,
        ]);

        return response()->json([
            'message' => 'Item created successfully.',
            'item'    => $item,
        ]);
    }


    private function generateUniqueBarcode(): string
    {
        do {
            /*
            * 12 digit numeric internal barcode
            * Example: 268315742901
            */
            $barcode = (string) random_int(100000000000, 999999999999);

        } while (
            Item::query()
                ->where('barcode', $barcode)
                ->exists()
        );

        return $barcode;
    }



    /*
    |--------------------------------------------------------------------------
    | Excel Import Form
    |--------------------------------------------------------------------------
    */
    public function importForm(Request $request)
    {
        $businessId = $this->resolveActiveBusinessId(
            $request
        );

        abort_unless(
            $businessId,
            422,
            'Active business not found.'
        );

        $business = Business::with(
            'businessType.itemFields'
        )->findOrFail($businessId);

        [
            $allowedFields,
            $requiredFields
        ] = $this->getImportFieldConfiguration(
            $business
        );

        $categories = Category::query()
            ->where(
                'business_id',
                $businessId
            )
            ->orderBy('name')
            ->get([
                'id',
                'name',
            ]);

        $units = Unit::query()
            ->withoutGlobalScope('business')
            ->where(function ($query) use (
                $businessId
            ) {
                $query->whereNull(
                    'business_id'
                );

                $query->orWhere(
                    'business_id',
                    $businessId
                );
            })
            ->orderBy('name')
            ->get([
                'id',
                'name',
            ]);

        /*
        |--------------------------------------------------------------------------
        | Display Columns
        |--------------------------------------------------------------------------
        */
        $columnDetails =
            $this->getImportColumnDetails(
                $allowedFields,
                $requiredFields
            );

        return view(
            'items.import',
            compact(
                'business',
                'businessId',
                'allowedFields',
                'requiredFields',
                'columnDetails',
                'categories',
                'units'
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Process Excel Import
    |--------------------------------------------------------------------------
    */
    public function importStore(
        Request $request,
        StockService $stock
    ) {
        /*
        |--------------------------------------------------------------------------
        | Active Business Resolve
        |--------------------------------------------------------------------------
        */
        $businessId = $this->resolveActiveBusinessId(
            $request
        );

        abort_unless(
            $businessId,
            422,
            'Active business not found.'
        );

        /*
        |--------------------------------------------------------------------------
        | File Validation
        |--------------------------------------------------------------------------
        |
        | CSV file PHP/Windows me kabhi-kabhi text/plain detect hoti hai,
        | isliye MIME validation intentionally nahi rakhi gayi.
        |
        */
        $request->validate([
            'import_file' => [
                'required',
                'file',
                'extensions:xlsx,xls,csv',
                'max:10240',
            ],
        ], [
            'import_file.required' =>
                'Excel/CSV file select karein.',

            'import_file.file' =>
                'Selected upload valid file nahi hai.',

            'import_file.extensions' =>
                'Sirf XLSX, XLS ya CSV file allowed hai.',

            'import_file.max' =>
                'File maximum 10 MB honi chahiye.',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Business + Allowed Fields
        |--------------------------------------------------------------------------
        */
        $business = Business::with(
            'businessType.itemFields'
        )->findOrFail($businessId);

        [
            $allowedFields,
            $requiredFields
        ] = $this->getImportFieldConfiguration(
            $business
        );

        /*
        |--------------------------------------------------------------------------
        | Import
        |--------------------------------------------------------------------------
        */
        try {

            $import = new ItemsImport(
                (int) $businessId,
                $allowedFields,
                $requiredFields,
                $stock
            );

            /*
            |--------------------------------------------------------------------------
            | Execute Import
            |--------------------------------------------------------------------------
            */
            Excel::import(
                $import,
                $request->file('import_file')
            );

            /*
            |--------------------------------------------------------------------------
            | Import Result
            |--------------------------------------------------------------------------
            */
            $imported = (int) $import->getImportedCount();

            $skipped = (int) $import->getSkippedCount();

            $importErrors = $import->getErrors();

            /*
            |--------------------------------------------------------------------------
            | Empty File / Only Header
            |--------------------------------------------------------------------------
            */
            if (
                $imported === 0
                && $skipped === 0
            ) {
                return redirect()
                    ->route('items.import.form')
                    ->withErrors([
                        'import_file' =>
                            'File me import karne ke liye koi item row nahi mili. Header ke neeche item data add karein.',
                    ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Full Success
            |--------------------------------------------------------------------------
            */
            if (
                $imported > 0
                && $skipped === 0
            ) {
                return redirect()
                    ->route('items.import.form')
                    ->with(
                        'success',
                        "Import successful. {$imported} item(s) successfully imported."
                    )
                    ->with(
                        'import_summary',
                        [
                            'imported' => $imported,
                            'skipped'  => 0,
                        ]
                    )
                    ->with(
                        'import_errors',
                        []
                    );
            }

            /*
            |--------------------------------------------------------------------------
            | Partial Success
            |--------------------------------------------------------------------------
            */
            if (
                $imported > 0
                && $skipped > 0
            ) {
                return redirect()
                    ->route('items.import.form')
                    ->with(
                        'success',
                        "Import completed. {$imported} item(s) successfully imported and {$skipped} row(s) skipped."
                    )
                    ->with(
                        'import_summary',
                        [
                            'imported' => $imported,
                            'skipped'  => $skipped,
                        ]
                    )
                    ->with(
                        'import_errors',
                        $importErrors
                    );
            }

            /*
            |--------------------------------------------------------------------------
            | All Rows Skipped
            |--------------------------------------------------------------------------
            */
            return redirect()
                ->route('items.import.form')
                ->with(
                    'warning',
                    "Koi item import nahi hua. {$skipped} row(s) skipped."
                )
                ->with(
                    'import_summary',
                    [
                        'imported' => 0,
                        'skipped'  => $skipped,
                    ]
                )
                ->with(
                    'import_errors',
                    $importErrors
                );

        } catch (
            \Maatwebsite\Excel\Validators\ValidationException $e
        ) {

            /*
            |--------------------------------------------------------------------------
            | Laravel Excel Validation Exception
            |--------------------------------------------------------------------------
            */
            $failures = [];

            foreach (
                $e->failures()
                as $failure
            ) {
                $failures[] = [
                    'row' =>
                        $failure->row(),

                    'message' =>
                        implode(
                            ' | ',
                            $failure->errors()
                        ),
                ];
            }

            Log::warning(
                'Item Excel validation failed',
                [
                    'business_id' =>
                        $businessId,

                    'user_id' =>
                        $request->user()?->id,

                    'failures' =>
                        $failures,
                ]
            );

            return redirect()
                ->route('items.import.form')
                ->withErrors([
                    'import_file' =>
                        'Excel file me validation errors mile. Neeche skipped rows check karein.',
                ])
                ->with(
                    'import_summary',
                    [
                        'imported' => 0,
                        'skipped'  => count($failures),
                    ]
                )
                ->with(
                    'import_errors',
                    $failures
                );

        } catch (\Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | General Import Exception
            |--------------------------------------------------------------------------
            */
            Log::error(
                'Item Excel import failed',
                [
                    'business_id' =>
                        $businessId,

                    'user_id' =>
                        $request->user()?->id,

                    'original_name' =>
                        $request->file(
                            'import_file'
                        )?->getClientOriginalName(),

                    'extension' =>
                        $request->file(
                            'import_file'
                        )?->getClientOriginalExtension(),

                    'message' =>
                        $e->getMessage(),

                    'line' =>
                        $e->getLine(),

                    'file' =>
                        $e->getFile(),
                ]
            );

            return redirect()
                ->route('items.import.form')
                ->withErrors([
                    'import_file' =>
                        'Import failed: '
                        . $e->getMessage(),
                ]);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Download Dynamic CSV Template
    |--------------------------------------------------------------------------
    */
    public function downloadImportTemplate(
        Request $request
    ): StreamedResponse {
        $businessId =
            $this->resolveActiveBusinessId(
                $request
            );

        abort_unless(
            $businessId,
            422,
            'Active business not found.'
        );

        $business = Business::with(
            'businessType.itemFields'
        )->findOrFail($businessId);

        [
            $allowedFields,
            $requiredFields
        ] = $this->getImportFieldConfiguration(
            $business
        );

        /*
        |--------------------------------------------------------------------------
        | Dynamic Excel/CSV Headers
        |--------------------------------------------------------------------------
        */
        $headers =
            $this->getImportHeaders(
                $allowedFields
            );

        /*
        |--------------------------------------------------------------------------
        | Example Row
        |--------------------------------------------------------------------------
        */
        $example = [];

        foreach ($headers as $header) {

            $example[] = match ($header) {

                'name' =>
                    'Gold Ring',

                'huid' =>
                    'AB12CD',

                'sku' =>
                    'RING-001',

                'category' =>
                    'Gold Jewellery',

                'type' =>
                    'product',

                'sac' =>
                    '',

                'description' =>
                    '22K Gold Ring',

                'price' =>
                    '55000',

                'cost_price' =>
                    '50000',

                'making_charge_type' =>
                    'percentage',

                'making_charge' =>
                    '10',

                'stock_qty' =>
                    '5',

                'unit' =>
                    'pcs',

                'tax_rate' =>
                    '3',

                'is_active' =>
                    '1',

                'metal_type' =>
                    'gold',

                'purity' =>
                    '22K',

                'gross_weight' =>
                    '10.500',

                'metal_weight' =>
                    '10.000',

                'stone_weight' =>
                    '0.500',

                'stone_charges' =>
                    '500',

                'gold_weight' =>
                    '10.000',

                'gold_purity' =>
                    '22K (916)',

                'silver_weight' =>
                    '',

                'silver_purity' =>
                    '',

                'diamond_weight' =>
                    '',

                'diamond_charges' =>
                    '',

                'barcode' =>
                    '',

                default =>
                    '',
            };
        }

        /*
        |--------------------------------------------------------------------------
        | CSV Download
        |--------------------------------------------------------------------------
        */
        return response()->streamDownload(
            function () use (
                $headers,
                $example
            ) {

                $handle = fopen(
                    'php://output',
                    'w'
                );

                /*
                * UTF-8 BOM for Excel
                */
                fwrite(
                    $handle,
                    "\xEF\xBB\xBF"
                );

                /*
                * Header
                */
                fputcsv(
                    $handle,
                    $headers
                );

                /*
                * Example Row
                */
                fputcsv(
                    $handle,
                    $example
                );

                fclose($handle);
            },
            'item-import-template.csv',
            [
                'Content-Type' =>
                    'text/csv; charset=UTF-8',
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Resolve Active Business
    |--------------------------------------------------------------------------
    */
    private function resolveActiveBusinessId(
        Request $request
    ): ?int {
        $user = $request->user();

        if (!$user) {
            return null;
        }

        $businessId =
            $user->current_business_id
            ?? session(
                'active_business_id'
            );

        if (!$businessId) {

            $businessId =
                $user->businesses()
                    ->pluck('businesses.id')
                    ->first();
        }

        return $businessId
            ? (int) $businessId
            : null;
    }


    /*
    |--------------------------------------------------------------------------
    | Import Field Configuration
    |--------------------------------------------------------------------------
    */
    private function getImportFieldConfiguration(
        Business $business
    ): array {
        $allowedFields = [];

        $requiredFields = [];

        if ($business->businessType) {

            $allowedFields =
                $business
                    ->businessType
                    ->itemFields
                    ->pluck('field_name')
                    ->toArray();

            $requiredFields =
                $business
                    ->businessType
                    ->itemFields
                    ->where(
                        'is_required',
                        1
                    )
                    ->pluck('field_name')
                    ->toArray();
        }

        /*
        |--------------------------------------------------------------------------
        | Default Item Fields
        |--------------------------------------------------------------------------
        */
        if (empty($allowedFields)) {

            $allowedFields = [
                'name',
                'huid',
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

        /*
        * Import me item name required rakhein.
        */
        if (
            in_array(
                'name',
                $allowedFields,
                true
            )
            && !in_array(
                'name',
                $requiredFields,
                true
            )
        ) {
            $requiredFields[] = 'name';
        }

        return [
            $allowedFields,
            $requiredFields,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Excel Headers
    |--------------------------------------------------------------------------
    */
    private function getImportHeaders(
        array $allowedFields
    ): array {
        $headers = [];

        $allFields = [
            'name',
            'huid',
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

        foreach ($allFields as $field) {

            if (
                !in_array(
                    $field,
                    $allowedFields,
                    true
                )
            ) {
                continue;
            }

            /*
            * User Excel me category_id
            * ya numeric ID nahi dalega.
            */
            if ($field === 'category_id') {
                $headers[] = 'category';

                continue;
            }

            $headers[] = $field;
        }

        /*
        * Barcode business type fields se
        * independent hai.
        */
        $headers[] = 'barcode';

        return array_values(
            array_unique($headers)
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Column Details For Upload Screen
    |--------------------------------------------------------------------------
    */
    private function getImportColumnDetails(
        array $allowedFields,
        array $requiredFields
    ): array {
        $headers =
            $this->getImportHeaders(
                $allowedFields
            );

        $details = [];

        foreach ($headers as $column) {

            $originalField =
                $column === 'category'
                    ? 'category_id'
                    : $column;

            $required =
                in_array(
                    $originalField,
                    $requiredFields,
                    true
                );

            $details[] = [
                'column' =>
                    $column,

                'required' =>
                    $required,

                'format' => match ($column) {

                    'name' =>
                        'Text e.g. Gold Ring',

                    'huid' =>
                        'Text e.g. AB12CD',

                    'sku' =>
                        'Unique text e.g. RING-001',

                    'category' =>
                        'Existing category name exactly as system',

                    'type' =>
                        'product / service',

                    'sac' =>
                        'Text / SAC code',

                    'description' =>
                        'Optional text',

                    'price',
                    'cost_price',
                    'making_charge',
                    'stone_charges',
                    'diamond_charges' =>
                        'Number, minimum 0',

                    'making_charge_type' =>
                        'percentage / fixed / per_gram / per_product',

                    'stock_qty' =>
                        'Whole number e.g. 10',

                    'unit' =>
                        'Text e.g. pcs / gm',

                    'tax_rate' =>
                        '0 to 100 e.g. 3 / 5 / 18',

                    'is_active' =>
                        '1/0, Yes/No, Active/Inactive',

                    'metal_type' =>
                        'gold / silver / other',

                    'purity' =>
                        'Text e.g. 22K',

                    'gross_weight',
                    'metal_weight',
                    'stone_weight',
                    'gold_weight',
                    'silver_weight',
                    'diamond_weight' =>
                        'Decimal allowed e.g. 10.500',

                    'gold_purity' =>
                        'e.g. 22K (916)',

                    'silver_purity' =>
                        'e.g. 999',

                    'barcode' =>
                        'Optional unique barcode. Blank = auto generate',

                    default =>
                        'Text / Number',
                },
            ];
        }

        return $details;
    }

}
