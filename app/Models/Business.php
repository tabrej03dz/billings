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
        return $this->belongsTo(\App\Models\BusinessType::class, 'type');
    }


    public function isHospitalBusiness(): bool
    {
        return in_array(
            $this->businessType?->slug,
            [
                'hospital',
                'clinic',
                'diagnostic_center',
                'pathology_lab',
            ],
            true
        );
    }











    public function getProfileRequiredFields(): array
{
    return [
        'name',
        'email',
        'mobile',
        'type',
        'address',
        'state',
        'logo',
        'pdf_template_id',
    ];
}

public function calculateProfileCompletion(): int
{
    $fields = $this->getProfileRequiredFields();

    $completedFields = collect($fields)
        ->filter(function ($field) {
            return filled($this->{$field});
        })
        ->count();

    if (count($fields) === 0) {
        return 0;
    }

    return (int) round(
        ($completedFields / count($fields)) * 100
    );
}

public function refreshProfileCompletion(): int
{
    $percentage = $this->calculateProfileCompletion();
    $isCompleted = $percentage >= 100;

    $updateData = [
        'profile_completion' => $percentage,
        'profile_setup_completed' => $isCompleted,
    ];

    if ($isCompleted && !$this->profile_setup_completed_at) {
        $updateData['profile_setup_completed_at'] = now();
    }

    if (!$isCompleted) {
        $updateData['profile_setup_completed_at'] = null;
    }

    $this->forceFill($updateData)->saveQuietly();

    return $percentage;
}

public function isProfileIncomplete(): bool
{
    return !$this->profile_setup_completed
        || $this->profile_completion < 100;
}

public function missingProfileFields(): array
{
    $labels = [
        'name' => 'Business name',
        'email' => 'Email address',
        'mobile' => 'Mobile number',
        'type' => 'Business type',
        'address' => 'Business address',
        'state' => 'State',
        'logo' => 'Business logo',
        'pdf_template_id' => 'Invoice template',
    ];

    return collect($this->getProfileRequiredFields())
        ->filter(fn ($field) => blank($this->{$field}))
        ->map(fn ($field) => $labels[$field] ?? ucfirst($field))
        ->values()
        ->all();
}
}
