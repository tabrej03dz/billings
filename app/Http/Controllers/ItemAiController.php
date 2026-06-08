<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ItemAiController extends Controller
{

    public function create()
    {
        $categories = Category::orderBy('name')->get(['id', 'name']);

        return view('items.ai-photo-entry', compact('categories'));
    }

    public function photoEntry(Request $request)
    {
        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $image = $request->file('photo');
        $base64 = base64_encode(file_get_contents($image->getRealPath()));
        $mime = $image->getMimeType();

        try {
            $response = Http::withToken(config('services.openai.key'))
                ->timeout(90)
                ->post('https://api.openai.com/v1/responses', [
                    'model' => 'gpt-4.1-mini',
                    'input' => [
                        [
                            'role' => 'user',
                            'content' => [
                                [
                                    'type' => 'input_text',
                                    'text' => '
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
                                    '
                                ],
                                [
                                    'type' => 'input_image',
                                    'image_url' => "data:$mime;base64,$base64",
                                ],
                            ],
                        ],
                    ],
                ]);

            if (!$response->successful()) {
                return response()->json([
                    'message' => 'AI request failed.',
                    'error' => $response->json(),
                ], 500);
            }

            $text = data_get($response->json(), 'output.0.content.0.text');

            $json = json_decode($text, true);

            if (!$json) {
                return response()->json([
                    'message' => 'AI response JSON format me nahi mila.',
                    'raw' => $text,
                ], 422);
            }

            return response()->json([
                'message' => 'Photo se item details mil gayi.',
                'data' => $json,
            ]);

        } catch (\Throwable $e) {
            Log::error('AI photo item entry failed', [
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'AI photo entry failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}