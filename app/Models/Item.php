<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Concerns\BelongsToBusiness;
use App\Services\ItemBarcodeService;

class Item extends Model
{
    use BelongsToBusiness;

    protected $guarded = ['id'];

    protected $casts = [
        'price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'is_active' => 'boolean',
        'stock_qty' => 'decimal:3',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    // Accessor: $item->current_stock
    public function getCurrentStockAttribute()
    {
        // agar stock_movements use karna hai live
        return $this->stockMovements()->sum('qty_change');
    }

    // Cache column sync (items.stock_qty)
//    public function refreshStockQty(): void
//    {
//        $this->stock_qty = $this->stockMovements()->sum('qty_change');
//        $this->saveQuietly();
//    }


    // public function refreshStockQty(): void
    // {
    //     $total = \App\Models\StockMovement::where('item_id', $this->id)
    //         ->sum('qty_change');    // purchase +, sale -, adjustment ±

    //     $this->stock_qty = (int)$total;
    //     $this->save();
    // }


    public function refreshStockQty(): void
    {
        $stock = \App\Models\StockMovement::query()
            ->where('business_id', $this->business_id)
            ->where('item_id', $this->id)
            ->sum('qty_change');

        $this->forceFill([
            'stock_qty' => number_format(
                round((float) $stock, 3),
                3,
                '.',
                ''
            ),
        ])->saveQuietly();

        $this->refresh();
    }

    /**
     * Qty +ve (purchase) ya -ve (sale) adjust karega.
     */
    public function adjustStock(int $qtyChange): void
    {
        $this->stock_qty = ($this->stock_qty ?? 0) + $qtyChange;
        $this->saveQuietly(); // silent save, events fire nahi honge
    }

    /**
     * Agar kabhi full recalc karna ho ledger se.
     */
    public function refreshStockFromLedger(): void
    {
        $this->stock_qty = $this->stockMovements()->sum('qty_change');
        $this->saveQuietly();
    }

    protected static function booted(): void
    {
        static::created(function (Item $item): void {
            app(ItemBarcodeService::class)->generate($item);
        });
    }

}
