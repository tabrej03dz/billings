<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegisterOtp extends Model
{
    protected $guarded = [
        'id',
    ];

    protected $casts = [
        'payload' => 'array',
        'expires_at' => 'datetime',
    ];
}
