<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'address_id',
        'order_number',
        'subtotal',
        'discount_amount',
        'shipping_price',
        'total_price',
        'payment_method',
        'status',
        'tracking_number',
        'notes',
        'coupon_code',
    'currency_id',
    'tap_charge_id', // <<< ADD THIS FIELD
        'payment_status'
    ];


    protected $casts = [
        'subtotal'        => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'shipping_price'  => 'decimal:2',
        'total_price'     => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function address()
    {
        return $this->belongsTo(Address::class);
    }
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function scopeFilter($query, array $filters)
    {
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['date_from']) && isset($filters['date_to'])) {
            $query->whereBetween('created_at', [$filters['date_from'], $filters['date_to']]);
        }
    }
}
