<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VariantOptionValue extends Model
{
    use HasFactory;
    protected $fillable = [
        'product_variant_id',
        'product_option_value_id',
    ];
    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }
    public function optionValue()
    {
        return $this->belongsTo(ProductOptionValue::class, 'product_option_value_id');
    }
    public function option()
    {
        return $this->hasOneThrough(
            ProductOption::class,
            ProductOptionValue::class,
            'id', // Foreign key on ProductOptionValue table
            'id', // Foreign key on ProductOption table
            'product_option_value_id', // Local key on VariantOptionValue table
            'product_option_id' // Local key on ProductOptionValue table
        );
    }
}
