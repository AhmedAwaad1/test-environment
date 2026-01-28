<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'image',
        'image_webp',
        'image_medium',
        'image_small',
        'is_main',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function setImageAttribute($value)
    {
        if (is_file($value)) {
            $this->attributes['image'] = $value->store('products', 'public');
        } else {
            $this->attributes['image'] = $value;
        }
    }

    public function getImageAttribute($value)
    {
        return $value ? asset('storage/' . $value) : null;
    }

    public function getImageWebpAttribute($value)
    {
        return $value ? asset('storage/' . $value) : $this->image;
    }

    public function getImageMediumAttribute($value)
    {
        return $value ? asset('storage/' . $value) : $this->image;
    }

    public function getImageSmallAttribute($value)
    {
        return $value ? asset('storage/' . $value) : $this->image;
    }
}
