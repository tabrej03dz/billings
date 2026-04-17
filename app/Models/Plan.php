<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission;

class Plan extends Model
{
    protected $guarded = [
        'id',
    ];

    public function permissions()
    {
        return $this->belongsToMany(
            Permission::class,
            'plan_permissions',
            'plan_id',
            'permission_id'
        )->withTimestamps();
    }
}
