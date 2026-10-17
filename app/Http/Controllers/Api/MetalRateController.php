<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MetalRate;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class MetalRateController extends Controller
{
    /**
     * GET /api/metal-rates
     *
     * Filters:
     * business_id
     * metal_type
     * purity
     * rate_date
     * from_date
     * to_date
     * is_active
     * per_page
     */
    // public function index(Request $request): JsonResponse
    // {
    //     try {
    //         $businessId = $this->resolveBusinessId($request);

    //         $validated = $request->validate([
    //             'metal_type' => [
    //                 'nullable',
    //                 Rule::in(['gold', 'silver']),
    //             ],
    //             'purity' => [
    //                 'nullable',
    //                 'string',
    //                 'max:50',
    //             ],
    //             'rate_date' => [
    //                 'nullable',
    //                 'date',
    //             ],
    //             'from_date' => [
    //                 'nullable',
    //                 'date',
    //             ],
    //             'to_date' => [
    //                 'nullable',
    //                 'date',
    //                 'after_or_equal:from_date',
    //             ],
    //             'is_active' => [
    //                 'nullable',
    //                 'boolean',
    //             ],
    //             'per_page' => [
    //                 'nullable',
    //                 'integer',
    //                 'min:1',
    //                 'max:100',
    //             ],
    //         ]);

    //         $perPage = (int) ($validated['per_page'] ?? 20);

    //         $query = MetalRate::withoutGlobalScope('business')
    //             ->where('business_id', $businessId);

    //         if (!empty($validated['metal_type'])) {
    //             $query->where(
    //                 'metal_type',
    //                 $validated['metal_type']
    //             );
    //         }

    //         if (
    //             array_key_exists('purity', $validated)
    //             && $validated['purity'] !== null
    //             && $validated['purity'] !== ''
    //         ) {
    //             $query->where(
    //                 'purity',
    //                 $this->normalizePurity(
    //                     $validated['purity']
    //                 )
    //             );
    //         }

    //         if (!empty($validated['rate_date'])) {
    //             $query->whereDate(
    //                 'rate_date',
    //                 $validated['rate_date']
    //             );
    //         }

    //         if (!empty($validated['from_date'])) {
    //             $query->whereDate(
    //                 'rate_date',
    //                 '>=',
    //                 $validated['from_date']
    //             );
    //         }

    //         if (!empty($validated['to_date'])) {
    //             $query->whereDate(
    //                 'rate_date',
    //                 '<=',
    //                 $validated['to_date']
    //             );
    //         }

    //         if (array_key_exists('is_active', $validated)) {
    //             $query->where(
    //                 'is_active',
    //                 $request->boolean('is_active')
    //             );
    //         }

    //         $rates = $query
    //             ->orderByDesc('rate_date')
    //             ->orderBy('metal_type')
    //             ->orderBy('purity')
    //             ->orderByDesc('id')
    //             ->paginate($perPage);

    //         return response()->json([
    //             'ok' => true,
    //             'msg' => 'Metal rates fetched successfully.',
    //             'data' => $rates,
    //         ]);
    //     } catch (ValidationException $exception) {
    //         return $this->validationError($exception);
    //     } catch (Throwable $exception) {
    //         Log::error('Metal rates list API failed', [
    //             'business_id' => $request->input('business_id'),
    //             'user_id' => $request->user()?->id,
    //             'message' => $exception->getMessage(),
    //             'file' => $exception->getFile(),
    //             'line' => $exception->getLine(),
    //         ]);

    //         return $this->serverError(
    //             $exception,
    //             'Unable to fetch metal rates.'
    //         );
    //     }
    // }



public function index(Request $request): JsonResponse
{
    try {
        $businessId = $this->resolveBusinessId($request);

        $validated = $request->validate([
            'metal_type' => [
                'nullable',
                Rule::in(['gold', 'silver']),
            ],
            'purity' => [
                'nullable',
                'string',
                'max:50',
            ],

            /*
             * Exact date filter.
             */
            'date' => [
                'nullable',
                'date',
            ],

            'from_date' => [
                'nullable',
                'date',
            ],
            'to_date' => [
                'nullable',
                'date',
                'after_or_equal:from_date',
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],
            'per_page' => [
                'nullable',
                'integer',
                'min:1',
                'max:100',
            ],
        ]);

        $perPage = (int) ($validated['per_page'] ?? 20);

        $query = MetalRate::withoutGlobalScope('business')
            ->where('business_id', $businessId);

        /*
         * Metal type filter.
         */
        if (!empty($validated['metal_type'])) {
            $query->where(
                'metal_type',
                $validated['metal_type']
            );
        }

        /*
         * Purity filter.
         */
        if (
            array_key_exists('purity', $validated)
            && $validated['purity'] !== null
            && $validated['purity'] !== ''
        ) {
            $query->where(
                'purity',
                $this->normalizePurity(
                    $validated['purity']
                )
            );
        }

        /*
         * Exact date filter.
         *
         * Example:
         * /api/metal-rates?date=2026-07-31
         */
        if (!empty($validated['date'])) {
            $query->whereDate(
                'rate_date',
                $validated['date']
            );
        }

        /*
         * Date range filter.
         *
         * Exact date diya gaya ho to from_date aur to_date
         * filters apply nahi honge.
         */
        if (empty($validated['date'])) {
            if (!empty($validated['from_date'])) {
                $query->whereDate(
                    'rate_date',
                    '>=',
                    $validated['from_date']
                );
            }

            if (!empty($validated['to_date'])) {
                $query->whereDate(
                    'rate_date',
                    '<=',
                    $validated['to_date']
                );
            }
        }

        /*
         * Active status filter.
         */
        if (
            array_key_exists('is_active', $validated)
            && $validated['is_active'] !== null
        ) {
            $query->where(
                'is_active',
                (bool) $validated['is_active']
            );
        }

        $rates = $query
            ->orderByDesc('rate_date')
            ->orderBy('metal_type')
            ->orderBy('purity')
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();

        return response()->json([
            'ok' => true,
            'msg' => 'Metal rates fetched successfully.',
            'filters' => [
                'date' => $validated['date'] ?? null,
                'from_date' => $validated['from_date'] ?? null,
                'to_date' => $validated['to_date'] ?? null,
                'metal_type' => $validated['metal_type'] ?? null,
                'purity' => $validated['purity'] ?? null,
                'is_active' => $validated['is_active'] ?? null,
            ],
            'data' => $rates,
        ]);
    } catch (ValidationException $exception) {
        return $this->validationError($exception);
    } catch (Throwable $exception) {
        Log::error('Metal rates list API failed', [
            'business_id' => $request->input('business_id'),
            'user_id' => $request->user()?->id,
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
        ]);

        return $this->serverError(
            $exception,
            'Unable to fetch metal rates.'
        );
    }
}




    /**
     * GET /api/metal-rates/single
     *
     * Single record using filters.
     *
     * Filters:
     * business_id
     * id
     * rate_date
     * metal_type
     * purity
     * is_active
     */
    public function single(Request $request): JsonResponse
    {
        try {
            $businessId = $this->resolveBusinessId($request);

            $validated = $request->validate([
                'id' => [
                    'nullable',
                    'integer',
                    'min:1',
                ],
                'rate_date' => [
                    'nullable',
                    'date',
                ],
                'metal_type' => [
                    'nullable',
                    Rule::in(['gold', 'silver']),
                ],
                'purity' => [
                    'nullable',
                    'string',
                    'max:50',
                ],
                'is_active' => [
                    'nullable',
                    'boolean',
                ],
            ]);

            $hasFilter =
                !empty($validated['id'])
                || !empty($validated['rate_date'])
                || !empty($validated['metal_type'])
                || (
                    array_key_exists('purity', $validated)
                    && $validated['purity'] !== null
                    && $validated['purity'] !== ''
                )
                || array_key_exists('is_active', $validated);

            if (!$hasFilter) {
                return response()->json([
                    'ok' => false,
                    'msg' => 'At least one metal rate filter is required.',
                    'code' => 'VALIDATION_ERROR',
                    'errors' => [
                        'filters' => [
                            'Send id, rate_date, metal_type, purity or is_active.',
                        ],
                    ],
                ], 422);
            }

            $query = MetalRate::withoutGlobalScope('business')
                ->where('business_id', $businessId);

            if (!empty($validated['id'])) {
                $query->where('id', $validated['id']);
            }

            if (!empty($validated['rate_date'])) {
                $query->whereDate(
                    'rate_date',
                    $validated['rate_date']
                );
            }

            if (!empty($validated['metal_type'])) {
                $query->where(
                    'metal_type',
                    $validated['metal_type']
                );
            }

            if (
                array_key_exists('purity', $validated)
                && $validated['purity'] !== null
                && $validated['purity'] !== ''
            ) {
                $query->where(
                    'purity',
                    $this->normalizePurity(
                        $validated['purity']
                    )
                );
            }

            if (array_key_exists('is_active', $validated)) {
                $query->where(
                    'is_active',
                    $request->boolean('is_active')
                );
            }

            $metalRate = $query
                ->orderByDesc('rate_date')
                ->orderByDesc('id')
                ->first();

            if (!$metalRate) {
                return response()->json([
                    'ok' => false,
                    'msg' => 'Metal rate not found for this business.',
                    'code' => 'METAL_RATE_NOT_FOUND',
                ], 404);
            }

            return response()->json([
                'ok' => true,
                'msg' => 'Metal rate fetched successfully.',
                'data' => $metalRate,
            ]);
        } catch (ValidationException $exception) {
            return $this->validationError($exception);
        } catch (Throwable $exception) {
            Log::error('Single metal rate API failed', [
                'business_id' => $request->input('business_id'),
                'user_id' => $request->user()?->id,
                'message' => $exception->getMessage(),
            ]);

            return $this->serverError(
                $exception,
                'Unable to fetch metal rate.'
            );
        }
    }

    /**
     * GET /api/metal-rates/show/{id}
     */
    public function show(
        Request $request,
        int $id
    ): JsonResponse {
        try {
            $businessId = $this->resolveBusinessId($request);

            $metalRate = $this->findBusinessRate(
                $businessId,
                $id
            );

            if (!$metalRate) {
                return response()->json([
                    'ok' => false,
                    'msg' => 'Metal rate not found for this business.',
                    'code' => 'METAL_RATE_NOT_FOUND',
                ], 404);
            }

            return response()->json([
                'ok' => true,
                'msg' => 'Metal rate fetched successfully.',
                'data' => $metalRate,
            ]);
        } catch (Throwable $exception) {
            Log::error('Metal rate show API failed', [
                'business_id' => $request->input('business_id'),
                'metal_rate_id' => $id,
                'user_id' => $request->user()?->id,
                'message' => $exception->getMessage(),
            ]);

            return $this->serverError(
                $exception,
                'Unable to fetch metal rate.'
            );
        }
    }

    /**
     * POST /api/metal-rates/store
     */
    public function store(Request $request): JsonResponse
    {
        $businessId = (int) (
            $request->input('business_id')
            ?: $request->header('X-Business-ID')
        );

        try {
            $businessId = $this->resolveBusinessId($request);

            $validated = $request->validate(
                $this->storeRules(),
                $this->validationMessages()
            );

            $purity = $this->normalizePurity(
                $validated['purity'] ?? ''
            );

            $duplicate = MetalRate::withoutGlobalScope('business')
                ->where('business_id', $businessId)
                ->whereDate(
                    'rate_date',
                    $validated['rate_date']
                )
                ->where(
                    'metal_type',
                    $validated['metal_type']
                )
                ->where('purity', $purity)
                ->exists();

            if ($duplicate) {
                return response()->json([
                    'ok' => false,
                    'msg' => 'Metal rate already exists.',
                    'code' => 'DUPLICATE_METAL_RATE',
                    'errors' => [
                        'rate_date' => [
                            'This business already has a rate for the selected date, metal and purity.',
                        ],
                    ],
                ], 422);
            }

            $metalRate = MetalRate::withoutGlobalScope('business')
                ->create([
                    'business_id' => $businessId,
                    'rate_date' => $validated['rate_date'],
                    'metal_type' => $validated['metal_type'],
                    'purity' => $purity,
                    'rate_per_gram' => $validated['rate_per_gram'],
                    'is_active' => array_key_exists(
                        'is_active',
                        $validated
                    )
                        ? $request->boolean('is_active')
                        : true,
                ]);

            return response()->json([
                'ok' => true,
                'msg' => 'Metal rate created successfully.',
                'data' => $metalRate,
            ], 201);
        } catch (ValidationException $exception) {
            return $this->validationError($exception);
        } catch (QueryException $exception) {
            Log::error('Metal rate database create error', [
                'business_id' => $businessId,
                'message' => $exception->getMessage(),
            ]);

            if ((string) $exception->getCode() === '23000') {
                return response()->json([
                    'ok' => false,
                    'msg' => 'Metal rate already exists.',
                    'code' => 'DUPLICATE_METAL_RATE',
                    'errors' => [
                        'rate_date' => [
                            'A rate already exists for this business, date, metal and purity.',
                        ],
                    ],
                ], 422);
            }

            return $this->serverError(
                $exception,
                'Server error while creating metal rate.'
            );
        } catch (Throwable $exception) {
            Log::error('Metal rate create API failed', [
                'business_id' => $businessId,
                'user_id' => $request->user()?->id,
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);

            return $this->serverError(
                $exception,
                'Server error while creating metal rate.'
            );
        }
    }

    /**
     * POST/PUT/PATCH /api/metal-rates/update/{id}
     *
     * Partial update supported.
     */
    public function update(
        Request $request,
        int $id
    ): JsonResponse {
        $businessId = (int) (
            $request->input('business_id')
            ?: $request->header('X-Business-ID')
        );

        try {
            $businessId = $this->resolveBusinessId($request);

            $metalRate = $this->findBusinessRate(
                $businessId,
                $id
            );

            if (!$metalRate) {
                return response()->json([
                    'ok' => false,
                    'msg' => 'Metal rate not found for this business.',
                    'code' => 'METAL_RATE_NOT_FOUND',
                ], 404);
            }

            $validated = $request->validate(
                $this->updateRules(),
                $this->validationMessages()
            );

            $rateDate = $validated['rate_date']
                ?? $metalRate->rate_date->toDateString();

            $metalType = $validated['metal_type']
                ?? $metalRate->metal_type;

            $purity = array_key_exists('purity', $validated)
                ? $this->normalizePurity(
                    $validated['purity']
                )
                : $this->normalizePurity(
                    $metalRate->purity
                );

            $duplicate = MetalRate::withoutGlobalScope('business')
                ->where('business_id', $businessId)
                ->whereDate('rate_date', $rateDate)
                ->where('metal_type', $metalType)
                ->where('purity', $purity)
                ->where('id', '!=', $metalRate->id)
                ->exists();

            if ($duplicate) {
                return response()->json([
                    'ok' => false,
                    'msg' => 'Another metal rate already exists.',
                    'code' => 'DUPLICATE_METAL_RATE',
                    'errors' => [
                        'rate_date' => [
                            'Another rate already exists for this business, date, metal and purity.',
                        ],
                    ],
                ], 422);
            }

            $payload = [];

            if (array_key_exists('rate_date', $validated)) {
                $payload['rate_date'] =
                    $validated['rate_date'];
            }

            if (array_key_exists('metal_type', $validated)) {
                $payload['metal_type'] =
                    $validated['metal_type'];
            }

            if (array_key_exists('purity', $validated)) {
                $payload['purity'] = $purity;
            }

            if (array_key_exists('rate_per_gram', $validated)) {
                $payload['rate_per_gram'] =
                    $validated['rate_per_gram'];
            }

            if (array_key_exists('is_active', $validated)) {
                $payload['is_active'] =
                    $request->boolean('is_active');
            }

            if (empty($payload)) {
                return response()->json([
                    'ok' => false,
                    'msg' => 'No update fields were provided.',
                    'code' => 'VALIDATION_ERROR',
                    'errors' => [
                        'fields' => [
                            'Send at least one field to update.',
                        ],
                    ],
                ], 422);
            }

            $metalRate->update($payload);
            $metalRate->refresh();

            return response()->json([
                'ok' => true,
                'msg' => 'Metal rate updated successfully.',
                'data' => $metalRate,
            ]);
        } catch (ValidationException $exception) {
            return $this->validationError($exception);
        } catch (QueryException $exception) {
            Log::error('Metal rate database update error', [
                'business_id' => $businessId,
                'metal_rate_id' => $id,
                'message' => $exception->getMessage(),
            ]);

            if ((string) $exception->getCode() === '23000') {
                return response()->json([
                    'ok' => false,
                    'msg' => 'Another metal rate already exists.',
                    'code' => 'DUPLICATE_METAL_RATE',
                ], 422);
            }

            return $this->serverError(
                $exception,
                'Server error while updating metal rate.'
            );
        } catch (Throwable $exception) {
            Log::error('Metal rate update API failed', [
                'business_id' => $businessId,
                'metal_rate_id' => $id,
                'user_id' => $request->user()?->id,
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);

            return $this->serverError(
                $exception,
                'Server error while updating metal rate.'
            );
        }
    }

    /**
     * DELETE /api/metal-rates/delete/{id}
     */
    public function destroy(
        Request $request,
        int $id
    ): JsonResponse {
        try {
            $businessId = $this->resolveBusinessId($request);

            $metalRate = $this->findBusinessRate(
                $businessId,
                $id
            );

            if (!$metalRate) {
                return response()->json([
                    'ok' => false,
                    'msg' => 'Metal rate not found for this business.',
                    'code' => 'METAL_RATE_NOT_FOUND',
                ], 404);
            }

            $metalRate->delete();

            return response()->json([
                'ok' => true,
                'msg' => 'Metal rate deleted successfully.',
            ]);
        } catch (Throwable $exception) {
            Log::error('Metal rate delete API failed', [
                'business_id' => $request->input('business_id'),
                'metal_rate_id' => $id,
                'user_id' => $request->user()?->id,
                'message' => $exception->getMessage(),
            ]);

            return $this->serverError(
                $exception,
                'Unable to delete metal rate.'
            );
        }
    }

    /**
     * POST/PATCH /api/metal-rates/status/{id}
     */
    public function toggle(
        Request $request,
        int $id
    ): JsonResponse {
        try {
            $businessId = $this->resolveBusinessId($request);

            $metalRate = $this->findBusinessRate(
                $businessId,
                $id
            );

            if (!$metalRate) {
                return response()->json([
                    'ok' => false,
                    'msg' => 'Metal rate not found for this business.',
                    'code' => 'METAL_RATE_NOT_FOUND',
                ], 404);
            }

            $metalRate->update([
                'is_active' => !$metalRate->is_active,
            ]);

            return response()->json([
                'ok' => true,
                'msg' => 'Metal rate status updated successfully.',
                'data' => $metalRate->fresh(),
            ]);
        } catch (Throwable $exception) {
            Log::error('Metal rate toggle API failed', [
                'business_id' => $request->input('business_id'),
                'metal_rate_id' => $id,
                'user_id' => $request->user()?->id,
                'message' => $exception->getMessage(),
            ]);

            return $this->serverError(
                $exception,
                'Unable to update metal rate status.'
            );
        }
    }

    /**
     * GET /api/metal-rates/latest
     *
     * Required:
     * business_id
     * metal_type
     *
     * Optional:
     * purity
     */
    public function latestRate(Request $request): JsonResponse
    {
        try {
            $businessId = $this->resolveBusinessId($request);

            $validated = $request->validate([
                'metal_type' => [
                    'required',
                    Rule::in(['gold', 'silver']),
                ],
                'purity' => [
                    'nullable',
                    'string',
                    'max:50',
                ],
                'is_active' => [
                    'nullable',
                    'boolean',
                ],
            ]);

            $query = MetalRate::withoutGlobalScope('business')
                ->where('business_id', $businessId)
                ->where(
                    'metal_type',
                    $validated['metal_type']
                );

            if (
                array_key_exists('purity', $validated)
                && $validated['purity'] !== null
                && $validated['purity'] !== ''
            ) {
                $query->where(
                    'purity',
                    $this->normalizePurity(
                        $validated['purity']
                    )
                );
            }

            $query->where(
                'is_active',
                array_key_exists('is_active', $validated)
                    ? $request->boolean('is_active')
                    : true
            );

            $metalRate = $query
                ->orderByDesc('rate_date')
                ->orderByDesc('id')
                ->first();

            if (!$metalRate) {
                return response()->json([
                    'ok' => false,
                    'msg' => 'Metal rate not found for this business.',
                    'code' => 'METAL_RATE_NOT_FOUND',
                ], 404);
            }

            return response()->json([
                'ok' => true,
                'msg' => 'Latest metal rate fetched successfully.',
                'data' => $metalRate,
            ]);
        } catch (ValidationException $exception) {
            return $this->validationError($exception);
        } catch (Throwable $exception) {
            Log::error('Latest metal rate API failed', [
                'business_id' => $request->input('business_id'),
                'user_id' => $request->user()?->id,
                'message' => $exception->getMessage(),
            ]);

            return $this->serverError(
                $exception,
                'Unable to fetch latest metal rate.'
            );
        }
    }

    /**
     * GET /api/metal-rates/by-date
     */
    public function rateByDate(Request $request): JsonResponse
    {
        try {
            $businessId = $this->resolveBusinessId($request);

            $validated = $request->validate([
                'date' => [
                    'required',
                    'date',
                ],
                'metal_type' => [
                    'required',
                    Rule::in(['gold', 'silver']),
                ],
                'purity' => [
                    'required',
                    'string',
                    'max:50',
                ],
                'is_active' => [
                    'nullable',
                    'boolean',
                ],
            ]);

            $query = MetalRate::withoutGlobalScope('business')
                ->where('business_id', $businessId)
                ->whereDate(
                    'rate_date',
                    $validated['date']
                )
                ->where(
                    'metal_type',
                    $validated['metal_type']
                )
                ->where(
                    'purity',
                    $this->normalizePurity(
                        $validated['purity']
                    )
                );

            if (array_key_exists('is_active', $validated)) {
                $query->where(
                    'is_active',
                    $request->boolean('is_active')
                );
            }

            $metalRate = $query->first();

            if (!$metalRate) {
                return response()->json([
                    'ok' => false,
                    'msg' => 'Metal rate not found for the selected business and filters.',
                    'code' => 'METAL_RATE_NOT_FOUND',
                ], 404);
            }

            return response()->json([
                'ok' => true,
                'msg' => 'Metal rate fetched successfully.',
                'data' => $metalRate,
            ]);
        } catch (ValidationException $exception) {
            return $this->validationError($exception);
        } catch (Throwable $exception) {
            Log::error('Rate by date API failed', [
                'business_id' => $request->input('business_id'),
                'user_id' => $request->user()?->id,
                'message' => $exception->getMessage(),
            ]);

            return $this->serverError(
                $exception,
                'Unable to fetch metal rate.'
            );
        }
    }

    /**
     * GET /api/metal-rates/current
     *
     * हर metal/purity का latest active record लौटाएगा।
     */
    public function currentRates(Request $request): JsonResponse
    {
        try {
            $businessId = $this->resolveBusinessId($request);

            $rates = MetalRate::withoutGlobalScope('business')
                ->where('business_id', $businessId)
                ->where('is_active', true)
                ->orderByDesc('rate_date')
                ->orderByDesc('id')
                ->get();

            $latestRates = $rates
                ->unique(function (MetalRate $rate) {
                    return $rate->metal_type
                        . '|'
                        . $rate->purity;
                })
                ->values();

            return response()->json([
                'ok' => true,
                'msg' => 'Current metal rates fetched successfully.',
                'data' => [
                    'gold' => $latestRates
                        ->where('metal_type', 'gold')
                        ->values(),
                    'silver' => $latestRates
                        ->where('metal_type', 'silver')
                        ->values(),
                ],
            ]);
        } catch (Throwable $exception) {
            Log::error('Current metal rates API failed', [
                'business_id' => $request->input('business_id'),
                'user_id' => $request->user()?->id,
                'message' => $exception->getMessage(),
            ]);

            return $this->serverError(
                $exception,
                'Unable to fetch current metal rates.'
            );
        }
    }

    /**
     * POST /api/metal-rates/today
     *
     * JSON:
     *
     * {
     *   "business_id": 56,
     *   "rates": {
     *     "gold": {
     *       "24K": 8000,
     *       "22K": 7000
     *     },
     *     "silver": {
     *       "999": 110
     *     }
     *   }
     * }
     */
    public function storeToday(Request $request): JsonResponse
    {
        $businessId = (int) (
            $request->input('business_id')
            ?: $request->header('X-Business-ID')
        );

        try {
            $businessId = $this->resolveBusinessId($request);

            $validated = $request->validate([
                'rates' => [
                    'nullable',
                    'array',
                ],
                'rates.gold' => [
                    'nullable',
                    'array',
                ],
                'rates.silver' => [
                    'nullable',
                    'array',
                ],
                'rates.gold.*' => [
                    'nullable',
                    'numeric',
                    'min:0',
                ],
                'rates.silver.*' => [
                    'nullable',
                    'numeric',
                    'min:0',
                ],

                'custom' => [
                    'nullable',
                    'array',
                ],
                'custom.gold' => [
                    'nullable',
                    'array',
                ],
                'custom.gold.*.purity' => [
                    'nullable',
                    'string',
                    'max:50',
                ],
                'custom.gold.*.rate' => [
                    'nullable',
                    'numeric',
                    'min:0',
                ],
                'custom.silver' => [
                    'nullable',
                    'array',
                ],
                'custom.silver.*.purity' => [
                    'nullable',
                    'string',
                    'max:50',
                ],
                'custom.silver.*.rate' => [
                    'nullable',
                    'numeric',
                    'min:0',
                ],
            ]);

            $rates = $validated['rates'] ?? [];
            $custom = $validated['custom'] ?? [];
            $today = Carbon::today()->toDateString();

            $savedIds = [];

            DB::transaction(function () use (
                $businessId,
                $rates,
                $custom,
                $today,
                &$savedIds
            ): void {
                foreach (['gold', 'silver'] as $metal) {
                    foreach (
                        ($rates[$metal] ?? [])
                        as $purity => $value
                    ) {
                        if ($value === null || $value === '') {
                            continue;
                        }

                        $normalizedPurity =
                            $this->normalizePurity($purity);

                        if ($normalizedPurity === '') {
                            continue;
                        }

                        $metalRate = MetalRate::withoutGlobalScope(
                            'business'
                        )->updateOrCreate(
                            [
                                'business_id' => $businessId,
                                'rate_date' => $today,
                                'metal_type' => $metal,
                                'purity' => $normalizedPurity,
                            ],
                            [
                                'rate_per_gram' => $value,
                                'is_active' => true,
                            ]
                        );

                        $savedIds[] = $metalRate->id;
                    }
                }

                foreach (['gold', 'silver'] as $metal) {
                    foreach (
                        ($custom[$metal] ?? [])
                        as $customRate
                    ) {
                        $purity = $this->normalizePurity(
                            $customRate['purity'] ?? ''
                        );

                        $value = $customRate['rate'] ?? null;

                        if (
                            $purity === ''
                            || $value === null
                            || $value === ''
                        ) {
                            continue;
                        }

                        $metalRate = MetalRate::withoutGlobalScope(
                            'business'
                        )->updateOrCreate(
                            [
                                'business_id' => $businessId,
                                'rate_date' => $today,
                                'metal_type' => $metal,
                                'purity' => $purity,
                            ],
                            [
                                'rate_per_gram' => $value,
                                'is_active' => true,
                            ]
                        );

                        $savedIds[] = $metalRate->id;
                    }
                }
            });

            $savedIds = array_values(
                array_unique($savedIds)
            );

            $savedRates = MetalRate::withoutGlobalScope('business')
                ->where('business_id', $businessId)
                ->whereIn('id', $savedIds)
                ->orderBy('metal_type')
                ->orderBy('purity')
                ->get();

            return response()->json([
                'ok' => true,
                'msg' => 'Today metal rates saved successfully.',
                'rate_date' => $today,
                'saved_count' => $savedRates->count(),
                'data' => $savedRates,
            ]);
        } catch (ValidationException $exception) {
            return $this->validationError($exception);
        } catch (QueryException $exception) {
            Log::error('Today metal rates database error', [
                'business_id' => $businessId,
                'message' => $exception->getMessage(),
            ]);

            if ((string) $exception->getCode() === '23000') {
                return response()->json([
                    'ok' => false,
                    'msg' => 'Duplicate metal rate detected.',
                    'code' => 'DUPLICATE_METAL_RATE',
                ], 422);
            }

            return $this->serverError(
                $exception,
                'Unable to save today metal rates.'
            );
        } catch (Throwable $exception) {
            Log::error('Today metal rates API failed', [
                'business_id' => $businessId,
                'user_id' => $request->user()?->id,
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
            ]);

            return $this->serverError(
                $exception,
                'Unable to save today metal rates.'
            );
        }
    }

    /**
     * Authenticated user के business access को verify करता है।
     *
     * business_id body/query से या X-Business-ID header से लिया जाएगा।
     */
    private function resolveBusinessId(Request $request): int
    {
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
     * ID और business दोनों से record find करता है।
     */
    private function findBusinessRate(
        int $businessId,
        int $id
    ): ?MetalRate {
        return MetalRate::withoutGlobalScope('business')
            ->where('business_id', $businessId)
            ->where('id', $id)
            ->first();
    }

    /**
     * Store validation rules.
     */
    private function storeRules(): array
    {
        return [
            'rate_date' => [
                'required',
                'date',
            ],
            'metal_type' => [
                'required',
                Rule::in(['gold', 'silver']),
            ],
            'purity' => [
                'required',
                'string',
                'max:50',
            ],
            'rate_per_gram' => [
                'required',
                'numeric',
                'min:0',
            ],
            'is_active' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    /**
     * Update में केवल भेजे गए fields update होंगे।
     */
    private function updateRules(): array
    {
        return [
            'rate_date' => [
                'sometimes',
                'date',
            ],
            'metal_type' => [
                'sometimes',
                Rule::in(['gold', 'silver']),
            ],
            'purity' => [
                'sometimes',
                'nullable',
                'string',
                'max:50',
            ],
            'rate_per_gram' => [
                'sometimes',
                'numeric',
                'min:0',
            ],
            'is_active' => [
                'sometimes',
                'boolean',
            ],
        ];
    }

    /**
     * Validation messages.
     */
    private function validationMessages(): array
    {
        return [
            'rate_date.required' => 'Rate date is required.',
            'rate_date.date' => 'Rate date must be a valid date.',
            'metal_type.required' => 'Metal type is required.',
            'metal_type.in' => 'Metal type must be gold or silver.',
            'purity.required' => 'Purity is required.',
            'rate_per_gram.required' =>
                'Rate per gram is required.',
            'rate_per_gram.numeric' =>
                'Rate per gram must be numeric.',
            'rate_per_gram.min' =>
                'Rate per gram cannot be negative.',
            'is_active.boolean' =>
                'is_active must be true, false, 1 or 0.',
        ];
    }

    /**
     * Validation exception response.
     */
    private function validationError(
        ValidationException $exception
    ): JsonResponse {
        return response()->json([
            'ok' => false,
            'msg' => 'Validation failed.',
            'code' => 'VALIDATION_ERROR',
            'errors' => $exception->errors(),
        ], 422);
    }

    /**
     * Exception response.
     */
    private function serverError(
        Throwable $exception,
        string $fallback
    ): JsonResponse {
        return response()->json([
            'ok' => false,
            'msg' => $this->safeExceptionMessage(
                $exception,
                $fallback
            ),
            'code' => 'SERVER_ERROR',
        ], $this->exceptionStatus($exception));
    }

    /**
     * HTTP exception status निकालता है।
     */
    private function exceptionStatus(
        Throwable $exception
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
     * 401, 403, 404, 422 का actual message रखता है।
     */
    private function safeExceptionMessage(
        Throwable $exception,
        string $fallback
    ): string {
        $status = $this->exceptionStatus($exception);

        if (
            in_array(
                $status,
                [401, 403, 404, 422],
                true
            )
            && trim($exception->getMessage()) !== ''
        ) {
            return $exception->getMessage();
        }

        return $fallback;
    }

    /**
     * Purity normalize करता है।
     */
    private function normalizePurity(mixed $purity): string
    {
        return strtoupper(
            trim((string) ($purity ?? ''))
        );
    }
}