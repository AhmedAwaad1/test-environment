<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCoupon extends Model
{
    protected $table = 'user_coupons';

    protected $fillable = ['user_id', 'promo_code_id', 'used_at'];

    public $timestamps = true;
}
