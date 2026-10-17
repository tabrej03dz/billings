<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessCaAssignment extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
        'assigned_at' => 'datetime',
    ];

    public function business()
    {
        return $this->belongsTo(Business::class);
    }

    public function ca()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}