<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Client;
use App\Models\InvoiceItem;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Exports\ClientPurchaseReportExport;
use App\Models\Purchase;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;



class ClientController extends Controller
{
    

    // public function index(Request $request)
    // {
    //     $q = trim((string) $request->get('q', ''));

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Base client query
    //     |--------------------------------------------------------------------------
    //     | Client model me active business ka Global Scope laga hai, isliye
    //     | yahan dobara business_id filter nahi lagaya gaya.
    //     */
    //     $baseQuery = Client::query()
    //         ->where('is_save', true);

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Total saved clients
    //     |--------------------------------------------------------------------------
    //     | Search ke bina actual client count.
    //     */
    //     $totalClients = (clone $baseQuery)->count();

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Client listing
    //     |--------------------------------------------------------------------------
    //     */
    //     $clients = $baseQuery
    //         ->when($q !== '', function ($query) use ($q) {
    //             $query->where(function ($subQuery) use ($q) {
    //                 $subQuery
    //                     ->where('name', 'like', "%{$q}%")
    //                     ->orWhere('mobile', 'like', "%{$q}%")
    //                     ->orWhere('gstin', 'like', "%{$q}%")
    //                     ->orWhere('pan', 'like', "%{$q}%")
    //                     ->orWhere('state', 'like', "%{$q}%")
    //                     ->orWhere('address', 'like', "%{$q}%");
    //             });
    //         })
    //         ->latest()
    //         ->paginate(15)
    //         ->withQueryString();

    //     /*
    //     |--------------------------------------------------------------------------
    //     | New user guide
    //     |--------------------------------------------------------------------------
    //     | 5 saved clients se kam hone tak user ko new user maana jayega.
    //     */
    //     $showClientSuggestion = $totalClients < 5;

    //     return view('clients.index', compact(
    //         'clients',
    //         'q',
    //         'totalClients',
    //         'showClientSuggestion'
    //     ));
    // }


    public function index(Request $request)
{
    $q = trim((string) $request->get('q', ''));

    /*
    |--------------------------------------------------------------------------
    | Active Tab
    |--------------------------------------------------------------------------
    |
    | client / supplier
    |
    */

    $type = $request->get('type', 'client');

    if (!in_array($type, ['client', 'supplier'], true)) {
        $type = 'client';
    }


    /*
    |--------------------------------------------------------------------------
    | Base Query
    |--------------------------------------------------------------------------
    */

    $baseQuery = Client::query()
        ->where('is_save', true)
        ->where('party_type', $type);


    /*
    |--------------------------------------------------------------------------
    | Counts
    |--------------------------------------------------------------------------
    */

    $totalClients = Client::query()
        ->where('is_save', true)
        ->whereIn('party_type', [
            'client',
            'both',
        ])
        ->count();


    $totalSuppliers = Client::query()
        ->where('is_save', true)
        ->whereIn('party_type', [
            'supplier',
            'both',
        ])
        ->count();


    /*
    |--------------------------------------------------------------------------
    | Current Tab Listing
    |--------------------------------------------------------------------------
    */

    $clients = $baseQuery
        ->when($q !== '', function ($query) use ($q) {

            $query->where(function ($subQuery) use ($q) {

                $subQuery
                    ->where('name', 'like', "%{$q}%")
                    ->orWhere('mobile', 'like', "%{$q}%")
                    ->orWhere('gstin', 'like', "%{$q}%")
                    ->orWhere('pan', 'like', "%{$q}%")
                    ->orWhere('state', 'like', "%{$q}%")
                    ->orWhere('address', 'like', "%{$q}%");

            });

        })
        ->latest()
        ->paginate(15)
        ->withQueryString();


    /*
    |--------------------------------------------------------------------------
    | Client Guide
    |--------------------------------------------------------------------------
    */

    $showClientSuggestion =
        $type === 'client'
        && $totalClients < 5;


    return view('clients.index', compact(
        'clients',
        'q',
        'type',
        'totalClients',
        'totalSuppliers',
        'showClientSuggestion'
    ));
}

    // public function create()
    // {
    //     return view('clients.create');
    // }

    public function create(Request $request)
    {
        $type = $request->get('type', 'client');

        if (!in_array($type, ['client', 'supplier'], true)) {
            $type = 'client';
        }

        return view('clients.create', compact('type'));
    }


    // public function store(Request $request)
    // {
    //     $bid = $request->user()->current_business_id ?? session('active_business_id');

    //     $data = $request->validate([
    //         'name'    => ['required','string','max:255'],
    //         'mobile'  => [
    //             'nullable','string','max:20',
    //             Rule::unique('clients','mobile')->where(fn($q) => $q->where('business_id',$bid)),
    //         ],
    //         'gstin'   => [
    //             'nullable','string','max:50',
    //             Rule::unique('clients','gstin')->where(fn($q) => $q->where('business_id',$bid)),
    //         ],
    //         'pan'     => [
    //             'nullable','string','max:50',
    //             Rule::unique('clients','pan')->where(fn($q) => $q->where('business_id',$bid)),
    //         ],
    //         'state'   => ['nullable','string','max:100'],
    //         'address' => ['nullable','string','max:1000'],
    //     ]);

    //     $data['state_code'] = null;

    //     // ✅ CASE 1: State dropdown selected
    //     if (!empty($data['state']) && str_contains($data['state'], ',')) {
    //         [$code, $name] = explode(',', $data['state'], 2);
    //         $data['state_code'] = trim($code);
    //         $data['state']      = trim($name);
    //     }

    //     // ✅ CASE 2: State NOT selected → derive from GSTIN
    //     if (
    //         empty($data['state']) &&
    //         !empty($data['gstin']) &&
    //         strlen($data['gstin']) >= 2
    //     ) {
    //         $gstStates = [
    //             '01'=>'Jammu and Kashmir','02'=>'Himachal Pradesh','03'=>'Punjab',
    //             '04'=>'Chandigarh','05'=>'Uttarakhand','06'=>'Haryana','07'=>'Delhi',
    //             '08'=>'Rajasthan','09'=>'Uttar Pradesh','10'=>'Bihar','11'=>'Sikkim',
    //             '12'=>'Arunachal Pradesh','13'=>'Nagaland','14'=>'Manipur','15'=>'Mizoram',
    //             '16'=>'Tripura','17'=>'Meghalaya','18'=>'Assam','19'=>'West Bengal',
    //             '20'=>'Jharkhand','21'=>'Odisha','22'=>'Chhattisgarh',
    //             '23'=>'Madhya Pradesh','24'=>'Gujarat',
    //             '26'=>'Dadra and Nagar Haveli and Daman and Diu',
    //             '27'=>'Maharashtra','29'=>'Karnataka','30'=>'Goa','31'=>'Lakshadweep',
    //             '32'=>'Kerala','33'=>'Tamil Nadu','34'=>'Puducherry',
    //             '35'=>'Andaman and Nicobar Islands','36'=>'Telangana',
    //             '37'=>'Andhra Pradesh','38'=>'Ladakh',
    //         ];

    //         $code = substr(strtoupper($data['gstin']), 0, 2);

    //         if (isset($gstStates[$code])) {
    //             $data['state_code'] = $code;
    //             $data['state']      = $gstStates[$code];
    //         }
    //     }

    //     $data['business_id'] = $bid;

    //     Client::create($data);

    //     return redirect()
    //         ->route('clients.index')
    //         ->with('success','Client created successfully.');
    // }

    public function store(Request $request)
    {
        $bid =
            $request->user()->current_business_id
            ?? session('active_business_id')
            ?? $request->user()
                ->businesses()
                ->pluck('businesses.id')
                ->first();


        abort_unless(
            $bid,
            403,
            'Active business not found.'
        );


        /*
        |--------------------------------------------------------------------------
        | Normalize
        |--------------------------------------------------------------------------
        */

        $request->merge([
            'name' => trim((string) $request->name),

            'mobile' => $request->mobile
                ? preg_replace('/\s+/', '', (string) $request->mobile)
                : null,

            'gstin' => $request->gstin
                ? strtoupper(
                    preg_replace(
                        '/\s+/',
                        '',
                        (string) $request->gstin
                    )
                )
                : null,

            'pan' => $request->pan
                ? strtoupper(
                    preg_replace(
                        '/\s+/',
                        '',
                        (string) $request->pan
                    )
                )
                : null,
        ]);


        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

        $data = $request->validate([

            'party_type' => [
                'required',
                Rule::in([
                    'client',
                    'supplier',
                ]),
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'mobile' => [
                'nullable',
                'string',
                'max:20',

                Rule::unique('clients', 'mobile')
                    ->where(
                        fn ($q) =>
                            $q->where('business_id', $bid)
                    ),
            ],

            'gstin' => [
                'nullable',
                'string',
                'max:50',

                Rule::unique('clients', 'gstin')
                    ->where(
                        fn ($q) =>
                            $q->where('business_id', $bid)
                    ),
            ],

            'pan' => [
                'nullable',
                'string',
                'max:50',

                Rule::unique('clients', 'pan')
                    ->where(
                        fn ($q) =>
                            $q->where('business_id', $bid)
                    ),
            ],

            'state' => [
                'nullable',
                'string',
                'max:100',
            ],
            'state_code' => [
                'nullable',
                'integer',
            ],

            'address' => [
                'nullable',
                'string',
                'max:1000',
            ],

        ]);


        /*
        |--------------------------------------------------------------------------
        | GST State Map
        |--------------------------------------------------------------------------
        */

        $gstStates = [
            '01' => 'Jammu and Kashmir',
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
            '26' => 'Dadra and Nagar Haveli and Daman and Diu',
            '27' => 'Maharashtra',
            '29' => 'Karnataka',
            '30' => 'Goa',
            '31' => 'Lakshadweep',
            '32' => 'Kerala',
            '33' => 'Tamil Nadu',
            '34' => 'Puducherry',
            '35' => 'Andaman and Nicobar Islands',
            '36' => 'Telangana',
            '37' => 'Andhra Pradesh',
            '38' => 'Ladakh',
        ];


        /*
        |--------------------------------------------------------------------------
        | State
        |--------------------------------------------------------------------------
        */

        $data['state_code'] = null;


        if (
            !empty($data['state'])
            && str_contains($data['state'], ',')
        ) {

            [$code, $name] =
                explode(',', $data['state'], 2);

            $data['state_code'] =
                trim($code);

            $data['state'] =
                trim($name);
        }


        /*
        |--------------------------------------------------------------------------
        | Auto state using GSTIN
        |--------------------------------------------------------------------------
        */

        if (
            empty($data['state'])
            && !empty($data['gstin'])
            && strlen($data['gstin']) >= 2
        ) {

            $code =
                substr(
                    strtoupper($data['gstin']),
                    0,
                    2
                );


            if (isset($gstStates[$code])) {

                $data['state_code'] =
                    $code;

                $data['state'] =
                    $gstStates[$code];
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Business
        |--------------------------------------------------------------------------
        */

        $data['business_id'] =
            $bid;

        $data['is_save'] =
            true;


        /*
        |--------------------------------------------------------------------------
        | Create
        |--------------------------------------------------------------------------
        */

        $client =
            Client::create($data);


        $label =
            $client->party_type === 'supplier'
                ? 'Supplier'
                : 'Client';


        return redirect()
            ->route(
                'clients.index',
                [
                    'type' =>
                        $client->party_type === 'supplier'
                            ? 'supplier'
                            : 'client'
                ]
            )
            ->with(
                'success',
                $label . ' created successfully.'
            );
    }

    public function edit(Client $client)
    {
        // GlobalScope se client already active business ka hoga
        return view('clients.edit', compact('client'));
    }

    // public function update(Request $request, Client $client)
    // {
    //     $bid = $request->user()->current_business_id ?? session('active_business_id');

    //     $data = $request->validate([
    //         'name'    => ['required','string','max:255'],
    //         'mobile'  => [
    //             'nullable','string','max:20',
    //             Rule::unique('clients','mobile')
    //                 ->ignore($client->id)
    //                 ->where(fn($q) => $q->where('business_id',$bid)),
    //         ],
    //         'gstin'   => [
    //             'nullable','string','max:50',
    //             Rule::unique('clients','gstin')
    //                 ->ignore($client->id)
    //                 ->where(fn($q) => $q->where('business_id',$bid)),
    //         ],
    //         'pan'     => [
    //             'nullable','string','max:50',
    //             Rule::unique('clients','pan')
    //                 ->ignore($client->id)
    //                 ->where(fn($q) => $q->where('business_id',$bid)),
    //         ],
    //         'state'   => ['nullable','string','max:100'],
    //         'address' => ['nullable','string','max:1000'],
    //     ]);
    //     if (!empty($data['state']) && str_contains($data['state'], ',')) {
    //         [$code, $name] = explode(',', $data['state'], 2);

    //         $data['state_code'] = trim($code); // "09"
    //         $data['state']      = trim($name); // "Uttar Pradesh"
    //     }

    //     $client->update($data);

    //     return redirect()->route('clients.index')->with('success','Client updated successfully.');
    // }


    public function update(
        Request $request,
        Client $client
    ) {
        $bid =
            $request->user()->current_business_id
            ?? session('active_business_id')
            ?? $request->user()
                ->businesses()
                ->pluck('businesses.id')
                ->first();


        abort_unless(
            $bid,
            403,
            'Active business not found.'
        );


        $data = $request->validate([

            'party_type' => [
                'required',
                Rule::in([
                    'client',
                    'supplier',
                ]),
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'mobile' => [
                'nullable',
                'string',
                'max:20',

                Rule::unique('clients', 'mobile')
                    ->ignore($client->id)
                    ->where(
                        fn ($q) =>
                            $q->where('business_id', $bid)
                    ),
            ],

            'gstin' => [
                'nullable',
                'string',
                'max:50',

                Rule::unique('clients', 'gstin')
                    ->ignore($client->id)
                    ->where(
                        fn ($q) =>
                            $q->where('business_id', $bid)
                    ),
            ],

            'pan' => [
                'nullable',
                'string',
                'max:50',

                Rule::unique('clients', 'pan')
                    ->ignore($client->id)
                    ->where(
                        fn ($q) =>
                            $q->where('business_id', $bid)
                    ),
            ],

            'state' => [
                'nullable',
                'string',
                'max:100',
            ],

            'address' => [
                'nullable',
                'string',
                'max:1000',
            ],

        ]);


        $data['state_code'] =
            $client->state_code;


        if (
            !empty($data['state'])
            && str_contains($data['state'], ',')
        ) {

            [$code, $name] =
                explode(',', $data['state'], 2);

            $data['state_code'] =
                trim($code);

            $data['state'] =
                trim($name);
        }


        $client->update($data);


        $type =
            $client->party_type === 'supplier'
                ? 'supplier'
                : 'client';


        return redirect()
            ->route(
                'clients.index',
                ['type' => $type]
            )
            ->with(
                'success',
                ucfirst($type) .
                ' updated successfully.'
            );
    }
    public function destroy(Client $client)
    {
        $client->delete();
        return redirect()->route('clients.index')->with('success','Client deleted successfully.');
    }


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
//            'clientAutoSelect'    => $request->clientAutoSelect ? trim((string)$request->clientAutoSelect) : null,
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
//                'clientAutoSelect' => ['nullable','string','max:20'],
                'is_save' => ['required','boolean'],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        $data['business_id'] = $bid;
        $data['is_save'] = (bool) $data['is_save'];
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


    /**
     * Client purchase report.
     */
    // public function show(Request $request, Client $client)
    // {
    //     $businessId = $this->resolveBusinessId($request);

    //     abort_unless($businessId, 403, 'Active business not found.');

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Filtered invoice query
    //     |--------------------------------------------------------------------------
    //     */
    //     $invoiceQuery = $this->buildInvoiceReportQuery(
    //         $request,
    //         $client,
    //         $businessId
    //     );

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Summary - filtered records only
    //     |--------------------------------------------------------------------------
    //     */
    //     $summary = $this->getReportSummary($invoiceQuery);

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Pagination
    //     |--------------------------------------------------------------------------
    //     */
    //     $perPage = (int) $request->get('per_page', 15);

    //     if (!in_array($perPage, [15, 25, 50, 100], true)) {
    //         $perPage = 15;
    //     }

    //     $invoices = (clone $invoiceQuery)
    //         ->withCount('items')
    //         ->paginate($perPage)
    //         ->withQueryString();

    //     /*
    //     |--------------------------------------------------------------------------
    //     | Recent items according to currently filtered invoices
    //     |--------------------------------------------------------------------------
    //     */
    //     $filteredInvoiceIds = (clone $invoiceQuery)
    //         ->reorder()
    //         ->select('id');

    //     $recentItems = InvoiceItem::query()
    //         ->with('invoice')
    //         ->whereIn('invoice_id', $filteredInvoiceIds)
    //         ->latest('created_at')
    //         ->limit(10)
    //         ->get();

    //     return view('clients.show', [
    //         'client'      => $client,
    //         'invoices'    => $invoices,
    //         'summary'     => $summary,
    //         'recentItems' => $recentItems,
    //         'filters'     => $request->query(),
    //     ]);
    // }


    public function show(Request $request, Client $client)
    {
        $businessId = $this->resolveBusinessId($request);

        abort_unless(
            $businessId,
            403,
            'Active business not found.'
        );


        /*
        |--------------------------------------------------------------------------
        | SUPPLIER RECORD
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                $client->party_type,
                ['supplier', 'both'],
                true
            )
            &&
            $request->get('view') === 'supplier'
        ) {

            $search = trim(
                (string) $request->get('search', '')
            );


            $dateFrom =
                $request->filled('date_from')
                    ? Carbon::parse($request->date_from)
                    : null;


            $dateTo =
                $request->filled('date_to')
                    ? Carbon::parse($request->date_to)
                    : null;


            $purchaseQuery = Purchase::query()
                ->where('business_id', $businessId)
                ->where('supplier_id', $client->id)

                ->when(
                    $search !== '',
                    function ($query) use ($search) {

                        $query->where(function ($subQuery) use ($search) {

                            $subQuery
                                ->where(
                                    'invoice_no',
                                    'like',
                                    "%{$search}%"
                                );

                        });

                    }
                )

                ->when(
                    $dateFrom,
                    fn ($query) =>
                        $query->whereDate(
                            'invoice_date',
                            '>=',
                            $dateFrom->format('Y-m-d')
                        )
                )

                ->when(
                    $dateTo,
                    fn ($query) =>
                        $query->whereDate(
                            'invoice_date',
                            '<=',
                            $dateTo->format('Y-m-d')
                        )
                );


            /*
            |--------------------------------------------------------------------------
            | Supplier Summary
            |--------------------------------------------------------------------------
            */

            $summary = [

                'total_purchases' =>
                    (clone $purchaseQuery)->count(),

                'total_amount' =>
                    (float) (clone $purchaseQuery)
                        ->sum('total_amount'),

                'paid_amount' =>
                    (float) (clone $purchaseQuery)
                        ->sum('paid_amount'),

                'due_amount' =>
                    (float) (clone $purchaseQuery)
                        ->sum('due_amount'),

            ];


            /*
            |--------------------------------------------------------------------------
            | Purchase Listing
            |--------------------------------------------------------------------------
            */

            $purchases = $purchaseQuery
                ->with('items.item')
                ->orderByDesc('invoice_date')
                ->orderByDesc('id')
                ->paginate(15)
                ->withQueryString();


            return view(
                'clients.supplier-show',
                [
                    'supplier' => $client,
                    'purchases' => $purchases,
                    'summary' => $summary,
                    'filters' => $request->query(),
                ]
            );
        }


        /*
        |--------------------------------------------------------------------------
        | NORMAL CLIENT RECORD
        |--------------------------------------------------------------------------
        |
        | Aapka existing invoice report bilkul same.
        |
        */

        $invoiceQuery =
            $this->buildInvoiceReportQuery(
                $request,
                $client,
                $businessId
            );


        $summary =
            $this->getReportSummary(
                $invoiceQuery
            );


        $perPage =
            (int) $request->get(
                'per_page',
                15
            );


        if (
            !in_array(
                $perPage,
                [15, 25, 50, 100],
                true
            )
        ) {
            $perPage = 15;
        }


        $invoices =
            (clone $invoiceQuery)
                ->withCount('items')
                ->paginate($perPage)
                ->withQueryString();


        $filteredInvoiceIds =
            (clone $invoiceQuery)
                ->reorder()
                ->select('id');


        $recentItems =
            InvoiceItem::query()
                ->with('invoice')
                ->whereIn(
                    'invoice_id',
                    $filteredInvoiceIds
                )
                ->latest('created_at')
                ->limit(10)
                ->get();


        return view(
            'clients.show',
            [
                'client' => $client,
                'invoices' => $invoices,
                'summary' => $summary,
                'recentItems' => $recentItems,
                'filters' => $request->query(),
            ]
        );
    }

    /**
     * Download filtered report as PDF.
     */
    public function exportPdf(Request $request, Client $client)
    {
        $businessId = $this->resolveBusinessId($request);

        abort_unless($businessId, 403, 'Active business not found.');

        $invoiceQuery = $this->buildInvoiceReportQuery(
            $request,
            $client,
            $businessId
        );

        $summary = $this->getReportSummary($invoiceQuery);

        $invoices = (clone $invoiceQuery)
            ->withCount('items')
            ->get();

        $pdf = Pdf::loadView('clients.exports.pdf', [
            'client'     => $client,
            'invoices'   => $invoices,
            'summary'    => $summary,
            'filters'    => $request->query(),
            'businessId' => $businessId,
        ])
            ->setPaper('a4', 'landscape');

        $clientName = preg_replace(
            '/[^A-Za-z0-9\-_]/',
            '_',
            $client->name
        );

        return $pdf->download(
            'client-purchase-report-' .
            $clientName .
            '-' .
            now()->format('d-m-Y-H-i-s') .
            '.pdf'
        );
    }


    /**
     * Download filtered report as Excel.
     */
    public function exportExcel(Request $request, Client $client)
    {
        $businessId = $this->resolveBusinessId($request);

        abort_unless($businessId, 403, 'Active business not found.');

        $invoiceQuery = $this->buildInvoiceReportQuery(
            $request,
            $client,
            $businessId
        );

        $summary = $this->getReportSummary($invoiceQuery);

        $invoices = (clone $invoiceQuery)
            ->withCount('items')
            ->get();

        $clientName = preg_replace(
            '/[^A-Za-z0-9\-_]/',
            '_',
            $client->name
        );

        return Excel::download(
            new ClientPurchaseReportExport(
                $client,
                $invoices,
                $summary,
                $request->query()
            ),
            'client-purchase-report-' .
            $clientName .
            '-' .
            now()->format('d-m-Y-H-i-s') .
            '.xlsx'
        );
    }


    /**
     * Resolve currently active business.
     */
    private function resolveBusinessId(Request $request): ?int
    {
        $user = $request->user();

        if (!$user) {
            return null;
        }

        $businessId =
            $user->current_business_id
            ?? session('active_business_id')
            ?? $user->businesses()->pluck('businesses.id')->first();

        return $businessId ? (int) $businessId : null;
    }


    /**
     * Common invoice report query.
     *
     * IMPORTANT:
     * Screen, PDF and Excel tino isi query ko use karenge.
     * Isliye filters kabhi mismatch nahi honge.
     */
    private function buildInvoiceReportQuery(
        Request $request,
        Client $client,
        int $businessId
    ) {
        $query = $client->invoices()
            ->where('business_id', $businessId);

        /*
        |--------------------------------------------------------------------------
        | DATE FILTER
        |--------------------------------------------------------------------------
        */

        $dateFrom = null;
        $dateTo   = null;

        /*
        * Custom dates ko priority milegi.
        */
        if ($request->filled('date_from')) {
            $dateFrom = Carbon::parse($request->date_from);
        }

        if ($request->filled('date_to')) {
            $dateTo = Carbon::parse($request->date_to);
        }

        /*
        * Custom dates nahi diye to Period apply hoga.
        */
        if (!$dateFrom && !$dateTo) {

            switch ($request->period) {

                case 'today':

                    $dateFrom = Carbon::today();
                    $dateTo   = Carbon::today();

                    break;


                case 'this_week':

                    $dateFrom = Carbon::now()->startOfWeek();
                    $dateTo   = Carbon::now()->endOfWeek();

                    break;


                case 'this_month':

                    $dateFrom = Carbon::now()->startOfMonth();
                    $dateTo   = Carbon::now()->endOfMonth();

                    break;


                case 'last_month':

                    $dateFrom = Carbon::now()
                        ->subMonthNoOverflow()
                        ->startOfMonth();

                    $dateTo = Carbon::now()
                        ->subMonthNoOverflow()
                        ->endOfMonth();

                    break;


                case 'this_year':

                    $dateFrom = Carbon::now()->startOfYear();
                    $dateTo   = Carbon::now()->endOfYear();

                    break;
            }
        }


        if ($dateFrom) {

            $query->whereDate(
                'invoice_date',
                '>=',
                $dateFrom->format('Y-m-d')
            );

        }


        if ($dateTo) {

            $query->whereDate(
                'invoice_date',
                '<=',
                $dateTo->format('Y-m-d')
            );

        }


        /*
        |--------------------------------------------------------------------------
        | PAYMENT STATUS
        |--------------------------------------------------------------------------
        */

        if ($request->filled('payment_status')) {

            switch ($request->payment_status) {

                case 'paid':

                    $query->where('balance', '<=', 0);

                    break;


                case 'partial':

                    $query
                        ->where('received_amount', '>', 0)
                        ->where('balance', '>', 0);

                    break;


                case 'unpaid':

                    $query
                        ->where(function ($q) {
                            $q->whereNull('received_amount')
                                ->orWhere('received_amount', '<=', 0);
                        })
                        ->where('balance', '>', 0);

                    break;


                case 'due':

                    $query->where('balance', '>', 0);

                    break;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | SEARCH
        |--------------------------------------------------------------------------
        | Same box:
        |
        | Invoice Number
        | Invoice Prefix
        | Item Description
        |--------------------------------------------------------------------------
        */

        if ($request->filled('search')) {

            $search = trim((string) $request->search);

            $query->where(function ($q) use ($search) {

                $q->where(
                    'invoice_number',
                    'like',
                    "%{$search}%"
                )

                ->orWhere(
                    'invoice_prefix',
                    'like',
                    "%{$search}%"
                )

                ->orWhereHas(
                    'items',
                    function ($itemQuery) use ($search) {

                        $itemQuery->where(
                            'description',
                            'like',
                            "%{$search}%"
                        );

                    }
                );

            });
        }


        /*
        |--------------------------------------------------------------------------
        | DEFAULT SORT
        |--------------------------------------------------------------------------
        */

        return $query
            ->orderByDesc('invoice_date')
            ->orderByDesc('id');
    }


    /**
     * Calculate totals from currently filtered query.
     */
    private function getReportSummary($invoiceQuery): array
    {
        return [
            'total_invoices' => (clone $invoiceQuery)
                ->reorder()
                ->count(),

            'total_subtotal' => (clone $invoiceQuery)
                ->reorder()
                ->sum('subtotal'),

            'total_tax' => (clone $invoiceQuery)
                ->reorder()
                ->sum('tax_amount'),

            'total_amount' => (clone $invoiceQuery)
                ->reorder()
                ->sum('total'),

            'total_received' => (clone $invoiceQuery)
                ->reorder()
                ->sum('received_amount'),

            'total_balance' => (clone $invoiceQuery)
                ->reorder()
                ->sum('balance'),
        ];
    }


}
