<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessType extends Model
{
    protected $guarded = [
        'id',
    ];

    public function itemFields()
    {
        return $this->hasMany(BusinessTypeItemField::class);
    }
}
