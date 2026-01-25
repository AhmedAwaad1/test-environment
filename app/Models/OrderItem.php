<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_variant_id',
        'product_set_item_id',
        'product_name',
        'product_price_id',
        'unit_price',
        'unit_price_after_discount',
        'quantity',
        'total_price',
    ];

    protected $casts = [
        'unit_price'                => 'decimal:2',
        'unit_price_after_discount' => 'decimal:2',
        'total_price'               => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function productSetItem()
    {
        return $this->belongsTo(ProductSetItems::class, 'product_set_item_id');
    }

    public function productPrice()
    {
        return $this->belongsTo(\App\Models\ProductPrice::class);
    }
}
