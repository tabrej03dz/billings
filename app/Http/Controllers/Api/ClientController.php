<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\InvoiceItem;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $bid = $this->resolveBusinessId($request);

        $q = trim((string) $request->query('q', ''));

        $clients = Client::query()
            ->where('business_id', $bid)
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
            ->get();

        return response()->json([
            'ok' => true,
            'q' => $q,
            'count' => $clients->count(),
            'data' => $clients,
        ]);
    }


    /**
     * POST /api/clients
     * ✅ Merged Store (normal + quick)
     */
    public function store(Request $request)
    {
        $bid = $this->resolveBusinessId($request);

        // ✅ Normalize inputs (avoid duplicates by formatting)
        $request->merge([
            'mobile'     => $request->mobile ? preg_replace('/\s+/', '', $request->mobile) : null,
            'gstin'      => $request->gstin ? strtoupper(preg_replace('/\s+/', '', $request->gstin)) : null,
            'pan'        => $request->pan ? strtoupper(preg_replace('/\s+/', '', $request->pan)) : null,
            'state'      => $request->state ? trim($request->state) : null,
            'state_code' => $request->state_code ? trim($request->state_code) : null,
            'address'    => $request->address ? trim($request->address) : null,
            'name'       => $request->name ? trim($request->name) : null,
            'pincode'    => $request->pincode ? trim($request->pincode) : null,
        ]);

        // ✅ Convert empty string to null for nullable fields
        foreach (['mobile','gstin','pan','state','state_code','address','pincode'] as $f) {
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

                // ✅ Quick modal me sirf state/state_code bhi aa sakta
                'state'      => ['nullable','string','max:100'],
                'state_code' => ['nullable','string','max:10'],

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

        // ✅ If state like "09, Uttar Pradesh" → split
        if (!empty($data['state']) && str_contains($data['state'], ',')) {
            [$code, $name] = explode(',', $data['state'], 2);
            $data['state_code'] = trim($code);
            $data['state']      = trim($name);
        }

        $data['business_id'] = $bid;

        $client = Client::create($data);

        return response()->json([
            'ok' => true,
            'message' => 'Client created successfully.',
            'client' => $this->clientPayload($client),
        ], 201);
    }

    /**
     * GET /api/clients/{client}
     * Show client + invoices summary + invoice list + recent items
     */
    public function show(Request $request, Client $client)
    {
        $bid = $this->resolveBusinessId($request);

        if ((int)$client->business_id !== (int)$bid) {
            return response()->json([
                'ok' => false,
                'message' => 'Client not found for active business.',
            ], 404);
        }

        $invoiceQuery = $client->invoices()
            ->where('business_id', $bid)
            ->orderByDesc('invoice_date')
            ->orderByDesc('id');

        $summary = [
            'total_invoices' => (clone $invoiceQuery)->count(),
            'total_amount'   => (clone $invoiceQuery)->sum('total'),
            'total_received' => (clone $invoiceQuery)->sum('received_amount'),
            'total_balance'  => (clone $invoiceQuery)->sum('balance'),
        ];

        $invoices = $invoiceQuery
            ->withCount('items')
            ->paginate((int) $request->query('per_page', 15))
            ->withQueryString();

        $recentItems = InvoiceItem::with(['invoice' => function ($q) use ($bid, $client) {
            $q->where('business_id', $bid)
                ->where('client_id', $client->id);
        }])
            ->whereHas('invoice', function ($q) use ($bid, $client) {
                $q->where('business_id', $bid)
                    ->where('client_id', $client->id);
            })
            ->latest('created_at')
            ->limit(10)
            ->get();

        return response()->json([
            'ok' => true,
            'client' => $this->clientPayload($client),
            'summary' => $summary,
            'invoices' => $invoices,
            'recent_items' => $recentItems,
        ]);
    }

    /**
     * PUT/PATCH /api/clients/{client}
     */
    public function update(Request $request, Client $client)
    {
        $bid = $this->resolveBusinessId($request);

        if ((int)$client->business_id !== (int)$bid) {
            return response()->json([
                'ok' => false,
                'message' => 'Client not found for active business.',
            ], 404);
        }

        // Normalize
        $request->merge([
            'mobile'     => $request->mobile ? preg_replace('/\s+/', '', $request->mobile) : null,
            'gstin'      => $request->gstin ? strtoupper(preg_replace('/\s+/', '', $request->gstin)) : null,
            'pan'        => $request->pan ? strtoupper(preg_replace('/\s+/', '', $request->pan)) : null,
            'state'      => $request->state ? trim($request->state) : null,
            'state_code' => $request->state_code ? trim($request->state_code) : null,
            'address'    => $request->address ? trim($request->address) : null,
            'name'       => $request->name ? trim($request->name) : null,
            'pincode'    => $request->pincode ? trim($request->pincode) : null,
        ]);

        foreach (['mobile','gstin','pan','state','state_code','address','pincode'] as $f) {
            if ($request->has($f) && $request->input($f) === '') {
                $request->merge([$f => null]);
            }
        }

        $data = $request->validate([
            'name'    => ['required','string','max:255'],

            'mobile'  => [
                'nullable','string','max:20',
                Rule::unique('clients','mobile')
                    ->ignore($client->id)
                    ->where(fn($q) => $q->where('business_id', $bid)),
            ],

            'gstin'   => [
                'nullable','string','max:50',
                Rule::unique('clients','gstin')
                    ->ignore($client->id)
                    ->where(fn($q) => $q->where('business_id', $bid)),
            ],

            'pan'     => [
                'nullable','string','max:50',
                Rule::unique('clients','pan')
                    ->ignore($client->id)
                    ->where(fn($q) => $q->where('business_id', $bid)),
            ],

            'state'      => ['nullable','string','max:100'],
            'state_code' => ['nullable','string','max:10'],
            'address'    => ['nullable','string','max:1000'],
            'pincode'    => ['nullable','string','max:20'],
        ]);

        if (!empty($data['state']) && str_contains($data['state'], ',')) {
            [$code, $name] = explode(',', $data['state'], 2);
            $data['state_code'] = trim($code);
            $data['state']      = trim($name);
        }

        $client->update($data);

        return response()->json([
            'ok' => true,
            'message' => 'Client updated successfully.',
            'client' => $this->clientPayload($client),
        ]);
    }

    /**
     * DELETE /api/clients/{client}
     */
    public function destroy(Request $request, Client $client)
    {
        $bid = $this->resolveBusinessId($request);

        if ((int)$client->business_id !== (int)$bid) {
            return response()->json([
                'ok' => false,
                'message' => 'Client not found for active business.',
            ], 404);
        }

        $client->delete();

        return response()->json([
            'ok' => true,
            'message' => 'Client deleted successfully.',
        ]);
    }

    // ---------------- Helpers ----------------

    private function resolveBusinessId(Request $request): int
    {
        $user = $request->user();

        $bid =
            $user->current_business_id
            ?? session('active_business_id')
            ?? $user->businesses()->pluck('businesses.id')->first();

        if (!$bid) {
            abort(response()->json([
                'ok' => false,
                'message' => 'Active business not found.',
            ], 422));
        }

        return (int) $bid;
    }

    private function clientPayload(Client $client): array
    {
        return [
            'id'         => $client->id,
            'name'       => $client->name,
            'mobile'     => $client->mobile,
            'address'    => $client->address,
            'state'      => $client->state,
            'state_code' => $client->state_code,
            'gstin'      => $client->gstin,
            'pan'        => $client->pan,
            'pincode'    => $client->pincode,
            'business_id'=> $client->business_id,
            'created_at' => $client->created_at,
            'updated_at' => $client->updated_at,
        ];
    }
}
