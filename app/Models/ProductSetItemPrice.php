<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductSetItemPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_set_item_id',
        'currency_id',
        'price',
        'price_after_discount',
    ];

    protected $casts = [
        'price'                => 'decimal:2',
        'price_after_discount' => 'decimal:2',
    ];

    public function productSetItem()
    {
        return $this->belongsTo(ProductSetItems::class, 'product_set_item_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}

