<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'session_id',
        'coupon_code',
        'discount_amount',
        'total_price',
        'total_price_after_discount',
        'currency_id'
    ];
    protected $casts = [
        'total_price' => 'decimal:2',
        'total_price_after_discount' => 'decimal:2',
    ];


    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function currency()
    {
        return $this->belongsTo(\App\Models\Currency::class);
    }
}
