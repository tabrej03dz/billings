<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory, BelongsToBusiness;

    protected $guarded = ['id'];

    protected $casts = [
        'business_id' => 'integer',
    ];

    /**
     * Unit belongs to a business.
     */
    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    /**
     * One unit can be assigned to many items.
     *
     * items table में unit_id column होने पर यह relationship काम करेगा।
     */
    public function items()
    {
        return $this->hasMany(Item::class);
    }
}