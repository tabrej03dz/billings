<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;

class InventoryController
{
    public function summary(Request $request)
    {
        // Date inputs (optional)
        $from = $request->input('from') ? Carbon::parse($request->input('from')) : null;
        $to   = $request->input('to')   ? Carbon::parse($request->input('to'))   : null;

        $items = Item::query()
            // 👉 business_id filter KO ABHI HATA DO, pehle check karein items aa rahe hain ya nahi
            // ->where('business_id', $businessId)

            ->withSum(['stockMovements as stock_qty' => function ($m) use ($from, $to) {
                if ($from && $to) {
                    $m->whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()]);
                }
            }], 'qty_change')

            ->withSum(['stockMovements as total_gross_weight' => function ($m) use ($from, $to) {
                if ($from && $to) {
                    $m->whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()]);
                }
            }], 'gross_weight')

            ->withSum(['stockMovements as total_metal_weight' => function ($m) use ($from, $to) {
                if ($from && $to) {
                    $m->whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()]);
                }
            }], 'metal_weight')

            ->withSum(['stockMovements as total_stone_weight' => function ($m) use ($from, $to) {
                if ($from && $to) {
                    $m->whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()]);
                }
            }], 'stone_weight')

            ->orderBy('name')
            ->get();

        return view('inventory.summary', compact('items', 'from', 'to'));
    }
}
