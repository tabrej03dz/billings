<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    protected $fillable = [
        'business_id',
        'item_id',
        'qty_change',
        'gross_weight',
        'metal_weight',
        'stone_weight',
        'type',
        'reference_type',
        'reference_id',
        'note',
    ];


    protected $casts = [
        'qty_change'   => 'decimal:3',
        'gross_weight' => 'decimal:3',
        'metal_weight' => 'decimal:3',
        'stone_weight' => 'decimal:3',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function reference()
    {
        return $this->morphTo();
    }
}
