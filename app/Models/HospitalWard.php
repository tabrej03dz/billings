<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class HospitalWard extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'daily_charge' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function rooms(): HasMany
    {
        return $this->hasMany(
            HospitalRoom::class,
            'ward_id'
        );
    }

    public function beds(): HasManyThrough
    {
        return $this->hasManyThrough(
            HospitalBed::class,
            HospitalRoom::class,
            'ward_id',
            'room_id',
            'id',
            'id'
        );
    }

    public function visits(): HasMany
    {
        return $this->hasMany(
            PatientVisit::class,
            'ward_id'
        );
    }
}