<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    /**
     * Display a listing of clients.
     */
    public function index()
    {
        $clients = Client::latest()->get();
        return view('clients.index', compact('clients'));
    }

    /**
     * Show the form for creating a new client.
     */
    public function create()
    {
        $businesses = Business::all();
        return view('clients.create', compact('businesses'));
    }

    /**
     * Store a newly created client in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'    => 'required|string|max:255',
            'mobile'  => 'required|string|max:15',
            'gstin'   => 'nullable|string|max:50',
            'pan'     => 'nullable|string|max:50',
            'state'   => 'nullable|string|max:100',
            'address' => 'required|string',
            'business_id' => 'required_if:role,super admin',
        ]);


        Client::create($request->all() + ['business_id' => $request->business_id ?? auth()->user()->business_id]);

        return redirect()->route('clients.index')->with('success', 'Client added successfully.');
    }

    /**
     * Show the form for editing the specified client.
     */
    public function edit(Client $client)
    {
        return view('clients.create', compact('client'));
    }

    /**
     * Update the specified client in storage.
     */
    public function update(Request $request, Client $client)
    {
        $request->validate([
            'name'    => 'required|string|max:255',
            'mobile'  => 'required|string|max:15',
            'gstin'   => 'nullable|string|max:50',
            'pan'     => 'nullable|string|max:50',
            'state'   => 'nullable|string|max:100',
            'address' => 'required|string',
        ]);

        $client->update($request->only([
            'name', 'mobile', 'gstin', 'pan', 'state', 'address'
        ]));

        return redirect()->route('clients.index')->with('success', 'Client updated successfully.');
    }

    /**
     * Remove the specified client from storage.
     */
    public function destroy(Client $client)
    {
        $client->delete();
        return redirect()->route('clients.index')->with('success', 'Client deleted successfully.');
    }
}
