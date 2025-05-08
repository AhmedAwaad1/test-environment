<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    use HasFactory;
    protected $fillable = [
        'name_en',
        'name_ar',
        'country_id',
        'shipping_price',
    ];

    public function country()
    {
        return $this->belongsTo(Country::class);
    }
    public function scopeFilter($query, $filters)
    {
        if ($filters['country_id'] ?? false) {
            $query->where('country_id', $filters['country_id']);
        }
    }
}
