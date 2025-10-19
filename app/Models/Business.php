<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'logo', 'email', 'mobile', 'gstin', 'address', 'signature', 'terms'
    ];

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
}
