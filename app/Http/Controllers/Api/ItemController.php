<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Category;
use App\Models\Item;
use App\Services\ItemBarcodeService;
use App\Services\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ItemController extends Controller
{
    /**
     * GET /api/items
     *
     * Query parameters:
     * business_id
     * q
     * category_id
     * active
     * per_page
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $businessId = $this->resolveBusinessId($request);

            $search = trim((string) $request->get('q', ''));
            $categoryId = $request->integer('category_id');
            $active = $request->get('active');

            $perPage = (int) $request->get('per_page', 15);
            $perPage = $perPage > 0 && $perPage <= 100
                ? $perPage
                : 15;

            $items = Item::withoutGlobalScope('business')
                ->with('category:id,name')
                ->where('business_id', $businessId)
                ->when($search !== '', function ($query) use ($search) {
                    $query->where(function ($searchQuery) use ($search) {
                        $searchQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('sku', 'like', "%{$search}%")
                            ->orWhere('barcode', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%");
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
                        filter_var(
                            $active,
                            FILTER_VALIDATE_BOOLEAN,
                            FILTER_NULL_ON_FAILURE
                        ) ?? (bool) $active
                    )
                )
                ->latest('id')
                ->paginate($perPage);

            return response()->json([
                'ok' => true,
                'msg' => 'Items fetched successfully.',
                'data' => $items,
            ]);
        } catch (\Throwable $exception) {
            Log::error('Items list API failed', [
                'business_id' => $request->input('business_id'),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);

            return response()->json([
                'ok' => false,
                'msg' => $this->safeExceptionMessage(
                    $exception,
                    'Unable to fetch items.'
                ),
                'code' => 'SERVER_ERROR',
            ], $this->exceptionStatus($exception));
        }
    }

    /**
     * GET /api/items/categories
     */
    public function categories(Request $request): JsonResponse
    {
        try {
            $businessId = $this->resolveBusinessId($request);

            $categories = Category::query()
                ->where('business_id', $businessId)
                ->orderBy('name')
                ->get([
                    'id',
                    'name',
                ]);

            return response()->json([
                'ok' => true,
                'msg' => 'Categories fetched successfully.',
                'data' => $categories,
            ]);
        } catch (\Throwable $exception) {
            Log::error('Item categories API failed', [
                'business_id' => $request->input('business_id'),
                'message' => $exception->getMessage(),
            ]);

            return response()->json([
                'ok' => false,
                'msg' => $this->safeExceptionMessage(
                    $exception,
                    'Unable to fetch categories.'
                ),
                'code' => 'SERVER_ERROR',
            ], $this->exceptionStatus($exception));
        }
    }

    /**
     * POST /api/items
     *
     * Barcode optional hai.
     * Barcode missing hone par backend automatically generate karega.
     */
    public function store(
        Request $request,
        StockService $stock,
        ItemBarcodeService $barcodeService
    ): JsonResponse {
        $businessId = (int) $request->input('business_id');

        if ($businessId <= 0) {
            return response()->json([
                'ok' => false,
                'msg' => 'Validation failed.',
                'code' => 'VALIDATION_ERROR',
                'errors' => [
                    'business_id' => [
                        'business_id is required.',
                    ],
                ],
            ], 422);
        }

        try {
            $this->resolveBusinessId($request);

            $business = Business::with(
                'businessType.itemFields'
            )->find($businessId);

            if (!$business) {
                return response()->json([
                    'ok' => false,
                    'msg' => 'Business not found.',
                    'code' => 'BUSINESS_NOT_FOUND',
                ], 404);
            }

            [
                $allowedFields,
                $requiredFields,
            ] = $this->resolveItemFields($business);

            $rules = $this->buildStoreRules(
                $businessId,
                $allowedFields,
                $requiredFields
            );

            $messages = [
                'name.required' => 'Item name is required.',
                'type.required' => 'Item type is required.',
                'type.in' => 'Item type must be product or service.',
                'price.numeric' => 'Price must be a valid number.',
                'tax_rate.numeric' => 'Tax rate must be a valid number.',
                'barcode.unique' => 'This barcode is already assigned to another item.',
                'sku.unique' => 'This SKU is already assigned to another item in this business.',
                'stock_qty.integer' => 'Stock quantity must be a whole number.',
            ];

            $data = $request->validate($rules, $messages);

            $this->validateCategory(
                $businessId,
                $data['category_id'] ?? null
            );

            return DB::transaction(function () use (
                $data,
                $request,
                $stock,
                $barcodeService,
                $businessId,
                $allowedFields
            ) {
                $type = $data['type'] ?? 'product';

                $openingQuantity = $type === 'product'
                    ? (int) ($data['stock_qty'] ?? 0)
                    : 0;

                $payload = Arr::except(
                    $data,
                    [
                        'stock_qty',
                        'business_id',
                    ]
                );

                $payload['business_id'] = $businessId;

                $payload['is_active'] = $request->has('is_active')
                    ? $request->boolean('is_active')
                    : true;

                /*
                 * Actual opening stock StockService me record hoga.
                 */
                $payload['stock_qty'] = 0;

                if (array_key_exists('barcode', $payload)) {
                    $payload['barcode'] = $this->normalizeBarcode(
                        $payload['barcode']
                    );
                }

                $payload = $this->removeDisallowedFields(
                    $payload,
                    $allowedFields,
                    [
                        'barcode',
                        'stock_qty',
                        'is_active',
                    ]
                );

                if ($type === 'service') {
                    $payload = $this->clearProductFields($payload);
                }

                $item = Item::create($payload);

                /*
                 * App barcode na bheje to automatic barcode generate hoga.
                 */
                if (empty($item->barcode)) {
                    $barcodeService->generate($item);
                    $item->refresh();
                }

                if (
                    $type === 'product'
                    && $openingQuantity > 0
                ) {
                    $stock->recordOpening(
                        $item,
                        $openingQuantity,
                        'Opening stock (item created through API)'
                    );
                }

                $item->load('category:id,name');

                return response()->json([
                    'ok' => true,
                    'msg' => 'Item created successfully.',
                    'item' => $item,
                ], 201);
            });
        } catch (ValidationException $exception) {
            return response()->json([
                'ok' => false,
                'msg' => 'Validation failed.',
                'code' => 'VALIDATION_ERROR',
                'errors' => $exception->errors(),
            ], 422);
        } catch (\Throwable $exception) {
            Log::error('Item create API failed', [
                'business_id' => $businessId,
                'user_id' => $request->user()?->id,
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);

            return response()->json([
                'ok' => false,
                'msg' => $this->safeExceptionMessage(
                    $exception,
                    'Server error while creating item.'
                ),
                'code' => 'SERVER_ERROR',
            ], $this->exceptionStatus($exception));
        }
    }

    /**
     * GET /api/items/{item}
     */
    public function show(
        Request $request,
        Item $item
    ): JsonResponse {
        try {
            $businessId = $this->resolveBusinessId($request);

            if ((int) $item->business_id !== $businessId) {
                return response()->json([
                    'ok' => false,
                    'msg' => 'You are not allowed to access this item.',
                    'code' => 'FORBIDDEN',
                ], 403);
            }

            return response()->json([
                'ok' => true,
                'msg' => 'Item fetched successfully.',
                'item' => $item->load('category:id,name'),
            ]);
        } catch (\Throwable $exception) {
            Log::error('Item show API failed', [
                'business_id' => $request->input('business_id'),
                'item_id' => $item->id,
                'message' => $exception->getMessage(),
            ]);

            return response()->json([
                'ok' => false,
                'msg' => $this->safeExceptionMessage(
                    $exception,
                    'Unable to fetch item.'
                ),
                'code' => 'SERVER_ERROR',
            ], $this->exceptionStatus($exception));
        }
    }

    /**
     * PUT/PATCH /api/items/{id}
     */
    public function update(
        Request $request,
        StockService $stock,
        ItemBarcodeService $barcodeService,
        int $id
    ): JsonResponse {
        $businessId = (int) $request->input('business_id');

        if ($businessId <= 0) {
            return response()->json([
                'ok' => false,
                'msg' => 'Validation failed.',
                'code' => 'VALIDATION_ERROR',
                'errors' => [
                    'business_id' => [
                        'business_id is required.',
                    ],
                ],
            ], 422);
        }

        try {
            $this->resolveBusinessId($request);

            $item = Item::withoutGlobalScope('business')
                ->where('id', $id)
                ->where('business_id', $businessId)
                ->first();

            if (!$item) {
                return response()->json([
                    'ok' => false,
                    'msg' => 'Item not found for this business.',
                    'code' => 'ITEM_NOT_FOUND',
                ], 404);
            }

            $business = Business::with(
                'businessType.itemFields'
            )->find($businessId);

            if (!$business) {
                return response()->json([
                    'ok' => false,
                    'msg' => 'Business not found.',
                    'code' => 'BUSINESS_NOT_FOUND',
                ], 404);
            }

            [
                $allowedFields,
                $requiredFields,
            ] = $this->resolveItemFields($business);

            $rules = $this->buildUpdateRules(
                $businessId,
                $item,
                $allowedFields,
                $requiredFields
            );

            $data = $request->validate($rules, [
                'barcode.unique' => 'This barcode is already assigned to another item.',
                'sku.unique' => 'This SKU is already assigned to another item in this business.',
            ]);

            $this->validateCategory(
                $businessId,
                $data['category_id'] ?? null
            );

            return DB::transaction(function () use (
                $data,
                $request,
                $stock,
                $barcodeService,
                $businessId,
                $item,
                $allowedFields
            ) {
                $newType = $data['type']
                    ?? $item->type
                    ?? 'product';

                $targetQuantity = $request->has('stock_qty')
                    ? (int) ($data['stock_qty'] ?? 0)
                    : null;

                $payload = Arr::except(
                    $data,
                    [
                        'stock_qty',
                        'business_id',
                    ]
                );

                $payload['business_id'] = $businessId;

                $payload['is_active'] = $request->has('is_active')
                    ? $request->boolean('is_active')
                    : (bool) $item->is_active;

                /*
                 * Barcode request me nahi aaya to purana barcode preserve hoga.
                 */
                if (!$request->has('barcode')) {
                    unset($payload['barcode']);
                } elseif (array_key_exists('barcode', $payload)) {
                    $payload['barcode'] = $this->normalizeBarcode(
                        $payload['barcode']
                    );
                }

                $payload = $this->removeDisallowedFields(
                    $payload,
                    $allowedFields,
                    [
                        'barcode',
                        'stock_qty',
                        'is_active',
                    ]
                );

                if ($newType === 'service') {
                    $payload = $this->clearProductFields($payload);
                }

                /*
                 * Stock StockService se manage hoga.
                 */
                $payload['stock_qty'] = 0;

                $item->update($payload);

                /*
                 * Barcode explicitly blank bheja ya missing tha to
                 * automatic new barcode generate hoga.
                 */
                if (empty($item->barcode)) {
                    $barcodeService->generate($item);
                    $item->refresh();
                }

                /*
                 * Stock adjustment method project me available ho to
                 * is block ko enable karein.
                 */
                if (
                    $newType === 'product'
                    && $targetQuantity !== null
                ) {
                    // Example:
                    // $stock->setOnHand(
                    //     $item,
                    //     $targetQuantity,
                    //     'Stock adjusted through item API'
                    // );
                }

                $item->load('category:id,name');

                return response()->json([
                    'ok' => true,
                    'msg' => 'Item updated successfully.',
                    'item' => $item,
                ]);
            });
        } catch (ValidationException $exception) {
            return response()->json([
                'ok' => false,
                'msg' => 'Validation failed.',
                'code' => 'VALIDATION_ERROR',
                'errors' => $exception->errors(),
            ], 422);
        } catch (\Throwable $exception) {
            Log::error('Item update API failed', [
                'business_id' => $businessId,
                'item_id' => $id,
                'user_id' => $request->user()?->id,
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);

            return response()->json([
                'ok' => false,
                'msg' => $this->safeExceptionMessage(
                    $exception,
                    'Server error while updating item.'
                ),
                'code' => 'SERVER_ERROR',
            ], $this->exceptionStatus($exception));
        }
    }

    /**
     * GET /api/items/barcode/lookup
     *
     * Query:
     * business_id=1
     * barcode=8901234567890
     */
    public function barcodeLookup(
        Request $request
    ): JsonResponse {
        try {
            $businessId = $this->resolveBusinessId($request);

            $data = $request->validate([
                'barcode' => [
                    'required',
                    'string',
                    'max:100',
                ],
            ], [
                'barcode.required' => 'Barcode is required.',
            ]);

            $barcode = trim($data['barcode']);

            $item = Item::withoutGlobalScope('business')
                ->with('category:id,name')
                ->where('business_id', $businessId)
                ->where('is_active', true)
                ->where(function ($query) use ($barcode) {
                    $query
                        ->where('barcode', $barcode)
                        ->orWhere('sku', $barcode);
                })
                ->first();

            if (!$item) {
                return response()->json([
                    'ok' => false,
                    'msg' => 'Item not found for this barcode.',
                    'code' => 'ITEM_NOT_FOUND',
                ], 404);
            }

            return response()->json([
                'ok' => true,
                'msg' => 'Item found successfully.',
                'item' => $this->formatBarcodeItem($item),
            ]);
        } catch (ValidationException $exception) {
            return response()->json([
                'ok' => false,
                'msg' => 'Validation failed.',
                'code' => 'VALIDATION_ERROR',
                'errors' => $exception->errors(),
            ], 422);
        } catch (\Throwable $exception) {
            Log::error('Barcode lookup API failed', [
                'business_id' => $request->input('business_id'),
                'barcode' => $request->input('barcode'),
                'user_id' => $request->user()?->id,
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);

            return response()->json([
                'ok' => false,
                'msg' => $this->safeExceptionMessage(
                    $exception,
                    'Unable to find item.'
                ),
                'code' => 'SERVER_ERROR',
            ], $this->exceptionStatus($exception));
        }
    }

    /**
     * POST /api/items/{id}/barcode/generate
     */
    public function generateBarcode(
        Request $request,
        ItemBarcodeService $barcodeService,
        int $id
    ): JsonResponse {
        try {
            $businessId = $this->resolveBusinessId($request);

            $item = Item::withoutGlobalScope('business')
                ->where('business_id', $businessId)
                ->where('id', $id)
                ->first();

            if (!$item) {
                return response()->json([
                    'ok' => false,
                    'msg' => 'Item not found for this business.',
                    'code' => 'ITEM_NOT_FOUND',
                ], 404);
            }

            $barcode = $barcodeService->generate($item);

            $item->refresh();

            return response()->json([
                'ok' => true,
                'msg' => 'Barcode generated successfully.',
                'item' => [
                    'id' => $item->id,
                    'business_id' => $item->business_id,
                    'name' => $item->name,
                    'sku' => $item->sku,
                    'barcode' => $barcode,
                ],
            ]);
        } catch (\Throwable $exception) {
            Log::error('Barcode generation API failed', [
                'business_id' => $request->input('business_id'),
                'item_id' => $id,
                'user_id' => $request->user()?->id,
                'message' => $exception->getMessage(),
            ]);

            return response()->json([
                'ok' => false,
                'msg' => $this->safeExceptionMessage(
                    $exception,
                    'Unable to generate barcode.'
                ),
                'code' => 'SERVER_ERROR',
            ], $this->exceptionStatus($exception));
        }
    }

    /**
     * DELETE /api/items/{item}
     */
    public function destroy(
        Request $request,
        Item $item
    ): JsonResponse {
        try {
            $businessId = $this->resolveBusinessId($request);

            if ((int) $item->business_id !== $businessId) {
                return response()->json([
                    'ok' => false,
                    'msg' => 'You are not allowed to delete this item.',
                    'code' => 'FORBIDDEN',
                ], 403);
            }

            $item->delete();

            return response()->json([
                'ok' => true,
                'msg' => 'Item deleted successfully.',
            ]);
        } catch (\Throwable $exception) {
            Log::error('Item delete API failed', [
                'business_id' => $request->input('business_id'),
                'item_id' => $item->id,
                'message' => $exception->getMessage(),
            ]);

            return response()->json([
                'ok' => false,
                'msg' => $this->safeExceptionMessage(
                    $exception,
                    'Unable to delete item.'
                ),
                'code' => 'SERVER_ERROR',
            ], $this->exceptionStatus($exception));
        }
    }

    /**
     * Testing method.
     */
    public function index1(Request $request): JsonResponse
    {
        $items = Item::query()
            ->with('category:id,name')
            ->where('items.business_id', 1)
            ->orderByDesc('items.id')
            ->get();

        return response()->json([
            'ok' => true,
            'data' => $items,
        ]);
    }

    /**
     * GET /api/items/allowed-fields
     */
    public function allowedFields(
        Request $request
    ): JsonResponse {
        try {
            $businessId = $this->resolveBusinessId($request);

            $business = Business::with(
                'businessType.itemFields'
            )->find($businessId);

            if (!$business) {
                return response()->json([
                    'status' => false,
                    'message' => 'Business not found.',
                    'allowed_fields' => [],
                ], 404);
            }

            $allowedFields = [];

            if ($business->businessType) {
                $allowedFields = $business
                    ->businessType
                    ->itemFields
                    ->pluck('field_name')
                    ->values()
                    ->toArray();
            }

            /*
             * Barcode API ke liye hamesha available rahega.
             */
            if (!in_array('barcode', $allowedFields, true)) {
                $allowedFields[] = 'barcode';
            }

            return response()->json([
                'status' => true,
                'message' => 'Allowed item fields fetched successfully.',
                'business_id' => $business->id,
                'business_type' => $business->businessType?->name,
                'allowed_fields' => $allowedFields,
            ]);
        } catch (\Throwable $exception) {
            Log::error('Allowed item fields API failed', [
                'business_id' => $request->input('business_id'),
                'message' => $exception->getMessage(),
            ]);

            return response()->json([
                'status' => false,
                'message' => $this->safeExceptionMessage(
                    $exception,
                    'Unable to fetch allowed item fields.'
                ),
                'allowed_fields' => [],
            ], $this->exceptionStatus($exception));
        }
    }

    /**
     * Authenticated user ke business access ko verify karega.
     */
    private function resolveBusinessId(
        Request $request
    ): int {
        $businessId = (int) (
            $request->input('business_id')
            ?: $request->header('X-Business-ID')
        );

        abort_unless(
            $businessId > 0,
            422,
            'business_id is required.'
        );

        $user = $request->user();

        abort_unless(
            $user,
            401,
            'Unauthenticated.'
        );

        $hasBusiness = $user
            ->businesses()
            ->where('businesses.id', $businessId)
            ->exists();

        abort_unless(
            $hasBusiness,
            403,
            'You do not have access to this business.'
        );

        return $businessId;
    }

    /**
     * Business type ke allowed aur required item fields.
     */
    private function resolveItemFields(
        Business $business
    ): array {
        $allowedFields = [];
        $requiredFields = [];

        if ($business->businessType) {
            $allowedFields = $business
                ->businessType
                ->itemFields
                ->pluck('field_name')
                ->filter()
                ->unique()
                ->values()
                ->toArray();

            $requiredFields = $business
                ->businessType
                ->itemFields
                ->where('is_required', 1)
                ->pluck('field_name')
                ->filter()
                ->unique()
                ->values()
                ->toArray();
        }

        if (empty($allowedFields)) {
            $allowedFields = $this->defaultItemFields();
        }

        /*
         * Barcode ko dynamic business configuration par depend nahi rakhenge.
         */
        if (!in_array('barcode', $allowedFields, true)) {
            $allowedFields[] = 'barcode';
        }

        return [
            $allowedFields,
            $requiredFields,
        ];
    }

    /**
     * Store validation rules.
     */
    private function buildStoreRules(
        int $businessId,
        array $allowedFields,
        array $requiredFields
    ): array {
        $isAllowed = fn (string $field): bool => in_array(
            $field,
            $allowedFields,
            true
        );

        $requirement = fn (string $field): string => in_array(
            $field,
            $requiredFields,
            true
        )
            ? 'required'
            : 'nullable';

        $rules = [
            'business_id' => [
                'required',
                'integer',
                'min:1',
            ],

            /*
             * Barcode always accepted.
             */
            'barcode' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('items', 'barcode'),
            ],
        ];

        if ($isAllowed('name')) {
            $rules['name'] = [
                $requirement('name'),
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
                        fn ($query) => $query->where(
                            'business_id',
                            $businessId
                        )
                    ),
            ];
        }

        if ($isAllowed('category_id')) {
            $rules['category_id'] = [
                'nullable',
                'integer',
            ];
        }

        if ($isAllowed('type')) {
            $rules['type'] = [
                $requirement('type'),
                Rule::in([
                    'product',
                    'service',
                ]),
            ];
        }

        if ($isAllowed('sac')) {
            $rules['sac'] = [
                $requirement('sac'),
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
                $requirement('price'),
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
            $rules['making_charge'] = [
                'nullable',
                'numeric',
                'min:0',
            ];
        }

        if ($isAllowed('stock_qty')) {
            $rules['stock_qty'] = [
                $requirement('stock_qty'),
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
                $requirement('tax_rate'),
                'numeric',
                'min:0',
                'max:100',
            ];
        }

        if ($isAllowed('is_active')) {
            $rules['is_active'] = [
                'nullable',
                'boolean',
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

        foreach (
            [
                'gross_weight',
                'metal_weight',
                'stone_weight',
                'stone_charges',
                'gold_weight',
                'silver_weight',
                'diamond_weight',
                'diamond_charges',
            ] as $numericField
        ) {
            if ($isAllowed($numericField)) {
                $rules[$numericField] = [
                    'nullable',
                    'numeric',
                    'min:0',
                ];
            }
        }

        foreach (
            [
                'gold_purity',
                'silver_purity',
            ] as $stringField
        ) {
            if ($isAllowed($stringField)) {
                $rules[$stringField] = [
                    'nullable',
                    'string',
                    'max:50',
                ];
            }
        }

        return $rules;
    }

    /**
     * Update validation rules.
     */
    private function buildUpdateRules(
        int $businessId,
        Item $item,
        array $allowedFields,
        array $requiredFields
    ): array {
        $rules = $this->buildStoreRules(
            $businessId,
            $allowedFields,
            $requiredFields
        );

        $rules['barcode'] = [
            'sometimes',
            'nullable',
            'string',
            'max:100',
            Rule::unique('items', 'barcode')
                ->ignore($item->id),
        ];

        if (isset($rules['sku'])) {
            $rules['sku'] = [
                'sometimes',
                'nullable',
                'string',
                'max:100',
                Rule::unique('items', 'sku')
                    ->where(
                        fn ($query) => $query->where(
                            'business_id',
                            $businessId
                        )
                    )
                    ->ignore($item->id),
            ];
        }

        foreach ($rules as $field => $fieldRules) {
            if (
                $field !== 'business_id'
                && $field !== 'barcode'
                && $field !== 'sku'
            ) {
                $rules[$field] = $this->makeRulesOptional(
                    $fieldRules
                );
            }
        }

        return $rules;
    }

    /**
     * Update request ke liye required ko sometimes me change karta hai.
     */
    private function makeRulesOptional(
        array $rules
    ): array {
        $filteredRules = array_values(
            array_filter(
                $rules,
                fn ($rule) => $rule !== 'required'
            )
        );

        if (!in_array('sometimes', $filteredRules, true)) {
            array_unshift($filteredRules, 'sometimes');
        }

        return $filteredRules;
    }

    /**
     * Category isi business ki honi chahiye.
     */
    private function validateCategory(
        int $businessId,
        mixed $categoryId
    ): void {
        if (empty($categoryId)) {
            return;
        }

        $categoryExists = Category::query()
            ->where('id', $categoryId)
            ->where('business_id', $businessId)
            ->exists();

        if (!$categoryExists) {
            throw ValidationException::withMessages([
                'category_id' => [
                    'Invalid category for this business.',
                ],
            ]);
        }
    }

    /**
     * Disallowed fields payload se hata deta hai.
     */
    private function removeDisallowedFields(
        array $payload,
        array $allowedFields,
        array $alwaysAllowed = []
    ): array {
        foreach (array_keys($payload) as $field) {
            if (
                !in_array($field, $allowedFields, true)
                && !in_array($field, $alwaysAllowed, true)
                && $field !== 'business_id'
            ) {
                unset($payload[$field]);
            }
        }

        return $payload;
    }

    /**
     * Service item ke product-only fields clear karta hai.
     */
    private function clearProductFields(
        array $payload
    ): array {
        foreach (
            [
                'making_charge',
                'unit',
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
            ] as $field
        ) {
            $payload[$field] = null;
        }

        return $payload;
    }

    /**
     * Barcode empty string ko null banata hai.
     */
    private function normalizeBarcode(
        mixed $barcode
    ): ?string {
        $barcode = trim((string) $barcode);

        return $barcode !== ''
            ? $barcode
            : null;
    }

    /**
     * Scanner response format.
     */
    private function formatBarcodeItem(
        Item $item
    ): array {
        return [
            'id' => $item->id,
            'business_id' => $item->business_id,
            'name' => $item->name,
            'sku' => $item->sku,
            'barcode' => $item->barcode,
            'description' => $item->description,
            'category_id' => $item->category_id,
            'category' => $item->category,
            'type' => $item->type,
            'sac' => $item->sac,

            'price' => (float) ($item->price ?? 0),
            'cost_price' => (float) ($item->cost_price ?? 0),
            'making_charge' => (float) (
                $item->making_charge ?? 0
            ),

            'tax_rate' => (float) ($item->tax_rate ?? 0),
            'stock_qty' => (float) ($item->stock_qty ?? 0),
            'unit' => $item->unit,

            'metal_type' => $item->metal_type,
            'purity' => $item->purity,
            'gross_weight' => (float) (
                $item->gross_weight ?? 0
            ),
            'metal_weight' => (float) (
                $item->metal_weight ?? 0
            ),
            'stone_weight' => (float) (
                $item->stone_weight ?? 0
            ),
            'stone_charges' => (float) (
                $item->stone_charges ?? 0
            ),

            'gold_weight' => (float) (
                $item->gold_weight ?? 0
            ),
            'gold_purity' => $item->gold_purity,
            'silver_weight' => (float) (
                $item->silver_weight ?? 0
            ),
            'silver_purity' => $item->silver_purity,
            'diamond_weight' => (float) (
                $item->diamond_weight ?? 0
            ),
            'diamond_charges' => (float) (
                $item->diamond_charges ?? 0
            ),

            'is_active' => (bool) $item->is_active,
        ];
    }

    /**
     * Default fields jab business type configuration empty ho.
     */
    private function defaultItemFields(): array
    {
        return [
            'name',
            'sku',
            'barcode',
            'category_id',
            'type',
            'sac',
            'description',
            'price',
            'cost_price',
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

    /**
     * HTTP exception ka proper status.
     */
    private function exceptionStatus(
        \Throwable $exception
    ): int {
        if (
            method_exists($exception, 'getStatusCode')
            && is_int($exception->getStatusCode())
        ) {
            return $exception->getStatusCode();
        }

        return 500;
    }

    /**
     * HTTP exceptions ka useful message preserve karta hai.
     */
    private function safeExceptionMessage(
        \Throwable $exception,
        string $fallback
    ): string {
        $status = $this->exceptionStatus($exception);

        if (
            in_array($status, [
                401,
                403,
                404,
                422,
            ], true)
            && trim($exception->getMessage()) !== ''
        ) {
            return $exception->getMessage();
        }

        return $fallback;
    }
}