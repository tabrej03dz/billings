<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function summary(Request $request)
    {
        $request->validate([
            'from' => ['nullable', 'date'],
            'to'   => ['nullable', 'date', 'after_or_equal:from'],
            'q'    => ['nullable', 'string', 'max:100'],
        ]);

        $businessId = $this->getCurrentBusinessId($request);

        $from = $request->filled('from')
            ? Carbon::parse($request->input('from'))->startOfDay()
            : null;

        $to = $request->filled('to')
            ? Carbon::parse($request->input('to'))->endOfDay()
            : now()->endOfDay();

        $search = trim((string) $request->input('q'));

        /*
        |--------------------------------------------------------------------------
        | Inventory Query
        |--------------------------------------------------------------------------
        |
        | qty_change positive  = Stock In
        | qty_change negative  = Stock Out
        |
        */
        $query = Item::query()
            ->select('items.*')
            ->where('items.business_id', $businessId)
            ->with('category:id,name')

            /*
             * Opening Stock:
             * Selected from date से पहले का पूरा stock.
             */
            ->withSum([
                'stockMovements as opening_stock' => function ($movementQuery) use ($from) {
                    if ($from) {
                        $movementQuery->where('created_at', '<', $from);
                    } else {
                        $movementQuery->whereRaw('1 = 0');
                    }
                },
            ], 'qty_change')

            /*
             * Stock In:
             * Selected period में positive movements.
             */
            ->withSum([
                'stockMovements as stock_in_qty' => function ($movementQuery) use ($from, $to) {
                    $movementQuery
                        ->where('qty_change', '>', 0)
                        ->where('created_at', '<=', $to);

                    if ($from) {
                        $movementQuery->where('created_at', '>=', $from);
                    }
                },
            ], 'qty_change')

            /*
             * Stock Out:
             * Selected period में negative movements.
             */
            ->withSum([
                'stockMovements as stock_out_qty' => function ($movementQuery) use ($from, $to) {
                    $movementQuery
                        ->where('qty_change', '<', 0)
                        ->where('created_at', '<=', $to);

                    if ($from) {
                        $movementQuery->where('created_at', '>=', $from);
                    }
                },
            ], 'qty_change')

            /*
             * Closing Stock:
             * शुरुआत से selected to date तक का पूरा stock.
             */
            ->withSum([
                'stockMovements as closing_stock' => function ($movementQuery) use ($to) {
                    $movementQuery->where('created_at', '<=', $to);
                },
            ], 'qty_change')

            /*
             * Search by item, SKU or category.
             */
            ->when($search !== '', function (Builder $itemQuery) use ($search) {
                $itemQuery->where(function (Builder $searchQuery) use ($search) {
                    $searchQuery
                        ->where('items.name', 'like', '%' . $search . '%')
                        ->orWhere('items.sku', 'like', '%' . $search . '%')
                        ->orWhereHas('category', function (Builder $categoryQuery) use ($search) {
                            $categoryQuery->where('name', 'like', '%' . $search . '%');
                        });
                });
            });

        /*
        |--------------------------------------------------------------------------
        | Overall Totals
        |--------------------------------------------------------------------------
        |
        | यह सिर्फ current pagination page का total नहीं है।
        | सभी filtered items का total है।
        |
        */
        $stats = DB::query()
            ->fromSub((clone $query)->toBase(), 'inventory_rows')
            ->selectRaw('COUNT(*) AS total_items')
            ->selectRaw(
                'COALESCE(SUM(COALESCE(closing_stock, 0)), 0) AS total_stock'
            )
            ->selectRaw(
                'COALESCE(
                    SUM(
                        COALESCE(closing_stock, 0)
                        * COALESCE(cost_price, price, 0)
                    ),
                    0
                ) AS total_value'
            )
            ->first();

        $items = $query
            ->orderBy('items.name')
            ->paginate(25)
            ->withQueryString();

        return view('inventory.summary', [
            'items' => $items,
            'stats' => $stats,
            'from'  => $from,
            'to'    => $to,
        ]);
    }

    /**
     * Logged-in user का current business निकालता है।
     */
    private function getCurrentBusinessId(Request $request): int
    {
        $user = $request->user();

        abort_unless($user, 401, 'Please login first.');

        $businessId =
            session('business_id')
            ?? data_get($user, 'current_business_id')
            ?? data_get($user, 'business_id');

        /*
         * अगर user के पास businesses relationship है,
         * तो first business fallback के रूप में लिया जाएगा।
         */
        if (!$businessId && method_exists($user, 'businesses')) {
            $businessId = $user->businesses()->value('businesses.id');
        }

        abort_if(
            !$businessId,
            422,
            'Active business is not selected. Please select a business first.'
        );

        return (int) $businessId;
    }
}