<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id',
        'product_variant_id',
        'product_id',
        'product_set_item_id',
        'selected_product_ids',
        'quantity',
        'product_price_id',
        'currency_id',
        'unit_price',
        'unit_price_after_discount',
        'total_price',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'unit_price_after_discount' => 'decimal:2',
        'total_price' => 'decimal:2',
        'selected_product_ids' => 'array',
    ];

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function productVariant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function productSetItem()
    {
        return $this->belongsTo(ProductSetItems::class, 'product_set_item_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
    public function productPrice()
    {
        return $this->belongsTo(ProductPrice::class);
    }
}
