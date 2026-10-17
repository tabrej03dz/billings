<?php

namespace App\Models;

use App\Models\Concerns\BelongsToBusiness;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
//    use HasFactory;


    use BelongsToBusiness;

    protected $guarded = ['id'];

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function patientProfile()
{
    return $this->hasOne(PatientProfile::class);
}

public function patientVisits()
{
    return $this->hasMany(
        PatientVisit::class,
        'client_id'
    );
}
}
