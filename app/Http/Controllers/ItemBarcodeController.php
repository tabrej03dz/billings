<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Services\ItemBarcodeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ItemBarcodeController extends Controller
{
    public function __construct(
        private readonly ItemBarcodeService $barcodeService
    ) {
    }

    /**
     * Single item ka barcode generate karega.
     */
    // public function generate(Request $request, Item $item): RedirectResponse
    // {
    //     $this->authorizeItem($request, $item);

    //     $this->barcodeService->generate($item);

    //     return back()->with(
    //         'success',
    //         'Barcode generated successfully.'
    //     );
    // }

public function generate(
    Request $request,
    Item $item
): RedirectResponse {

    $this->authorizeItem(
        $request,
        $item
    );

    /*
    |--------------------------------------------------------------------------
    | Generate only when missing
    |--------------------------------------------------------------------------
    */

    $this->barcodeService->generate(
        $item
    );

    return back()->with(
        'success',
        'Barcode generated successfully.'
    );
}

    /**
     * Current business ke sab missing barcodes generate karega.
     */
    public function generateMissing(Request $request): RedirectResponse
    {
        $businessId = $this->getBusinessId($request);

        $generatedCount = 0;

        Item::query()
            ->where('business_id', $businessId)
            ->where(function ($query) {
                $query->whereNull('barcode')
                    ->orWhere('barcode', '');
            })
            ->orderBy('id')
            ->chunkById(200, function ($items) use (&$generatedCount) {
                foreach ($items as $item) {
                    $this->barcodeService->generate($item);
                    $generatedCount++;
                }
            });

        return back()->with(
            'success',
            "{$generatedCount} missing barcode(s) generated successfully."
        );
    }

    /**
     * Single item ke barcode labels print karega.
     */
public function printOne(
    Request $request,
    Item $item
): View {

    $this->authorizeItem(
        $request,
        $item
    );

    /*
    |--------------------------------------------------------------------------
    | Barcode
    |--------------------------------------------------------------------------
    |
    | Existing hai = same rahega
    | Blank hai = naya generate hoga
    |
    */

    $this->barcodeService->generate(
        $item
    );

    $item->refresh();

    $quantity = max(
        1,
        min(
            200,
            $request->integer(
                'quantity',
                1
            )
        )
    );

    $item->forceFill([
        'barcode_printed_at' => now(),
    ])->saveQuietly();

    return view(
        'items.barcodes.print',
        [
            'items' => collect([
                [
                    'item' => $item->fresh(),
                    'quantity' => $quantity,
                ],
            ]),

            'autoPrint' =>
                $request->boolean(
                    'print',
                    true
                ),
        ]
    );
}

    /**
     * Selected multiple items ke labels print karega.
     */
    public function printBulk(Request $request): View
    {
        $validated = $request->validate([
            'item_ids' => [
                'required',
                'array',
                'min:1',
                'max:100',
            ],
            'item_ids.*' => [
                'required',
                'integer',
                'distinct',
            ],
            'quantity' => [
                'nullable',
                'integer',
                'min:1',
                'max:200',
            ],
        ], [
            'item_ids.required' => 'Please select at least one item.',
            'item_ids.min' => 'Please select at least one item.',
            'quantity.max' => 'Maximum 200 copies are allowed.',
        ]);

        $businessId = $this->getBusinessId($request);
        $quantity = (int) ($validated['quantity'] ?? 1);

        $items = Item::query()
            ->where('business_id', $businessId)
            ->whereIn('id', $validated['item_ids'])
            ->orderBy('name')
            ->get();

        abort_if(
            $items->isEmpty(),
            404,
            'No valid items were selected.'
        );

        foreach ($items as $item) {
            $this->barcodeService->generate($item);

            $item->forceFill([
                'barcode_printed_at' => now(),
            ])->saveQuietly();
        }

        return view('items.barcodes.print', [
            'items' => $items->map(function (Item $item) use ($quantity) {
                return [
                    'item' => $item->fresh(),
                    'quantity' => $quantity,
                ];
            }),
            'autoPrint' => true,
        ]);
    }

    /**
     * Scanner se item lookup karega.
     */
    public function lookup(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'barcode' => [
                'required',
                'string',
                'max:100',
            ],
        ], [
            'barcode.required' => 'Barcode is required.',
        ]);

        $businessId = $this->getBusinessId($request);
        $barcode = trim($validated['barcode']);

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
                'ok' => false,
                'message' => 'Item not found for this barcode.',
            ], 404);
        }

        return response()->json([
            'ok' => true,
            'message' => 'Item found successfully.',
            'item' => $item,
        ]);
    }

    /**
     * Current active business ID.
     */
    private function getBusinessId(Request $request): int
    {
        $user = $request->user();

        $businessId = $user->current_business_id
            ?? session('active_business_id')
            ?? $user->businesses()
                ->pluck('businesses.id')
                ->first();

        abort_unless(
            $businessId,
            422,
            'Active business not found.'
        );

        return (int) $businessId;
    }

    /**
     * Check karega item current business ka hi hai.
     */
    private function authorizeItem(
        Request $request,
        Item $item
    ): void {
        $businessId = $this->getBusinessId($request);

        abort_unless(
            (int) $item->business_id === $businessId,
            403,
            'You are not allowed to access this item.'
        );
    }
}