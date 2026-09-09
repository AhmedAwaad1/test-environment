<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name_en',
        'name_ar',
        'slug',
        'image',
        'order',
        'is_active',
        'notes',
    ];

    // public function products()
    // {
    //     return $this->hasMany(Product::class);
    // }
    public function subCategories()
    {
        return $this->hasMany(SubCategory::class)->orderBy('order', 'asc');
    }

    public function subSubCategories()
    {
        return $this->hasMany(SubSubCategory::class)->orderBy('order', 'asc');
    }
    public function getImageAttribute($value)
    {
        if ($value) {
            return asset('storage/' . $value);
        }
        return null;
    }
    public function setImageAttribute($value)
    {
        if (is_string($value)) {
            $this->attributes['image'] = $value;
        } else {
            $this->attributes['image'] = $value->store('categories', 'public');
        }
    }
    public function getSlugAttribute($value)
    {
        return $value;
    }

    public function scopeFilter($query, array $filters)
    {
        if (isset($filters['is_active'])) {
            $query->where('is_active', (int) $filters['is_active']);
        }
    }
}
