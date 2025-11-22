<?php

namespace App\Http\Controllers;

use App\Models\MetalRate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MetalRateController extends Controller
{
    public function index(Request $request)
    {
        $query = MetalRate::orderBy('rate_date', 'desc')
            ->orderBy('metal_type');

        // Metal type filter (gold/silver)
        if ($request->filled('metal_type')) {
            $query->where('metal_type', $request->metal_type);
        }

        // Purity filter
        if ($request->filled('purity')) {
            $query->where('purity', $request->purity);
        }

        // From date filter
        if ($request->filled('from_date')) {
            $query->whereDate('rate_date', '>=', $request->from_date);
        }

        // To date filter
        if ($request->filled('to_date')) {
            $query->whereDate('rate_date', '<=', $request->to_date);
        }

        // Active status filter
        if ($request->active !== null && $request->active !== '') {
            $query->where('is_active', $request->active == '1');
        }

        $rates = $query->paginate(20)->appends($request->query());

        return view('metal_rates.index', [
            'rates'      => $rates,
            'from_date'  => $request->from_date ?? '',
            'to_date'    => $request->to_date ?? '',
            'metal_type' => $request->metal_type ?? '',
            'purity'     => $request->purity ?? '',
            'active'     => $request->active ?? '',
        ]);
    }


    /**
     * Create form.
     */
    public function create()
    {
        return view('metal_rates.create');
    }

    /**
     * Store new daily rate.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'rate_date'    => ['required', 'date'],
            'metal_type'   => ['required', Rule::in(['gold', 'silver'])],
            'purity'       => ['nullable', 'string'],
            'rate_per_gram'=> ['required', 'numeric', 'min:0'],
            'is_active'    => ['nullable', 'boolean'],
        ]);

        // Ensure only ONE rate per day+metal+purity
        $exists = MetalRate::where('rate_date', $data['rate_date'])
            ->where('metal_type', $data['metal_type'])
            ->where('purity', $data['purity'])
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'rate_date' => 'Rate already exists for this date & purity!'
            ])->withInput();
        }

        MetalRate::create($data);

        return redirect()
            ->route('metal-rates.index')
            ->with('success', 'Metal rate added successfully.');
    }

    /**
     * Edit form.
     */
    public function edit(MetalRate $metalRate)
    {
        return view('metal_rates.edit', compact('metalRate'));
    }

    /**
     * Update an existing rate.
     */
    public function update(Request $request, MetalRate $metalRate)
    {
        $data = $request->validate([
            'rate_date'    => ['required', 'date'],
            'metal_type'   => ['required', Rule::in(['gold', 'silver'])],
            'purity'       => ['nullable', 'string'],
            'rate_per_gram'=> ['required', 'numeric', 'min:0'],
            'is_active'    => ['nullable', 'boolean'],
        ]);

        // Unique validation (except current row)
        $exists = MetalRate::where('rate_date', $data['rate_date'])
            ->where('metal_type', $data['metal_type'])
            ->where('purity', $data['purity'])
            ->where('id', '!=', $metalRate->id)
            ->exists();

        if ($exists) {
            return back()->withErrors([
                'rate_date' => 'Another rate already exists for this date & purity!'
            ])->withInput();
        }

        $metalRate->update($data);

        return redirect()
            ->route('metal-rates.index')
            ->with('success', 'Metal rate updated successfully.');
    }

    /**
     * Delete a rate.
     */
    public function destroy(MetalRate $metalRate)
    {
        $metalRate->delete();

        return redirect()
            ->route('metal-rates.index')
            ->with('success', 'Metal rate deleted successfully.');
    }

    /**
     * Toggle active/inactive.
     */
    public function toggle(MetalRate $metalRate)
    {
        $metalRate->update([
            'is_active' => !$metalRate->is_active
        ]);

        return back()->with('success', 'Status updated.');
    }

    /**
     * API: Get latest active rate (gold/silver/purity).
     */
    public function latestRate(Request $request)
    {
        $rate = MetalRate::where('metal_type', $request->metal_type)
            ->when($request->purity, fn($q) => $q->where('purity', $request->purity))
            ->where('is_active', true)
            ->orderBy('rate_date', 'desc')
            ->first();

        if (! $rate) {
            return response()->json([
                'success' => false,
                'message' => 'Rate not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'rate' => $rate
        ]);
    }

    /**
     * API: Get rate by specific date.
     */
    public function rateByDate(Request $request)
    {
        $request->validate([
            'date'        => 'required|date',
            'metal_type'  => ['required', Rule::in(['gold', 'silver'])],
            'purity'      => 'nullable|string',
        ]);

        $rate = MetalRate::where('rate_date', $request->date)
            ->where('metal_type', $request->metal_type)
            ->where('purity', $request->purity)
            ->first();

        if (! $rate) {
            return response()->json([
                'success' => false,
                'message' => 'Rate not found for this date.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'rate' => $rate
        ]);
    }

//    public function storeToday(Request $request)
//    {
//        $bid = auth()->user()->current_business_id ?? session('active_business_id');
//        if (!$bid) {
//            return back()->withErrors(['business' => 'Active business select/attach नहीं है.']);
//        }
//
//        $today = Carbon::today();
//
//        $data = $request->validate([
//            'rates'               => ['array'],
//            'rates.gold'          => ['array'],
//            'rates.silver'        => ['array'],
//            'rates.gold.*'        => ['nullable', 'numeric', 'min:0'],
//            'rates.silver.*'      => ['nullable', 'numeric', 'min:0'],
//        ]);
//
//        $rates = $data['rates'] ?? [];
//
//        foreach (['gold', 'silver'] as $metal) {
//            foreach (($rates[$metal] ?? []) as $purity => $value) {
//                if ($value === null || $value === '') {
//                    continue;
//                }
//
//                MetalRate::updateOrCreate(
//                    [
//                        'business_id' => $bid,
//                        'rate_date'   => $today->toDateString(),
//                        'metal_type'  => $metal,
//                        'purity'      => $purity,
//                    ],
//                    [
//                        'rate_per_gram' => $value,
//                        'is_active'     => true,
//                    ]
//                );
//            }
//        }
//
//        return back()->with('success', 'Today metal rates saved successfully.');
//    }

    public function storeToday(Request $request)
    {
        $bid = auth()->user()->current_business_id ?? session('active_business_id');
        if (!$bid) {
            return back()->withErrors(['business' => 'Active business select/attach नहीं है.']);
        }

        $today = Carbon::today();

        // ✅ Validation (normal + custom purities)
        $data = $request->validate([
            'rates'               => ['array'],
            'rates.gold'          => ['array'],
            'rates.silver'        => ['array'],
            'rates.gold.*'        => ['nullable', 'numeric', 'min:0'],
            'rates.silver.*'      => ['nullable', 'numeric', 'min:0'],

            // custom purities
            'custom'                   => ['nullable', 'array'],
            'custom.gold.purity'       => ['nullable', 'array'],
            'custom.gold.purity.*'     => ['nullable', 'string', 'max:50'],
            'custom.gold.rate'         => ['nullable', 'array'],
            'custom.gold.rate.*'       => ['nullable', 'numeric', 'min:0'],

            'custom.silver.purity'     => ['nullable', 'array'],
            'custom.silver.purity.*'   => ['nullable', 'string', 'max:50'],
            'custom.silver.rate'       => ['nullable', 'array'],
            'custom.silver.rate.*'     => ['nullable', 'numeric', 'min:0'],
        ]);

        $rates  = $data['rates']  ?? [];
        $custom = $data['custom'] ?? [];

        // ✅ 1) Fixed (pre-defined) purities save/update
        foreach (['gold', 'silver'] as $metal) {
            foreach (($rates[$metal] ?? []) as $purity => $value) {
                if ($value === null || $value === '') {
                    continue;
                }

                MetalRate::updateOrCreate(
                    [
                        'business_id' => $bid,
                        'rate_date'   => $today->toDateString(),
                        'metal_type'  => $metal,
                        'purity'      => $purity,
                    ],
                    [
                        'rate_per_gram' => $value,
                        'is_active'     => true,
                    ]
                );
            }
        }

        // ✅ 2) Custom purities save/update
        foreach (['gold', 'silver'] as $metal) {
            $purities = $custom[$metal]['purity'] ?? [];
            $values   = $custom[$metal]['rate']   ?? [];

            foreach ($purities as $index => $purity) {
                $purity = trim((string) $purity);
                $value  = $values[$index] ?? null;

                if ($purity === '' || $value === null || $value === '') {
                    continue;
                }

                MetalRate::updateOrCreate(
                    [
                        'business_id' => $bid,
                        'rate_date'   => $today->toDateString(),
                        'metal_type'  => $metal,
                        'purity'      => $purity,
                    ],
                    [
                        'rate_per_gram' => $value,
                        'is_active'     => true,
                    ]
                );
            }
        }

        return back()->with('success', 'Today metal rates saved successfully.');
    }

}
