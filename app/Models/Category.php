<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\Concerns\BelongsToBusiness;

class Category extends Model
{
    use HasFactory, BelongsToBusiness;
    protected $guarded = ['id'];

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

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Direct Sub Categories
     */
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')
            ->orderBy('name');
    }

    /**
     * Nested Sub Categories
     */
    public function childrenRecursive()
    {
        return $this->children()->with('childrenRecursive');
    }

    /**
     * Check Main Category
     */
    public function isParent(): bool
    {
        return is_null($this->parent_id);
    }

    /**
     * Check Sub Category
     */
    public function isSubCategory(): bool
    {
        return ! is_null($this->parent_id);
    }
}
