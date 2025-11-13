<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Model;

class MetalRate extends Model
{
    use BelongsToBusiness;

    protected $guarded = ['id'];

    protected $casts = [
        'rate_date' => 'date',
    ];
}
