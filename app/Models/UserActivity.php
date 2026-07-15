<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'business_id',
        'session_key',
        'route_name',
        'page_title',
        'url',
        'path',
        'method',
        'duration_seconds',
        'heartbeat_count',
        'started_at',
        'last_seen_at',
        'ended_at',
        'ip_address',
        'user_agent',
        'device_type',
        'browser',
        'platform',
    ];

    protected $casts = [
        'duration_seconds' => 'integer',
        'heartbeat_count' => 'integer',
        'started_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'ended_at' => 'datetime',
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