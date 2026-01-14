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

    public function productPrices()
    {
        return $this->hasMany(ProductPrice::class, 'product_id', 'product_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function optionValues()
    {
        return $this->belongsToMany(ProductOptionValue::class, 'variant_option_values');
    }
    public function variantOptionValues()
    {
        return $this->hasMany(VariantOptionValue::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class, 'product_id', 'product_id');
    }

    public function getIsActiveAttribute($value)
    {
        return $value == 1;
    }

    public function getTitle()
    {
        return $this->optionValues()
            ->with('productOption')
            ->get()
            ->sortBy(fn($val) => $val->productOption->order ?? 0)
            ->pluck('value')
            ->implode(' / ');
    }

    public function getVariantAttributes()
    {
        return $this->optionValues()
            ->with('productOption.optionType')
            ->get()
            ->mapWithKeys(function ($item) {
                $name = $item->productOption->optionType->name ?? 'Option';
                return [$name => $item->value];
            })
            ->toArray();
    }

    public function scopeFilter($query, $filters)
    {
        if (isset($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }
        return $query;
    }

}
