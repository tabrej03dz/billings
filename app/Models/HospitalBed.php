<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HospitalBed extends Model
{
        protected $guarded = ['id'];

    protected $casts = [
        'daily_charge' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function room()
    {
        return $this->belongsTo(
            HospitalRoom::class,
            'room_id'
        );
    }

    public function visits()
    {
        return $this->hasMany(
            PatientVisit::class,
            'bed_id'
        );
    }
}
