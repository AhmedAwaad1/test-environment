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
        'city_id',
        'district_id',
        'is_default',
    ];

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

}
