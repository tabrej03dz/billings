<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    /**
     * Display a listing of clients.
     */
    public function index(Request $request)
    {
        $q = trim($request->get('q', ''));
        $clients = Client::query()
            ->when($q !== '', function ($w) use ($q) {
                $w->where(function ($s) use ($q) {
                    $s->where('name', 'like', "%{$q}%")
                        ->orWhere('mobile', 'like', "%{$q}%")
                        ->orWhere('gstin', 'like', "%{$q}%")
                        ->orWhere('pan', 'like', "%{$q}%")
                        ->orWhere('address', 'like', "%{$q}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('clients.index', compact('clients', 'q'));
    }

    public function create()
    {
        return view('clients.create');
    }

    public function store(Request $request)
    {
        $bid = $request->user()->current_business_id ?? session('active_business_id');

        $data = $request->validate([
            'name'    => ['required','string','max:255'],
            'mobile'  => [
                'nullable','string','max:20',
                Rule::unique('clients','mobile')->where(fn($q) => $q->where('business_id',$bid)),
            ],
            'gstin'   => [
                'nullable','string','max:50',
                Rule::unique('clients','gstin')->where(fn($q) => $q->where('business_id',$bid)),
            ],
            'pan'     => [
                'nullable','string','max:50',
                Rule::unique('clients','pan')->where(fn($q) => $q->where('business_id',$bid)),
            ],
            'state'  => ['nullable','string','max:100'],
            'address' => ['nullable','string','max:1000'],
        ]);

        $data['state_code'] = null;

        if (!empty($data['state']) && str_contains($data['state'], ',')) {
            [$code, $name] = explode(',', $data['state'], 2);

            $data['state_code'] = trim($code); // "09"
            $data['state']      = trim($name); // "Uttar Pradesh"
        }

        // BelongsToBusiness trait creation time pe business_id auto set kar dega;
        // phir bhi explicit set karna chahte ho to:
        $data['business_id'] = $bid;

        Client::create($data);

        return redirect()->route('clients.index')->with('success','Client created successfully.');
    }

    public function edit(Client $client)
    {
        // GlobalScope se client already active business ka hoga
        return view('clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        $bid = $request->user()->current_business_id ?? session('active_business_id');

        $data = $request->validate([
            'name'    => ['required','string','max:255'],
            'mobile'  => [
                'nullable','string','max:20',
                Rule::unique('clients','mobile')
                    ->ignore($client->id)
                    ->where(fn($q) => $q->where('business_id',$bid)),
            ],
            'gstin'   => [
                'nullable','string','max:50',
                Rule::unique('clients','gstin')
                    ->ignore($client->id)
                    ->where(fn($q) => $q->where('business_id',$bid)),
            ],
            'pan'     => [
                'nullable','string','max:50',
                Rule::unique('clients','pan')
                    ->ignore($client->id)
                    ->where(fn($q) => $q->where('business_id',$bid)),
            ],
            'state'   => ['nullable','string','max:100'],
            'address' => ['nullable','string','max:1000'],
        ]);
        if (!empty($data['state']) && str_contains($data['state'], ',')) {
            [$code, $name] = explode(',', $data['state'], 2);

            $data['state_code'] = trim($code); // "09"
            $data['state']      = trim($name); // "Uttar Pradesh"
        }

        $client->update($data);

        return redirect()->route('clients.index')->with('success','Client updated successfully.');
    }

    public function destroy(Client $client)
    {
        $client->delete();
        return redirect()->route('clients.index')->with('success','Client deleted successfully.');
    }


//    public function quickStore(Request $request)
//    {
//        // ✅ Business resolve (fallback added)
//        $bid = $request->user()->current_business_id
//            ?? session('active_business_id')
//            ?? $request->user()->businesses()->pluck('businesses.id')->first();
//
//        if (!$bid) {
//            return response()->json([
//                'ok' => false,
//                'message' => 'Active business not found.'
//            ], 422);
//        }
//
//        // ✅ Normalize inputs (avoid duplicates by formatting)
//        $request->merge([
//            'mobile'  => $request->mobile ? preg_replace('/\s+/', '', $request->mobile) : null,
//            'gstin'   => $request->gstin ? strtoupper(preg_replace('/\s+/', '', $request->gstin)) : null,
//            'pan'     => $request->pan ? strtoupper(preg_replace('/\s+/', '', $request->pan)) : null,
//            'state'   => $request->state ? trim($request->state) : null,
//            'state_code'   => $request->state_code ? trim($request->state_code) : null,
//            'address' => $request->address ? trim($request->address) : null,
//            'name'    => $request->name ? trim($request->name) : null,
//            'pincode'    => $request->pincode ? trim($request->pincode) : null,
//        ]);
//
//        // ✅ Convert empty string to null for nullable fields
//        foreach (['gstin','pan','state','address'] as $f) {
//            if ($request->has($f) && $request->input($f) === '') {
//                $request->merge([$f => null]);
//            }
//        }
//
//        try {
//            $data = $request->validate([
//                'name'    => ['required','string','max:255'],
//                'mobile'  => [
//                    'nullable','string','max:20',
//                    Rule::unique('clients','mobile')->where(fn($q) => $q->where('business_id', $bid)),
//                ],
//                'gstin'   => [
//                    'nullable','string','max:50',
//                    Rule::unique('clients','gstin')->where(fn($q) => $q->where('business_id', $bid)),
//                ],
//                'pan'     => [
//                    'nullable','string','max:50',
//                    Rule::unique('clients','pan')->where(fn($q) => $q->where('business_id', $bid)),
//                ],
//                'state'   => ['nullable','string','max:100'],
//                'state_code'   => ['nullable','string','max:100'],
//                'address' => ['nullable','string','max:1000'],
//                'pincode' => ['nullable'],
//            ]);
//        } catch (ValidationException $e) {
//            // ✅ return validation errors as JSON for modal
//            return response()->json([
//                'ok' => false,
//                'message' => 'Validation failed',
//                'errors' => $e->errors(),
//            ], 422);
//        }
//
//        $data['business_id'] = $bid;
//
//        $client = \App\Models\Client::create($data);
//
//        return response()->json([
//            'ok' => true,
//            'client' => [
//                'id'         => $client->id,
//                'name'       => $client->name,
//                'mobile'     => $client->mobile,
//                'address'    => $client->address,
//                'state'      => $client->state,
//                'state_code' => $client->state_code,
//                'gstin'      => $client->gstin,
//                'pincode'      => $client->pincode,
//            ]
//        ]);
//    }


    public function quickStore(Request $request)
    {
        // ✅ Business resolve (fallback added)
        $bid = $request->user()->current_business_id
            ?? session('active_business_id')
            ?? $request->user()->businesses()->pluck('businesses.id')->first();

        if (!$bid) {
            return response()->json([
                'ok' => false,
                'message' => 'Active business not found.'
            ], 422);
        }

        // ✅ Normalize inputs (avoid duplicates by formatting)
        $request->merge([
            'mobile'     => $request->mobile ? preg_replace('/\s+/', '', (string)$request->mobile) : null,
            'gstin'      => $request->gstin ? strtoupper(preg_replace('/\s+/', '', (string)$request->gstin)) : null,
            'pan'        => $request->pan ? strtoupper(preg_replace('/\s+/', '', (string)$request->pan)) : null,
            'state'      => $request->state ? trim((string)$request->state) : null,
            'state_code' => $request->state_code ? trim((string)$request->state_code) : null,
            'address'    => $request->address ? trim((string)$request->address) : null,
            'name'       => $request->name ? trim((string)$request->name) : null,
            'pincode'    => $request->pincode ? trim((string)$request->pincode) : null,
        ]);

        // ✅ Convert empty string to null for nullable fields
        foreach (['mobile','gstin','pan','state','state_code','address','pincode'] as $f) {
            if ($request->has($f) && $request->input($f) === '') {
                $request->merge([$f => null]);
            }
        }

        // ✅ GST State Code → State Name map (India)
        $gstStates = [
            '01' => 'Jammu & Kashmir',
            '02' => 'Himachal Pradesh',
            '03' => 'Punjab',
            '04' => 'Chandigarh',
            '05' => 'Uttarakhand',
            '06' => 'Haryana',
            '07' => 'Delhi',
            '08' => 'Rajasthan',
            '09' => 'Uttar Pradesh',
            '10' => 'Bihar',
            '11' => 'Sikkim',
            '12' => 'Arunachal Pradesh',
            '13' => 'Nagaland',
            '14' => 'Manipur',
            '15' => 'Mizoram',
            '16' => 'Tripura',
            '17' => 'Meghalaya',
            '18' => 'Assam',
            '19' => 'West Bengal',
            '20' => 'Jharkhand',
            '21' => 'Odisha',
            '22' => 'Chhattisgarh',
            '23' => 'Madhya Pradesh',
            '24' => 'Gujarat',
            '25' => 'Daman & Diu',          // legacy
            '26' => 'Dadra & Nagar Haveli',  // legacy
            '27' => 'Maharashtra',
            '28' => 'Andhra Pradesh (Old)',
            '29' => 'Karnataka',
            '30' => 'Goa',
            '31' => 'Lakshadweep',
            '32' => 'Kerala',
            '33' => 'Tamil Nadu',
            '34' => 'Puducherry',
            '35' => 'Andaman & Nicobar Islands',
            '36' => 'Telangana',
            '37' => 'Andhra Pradesh',
            '38' => 'Ladakh',
            '97' => 'Other Territory',
            '99' => 'Centre Jurisdiction',
        ];

        // ✅ PRO: If state/state_code missing, derive from GSTIN first 2 digits
        // Also: basic GSTIN format check (15 chars) before deriving
        $gstin = $request->gstin;

        if (!empty($gstin)) {
            // Basic GSTIN pattern: 2 digits + PAN(10) + 1 + Z + 1 (total 15)
            // Example: 09ABCDE1234F1Z5
            $gstinPattern = '/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/';

            // If user entered gstin but wrong format, throw validation-like JSON error early
            if (strlen($gstin) !== 15 || !preg_match($gstinPattern, $gstin)) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Validation failed',
                    'errors' => [
                        'gstin' => ['GSTIN format is invalid. Example: 09ABCDE1234F1Z5'],
                    ],
                ], 422);
            }

            $code = substr($gstin, 0, 2); // "09"

            // If state_code not provided -> set from GSTIN
            if (empty($request->state_code) && ctype_digit($code)) {
                $request->merge(['state_code' => $code]);
            }

            // If state not provided -> set from map (if exists)
            if (empty($request->state) && isset($gstStates[$code])) {
                $request->merge(['state' => $gstStates[$code]]);
            }
        }

        try {
            $data = $request->validate([
                'name'    => ['required','string','max:255'],

                'mobile'  => [
                    'nullable','string','max:20',
                    Rule::unique('clients','mobile')->where(fn($q) => $q->where('business_id', $bid)),
                ],

                'gstin'   => [
                    'nullable','string','max:50',
                    Rule::unique('clients','gstin')->where(fn($q) => $q->where('business_id', $bid)),
                ],

                'pan'     => [
                    'nullable','string','max:50',
                    Rule::unique('clients','pan')->where(fn($q) => $q->where('business_id', $bid)),
                ],

                'state'      => ['nullable','string','max:100'],
                'state_code' => ['nullable','string','max:10'], // "09" etc.

                'address' => ['nullable','string','max:1000'],
                'pincode' => ['nullable','string','max:20'],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        $data['business_id'] = $bid;

        $client = Client::create($data);

        return response()->json([
            'ok' => true,
            'client' => [
                'id'         => $client->id,
                'name'       => $client->name,
                'mobile'     => $client->mobile,
                'address'    => $client->address,
                'state'      => $client->state,
                'state_code' => $client->state_code,
                'gstin'      => $client->gstin,
                'pincode'    => $client->pincode,
            ]
        ]);
    }


    // App\Http\Controllers\ClientController.php

    public function show(Request $request, \App\Models\Client $client)
    {
        // Multi-business context (same logic jaisa invoice edit me)
        $user = $request->user();

        $businessId =
            $user->current_business_id
            ?? session('active_business_id')
            ?? $user->businesses()->pluck('businesses.id')->first();

        // ✔️ Invoices for this client + business
        $invoiceQuery = $client->invoices()
            ->where('business_id', $businessId)
            ->orderByDesc('invoice_date')
            ->orderByDesc('id');

        // Summary totals (alag se, taaki full history ka data mile)
        $summary = [
            'total_invoices' => (clone $invoiceQuery)->count(),
            'total_amount'   => (clone $invoiceQuery)->sum('total'),
            'total_received' => (clone $invoiceQuery)->sum('received_amount'),
            'total_balance'  => (clone $invoiceQuery)->sum('balance'),
        ];

        // List ke liye paginate
        $invoices = $invoiceQuery
            ->withCount('items')
            ->paginate(15)
            ->withQueryString();

        // Recent purchased items (last 10 lines)
        $recentItems = \App\Models\InvoiceItem::with(['invoice' => function ($q) use ($businessId, $client) {
            $q->where('business_id', $businessId)
                ->where('client_id', $client->id);
        }])
            ->whereHas('invoice', function ($q) use ($businessId, $client) {
                $q->where('business_id', $businessId)
                    ->where('client_id', $client->id);
            })
            ->latest('created_at')
            ->limit(10)
            ->get();

        return view('clients.show', [
            'client'      => $client,
            'invoices'    => $invoices,
            'summary'     => $summary,
            'recentItems' => $recentItems,
        ]);
    }


}
