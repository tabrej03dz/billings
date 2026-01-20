<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstallmentReminder extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'reminder_date' => 'date',
        'installment_date' => 'date',
    ];

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }
}
