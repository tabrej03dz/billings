<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
//    use HasFactory;


    use BelongsToBusiness;

    protected $fillable = ['business_id','name','address','gstin','pan','mobile','state'];

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
