<?php

namespace App\Http\Controllers\Cart;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cart\ApplyCouponRequest;
use App\Http\Services\Cart\CouponService;

class CouponController extends Controller
{
    public $couponService;
    public function __construct(CouponService $couponService)
    {
        $this->middleware('auth:api');
        $this->couponService = $couponService;
    }

    public function applyCoupon(ApplyCouponRequest $request)
    {
        return $this->couponService->applyCoupon($request->validated());
    }

    public function removeCoupon()
    {
        return $this->couponService->removeCoupon();
    }
}
