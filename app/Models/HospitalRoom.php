<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HospitalRoom extends Model
{
     protected $guarded = ['id'];

    protected $casts = [
        'daily_charge' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function ward()
    {
        return $this->belongsTo(
            HospitalWard::class,
            'ward_id'
        );
    }

    public function beds()
    {
        return $this->hasMany(
            HospitalBed::class,
            'room_id'
        );
    }

    public function visits()
    {
        return $this->hasMany(
            PatientVisit::class,
            'room_id'
        );
    }
}
