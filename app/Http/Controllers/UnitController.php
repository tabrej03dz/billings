<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Throwable;

class UnitController extends Controller
{
    /**
     * Units listing.
     *
     * Global units:
     * business_id = null
     *
     * Business units:
     * business_id = active business ID
     */
    // public function index(Request $request)
    // {
    //     $businessId = $this->getBusinessId($request);

    //     $units = Unit::query()
    //         /*
    //          * BelongsToBusiness trait agar global scope laga raha hai,
    //          * to use temporarily remove karna pad sakta hai.
    //          *
    //          * Apne scope class ke according ye line use karein:
    //          *
    //          * ->withoutGlobalScope(BusinessScope::class)
    //          */
    //         ->where(function ($query) use ($businessId) {
    //             // Global units sabko dikhengi
    //             $query->whereNull('business_id');

    //             // Active business ki units bhi dikhengi
    //             if ($businessId) {
    //                 $query->orWhere('business_id', $businessId);
    //             }
    //         })
    //         ->latest()
    //         ->paginate(50);

    //     return view('units.index', compact('units'));
    // }

    public function index(Request $request)
{
    $businessId = $this->getBusinessId($request);

    /*
     * BelongsToBusiness global scope ko remove karna zaroori hai,
     * warna business_id = null wali global units query me nahi aayengi.
     */
    $units = Unit::query()
        ->withoutGlobalScope('business')
        ->where(function ($query) use ($businessId) {
            /*
             * Global units:
             * business_id null hone par sabhi businesses me dikhegi.
             */
            $query->whereNull('business_id');

            /*
             * Active business ki specific units.
             */
            if ($businessId !== null) {
                $query->orWhere(
                    'business_id',
                    (int) $businessId
                );
            }
        })
        ->orderBy('name')
        ->paginate(50)
        ->withQueryString();

    return view('units.index', compact(
        'units',
        'businessId'
    ));
}

    /**
     * Store a new unit.
     *
     * Active business hai:
     * business_id active business ID hoga.
     *
     * Active business nahi hai:
     * business_id null hoga aur unit global banegi.
     */
    public function store(Request $request)
    {
        $businessId = $this->getBusinessId($request);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',

                Rule::unique('units', 'name')
                    ->where(function ($query) use ($businessId) {
                        if ($businessId) {
                            $query->where('business_id', $businessId);
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
        ], [
            'name.required' => 'Unit name is required.',
            'name.unique' => $businessId
                ? 'This unit already exists for the active business.'
                : 'This global unit already exists.',
            'name.max' => 'Unit name cannot exceed 255 characters.',
            'description.max' => 'Description cannot exceed 2000 characters.',
        ]);

        /*
         * business_id manually set karna zaroori hai,
         * kyunki global unit ke liye null save karna hai.
         */
        $unit = Unit::withoutEvents(function () use ($validated, $businessId) {
            return Unit::create([
                'business_id' => $businessId,
                'name' => trim($validated['name']),
                'description' => filled($validated['description'] ?? null)
                    ? trim($validated['description'])
                    : null,
            ]);
        });

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $businessId
                    ? 'Unit created successfully!'
                    : 'Global unit created successfully!',
                'unit' => $unit,
            ], 201);
        }

        return redirect()
            ->route('units.index')
            ->with(
                'success',
                $businessId
                    ? 'Unit created successfully!'
                    : 'Global unit created successfully!'
            );
    }

    /**
     * Update unit.
     */
    public function update(Request $request, Unit $unit)
    {
        $businessId = $this->getBusinessId($request);

        /*
         * Global unit ko allow karna hai.
         *
         * Business-specific unit tabhi update hogi jab current business
         * us unit ke business se match kare.
         */
        if (
            $unit->business_id !== null &&
            (int) $unit->business_id !== $businessId
        ) {
            abort(403, 'You are not allowed to update this unit.');
        }

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
                        if ($unitBusinessId) {
                            $query->where('business_id', $unitBusinessId);
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
            'name.unique' => $unitBusinessId
                ? 'This unit already exists for the active business.'
                : 'This global unit already exists.',
            'name.max' => 'Unit name cannot exceed 255 characters.',
            'description.max' => 'Description cannot exceed 2000 characters.',
        ]);

        $unit->update([
            'name' => trim($validated['name']),
            'description' => filled($validated['description'] ?? null)
                ? trim($validated['description'])
                : null,
        ]);

        $unit->refresh();

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Unit updated successfully!',
                'unit' => $unit,
            ]);
        }

        return redirect()
            ->route('units.index')
            ->with('success', 'Unit updated successfully!');
    }

    /**
     * Delete unit.
     */
    public function destroy(Request $request, Unit $unit)
    {
        $businessId = $this->getBusinessId($request);

        /*
         * Global unit delete allow hai.
         * Business unit sirf matching business delete kar sakta hai.
         */
        if (
            $unit->business_id !== null &&
            (int) $unit->business_id !== $businessId
        ) {
            abort(403, 'You are not allowed to delete this unit.');
        }

        try {
            $unit->delete();

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Unit deleted successfully.',
                ]);
            }

            return redirect()
                ->route('units.index')
                ->with('success', 'Unit deleted successfully.');
        } catch (Throwable $exception) {
            report($exception);

            $message = in_array(
                (string) $exception->getCode(),
                ['23000', '23503'],
                true
            )
                ? 'Cannot delete this unit because it is linked with items.'
                : 'Unit delete failed. Please try again.';

            return $this->errorResponse($request, $message, 422);
        }
    }

    /**
     * Quick create unit.
     */
    public function quickStore(Request $request)
    {
        $businessId = $this->getBusinessId($request);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:150',

                Rule::unique('units', 'name')
                    ->where(function ($query) use ($businessId) {
                        if ($businessId) {
                            $query->where('business_id', $businessId);
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
        ], [
            'name.required' => 'Unit name is required.',
            'name.unique' => $businessId
                ? 'This unit already exists for the active business.'
                : 'This global unit already exists.',
        ]);

        $unit = Unit::withoutEvents(function () use ($validated, $businessId) {
            return Unit::create([
                'business_id' => $businessId,
                'name' => trim($validated['name']),
                'description' => filled($validated['description'] ?? null)
                    ? trim($validated['description'])
                    : null,
            ]);
        });

        return response()->json([
            'success' => true,
            'message' => $businessId
                ? 'Unit successfully created.'
                : 'Global unit successfully created.',
            'unit' => [
                'id' => $unit->id,
                'business_id' => $unit->business_id,
                'name' => $unit->name,
                'description' => $unit->description,
                'is_global' => $unit->business_id === null,
            ],
        ], 201);
    }

    /**
     * Show single unit.
     */
    public function show(Request $request, Unit $unit)
    {
        $businessId = $this->getBusinessId($request);

        /*
         * Global unit sabhi ko dikhegi.
         * Business unit sirf matching business ko dikhegi.
         */
        if (
            $unit->business_id !== null &&
            (int) $unit->business_id !== $businessId
        ) {
            abort(404);
        }

        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => true,
                'unit' => $unit,
            ]);
        }

        return view('units.show', compact('unit'));
    }

    /**
     * Resolve active business.
     *
     * Business na mile to null return hoga.
     */
    private function getBusinessId(Request $request): ?int
    {
        $businessId = session('active_business_id')
            ?? $request->user()?->business_id;

        return filled($businessId)
            ? (int) $businessId
            : null;
    }

    /**
     * Common error response.
     */
    private function errorResponse(
        Request $request,
        string $message,
        int $status = 422
    ) {
        if ($request->ajax() || $request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], $status);
        }

        return back()
            ->withInput()
            ->withErrors(['error' => $message]);
    }
}