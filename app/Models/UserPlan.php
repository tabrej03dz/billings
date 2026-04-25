<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserPlan extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'start_date' => 'date',
        'expiry_date' => 'date',
        'status' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}
