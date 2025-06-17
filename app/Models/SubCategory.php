<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubCategory extends Model
{
    use HasFactory;
    protected $fillable = [
        'category_id',
        'name_en',
        'name_ar',
        'is_active',
        'order',
        'slug',
        'image',
    ];

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
            $this->attributes['image'] = $value->store('sub_categories', 'public');
        }
    }
    public function getSlugAttribute($value)
    {
        return $value;
    }
    public function scopeFilter($query, array $filters)
    {
        $query->when(
            $filters['is_active'] ?? false,
            function ($query, $is_active) {
                $query->where('is_active', (int) $is_active);
            }
        );

        $query->when(
            $filters['category_id'] ?? false,
            function ($query, $category_id) {
                $query->where('category_id', $category_id);
            }
        );
    }
}
