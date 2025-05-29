<?php

namespace App\Http\Services\Cart;

use App\Http\Resources\Cart\CartResource;
use App\Models\Cart;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Repositories\Cart\CartRepository;
use App\Repositories\Product\ProductRepository;
use App\Repositories\PromoCode\PromoCodeRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class CouponService
{
    protected $cartRepo, $couponRepo, $productRepo;

    public function __construct(
        CartRepository $cartRepo,
        PromoCodeRepository $couponRepo,
    ) {
        $this->cartRepo = $cartRepo;
        $this->couponRepo = $couponRepo;
    }

    public function checkAndApplyCouponIfExists(Cart $cart)
    {
        if (!$cart->coupon_code) {
            return;
        }

        $coupon = $this->couponRepo->findByCode($cart->coupon_code);

        if (!$coupon || !$coupon->is_active) {
            return;
        }

        $this->applyCouponDiscount($cart, $coupon);
    }

    public function applyCoupon($data)
    {
        try {
            $user = Auth::user();
            $cart = $this->cartRepo->findUserCart($user->id);

            if (!$cart) {
                return Response::errorResponse('Cart not found', [], 404);
            }

            $coupon = $this->couponRepo->findByCode($data['coupon_code']);

            if (!$coupon) {
                return Response::errorResponse('Coupon not found', [], 404);
            }

            if (!$coupon->is_active) {
                return Response::errorResponse('Coupon is not active', [], 400);
            }

            // Check if user used this coupon before
            // if ($user->usedCoupons()->where('coupon_id', $coupon->id)->exists()) {
            //     return Response::errorResponse('You have already used this coupon', [], 400);
            // }

            $this->applyCouponDiscount($cart, $coupon);

            return Response::successResponse(new CartResource($cart), 'Coupon applied successfully');

        } catch (\Exception $e) {
            return Response::handleException($e, 'apply coupon');
        }
    }

    public function removeCoupon()
    {
        try {
            $user = Auth::user();
            $cart = $this->cartRepo->findUserCart($user->id);

            if (!$cart) {
                return Response::errorResponse('Cart not found', [], 404);
            }

            $cart->coupon_code = null;
            $cart->discount_amount = 0;
            $cart->total_price_after_discount = null;
            $cart->save();

            return Response::successResponse(new CartResource($cart), 'Coupon removed successfully');

        } catch (\Exception $e) {
            return Response::handleException($e, 'remove coupon');
        }
    }

    public function applyCouponDiscount(Cart $cart, $coupon)
    {
        $total = $cart->total_price;

        if ($coupon->discount_percentage) {
            $cart->coupon_code = $coupon->code;
            $cart->discount_amount = ($total * $coupon->discount_percentage) / 100;
        } else {
            $cart->discount_amount = 0;
        }

        $cart->total_price_after_discount = $total - $cart->discount_amount;
        $cart->save();

        return $cart->total_price_after_discount;
    }

}
