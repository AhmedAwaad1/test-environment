<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'address',
        'phone',
        'user_id',
        'country_id',
        'city_id',
        'district_id',
        'is_default',
        'session_id',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeFilter($query, $request)
    {
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->has('is_default')) {
            $query->where('is_default', $request->is_default);
        }
        return $query;
    }

    public function getShippingPrice(): ?float
    {
        if ($this->district && $this->district->shipping_price > 0) {
            return (float)$this->district->shipping_price;
        }

        if ($this->city && $this->city->shipping_price > 0) {
            return (float)$this->city->shipping_price;
        }

        if ($this->city && $this->city->country && $this->city->country->shipping_price > 0) {
            return (float)$this->city->country->shipping_price;
        }

        return 0;
    }


}
