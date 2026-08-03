<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HospitalWard extends Model
{
     protected $guarded = ['id'];

    protected $casts = [
        'daily_charge' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function rooms()
    {
        return $this->hasMany(
            HospitalRoom::class,
            'ward_id'
        );
    }

    public function beds()
    {
        return $this->hasManyThrough(
            HospitalBed::class,
            HospitalRoom::class,
            'ward_id', // hospital_rooms.ward_id
            'room_id', // hospital_beds.room_id
            'id',      // hospital_wards.id
            'id'       // hospital_rooms.id
        );
    }

    public function visits()
    {
        return $this->hasMany(
            PatientVisit::class,
            'ward_id'
        );
    }
}
