<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory, BelongsToBusiness;

    protected $guarded = ['id'];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function business(){
        return $this->belongsTo(Business::class, 'business_id');
    }

//    public function additonalCharges(){
//        return $this->hasMany(Additional)
//    }
}
