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

    public function getShippingPrice(): float
    {
        // 1. District Priority
        if ($this->district_id && $this->district && (float)$this->district->shipping_price > 0) {
            return (float)$this->district->shipping_price;
        }

        // 2. City Priority (Fallback)
        if ($this->city_id && $this->city && (float)$this->city->shipping_price > 0) {
            return (float)$this->city->shipping_price;
        }

        // 3. Country Priority (Final Fallback for Egypt)
        $country = $this->country ?? ($this->city ? $this->city->country : null);
        
        // If still no country, fallback to Egypt record
        if (!$country) {
            $country = \App\Models\Country::where('country_code', 'EG')->first();
        }

        if ($country && (float)$country->shipping_price > 0) {
            return (float)$country->shipping_price;
        }

        // Default to 0 if no shipping price is found in the hierarchy
        return 0.0;
    }


}
