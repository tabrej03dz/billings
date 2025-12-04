<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Model;

class ApiKey extends Model
{
    use BelongsToBusiness;

    protected $guarded = ['id'];

    public function business()
    {
        return $this->belongsTo(\App\Models\Business::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

}
