<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OnboardingRegistration extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'business_data' => 'array',
            'billing_data' => 'array',
            'phone_verified_at' => 'datetime',
            'completed_at' => 'datetime',
            'last_completed_step' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}