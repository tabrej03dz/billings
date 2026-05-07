<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DemoRequest extends Model
{
    protected $guarded = ['id'];

    public function updatedBy(){
        return $this->belongsTo(User::class, 'updated_by');
    }
}
