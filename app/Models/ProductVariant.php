<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory;
    protected $fillable = [
        'product_id',
        'color_id',
        'size_id',
        'sku',
        'price',
        'price_after_discount',
        'quantity',
        'is_active',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    public function color()
    {
        return $this->belongsTo(Color::class);
    }
    public function size()
    {
        return $this->belongsTo(Size::class);
    }
    public function getIsActiveAttribute($value)
    {
        return $value == 1;
    }
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
