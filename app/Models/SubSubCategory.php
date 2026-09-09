<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubSubCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'ucode1',
        'ucode2',
        'category_id',
        'sub_category_id',
        'name_en',
        'name_ar',
        'is_active',
        'order',
        'slug',
        'image',
        'notes',
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
            $this->attributes['image'] = $value->store('sub_sub_categories', 'public');
        }
    }

    public function getSlugAttribute($value)
    {
        return $value;
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
