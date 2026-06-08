<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use App\Services\StockService;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use BelongsToBusiness;
    protected $guarded = ['id'];

    protected $casts = [
        'invoice_date' => 'date',
    ];

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Client::class, 'supplier_id');
    }


    protected static function booted()
    {
        static::created(function (Purchase $purchase) {
            $purchase->loadMissing('items.item');
            app(StockService::class)->recordPurchase($purchase);
        });

        static::updated(function (Purchase $purchase) {
            $purchase->loadMissing('items.item');
            $service = app(StockService::class);

            // purane movements hatao + naya record banao
            $service->rollbackReference($purchase);
            $service->recordPurchase($purchase);
        });

        static::deleted(function (Purchase $purchase) {
            $purchase->loadMissing('items.item');
            app(StockService::class)->rollbackReference($purchase);
        });
    }
}
