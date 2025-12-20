<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
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
