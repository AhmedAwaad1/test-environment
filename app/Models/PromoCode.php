<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model
{
    use HasFactory;
    protected $fillable = [
        'code',
        'discount_percentage',
        'is_active',
    ];

    // public function usersUsed()
    // {
    //     return $this->belongsToMany(User::class, 'coupon_user')->withTimestamps();
    // }
}
