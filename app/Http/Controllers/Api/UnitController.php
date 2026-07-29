<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Throwable;

class UnitController extends Controller
{
    /**
     * List units.
     *
     * business_id = null:
     * Sabhi businesses ko dikhegi.
     *
     * business_id = current business:
     * Sirf current business ko dikhegi.
     */
    public function index(Request $request)
    {
        $businessId = $this->resolveBusinessId($request);

        $q = trim((string) $request->query('q', ''));

        $perPage = (int) $request->query('per_page', 50);
        $perPage = max(1, min($perPage, 200));

        $units = Unit::query()
            /*
             * BelongsToBusiness global scope ko remove karna zaroori hai,
             * warna business_id = null wali units hide ho sakti hain.
             */
            ->withoutGlobalScope('business')
            ->where(function ($query) use ($businessId) {
                /*
                 * Global units har business ko milengi.
                 */
                $query->whereNull('business_id');

                /*
                 * Current business ki units bhi milengi.
                 */
                if ($businessId !== null) {
                    $query->orWhere(
                        'business_id',
                        $businessId
                    );
                }
            })
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($searchQuery) use ($q) {
                    $searchQuery
                        ->where('name', 'like', "%{$q}%")
                        ->orWhere('description', 'like', "%{$q}%");
                });
            })
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        return response()->json([
            'ok' => true,
            'message' => 'Units fetched successfully.',
            'business_id' => $businessId,
            'q' => $q,
            'data' => $units,
        ]);
    }

    /**
     * Create unit.
     *
     * Business resolve hua:
     * Business-specific unit create hogi.
     *
     * Business resolve nahi hua:
     * business_id null ke saath global unit create hogi.
     */
    public function store(Request $request)
    {
        $businessId = $this->resolveBusinessId($request);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',

                Rule::unique('units', 'name')
                    ->where(function ($query) use ($businessId) {
                        if ($businessId !== null) {
                            $query->where(
                                'business_id',
                                $businessId
                            );
                        } else {
                            $query->whereNull('business_id');
                        }
                    }),
            ],

            'description' => [
                'nullable',
                'string',
                'max:2000',
            ],

            /*
             * Optional hai, kyunki header ya current_business_id
             * se bhi business resolve ho sakta hai.
             */
            'business_id' => [
                'nullable',
                'integer',
                'exists:businesses,id',
            ],
        ], [
            'name.required' => 'Unit name is required.',
            'name.unique' => $businessId !== null
                ? 'This unit already exists for this business.'
                : 'This global unit already exists.',
            'name.max' => 'Unit name cannot exceed 255 characters.',
            'description.max' => 'Description cannot exceed 2000 characters.',
            'business_id.exists' => 'Selected business does not exist.',
        ]);

        /*
         * withoutEvents use kiya hai taaki BelongsToBusiness trait
         * business_id automatically overwrite na kare.
         */
        $unit = Unit::withoutEvents(function () use (
            $validated,
            $businessId
        ) {
            return Unit::create([
                'business_id' => $businessId,
                'name' => trim($validated['name']),
                'description' => filled(
                    $validated['description'] ?? null
                )
                    ? trim($validated['description'])
                    : null,
            ]);
        });

        return response()->json([
            'ok' => true,
            'message' => $businessId !== null
                ? 'Unit created successfully.'
                : 'Global unit created successfully.',
            'unit' => $this->unitResponse($unit),
        ], 201);
    }

    /**
     * Show unit.
     */
    public function show(Request $request, int $id)
    {
        $businessId = $this->resolveBusinessId($request);

        $unit = $this->findAccessibleUnit(
            $id,
            $businessId
        );

        if (!$unit) {
            return response()->json([
                'ok' => false,
                'message' => 'Unit not found.',
            ], 404);
        }

        return response()->json([
            'ok' => true,
            'unit' => $this->unitResponse($unit),
        ]);
    }

    /**
     * Update unit.
     */
    public function update(Request $request, int $id)
    {
        $businessId = $this->resolveBusinessId($request);

        $unit = $this->findAccessibleUnit(
            $id,
            $businessId
        );

        if (!$unit) {
            return response()->json([
                'ok' => false,
                'message' => 'Unit not found or access denied.',
            ], 404);
        }

        /*
         * Unit jis scope ki hai usi scope me duplicate check hoga.
         *
         * Global unit:
         * business_id null ke against check.
         *
         * Business unit:
         * uske business_id ke against check.
         */
        $unitBusinessId = $unit->business_id !== null
            ? (int) $unit->business_id
            : null;

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',

                Rule::unique('units', 'name')
                    ->where(function ($query) use ($unitBusinessId) {
                        if ($unitBusinessId !== null) {
                            $query->where(
                                'business_id',
                                $unitBusinessId
                            );
                        } else {
                            $query->whereNull('business_id');
                        }
                    })
                    ->ignore($unit->id),
            ],

            'description' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ], [
            'name.required' => 'Unit name is required.',
            'name.unique' => $unitBusinessId !== null
                ? 'This unit already exists for this business.'
                : 'This global unit already exists.',
            'name.max' => 'Unit name cannot exceed 255 characters.',
            'description.max' => 'Description cannot exceed 2000 characters.',
        ]);

        $unit->update([
            'name' => trim($validated['name']),
            'description' => filled(
                $validated['description'] ?? null
            )
                ? trim($validated['description'])
                : null,
        ]);

        $unit->refresh();

        return response()->json([
            'ok' => true,
            'message' => 'Unit updated successfully.',
            'unit' => $this->unitResponse($unit),
        ]);
    }

    /**
     * Delete unit.
     */
    public function destroy(Request $request, int $id)
    {
        $businessId = $this->resolveBusinessId($request);

        $unit = $this->findAccessibleUnit(
            $id,
            $businessId
        );

        if (!$unit) {
            return response()->json([
                'ok' => false,
                'message' => 'Unit not found or access denied.',
            ], 404);
        }

        try {
            $unit->delete();

            return response()->json([
                'ok' => true,
                'message' => 'Unit deleted successfully.',
            ]);
        } catch (Throwable $exception) {
            report($exception);

            $message = in_array(
                (string) $exception->getCode(),
                ['23000', '23503'],
                true
            )
                ? 'Cannot delete this unit because it is linked with items.'
                : 'Unit delete failed. Please try again.';

            return response()->json([
                'ok' => false,
                'message' => $message,
            ], 422);
        }
    }

    /**
     * Quick create unit for dropdown/dialog.
     */
    public function quickStore(Request $request)
    {
        $businessId = $this->resolveBusinessId($request);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:150',

                Rule::unique('units', 'name')
                    ->where(function ($query) use ($businessId) {
                        if ($businessId !== null) {
                            $query->where(
                                'business_id',
                                $businessId
                            );
                        } else {
                            $query->whereNull('business_id');
                        }
                    }),
            ],

            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'business_id' => [
                'nullable',
                'integer',
                'exists:businesses,id',
            ],
        ], [
            'name.required' => 'Unit name is required.',
            'name.unique' => $businessId !== null
                ? 'This unit already exists for this business.'
                : 'This global unit already exists.',
        ]);

        $unit = Unit::withoutEvents(function () use (
            $validated,
            $businessId
        ) {
            return Unit::create([
                'business_id' => $businessId,
                'name' => trim($validated['name']),
                'description' => filled(
                    $validated['description'] ?? null
                )
                    ? trim($validated['description'])
                    : null,
            ]);
        });

        return response()->json([
            'ok' => true,
            'message' => 'Unit created successfully.',
            'unit' => $this->unitResponse($unit),
        ], 201);
    }

    /**
     * Resolve business ID for API.
     *
     * Priority:
     * 1. X-Business-Id header
     * 2. business_id request/query
     * 3. User current_business_id
     * 4. User business_id
     *
     * Business na mile to null return hoga.
     */
    private function resolveBusinessId(Request $request): ?int
    {
        $businessId = $request->header('X-Business-Id')
            ?? $request->input('business_id')
            ?? $request->query('business_id')
            ?? $request->user()?->current_business_id
            ?? $request->user()?->business_id;

        return filled($businessId)
            ? (int) $businessId
            : null;
    }

    /**
     * Find unit accessible for current business.
     *
     * Accessible:
     * business_id null
     * OR
     * business_id current business ke equal
     */
    private function findAccessibleUnit(
        int $id,
        ?int $businessId
    ): ?Unit {
        return Unit::query()
            ->withoutGlobalScope('business')
            ->where('id', $id)
            ->where(function ($query) use ($businessId) {
                $query->whereNull('business_id');

                if ($businessId !== null) {
                    $query->orWhere(
                        'business_id',
                        $businessId
                    );
                }
            })
            ->first();
    }

    /**
     * Consistent API response.
     */
    private function unitResponse(Unit $unit): array
    {
        return [
            'id' => $unit->id,
            'business_id' => $unit->business_id,
            'name' => $unit->name,
            'description' => $unit->description,
            'created_at' => $unit->created_at,
            'updated_at' => $unit->updated_at,
        ];
    }
}