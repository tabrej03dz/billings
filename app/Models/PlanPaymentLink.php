<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanPaymentLink extends Model
{
    protected $guarded = [];

    protected $casts = [

        'base_amount' =>
            'decimal:2',

        'gst_rate' =>
            'decimal:2',

        'gst_amount' =>
            'decimal:2',

        'total_amount' =>
            'decimal:2',

        'paid_at' =>
            'datetime',

        'expired_at' =>
            'datetime',

        'cancelled_at' =>
            'datetime',

        'gateway_response' =>
            'array',
    ];


    public function plan()
    {
        return $this->belongsTo(
            Plan::class
        );
    }


    public function user()
    {
        return $this->belongsTo(
            User::class
        );
    }


    public function generatedBy()
    {
        return $this->belongsTo(
            User::class,
            'generated_by'
        );
    }
}