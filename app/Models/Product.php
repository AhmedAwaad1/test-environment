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
        'sub_category_id',
        'category_id',
        'quantity',
        'is_active',
        'has_variants',
        'sku',
    ];

    protected $casts = [
        'has_variants' => 'boolean',
        'is_active'    => 'boolean',
    ];

    public function prices()
    {
        return $this->hasMany(ProductPrice::class);
    }

    public function productVariants()
    {
        return $this->hasMany(ProductVariant::class);
    }
    public function productOptions()
    {
        return $this->hasMany(ProductOption::class)->orderBy('order', 'asc');
    }

    public function subCategory()
    {
        return $this->belongsTo(SubCategory::class);
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

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function productPrices()
    {
        return $this->hasMany(ProductPrice::class);
    }

    public function scopeFilter($query, $filters)
    {
        // Search in name and description
        if ($filters['search'] ?? false) {
            $query->where(function($q) use ($filters) {
                $q->where('name_en', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('name_ar', 'like', '%' . $filters['search'] . '%');
            });
        }

        // Category and subcategory filters
        if ($filters['category_id'] ?? false) {
            $query->where('category_id', $filters['category_id']);
        }
        if ($filters['sub_category_id'] ?? false) {
            $query->where('sub_category_id', $filters['sub_category_id']);
        }

        // Active status filter
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        // Has variants filter
        if (isset($filters['has_variants'])) {
            $query->where('has_variants', $filters['has_variants']);
        }

        // Stock status filter
        if (isset($filters['in_stock'])) {
            if ($filters['in_stock']) {
                $query->where(function($q) {
                    $q->where('quantity', '>', 0)
                      ->orWhereHas('productVariants', function($q) {
                          $q->where('quantity', '>', 0);
                      });
                });
            } else {
                $query->where(function($q) {
                    $q->where('quantity', '<=', 0)
                      ->orWhereDoesntHave('productVariants', function($q) {
                          $q->where('quantity', '>', 0);
                      });
                });
            }
        }

        // Sort options
        if ($filters['sort_by'] ?? false) {
            $direction = $filters['sort_direction'] ?? 'asc';
            switch ($filters['sort_by']) {
                case 'name':
                    $query->orderBy('name_en', $direction);
                    break;
                case 'created_at':
                    $query->orderBy('created_at', $direction);
                    break;
                default:
                    $query->latest();
            }
        } else {
            $query->latest();
        }

        return $query;
    }
}
