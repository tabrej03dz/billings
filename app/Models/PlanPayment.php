<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanPayment extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'gateway_response' => 'array',
    ];

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    
}
