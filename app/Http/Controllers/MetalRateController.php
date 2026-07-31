<?php

namespace App\Http\Controllers;

use App\Models\MetalRate;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MetalRateController extends Controller
{
    /**
     * Get current active business ID.
     */
    private function activeBusinessId(): ?int
    {
        $businessId = auth()->user()?->current_business_id
            ?? session('active_business_id');

        return $businessId
            ? (int) $businessId
            : null;
    }

    /**
     * Normalize purity value.
     */
    private function normalizePurity(mixed $purity): string
    {
        return strtoupper(
            trim((string) ($purity ?? ''))
        );
    }

    /**
     * Metal rates listing.
     */
    // public function index(Request $request): View|RedirectResponse
    // {
    //     $businessId = $this->activeBusinessId();

    //     if (!$businessId) {
    //         return redirect()
    //             ->back()
    //             ->withErrors([
    //                 'business' => 'Please select an active business first.',
    //             ]);
    //     }

    //     $query = MetalRate::query()
    //         ->where('business_id', $businessId)
    //         ->orderByDesc('rate_date')
    //         ->orderBy('metal_type')
    //         ->orderBy('purity');

    //     /*
    //      * Metal type filter.
    //      */
    //     if ($request->filled('metal_type')) {
    //         $query->where(
    //             'metal_type',
    //             $request->string('metal_type')->toString()
    //         );
    //     }

    //     /*
    //      * Purity filter.
    //      */
    //     if ($request->filled('purity')) {
    //         $query->where(
    //             'purity',
    //             $this->normalizePurity($request->purity)
    //         );
    //     }

    //     /*
    //      * From date filter.
    //      */
    //     if ($request->filled('from_date')) {
    //         $query->whereDate(
    //             'rate_date',
    //             '>=',
    //             $request->from_date
    //         );
    //     }

    //     /*
    //      * To date filter.
    //      */
    //     if ($request->filled('to_date')) {
    //         $query->whereDate(
    //             'rate_date',
    //             '<=',
    //             $request->to_date
    //         );
    //     }

    //     /*
    //      * Active status filter.
    //      */
    //     if (
    //         $request->has('active')
    //         && $request->active !== ''
    //         && $request->active !== null
    //     ) {
    //         $query->where(
    //             'is_active',
    //             $request->boolean('active')
    //         );
    //     }

    //     $rates = $query
    //         ->paginate(20)
    //         ->withQueryString();

    //     return view('metal_rates.index', [
    //         'rates'      => $rates,
    //         'from_date'  => $request->input('from_date', ''),
    //         'to_date'    => $request->input('to_date', ''),
    //         'metal_type' => $request->input('metal_type', ''),
    //         'purity'     => $request->input('purity', ''),
    //         'active'     => $request->input('active', ''),
    //     ]);
    // }



    public function index(Request $request): View|RedirectResponse
{
    $businessId = $this->activeBusinessId();

    if (!$businessId) {
        return redirect()
            ->back()
            ->withErrors([
                'business' => 'Please select an active business first.',
            ]);
    }

    $query = MetalRate::query()
        ->where('business_id', $businessId)
        ->orderByDesc('rate_date')
        ->orderBy('metal_type')
        ->orderBy('purity');

    /*
     * Exact date filter.
     */
    if ($request->filled('date')) {
        $query->whereDate(
            'rate_date',
            $request->input('date')
        );
    }

    /*
     * Metal type filter.
     */
    if ($request->filled('metal_type')) {
        $query->where(
            'metal_type',
            $request->string('metal_type')->toString()
        );
    }

    /*
     * Purity filter.
     */
    if ($request->filled('purity')) {
        $query->where(
            'purity',
            $this->normalizePurity($request->input('purity'))
        );
    }

    /*
     * From date filter.
     */
    if ($request->filled('from_date')) {
        $query->whereDate(
            'rate_date',
            '>=',
            $request->input('from_date')
        );
    }

    /*
     * To date filter.
     */
    if ($request->filled('to_date')) {
        $query->whereDate(
            'rate_date',
            '<=',
            $request->input('to_date')
        );
    }

    /*
     * Active status filter.
     */
    if (
        $request->has('active')
        && $request->input('active') !== ''
        && $request->input('active') !== null
    ) {
        $query->where(
            'is_active',
            $request->boolean('active')
        );
    }

    $rates = $query
        ->paginate(20)
        ->withQueryString();

    return view('metal_rates.index', [
        'rates'      => $rates,
        'date'       => $request->input('date', ''),
        'from_date'  => $request->input('from_date', ''),
        'to_date'    => $request->input('to_date', ''),
        'metal_type' => $request->input('metal_type', ''),
        'purity'     => $request->input('purity', ''),
        'active'     => $request->input('active', ''),
    ]);
}
    /**
     * Show create form.
     */
    public function create(): View|RedirectResponse
    {
        if (!$this->activeBusinessId()) {
            return redirect()
                ->back()
                ->withErrors([
                    'business' => 'Please select an active business first.',
                ]);
        }

        return view('metal_rates.create');
    }

    /**
     * Store a new metal rate.
     */
    public function store(Request $request): RedirectResponse
    {
        $businessId = $this->activeBusinessId();

        if (!$businessId) {
            return back()
                ->withErrors([
                    'business' => 'Please select an active business first.',
                ])
                ->withInput();
        }

        $validated = $request->validate([
            'rate_date' => [
                'required',
                'date',
            ],

            'metal_type' => [
                'required',
                Rule::in([
                    'gold',
                    'silver',
                ]),
            ],

            'purity' => [
                'nullable',
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
        ]);

        $purity = $this->normalizePurity(
            $validated['purity'] ?? ''
        );

        $exists = MetalRate::withoutGlobalScopes()
            ->where('business_id', $businessId)
            ->whereDate(
                'rate_date',
                $validated['rate_date']
            )
            ->where(
                'metal_type',
                $validated['metal_type']
            )
            ->where(
                'purity',
                $purity
            )
            ->exists();

        if ($exists) {
            return back()
                ->withErrors([
                    'rate_date' =>
                        'इस business में इस date, metal और purity का rate पहले से मौजूद है.',
                ])
                ->withInput();
        }

        MetalRate::create([
            'business_id'   => $businessId,
            'rate_date'     => $validated['rate_date'],
            'metal_type'    => $validated['metal_type'],
            'purity'        => $purity,
            'rate_per_gram' => $validated['rate_per_gram'],
            'is_active'     => $request->boolean(
                'is_active',
                true
            ),
        ]);

        return redirect()
            ->route('metal-rates.index')
            ->with(
                'success',
                'Metal rate added successfully.'
            );
    }

    /**
     * Show edit form.
     */
    public function edit(
        MetalRate $metalRate
    ): View|RedirectResponse {
        $businessId = $this->activeBusinessId();

        if (!$businessId) {
            return redirect()
                ->back()
                ->withErrors([
                    'business' => 'Please select an active business first.',
                ]);
        }

        abort_unless(
            (int) $metalRate->business_id === $businessId,
            403,
            'You cannot edit another business metal rate.'
        );

        return view(
            'metal_rates.edit',
            compact('metalRate')
        );
    }

    /**
     * Update metal rate.
     */
    public function update(
        Request $request,
        MetalRate $metalRate
    ): RedirectResponse {
        $businessId = $this->activeBusinessId();

        if (!$businessId) {
            return back()
                ->withErrors([
                    'business' => 'Please select an active business first.',
                ])
                ->withInput();
        }

        abort_unless(
            (int) $metalRate->business_id === $businessId,
            403,
            'You cannot update another business metal rate.'
        );

        $validated = $request->validate([
            'rate_date' => [
                'required',
                'date',
            ],

            'metal_type' => [
                'required',
                Rule::in([
                    'gold',
                    'silver',
                ]),
            ],

            'purity' => [
                'nullable',
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
        ]);

        $purity = $this->normalizePurity(
            $validated['purity'] ?? ''
        );

        $exists = MetalRate::withoutGlobalScopes()
            ->where('business_id', $businessId)
            ->whereDate(
                'rate_date',
                $validated['rate_date']
            )
            ->where(
                'metal_type',
                $validated['metal_type']
            )
            ->where(
                'purity',
                $purity
            )
            ->where(
                'id',
                '!=',
                $metalRate->id
            )
            ->exists();

        if ($exists) {
            return back()
                ->withErrors([
                    'rate_date' =>
                        'इस business में इस date, metal और purity का दूसरा rate पहले से मौजूद है.',
                ])
                ->withInput();
        }

        $metalRate->update([
            'rate_date'     => $validated['rate_date'],
            'metal_type'    => $validated['metal_type'],
            'purity'        => $purity,
            'rate_per_gram' => $validated['rate_per_gram'],
            'is_active'     => $request->boolean(
                'is_active',
                true
            ),
        ]);

        return redirect()
            ->route('metal-rates.index')
            ->with(
                'success',
                'Metal rate updated successfully.'
            );
    }

    /**
     * Delete metal rate.
     */
    public function destroy(
        MetalRate $metalRate
    ): RedirectResponse {
        $businessId = $this->activeBusinessId();

        if (!$businessId) {
            return back()->withErrors([
                'business' => 'Please select an active business first.',
            ]);
        }

        abort_unless(
            (int) $metalRate->business_id === $businessId,
            403,
            'You cannot delete another business metal rate.'
        );

        $metalRate->delete();

        return redirect()
            ->route('metal-rates.index')
            ->with(
                'success',
                'Metal rate deleted successfully.'
            );
    }

    /**
     * Toggle active/inactive status.
     */
    public function toggle(
        MetalRate $metalRate
    ): RedirectResponse {
        $businessId = $this->activeBusinessId();

        if (!$businessId) {
            return back()->withErrors([
                'business' => 'Please select an active business first.',
            ]);
        }

        abort_unless(
            (int) $metalRate->business_id === $businessId,
            403,
            'You cannot update another business metal rate.'
        );

        $metalRate->update([
            'is_active' => !$metalRate->is_active,
        ]);

        return back()->with(
            'success',
            'Status updated successfully.'
        );
    }

    /**
     * API: Get latest active rate.
     */
    public function latestRate(Request $request)
    {
        $businessId = $this->activeBusinessId();

        if (!$businessId) {
            return response()->json([
                'success' => false,
                'message' => 'Active business not selected.',
            ], 422);
        }

        $validated = $request->validate([
            'metal_type' => [
                'required',
                Rule::in([
                    'gold',
                    'silver',
                ]),
            ],

            'purity' => [
                'nullable',
                'string',
                'max:50',
            ],
        ]);

        $query = MetalRate::query()
            ->where('business_id', $businessId)
            ->where(
                'metal_type',
                $validated['metal_type']
            )
            ->where('is_active', true);

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

        $rate = $query
            ->orderByDesc('rate_date')
            ->orderByDesc('id')
            ->first();

        if (!$rate) {
            return response()->json([
                'success' => false,
                'message' => 'Metal rate not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'rate'    => $rate,
        ]);
    }

    /**
     * API: Get rate by a specific date.
     */
    public function rateByDate(Request $request)
    {
        
        $businessId = $this->activeBusinessId();

        if (!$businessId) {
            return response()->json([
                'success' => false,
                'message' => 'Active business not selected.',
            ], 422);
        }

        $validated = $request->validate([
            'date' => [
                'required',
                'date',
            ],

            'metal_type' => [
                'required',
                Rule::in([
                    'gold',
                    'silver',
                ]),
            ],

            'purity' => [
                'nullable',
                'string',
                'max:50',
            ],
        ]);

        $purity = $this->normalizePurity(
            $validated['purity'] ?? ''
        );

        $rate = MetalRate::query()
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
                $purity
            )
            ->first();

        if (!$rate) {
            return response()->json([
                'success' => false,
                'message' =>
                    'Rate not found for the selected date.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'rate'    => $rate,
        ]);
    }

    /**
     * Save or update today's fixed and custom metal rates.
     */
    public function storeToday(
        Request $request
    ): RedirectResponse {
        $businessId = $this->activeBusinessId();

        if (!$businessId) {
            return back()
                ->withErrors([
                    'business' => 'Please select an active business first.',
                ])
                ->withInput();
        }

        $validated = $request->validate([
            /*
             * Fixed gold and silver rates.
             */
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

            /*
             * Custom purity rates.
             */
            'custom' => [
                'nullable',
                'array',
            ],

            'custom.gold' => [
                'nullable',
                'array',
            ],

            'custom.gold.purity' => [
                'nullable',
                'array',
            ],

            'custom.gold.purity.*' => [
                'nullable',
                'string',
                'max:50',
            ],

            'custom.gold.rate' => [
                'nullable',
                'array',
            ],

            'custom.gold.rate.*' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'custom.silver' => [
                'nullable',
                'array',
            ],

            'custom.silver.purity' => [
                'nullable',
                'array',
            ],

            'custom.silver.purity.*' => [
                'nullable',
                'string',
                'max:50',
            ],

            'custom.silver.rate' => [
                'nullable',
                'array',
            ],

            'custom.silver.rate.*' => [
                'nullable',
                'numeric',
                'min:0',
            ],
        ]);

        $today = Carbon::today()->toDateString();

        $rates = $validated['rates'] ?? [];
        $custom = $validated['custom'] ?? [];

        DB::transaction(function () use (
            $businessId,
            $today,
            $rates,
            $custom
        ): void {
            /*
             * Save fixed/predefined purity rates.
             */
            foreach (['gold', 'silver'] as $metal) {
                $metalRates = $rates[$metal] ?? [];

                foreach ($metalRates as $purity => $value) {
                    if (
                        $value === null
                        || $value === ''
                    ) {
                        continue;
                    }

                    $normalizedPurity =
                        $this->normalizePurity($purity);

                    MetalRate::withoutGlobalScopes()
                        ->updateOrCreate(
                            [
                                'business_id' => $businessId,
                                'rate_date'   => $today,
                                'metal_type'  => $metal,
                                'purity'      => $normalizedPurity,
                            ],
                            [
                                'rate_per_gram' => $value,
                                'is_active'     => true,
                            ]
                        );
                }
            }

            /*
             * Save custom purity rates.
             */
            foreach (['gold', 'silver'] as $metal) {
                $purities =
                    $custom[$metal]['purity'] ?? [];

                $values =
                    $custom[$metal]['rate'] ?? [];

                foreach ($purities as $index => $purity) {
                    $normalizedPurity =
                        $this->normalizePurity($purity);

                    $value = $values[$index] ?? null;

                    if (
                        $normalizedPurity === ''
                        || $value === null
                        || $value === ''
                    ) {
                        continue;
                    }

                    MetalRate::withoutGlobalScopes()
                        ->updateOrCreate(
                            [
                                'business_id' => $businessId,
                                'rate_date'   => $today,
                                'metal_type'  => $metal,
                                'purity'      => $normalizedPurity,
                            ],
                            [
                                'rate_per_gram' => $value,
                                'is_active'     => true,
                            ]
                        );
                }
            }
        });

        return back()->with(
            'success',
            'Today metal rates saved successfully.'
        );
    }
}