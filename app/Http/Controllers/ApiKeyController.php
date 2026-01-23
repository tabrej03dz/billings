<?php

namespace App\Http\Controllers;

use App\Models\ApiKey;
use Illuminate\Http\Request;

class ApiKeyController extends Controller
{
    public function index()
    {
        $keys = ApiKey::withoutGlobalScope('business')->latest()->paginate(20);

        return view('api_keys.index', compact('keys'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        return view('api_keys.create');
    }

    /**
     * Store new API key
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'base_url' => ['nullable', 'string', 'max:255'],
            'key'      => ['nullable', 'string', 'max:255'],
            'secret'   => ['nullable', 'string', 'max:255'],
        ]);

        // BelongsToBusiness trait auto business_id set karega
        ApiKey::updateOrCreate(
            [
                'business_id' => session('active_business_id') ?: (auth()->check() ? auth()->user()->current_business_id : null), // ya auth()->user()->business_id
            ],
            $data
        );

        return redirect()
            ->route('api-keys.index')
            ->with('success', 'API Key saved successfully.');
    }

    /**
     * Show edit form
     */
    public function edit(ApiKey $apiKey)
    {
        // Route Model Binding + BelongsToBusiness global scope:
        // agar dusre business ka id aayega to 404 dega
        return view('api_keys._form', compact('apiKey'));
    }

    /**
     * Update existing API key
     */
    public function update(Request $request, ApiKey $apiKey)
    {
        $data = $request->validate([
            'base_url' => ['nullable', 'string', 'max:255'],
            'key'      => ['nullable', 'string', 'max:255'],
            'secret'   => ['nullable', 'string', 'max:255'],
        ]);

        $apiKey->update($data);

        return redirect()
            ->route('api-keys.index')
            ->with('success', 'API Key updated successfully.');
    }

    /**
     * Delete API key
     */
    public function destroy(ApiKey $apiKey)
    {
        $apiKey->delete();

        return redirect()
            ->route('api-keys.index')
            ->with('success', 'API Key deleted successfully.');
    }

    /**
     * (Optional) Single show — agar chahiye ho
     */
    public function show(ApiKey $apiKey)
    {
        return view('api_keys.show', compact('apiKey'));
    }
}
