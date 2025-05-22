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
            $this->attributes['image'] = $value->store('product_types', 'public');
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
