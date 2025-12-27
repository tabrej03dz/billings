<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    use BelongsToBusiness;
    protected $guarded = ['id'];

    public function business(){
        return $this->belongsTo(Business::class, 'business_id');
    }

    public function displayTitle(): string
    {
        $title = $this->label ?: ($this->bank_name ?: ($this->account_holder ?: 'Bank'));
        $line  = $this->upi_id ?: $this->account_no;
        return trim($title . ($line ? " - {$line}" : ''));
    }
}
