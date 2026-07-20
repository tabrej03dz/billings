<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserActivity extends Model
{
    use HasFactory;

    protected $guarded = [
        'id',
    ];

    protected $casts = [
        'duration_seconds' => 'integer',
        'heartbeat_count' => 'integer',
        'started_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'ended_at' => 'datetime',
        'last_error_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function activities()
    {
        return $this->hasMany(UserActivity::class);
    }
}