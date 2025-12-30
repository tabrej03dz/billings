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
                'required','string','max:20',
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
                'required','string','max:20',
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
            'mobile'  => $request->mobile ? preg_replace('/\s+/', '', $request->mobile) : null,
            'gstin'   => $request->gstin ? strtoupper(preg_replace('/\s+/', '', $request->gstin)) : null,
            'pan'     => $request->pan ? strtoupper(preg_replace('/\s+/', '', $request->pan)) : null,
            'state'   => $request->state ? trim($request->state) : null,
            'address' => $request->address ? trim($request->address) : null,
            'name'    => $request->name ? trim($request->name) : null,
        ]);

        // ✅ Convert empty string to null for nullable fields
        foreach (['gstin','pan','state','address'] as $f) {
            if ($request->has($f) && $request->input($f) === '') {
                $request->merge([$f => null]);
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
                'state'   => ['nullable','string','max:100'],
                'address' => ['nullable','string','max:1000'],
            ]);
        } catch (ValidationException $e) {
            // ✅ return validation errors as JSON for modal
            return response()->json([
                'ok' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        }

        $data['business_id'] = $bid;

        $client = \App\Models\Client::create($data);

        return response()->json([
            'ok' => true,
            'client' => [
                'id'     => $client->id,
                'name'   => $client->name,
                'mobile' => $client->mobile,
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
