<?php

namespace App\Http\Services\Cart;

use App\Models\Cart;
use App\Http\Services\Cart\CouponService;

class CartCalculationService
{
    public function __construct(
        protected CouponService $couponService
    ) {}

    /**
     * Calculate item price based on unit price and quantity.
     */
    public function calculateItemPrice($unitPrice, $quantity)
    {
        return $unitPrice * $quantity;
    }

    /**
     * Calculate total price for the cart.
     */
    public function calculateTotalPrice(Cart $cart)
    {
        $total = 0;
        $totalAfterDiscount = 0;

        foreach ($cart->cartItems as $item) {
            $unitPrice = (float) $item->unit_price;
            $unitPriceAfter = $item->unit_price_after_discount !== null 
                ? (float) $item->unit_price_after_discount 
                : $unitPrice;

            $total += $unitPrice * $item->quantity;
            $totalAfterDiscount += $unitPriceAfter * $item->quantity;
        }

        $cart->total_price = round($total, 2);
        $cart->total_price_after_discount = round($totalAfterDiscount, 2);
        $cart->save();

        return $cart->total_price;
    }

    /**
     * Recalculate cart totals and apply coupon if exists.
     */
    public function recalculateCart(Cart $cart): Cart
    {
        $this->calculateTotalPrice($cart);
        $this->couponService->checkAndApplyCouponIfExists($cart);
        
        return $cart->refresh();
    }
}
