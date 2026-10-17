<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EwayBill extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'eway_bill_date' => 'datetime',
        'valid_upto' => 'datetime',
        'transport_doc_date' => 'date',
        'request_payload' => 'array',
        'api_response' => 'array',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}