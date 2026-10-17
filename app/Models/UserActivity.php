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
        /*
        |--------------------------------------------------------------------------
        | Numeric fields
        |--------------------------------------------------------------------------
        */

        'duration_seconds' => 'integer',
        'heartbeat_count' => 'integer',
        'error_count' => 'integer',

        /*
        |--------------------------------------------------------------------------
        | Error details
        |--------------------------------------------------------------------------
        |
        | Database ke JSON data ko automatically PHP array mein convert karega.
        |
        */

        'errors' => 'array',

        /*
        |--------------------------------------------------------------------------
        | Date fields
        |--------------------------------------------------------------------------
        */

        'started_at' => 'datetime',
        'last_seen_at' => 'datetime',
        'ended_at' => 'datetime',
        'last_error_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | User Relationship
    |--------------------------------------------------------------------------
    */

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Business Relationship
    |--------------------------------------------------------------------------
    */

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }
}