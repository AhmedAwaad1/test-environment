<?php

namespace App\Observers\Order;

use App\Models\Cart;
use App\Models\Order;

class OrderObserver
{
    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order)
    {
        if ($order->status === 'processing') {
            $this->clearUserCart($order->user_id);
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
