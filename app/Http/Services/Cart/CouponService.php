<?php

namespace App\Http\Services\Cart;

use App\Http\Resources\Cart\CartResource;
use App\Models\Cart;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\UserCoupon;
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

            if ($user) {
                $cart = $this->cartRepo->findUserCart($user->id);
            } elseif (!empty($data['session_id'])) {
                $cart = $this->cartRepo->findBySessionId($data['session_id']);
            } else {
                return Response::errorResponse('Cart not found', [], 404);
            }

            if (!$cart) {
                return Response::errorResponse('Cart not found', [], 404);
            }

            $coupon = $this->couponRepo->findByCode($data['coupon_code']);

            if (!$coupon) {
                return Response::errorResponse('Coupon not found', [], 404);
            }
            //checking if user_copuons already exists
            if($user){
                $exists = UserCoupon::where('user_id', $user->id)
                    ->where('promo_code_id', $coupon->id)
                    ->exists();
                if ($exists) {
                    return Response::errorResponse('Coupon already used', [], 400);
                }
            }
            
            if (!$coupon->is_active) {
                return Response::errorResponse('Coupon is not active', [], 400);
            }

            $this->applyCouponDiscount($cart, $coupon);

            return Response::successResponse(new CartResource($cart), 'تم تطبيق الكوبون بنجاح');

        } catch (\Exception $e) {
            return Response::handleException($e, 'apply coupon');
        }
    }

    public function removeCoupon($sessionId = null)
    {
        try {
            $user = Auth::user();

            if ($user) {
                $cart = $this->cartRepo->findUserCart($user->id);
            } elseif (!empty($sessionId)) {
                $cart = $this->cartRepo->findBySessionId($sessionId);
            } else {
                return Response::errorResponse('Cart not found', [], 404);
            }

            if (!$cart) {
                return Response::errorResponse('Cart not found', [], 404);
            }

            $cart->coupon_code = null;
            $cart->discount_amount = 0;
            $cart->total_price_after_discount = null;
            $cart->save();

            return Response::successResponse(new CartResource($cart), 'تم إزالة الكوبون بنجاح');

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
