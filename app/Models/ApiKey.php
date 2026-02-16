<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Model;

class ApiKey extends Model
{
    use BelongsToBusiness;

    protected $guarded = ['id'];

    protected $casts = [
        'birthday_wish_video_url_updated_on' => 'date',
    ];

    public function business()
    {
        return $this->belongsTo(\App\Models\Business::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

}
