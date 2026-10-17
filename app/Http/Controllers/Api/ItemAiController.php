<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


class ItemAiController extends Controller
{
   public function photoEntry(Request $request)
{
    $request->validate([
        'photo' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
    ]);

    if (!$request->hasFile('photo')) {
        return response()->json([
            'status' => false,
            'message' => 'Photo request me nahi mili.',
        ], 422);
    }

    $image = $request->file('photo');

    if (!$image->isValid()) {
        return response()->json([
            'status' => false,
            'message' => 'Uploaded photo valid nahi hai.',
            'error' => $image->getErrorMessage(),
        ], 422);
    }
    
    $apiKey = config('services.openai.key');

    if (empty($apiKey)) {
        return response()->json([
            'status' => false,
            'message' => 'OpenAI API key missing hai. .env me OPENAI_API_KEY set karo.',
        ], 500);
    }

    $base64 = base64_encode(file_get_contents($image->getRealPath()));
    $mime = $image->getMimeType();

    try {
        $response = Http::withToken($apiKey)
            ->acceptJson()
            ->asJson()
            ->timeout(90)
            ->post('https://api.openai.com/v1/responses', [
                'model' => 'gpt-4.1-mini',
                'input' => [
                    [
                        'role' => 'user',
                        'content' => [
                            [
                                'type' => 'input_text',
                                'text' => <<<PROMPT
                                You are a jewellery billing item extractor.

                                Analyze this product photo and return ONLY valid JSON.

                                Use this exact JSON format:
                                {
                                "name": "",
                                "type": "product",
                                "description": "",
                                "metal_type": "gold",
                                "purity": "",
                                "gross_weight": null,
                                "metal_weight": null,
                                "stone_weight": null,
                                "stone_charges": null,
                                "gold_weight": null,
                                "gold_purity": "",
                                "silver_weight": null,
                                "silver_purity": "",
                                "diamond_weight": null,
                                "diamond_charges": null,
                                "making_charge": null,
                                "price": null,
                                "cost_price": null,
                                "stock_qty": 1,
                                "unit": "pcs",
                                "tax_rate": 3,
                                "sac": null,
                                "sku": ""
                                }

                                Rules:
                                - Do not guess exact price or weight if not visible.
                                - If item looks like gold jewellery, metal_type = gold.
                                - If item looks like silver jewellery, metal_type = silver.
                                - If unknown, metal_type = other.
                                - type should be product.
                                - Return only JSON, no markdown.
                                PROMPT
                            ],
                            [
                                'type' => 'input_image',
                                'image_url' => "data:{$mime};base64,{$base64}",
                            ],
                        ],
                    ],
                ],
            ]);

        if (!$response->successful()) {
            return response()->json([
                'status' => false,
                'message' => 'AI request failed.',
                'http_status' => $response->status(),
                'error' => $response->json(),
            ], 500);
        }

        $result = $response->json();

        $text = data_get($result, 'output.0.content.0.text');

        if (!$text) {
            return response()->json([
                'status' => false,
                'message' => 'AI se text response nahi mila.',
                'raw' => $result,
            ], 422);
        }

        $json = json_decode(trim($text), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json([
                'status' => false,
                'message' => 'AI response valid JSON format me nahi mila.',
                'json_error' => json_last_error_msg(),
                'raw' => $text,
            ], 422);
        }

        return response()->json([
            'status' => true,
            'message' => 'Photo se item details mil gayi.',
            'data' => $json,
        ], 200);

    } catch (\Throwable $e) {
        Log::error('AI photo item entry failed', [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
        ]);

        return response()->json([
            'status' => false,
            'message' => 'AI photo entry failed.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

// public function photoEntry(Request $request)
// {
//     return response()->json([
//         'has_photo' => $request->hasFile('photo'),
//         'photo_name' => $request->file('photo')?->getClientOriginalName(),
//         'mime' => $request->file('photo')?->getMimeType(),
//         'size' => $request->file('photo')?->getSize(),
//         'content_type' => $request->header('Content-Type'),
//     ]);
// }
}
