<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessBillTemplateSetting extends Model
{
    protected $guarded = [
        'id',
    ];

    protected $casts = [
        'show_logo' => 'boolean',
        'show_tagline' => 'boolean',
        'show_signature' => 'boolean',
        'show_terms' => 'boolean',
    ];

    public function template()
    {
        return $this->belongsTo(BillTemplate::class, 'bill_template_id');
    }

    public function business()
    {
        return $this->belongsTo(Business::class);
    }
}
