<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Model;

class InvoiceSequence extends Model
{
    use BelongsToBusiness;
    protected $fillable = ['business_id','fiscal_year','prefix','next_number','pad'];
}
