<?php

namespace App\Observers\Order;

use App\Models\Cart;
use App\Models\Order;
use App\Models\PromoCode;
use App\Models\UserCoupon;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order)
    {
        if ($order->status === 'processing') {
            $this->clearUserCart($order->user_id);
            Log::info('Order processing', ['order_id' => $order->id]);
            $this->markCouponAsUsed($order);
        }
    }

    public function updated(Order $order)
    {
        if (
            $order->wasChanged('status') &&
            $order->status === 'processing'
        ) {
            $this->clearUserCart($order->user_id);
        }
    }

    protected function clearUserCart($userId)
    {
        $cart = Cart::where('user_id', $userId)->first();
        if ($cart) {
            $cart->cartItems()->delete();
            $cart->total_price = 0;
            $cart->save();
        }
    }

    protected function markCouponAsUsed(Order $order)
    {
        if ($order->coupon_code != null && $order->user_id) {
            //check if user has already used this coupon
            $promoCodeId = PromoCode::where('code', $order->coupon_code)->first()->id;

            $exists = UserCoupon::where('user_id', $order->user_id)
                ->where('promo_code_id', $promoCodeId)
                ->exists();
            Log::info('Coupon used', ['order_id' => $order->id]);

            if (!$exists) {
                UserCoupon::create([
                    'user_id' => $order->user_id,
                    'promo_code_id' => $promoCodeId,
                    'used_at' => now(),
                ]);
            }
        }
    }
    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "restored" event.
     */
    public function restored(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "force deleted" event.
     */
    public function forceDeleted(Order $order): void
    {
        //
    }
}
