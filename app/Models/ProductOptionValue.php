<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductOptionValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_option_id',
        'value',
        'standard_value',
        'hex_code',
        'order',
    ];

    protected $casts = [
        'order' => 'integer',
    ];

    public function productOption(): BelongsTo
    {
        return $this->belongsTo(ProductOption::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_option_value_product', 'product_option_value_id', 'product_id');
    }

    public function productVariants()
    {
        return $this->belongsToMany(ProductVariant::class, 'variant_option_values');
    }

    public function variantOptionValues(): HasMany
    {
        return $this->hasMany(VariantOptionValue::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductOptionValueImage::class);
    }

    public function variants()
    {
        return $this->belongsToMany(ProductVariant::class, 'variant_option_values');
    }
}
