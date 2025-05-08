<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    use HasFactory;

    protected $fillable = [
        'name_en',
        'name_ar',
        'city_id',
        'code',
        'shipping_price',
    ];

    public function city()
    {
        return $this->belongsTo(City::class);
    }
    public function scopeFilter($query, $filters)
    {
        if ($filters['city_id'] ?? false) {
            $query->where('city_id', $filters['city_id']);
        }
    }
}
