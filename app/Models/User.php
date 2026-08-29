<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;
    use HasRoles;
    use HasApiTokens, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'phone_verified_at' => 'datetime',
        ];
    }


    public function onboardingRegistration()
    {
        return $this->hasOne(OnboardingRegistration::class);
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    public function business(){
        return $this->belongsTo(Business::class);
    }

    public function businesses()
    {
        return $this->belongsToMany(\App\Models\Business::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Businesses that assigned this user as their CA.
     */
    public function caBusinesses()
    {
        return $this->belongsToMany(Business::class, 'business_ca_assignments', 'user_id', 'business_id')
            ->withPivot(['is_active', 'assigned_by', 'assigned_at'])
            ->withTimestamps();
    }

    public function caAssignments()
    {
        return $this->hasMany(BusinessCaAssignment::class, 'user_id');
    }

    public function api(){
        return $this->hasOne(ApiKey::class, 'user_id');
    }

    public function userPlans()
    {
        return $this->hasMany(UserPlan::class);
    }

    public function activePlan()
    {
        return $this->hasOne(UserPlan::class)
            ->where('status', 1)
            ->whereDate('start_date', '<=', now())
            ->whereDate('expiry_date', '>=', now())
            ->latestOfMany();
    }

    public function activities()
    {
        return $this->hasMany(UserActivity::class);
    }
}