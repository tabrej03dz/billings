<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Services\ItemBarcodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class ItemBarcodeController extends Controller
{
    public function __construct(
        private readonly ItemBarcodeService $barcodeService
    ) {
    }

    /**
     * Barcode scan karke item details return karega.
     */
    public function lookup(
        Request $request,
        string $barcode
    ): JsonResponse {
        try {
            $businessId = $this->getBusinessId($request);

            $barcode = trim($barcode);

            if ($barcode === '') {
                return response()->json([
                    'status' => false,
                    'message' => 'Barcode is required.',
                    'code' => 'BARCODE_REQUIRED',
                ], 422);
            }

            $item = Item::query()
                ->where('business_id', $businessId)
                ->where('is_active', true)
                ->where(function ($query) use ($barcode) {
                    $query->where('barcode', $barcode)
                        ->orWhere('sku', $barcode);
                })
                ->first();

            if (!$item) {
                return response()->json([
                    'status' => false,
                    'message' => 'No item found for this barcode.',
                    'code' => 'ITEM_NOT_FOUND',
                ], 404);
            }

            return response()->json([
                'status' => true,
                'message' => 'Item found successfully.',
                'data' => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'sku' => $item->sku,
                    'barcode' => $item->barcode,
                    'description' => $item->description,
                    'price' => (float) $item->price,
                    'cost_price' => (float) $item->cost_price,
                    'tax_rate' => (float) $item->tax_rate,
                    'stock_qty' => (float) $item->stock_qty,
                    'unit' => $item->unit,
                    'category_id' => $item->category_id,
                    'is_active' => (bool) $item->is_active,
                ],
            ]);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'status' => false,
                'message' => 'Unable to find item.',
                'code' => 'SERVER_ERROR',
            ], 500);
        }
    }

    /**
     * Existing item ka barcode generate karega.
     */
    public function generate(
        Request $request,
        Item $item
    ): JsonResponse {
        try {
            $businessId = $this->getBusinessId($request);

            if ((int) $item->business_id !== $businessId) {
                return response()->json([
                    'status' => false,
                    'message' => 'You are not allowed to access this item.',
                    'code' => 'FORBIDDEN',
                ], 403);
            }

            $barcode = $this->barcodeService->generate($item);

            return response()->json([
                'status' => true,
                'message' => 'Barcode generated successfully.',
                'data' => [
                    'item_id' => $item->id,
                    'barcode' => $barcode,
                ],
            ]);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'status' => false,
                'message' => 'Unable to generate barcode.',
                'code' => 'SERVER_ERROR',
            ], 500);
        }
    }

    /**
     * Mobile app se item create karega.
     * App barcode bhej sakta hai ya backend automatic generate karega.
     */
    public function storeWithBarcode(
        Request $request
    ): JsonResponse {
        try {
            $businessId = $this->getBusinessId($request);

            $validator = Validator::make($request->all(), [
                'name' => [
                    'required',
                    'string',
                    'max:255',
                ],
                'sku' => [
                    'nullable',
                    'string',
                    'max:100',
                ],
                'barcode' => [
                    'nullable',
                    'string',
                    'max:100',
                    'unique:items,barcode',
                ],
                'description' => [
                    'nullable',
                    'string',
                ],
                'price' => [
                    'required',
                    'numeric',
                    'min:0',
                ],
                'cost_price' => [
                    'nullable',
                    'numeric',
                    'min:0',
                ],
                'tax_rate' => [
                    'nullable',
                    'numeric',
                    'min:0',
                    'max:100',
                ],
                'stock_qty' => [
                    'nullable',
                    'numeric',
                    'min:0',
                ],
                'unit' => [
                    'nullable',
                    'string',
                    'max:50',
                ],
                'category_id' => [
                    'nullable',
                    'integer',
                    'exists:categories,id',
                ],
            ], [
                'name.required' => 'Item name is required.',
                'price.required' => 'Item price is required.',
                'barcode.unique' => 'This barcode is already assigned to another item.',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation failed.',
                    'code' => 'VALIDATION_ERROR',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $item = Item::create([
                'business_id' => $businessId,
                'name' => $request->name,
                'sku' => $request->sku,
                'barcode' => $request->filled('barcode')
                    ? trim($request->barcode)
                    : null,
                'description' => $request->description,
                'price' => $request->price,
                'cost_price' => $request->cost_price ?? 0,
                'tax_rate' => $request->tax_rate ?? 0,
                'stock_qty' => $request->stock_qty ?? 0,
                'unit' => $request->unit,
                'category_id' => $request->category_id,
                'is_active' => true,
            ]);

            if (!$item->barcode) {
                $this->barcodeService->generate($item);
                $item->refresh();
            }

            return response()->json([
                'status' => true,
                'message' => 'Item created successfully.',
                'data' => [
                    'id' => $item->id,
                    'name' => $item->name,
                    'sku' => $item->sku,
                    'barcode' => $item->barcode,
                    'price' => (float) $item->price,
                    'tax_rate' => (float) $item->tax_rate,
                    'stock_qty' => (float) $item->stock_qty,
                ],
            ], 201);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'status' => false,
                'message' => 'Unable to create item.',
                'code' => 'SERVER_ERROR',
            ], 500);
        }
    }

    /**
     * App ko barcode label data dega.
     */
    public function label(
        Request $request,
        Item $item
    ): JsonResponse {
        try {
            $businessId = $this->getBusinessId($request);

            if ((int) $item->business_id !== $businessId) {
                return response()->json([
                    'status' => false,
                    'message' => 'You are not allowed to access this item.',
                    'code' => 'FORBIDDEN',
                ], 403);
            }

            if (!$item->barcode) {
                $this->barcodeService->generate($item);
                $item->refresh();
            }

            $barcodeSvg = \DNS1D::getBarcodeSVG(
                $item->barcode,
                'C128',
                2,
                60,
                '000000',
                false
            );

            return response()->json([
                'status' => true,
                'message' => 'Barcode label generated successfully.',
                'data' => [
                    'item_id' => $item->id,
                    'item_name' => $item->name,
                    'sku' => $item->sku,
                    'barcode' => $item->barcode,
                    'price' => (float) $item->price,
                    'barcode_svg' => $barcodeSvg,
                ],
            ]);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'status' => false,
                'message' => 'Unable to generate barcode label.',
                'code' => 'SERVER_ERROR',
            ], 500);
        }
    }

    private function getBusinessId(Request $request): int
    {
        $user = $request->user();

        $businessId = $request->header('X-Business-ID')
            ?? $request->input('business_id')
            ?? $user->current_business_id
            ?? $user->businesses()
                ->pluck('businesses.id')
                ->first();

        abort_unless(
            $businessId,
            422,
            'Business not found.'
        );

        return (int) $businessId;
    }
}