<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnniversaryWishLog extends Model
{
    protected $guarded = ['id'];

    public function anniversary()
{
    return $this->belongsTo(Anniversary::class, 'anniversary_id');
}
}
