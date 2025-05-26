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

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($optionValue) {
            if (!$optionValue->standard_value) {
                $optionValue->standard_value = self::generateStandardValue($optionValue->value, $optionValue->productOption?->name);
            }
        });
    }

    private static function generateStandardValue($value, $optionType)
    {
        $value = strtolower(trim($value));

        // Color mapping
        $colorMap = [
            // Basic Colors
            'red' => '#FF0000',
            'blue' => '#0000FF',
            'green' => '#00FF00',
            'black' => '#000000',
            'white' => '#FFFFFF',
            'yellow' => '#FFFF00',
            'purple' => '#800080',
            'orange' => '#FFA500',

            // Metallic Colors
            'silver' => '#C0C0C0',
            'gold' => '#FFD700',
            'bronze' => '#CD7F32',

            // Light Colors
            'pink' => '#FFC0CB',
            'light blue' => '#ADD8E6',
            'light green' => '#90EE90',
            'lavender' => '#E6E6FA',
            'peach' => '#FFDAB9',

            // Dark Colors
            'navy' => '#000080',
            'dark green' => '#006400',
            'burgundy' => '#800020',
            'brown' => '#A52A2A',

            // Gray Shades
            'gray' => '#808080',
            'dark gray' => '#A9A9A9',
            'light gray' => '#D3D3D3',

            // Fashion Colors
            'beige' => '#F5F5DC',
            'khaki' => '#F0E68C',
            'olive' => '#808000',
            'teal' => '#008080',
            'coral' => '#FF7F50',
            'turquoise' => '#40E0D0',
            'indigo' => '#4B0082',
            'maroon' => '#800000',
            'mint' => '#98FF98',
            'salmon' => '#FA8072',
        ];

        // Size mapping
        $sizeMap = [
            'small' => 'S',
            'medium' => 'M',
            'large' => 'L',
            'extra large' => 'XL',
            'extra small' => 'XS',
            // Add more sizes as needed
        ];

        $optionType = strtolower($optionType ?? '');

        if ($optionType === 'color') {
            return $colorMap[$value] ?? null;
        } elseif ($optionType === 'size') {
            return $sizeMap[$value] ?? strtoupper($value);
        }

        // For other types, just return standardized version of the value
        return $value;
    }

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
}
