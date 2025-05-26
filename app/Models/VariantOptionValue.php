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
        return $this->belongsTo(ProductOptionValue::class, 'option_value_id');
    }
    public function option()
    {
        return $this->belongsTo(ProductOption::class, 'option_id');
    }
}
