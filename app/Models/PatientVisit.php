<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientVisit extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'visit_at' => 'datetime',
        'admitted_at' => 'datetime',
        'discharged_at' => 'datetime',
    ];

    public function patient()
    {
        return $this->belongsTo(
            Client::class,
            'client_id'
        );
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function department()
    {
        return $this->belongsTo(
            HospitalDepartment::class,
            'department_id'
        );
    }

    public function ward()
    {
        return $this->belongsTo(
            HospitalWard::class,
            'ward_id'
        );
    }

    public function room()
    {
        return $this->belongsTo(
            HospitalRoom::class,
            'room_id'
        );
    }

    public function bed()
    {
        return $this->belongsTo(
            HospitalBed::class,
            'bed_id'
        );
    }
}
