<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceAdditionalCharge extends Model
{
    protected $guarded = ['id'];

    public function invoice(){
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }
}
