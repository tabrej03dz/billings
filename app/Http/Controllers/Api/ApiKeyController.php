<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use Illuminate\Http\Request;

class ApiKeyController extends Controller
{
    private function bid(): int
    {
        $bid = app()->bound('active_business_id') ? (int) app('active_business_id') : 0;
        abort_if(!$bid, 422, 'X-Business-Id header required.');
        return $bid;
    }

    public function index(Request $request)
    {
        $user = $request->user(); // auth user
        $businessId = $request->header('X-Business-Id');

        $q = ApiKey::query()
            ->where('user_id', $user->id)   // 👈 USER filter
            ->latest();

        // 🔐 sirf jab business id ho tab filter
        if (!empty($businessId)) {
            $q->where('business_id', $businessId);
        }

        $keys = $q->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $keys,
        ]);
    }


    // GET /api/api-keys/me (current business key)
    public function show()
    {
        $this->bid();

        $key = ApiKey::latest()->first();

        return response()->json([
            'success' => true,
            'data' => $key,
        ]);
    }

    // POST /api/api-keys  (UPSERT: create/update current business key)
    public function store(Request $request)
    {
        $bid = $this->bid();

        $data = $request->validate([
            'base_url' => ['nullable', 'string', 'max:255'],
            'wishes_api' => ['nullable', 'string', 'max:255'],
            'installment_reminder_api' => ['nullable', 'string', 'max:255'],
            'key'      => ['nullable', 'string', 'max:255'],
            'secret'   => ['nullable', 'string', 'max:255'],
        ]);

        // ✅ current business ke record ko update/create
        $apiKey = ApiKey::updateOrCreate(
            ['business_id' => $bid],
            $data
        );

        return response()->json([
            'success' => true,
            'message' => 'API Key saved successfully.',
            'data' => $apiKey,
        ]);
    }

    // DELETE /api/api-keys (delete current business key)
    public function destroy(ApiKey $api)
    {
        $this->bid();
        if (!$api) {
            return response()->json([
                'success' => true,
                'message' => 'No API key found.',
                'data' => null,
            ]);
        }

        $api->delete();

        return response()->json([
            'success' => true,
            'message' => 'API Key deleted successfully.',
            'data' => null,
        ]);
    }
}
