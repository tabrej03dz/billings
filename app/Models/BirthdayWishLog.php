<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BirthdayWishLog extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['wish_date' => 'date'];
    public function record()
    {
        return $this->belongsTo(BirthdayRecord::class, 'birthday_record_id');
    }

}
