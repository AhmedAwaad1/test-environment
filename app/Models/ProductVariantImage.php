<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariantImage extends Model
{
    use HasFactory;
    protected $fillable = [
        'product_variant_id',
        'image',
    ];

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }
    public function setImageAttribute($value)
    {
        if (is_file($value)) {
            $this->attributes['image'] = $value->store('product_variant_images', 'public');
        } else {
            $this->attributes['image'] = $value;
        }
    }
    public function getImageAttribute($value)
    {
        return $value ? asset('storage/' . $value) : null;
    }
}
