<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Business extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    public function owners()
    {
        return $this->belongsToMany(User::class)
            ->wherePivot('role', 'owner');
    }
    public function apiKey(){
        return $this->hasOne(ApiKey::class, 'business_id');
    }

    public function billTemplate(){
        return $this->belongsTo(BillTemplate::class, 'pdf_template_id');
    }

    public function businessType()
    {
        return $this->belongsTo(\App\Models\BusinessType::class);
    }
}
