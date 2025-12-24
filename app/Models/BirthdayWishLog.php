<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BirthdayWishLog extends Model
{
    protected $guarded = ['id'];

    public function record()
    {
        return $this->belongsTo(BirthdayRecord::class, 'birthday_record_id');
    }
}
