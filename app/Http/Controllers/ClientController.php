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
            'state'   => ['nullable','string','max:100'],
            'address' => ['nullable','string','max:1000'],
        ]);

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
        $bid = $request->user()->current_business_id ?? session('active_business_id');

        $data = $request->validate([
            'name'    => ['required','string','max:255'],
            'mobile'  => ['required','string','max:20',
                Rule::unique('clients','mobile')->where(fn($q)=>$q->where('business_id',$bid))
            ],
            'gstin'   => ['nullable','string','max:50',
                Rule::unique('clients','gstin')->where(fn($q)=>$q->where('business_id',$bid))
            ],
            'pan'     => ['nullable','string','max:50',
                Rule::unique('clients','pan')->where(fn($q)=>$q->where('business_id',$bid))
            ],
            'state'   => ['nullable','string','max:100'],
            'address' => ['nullable','string','max:1000'],
        ]);

        $data['business_id'] = $bid;
        $client = \App\Models\Client::create($data);

        return response()->json([
            'ok' => true,
            'client' => [
                'id' => $client->id,
                'name' => $client->name,
                'mobile' => $client->mobile,
            ]
        ]);
    }

}
