<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\InvoiceItem;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    // -----------------------------
    // GET /api/clients?q=
    // -----------------------------
    public function index(Request $request)
    {
        $bid = $this->resolveBusinessId($request);
        $q   = trim((string) $request->query('q', ''));

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
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'ok'    => true,
            'q'     => $q,
            'count' => $clients->count(),
            'data'  => $clients,
        ]);
    }

    // -----------------------------
    // POST /api/clients
    // -----------------------------
    public function store(Request $request)
    {
        $bid = $this->resolveBusinessId($request);

        // Normalize
        $request->merge([
            'mobile'  => $request->mobile ? preg_replace('/\s+/', '', (string) $request->mobile) : null,
            'gstin'   => $request->gstin  ? strtoupper(preg_replace('/\s+/', '', (string) $request->gstin)) : null,
            'pan'     => $request->pan    ? strtoupper(preg_replace('/\s+/', '', (string) $request->pan)) : null,
            'state'   => $request->state  ? trim((string) $request->state) : null,
            'address' => $request->address? trim((string) $request->address) : null,
            'name'    => $request->name   ? trim((string) $request->name) : null,
            'pincode' => $request->pincode? trim((string) $request->pincode) : null,
        ]);

        // empty string -> null
        foreach (['mobile','gstin','pan','state','address','pincode'] as $f) {
            if ($request->has($f) && $request->input($f) === '') {
                $request->merge([$f => null]);
            }
        }

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
            'state'   => ['nullable','string','max:100'], // "09, Uttar Pradesh" OR "Uttar Pradesh"
            'address' => ['nullable','string','max:1000'],
            'pincode' => ['nullable','string','max:20'],
        ]);

        // State split/derive
        $data = $this->applyStateFromInputOrGstin($data);

        $data['business_id'] = $bid;

        $client = Client::create($data);

        return response()->json([
            'ok'      => true,
            'message' => 'Client created successfully.',
            'client'  => $this->clientPayload($client),
        ], 201);
    }

    // -----------------------------
    // GET /api/clients/{client}
    // -----------------------------
    public function show(Request $request, Client $client)
    {
        $bid = $this->resolveBusinessId($request);

        if ((int) $client->business_id !== (int) $bid) {
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

        $perPage = (int) $request->query('per_page', 15);

        $invoices = $invoiceQuery
            ->withCount('items')
            ->paginate($perPage);

        $recentItems = InvoiceItem::query()
            ->whereHas('invoice', function ($q) use ($bid, $client) {
                $q->where('business_id', $bid)->where('client_id', $client->id);
            })
            ->with(['invoice:id,client_id,business_id,invoice_date,invoice_no,total'])
            ->latest('created_at')
            ->limit(10)
            ->get();

        return response()->json([
            'ok'           => true,
            'client'       => $this->clientPayload($client),
            'summary'      => $summary,
            'invoices'     => $invoices,
            'recent_items' => $recentItems,
        ]);
    }

    // -----------------------------
    // PUT/PATCH /api/clients/{client}
    // -----------------------------
//    public function update(Request $request, Client $client)
//    {
//        $bid = $this->resolveBusinessId($request);
//
//        if ((int) $client->business_id !== (int) $bid) {
//            return response()->json([
//                'ok' => false,
//                'message' => 'Client not found for active business.',
//            ], 404);
//        }
//
//        // Normalize
//        $request->merge([
//            'mobile'     => $request->mobile ? preg_replace('/\s+/', '', (string) $request->mobile) : null,
//            'gstin'      => $request->gstin ? strtoupper(preg_replace('/\s+/', '', (string) $request->gstin)) : null,
//            'pan'        => $request->pan ? strtoupper(preg_replace('/\s+/', '', (string) $request->pan)) : null,
//            'state'      => $request->state ? trim((string) $request->state) : null,
//            'state_code' => $request->state_code ? trim((string) $request->state_code) : null,
//            'address'    => $request->address ? trim((string) $request->address) : null,
//            'name'       => $request->name ? trim((string) $request->name) : null,
//            'pincode'    => $request->pincode ? trim((string) $request->pincode) : null,
//        ]);
//
//        foreach (['mobile','gstin','pan','state','state_code','address','pincode'] as $f) {
//            if ($request->has($f) && $request->input($f) === '') {
//                $request->merge([$f => null]);
//            }
//        }
//
//        $data = $request->validate([
//            'name' => ['required','string','max:255'],
//            'mobile' => [
//                'nullable','string','max:20',
//                Rule::unique('clients','mobile')->ignore($client->id)->where(fn($q) => $q->where('business_id', $bid)),
//            ],
//            'gstin' => [
//                'nullable','string','max:50',
//                Rule::unique('clients','gstin')->ignore($client->id)->where(fn($q) => $q->where('business_id', $bid)),
//            ],
//            'pan' => [
//                'nullable','string','max:50',
//                Rule::unique('clients','pan')->ignore($client->id)->where(fn($q) => $q->where('business_id', $bid)),
//            ],
//            'state'      => ['nullable','string','max:100'],
//            'state_code' => ['nullable','string','max:10'],
//            'address'    => ['nullable','string','max:1000'],
//            'pincode'    => ['nullable','string','max:20'],
//        ]);
//
//        // If state looks like "09, Uttar Pradesh" -> split
//        if (!empty($data['state']) && str_contains($data['state'], ',')) {
//            [$code, $name] = explode(',', $data['state'], 2);
//            $data['state_code'] = trim($code);
//            $data['state']      = trim($name);
//        }
//
//        // If state empty but gstin present -> derive (optional)
//        $data = $this->applyStateFromInputOrGstin($data);
//
//        $client->update($data);
//
//        return response()->json([
//            'ok'      => true,
//            'message' => 'Client updated successfully.',
//            'client'  => $this->clientPayload($client),
//        ]);
//    }


    public function update(Request $request, Client $client)
    {
        $bid = $this->resolveBusinessId($request);

        // ✅ Security: same business client hi update ho
        if ((int)$client->business_id !== (int)$bid) {
            return response()->json([
                'ok' => false,
                'message' => 'Client not found for this business.',
            ], 404);
        }

        // ✅ Normalize (same as store)
        $request->merge([
            'mobile'  => $request->has('mobile')  ? ($request->mobile ? preg_replace('/\s+/', '', (string)$request->mobile) : null) : $request->mobile,
            'gstin'   => $request->has('gstin')   ? ($request->gstin  ? strtoupper(preg_replace('/\s+/', '', (string)$request->gstin)) : null) : $request->gstin,
            'pan'     => $request->has('pan')     ? ($request->pan    ? strtoupper(preg_replace('/\s+/', '', (string)$request->pan)) : null) : $request->pan,
            'state'   => $request->has('state')   ? ($request->state  ? trim((string)$request->state) : null) : $request->state,
            'address' => $request->has('address') ? ($request->address? trim((string)$request->address) : null) : $request->address,
            'name'    => $request->has('name')    ? ($request->name   ? trim((string)$request->name) : null) : $request->name,
            'pincode' => $request->has('pincode') ? ($request->pincode? trim((string)$request->pincode) : null) : $request->pincode,
        ]);

        // ✅ empty string -> null (sirf jo fields aaye hain unpe)
        foreach (['mobile','gstin','pan','state','address','pincode','name'] as $f) {
            if ($request->has($f) && $request->input($f) === '') {
                $request->merge([$f => null]);
            }
        }

        // ✅ Validation (PATCH friendly: required nahi, sometimes use)
        $data = $request->validate([
            'name'    => ['sometimes','required','string','max:255'],
            'mobile'  => [
                'sometimes','nullable','string','max:20',
                Rule::unique('clients','mobile')
                    ->where(fn($q) => $q->where('business_id', $bid))
                    ->ignore($client->id),
            ],
            'gstin'   => [
                'sometimes','nullable','string','max:50',
                Rule::unique('clients','gstin')
                    ->where(fn($q) => $q->where('business_id', $bid))
                    ->ignore($client->id),
            ],
            'pan'     => [
                'sometimes','nullable','string','max:50',
                Rule::unique('clients','pan')
                    ->where(fn($q) => $q->where('business_id', $bid))
                    ->ignore($client->id),
            ],
            'state'   => ['sometimes','nullable','string','max:100'], // "09, Uttar Pradesh" OR "Uttar Pradesh"
            'address' => ['sometimes','nullable','string','max:1000'],
            'pincode' => ['sometimes','nullable','string','max:20'],
        ]);

        // ✅ State / state_code derive (only if relevant fields are being updated)
        // NOTE: aapka helper internally handle kare. Agar nahi karta,
        // to ye call safe hai, kyunki $data me sirf updated keys hi hongi.
        $data = $this->applyStateFromInputOrGstin($data);

        // ✅ Ensure business_id update na ho
        unset($data['business_id']);

        $client->fill($data);
        $client->save();

        return response()->json([
            'ok'      => true,
            'message' => 'Client updated successfully.',
            'client'  => $this->clientPayload($client->fresh()),
        ], 200);
    }

    // -----------------------------
    // DELETE /api/clients/{client}
    // -----------------------------
    public function destroy(Request $request, Client $client)
    {
        $bid = $this->resolveBusinessId($request);

        if ((int) $client->business_id !== (int) $bid) {
            return response()->json([
                'ok' => false,
                'message' => 'Client not found for active business.',
            ], 404);
        }

        $client->delete();

        return response()->json([
            'ok'      => true,
            'message' => 'Client deleted successfully.',
        ]);
    }

    // ---------------- Helpers ----------------

    /**
     * ✅ API best practice:
     * - X-Business-Id header OR business_id query OR user->current_business_id OR first business relation
     */
    private function resolveBusinessId(Request $request): int
    {
        $user = $request->user();
        if (!$user) {
            abort(response()->json([
                'ok' => false,
                'message' => 'Unauthenticated.',
            ], 401));
        }

        $headerBid = $request->header('X-Business-Id');
        if ($headerBid && is_numeric($headerBid)) return (int) $headerBid;

        $queryBid = $request->query('business_id');
        if ($queryBid && is_numeric($queryBid)) return (int) $queryBid;

        if (!empty($user->current_business_id)) return (int) $user->current_business_id;

        if (method_exists($user, 'businesses')) {
            $first = $user->businesses()->select('businesses.id')->first();
            if ($first) return (int) $first->id;
        }

        abort(response()->json([
            'ok' => false,
            'message' => 'Active business not found. Send X-Business-Id header or set current_business_id.',
        ], 422));
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

    private function applyStateFromInputOrGstin(array $data): array
    {
        $data['state_code'] = $data['state_code'] ?? null;

        // CASE 1: "09, Uttar Pradesh"
        if (!empty($data['state']) && str_contains($data['state'], ',')) {
            [$code, $name] = explode(',', $data['state'], 2);
            $data['state_code'] = trim($code);
            $data['state']      = trim($name);
            return $data;
        }

        // CASE 2: derive from GSTIN
        if ((empty($data['state']) || empty($data['state_code'])) && !empty($data['gstin']) && strlen($data['gstin']) >= 2) {
            $gstStates = [
                '01'=>'Jammu and Kashmir','02'=>'Himachal Pradesh','03'=>'Punjab',
                '04'=>'Chandigarh','05'=>'Uttarakhand','06'=>'Haryana','07'=>'Delhi',
                '08'=>'Rajasthan','09'=>'Uttar Pradesh','10'=>'Bihar','11'=>'Sikkim',
                '12'=>'Arunachal Pradesh','13'=>'Nagaland','14'=>'Manipur','15'=>'Mizoram',
                '16'=>'Tripura','17'=>'Meghalaya','18'=>'Assam','19'=>'West Bengal',
                '20'=>'Jharkhand','21'=>'Odisha','22'=>'Chhattisgarh',
                '23'=>'Madhya Pradesh','24'=>'Gujarat',
                '26'=>'Dadra and Nagar Haveli and Daman and Diu',
                '27'=>'Maharashtra','29'=>'Karnataka','30'=>'Goa','31'=>'Lakshadweep',
                '32'=>'Kerala','33'=>'Tamil Nadu','34'=>'Puducherry',
                '35'=>'Andaman and Nicobar Islands','36'=>'Telangana',
                '37'=>'Andhra Pradesh','38'=>'Ladakh',
            ];

            $code = substr((string)$data['gstin'], 0, 2);

            if (isset($gstStates[$code])) {
                $data['state_code'] = $data['state_code'] ?: $code;
                $data['state']      = $data['state'] ?: $gstStates[$code];
            }
        }

        return $data;
    }
}
