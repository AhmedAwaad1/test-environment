<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'name_en',
        'name_ar',
        'description_en',
        'description_ar',
        'price',
        'product_type_id',
        'category_id',
    ];
    
    public function productType()
    {
        return $this->belongsTo(ProductType::class);
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function colors()
    {
        return $this->belongsToMany(Color::class, 'product_color');
    }
    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }
    public function scopeFilter($query, $filters)
    {
        if ($filters['search'] ?? false) {
            $query->where('name_en', 'like', '%' . $filters['search'] . '%')
                ->orWhere('name_ar', 'like', '%' . $filters['search'] . '%');
        }
        if ($filters['category_id'] ?? false) {
            $query->where('category_id', $filters['category_id']);
        }
        if ($filters['product_type_id'] ?? false) {
            $query->where('product_type_id', $filters['product_type_id']);
        }
        if ($filters['min_price'] ?? false) {
            $query->where('price', '>=', $filters['min_price']);
        }
        if ($filters['max_price'] ?? false) {
            $query->where('price', '<=', $filters['max_price']);
        }
        // if ($filters['sort_by'] ?? false) {
        //     $query->orderBy($filters['sort_by'], $filters['sort_direction'] ?? 'asc');
        // } else {
        //     $query->latest();
        // }
        return $query;
    }
}
