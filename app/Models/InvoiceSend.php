<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceSend extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'meta'    => 'array',
        'sent_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }




}
