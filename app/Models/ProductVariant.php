<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory;
    protected $fillable = [
        'product_id',
        'sku',
        'price',
        'price_after_discount',
        'quantity',
        'barcode',
        'weight',
        'order',
        'is_active',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function images()
    {
        return $this->hasMany(ProductVariantImage::class);
    }

    public function optionValues()
    {
        return $this->belongsToMany(ProductOptionValue::class, 'variant_option_values');
    }
    public function variantOptionValues()
    {
        return $this->hasMany(VariantOptionValue::class);
    }

    public function getIsActiveAttribute($value)
    {
        return $value == 1;
    }

    // public function getTitle()
    // {
    //     return $this->optionValues()
    //         ->orderBy('option_id')
    //         ->pluck('value')
    //         ->implode(' / ');
    // }

    // public function getAttributes()
    // {
    //     return $this->optionValues()
    //         ->with('option')
    //         ->get()
    //         ->mapWithKeys(function ($item) {
    //             return [$item->option->name => $item->value];
    //         })
    //         ->toArray();
    // }

    public function scopeFilter($query, $filters)
    {
        if (isset($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }
        if (isset($filters['color_id'])) {
            $query->where('color_id', $filters['color_id']);
        }
        if (isset($filters['size_id'])) {
            $query->where('size_id', $filters['size_id']);
        }
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }
        return $query;
    }

}
