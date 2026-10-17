<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessTypeItemField extends Model
{
    protected $guarded = [
        'id',
    ];

    public function businessType()
    {
        return $this->belongsTo(BusinessType::class);
    }
}
