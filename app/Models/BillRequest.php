<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillRequest extends Model
{
    protected $table = 'bill_requests';

    protected $guarded = ['id'];

    protected $casts = [
        'package_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'payment_amount' => 'decimal:2',
        'payment_date' => 'date',
        'requested_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function packageItem()
    {
        return $this->belongsTo(Item::class, 'package_name', 'id');
    }

    public function invoice()
    {
        return $this->hasOne(
            Invoice::class,
            'bil_request_id',
            'id'
        );
    }
}
