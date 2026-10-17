<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MetalRate extends Model
{
    use BelongsToBusiness;

    /**
     * Mass assignment में केवल id protected रहेगा।
     */
    protected $guarded = [
        'id',
    ];

    /**
     * Attribute casting.
     */
    protected $casts = [
        'business_id'   => 'integer',
        'rate_date'     => 'date:Y-m-d',
        'rate_per_gram' => 'decimal:2',
        'is_active'     => 'boolean',
    ];


    /**
     * Metal rate का business.
     */
    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}