<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HospitalRoom extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'daily_charge' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function ward(): BelongsTo
    {
        return $this->belongsTo(
            HospitalWard::class,
            'ward_id'
        );
    }

    public function beds(): HasMany
    {
        return $this->hasMany(
            HospitalBed::class,
            'room_id'
        );
    }

    public function visits(): HasMany
    {
        return $this->hasMany(
            PatientVisit::class,
            'room_id'
        );
    }
}