<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\Concerns\BelongsToBusiness;

class Category extends Model
{
    use HasFactory, BelongsToBusiness;
    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
    ];

    // Automatically generate slug from name
    protected static function booted()
    {
        static::creating(function ($category) {
            $category->slug = Str::slug($category->name);
        });
    }

    // Relationship: One category has many items
    public function items()
    {
        return $this->hasMany(Item::class);
    }
}
