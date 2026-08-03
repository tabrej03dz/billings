<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HospitalDepartment extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Department ke doctors.
     *
     * Ye relation doctor_department pivot table use karta hai.
     */
    public function doctors(): BelongsToMany
    {
        return $this->belongsToMany(
            Doctor::class,
            'doctor_department',
            'department_id',
            'doctor_id'
        )->withTimestamps();
    }

    /**
     * Department ke patient visits.
     */
    public function visits(): HasMany
    {
        return $this->hasMany(
            PatientVisit::class,
            'department_id'
        );
    }
}