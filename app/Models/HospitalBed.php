<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HospitalBed extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'daily_charge' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function room(): BelongsTo
    {
        return $this->belongsTo(
            HospitalRoom::class,
            'room_id'
        );
    }

    public function visits(): HasMany
    {
        return $this->hasMany(
            PatientVisit::class,
            'bed_id'
        );
    }
}