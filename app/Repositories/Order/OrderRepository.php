<?php

namespace App\Repositories\Order;

use App\Models\Order;

class OrderRepository
{
    public function getAllUserOrder($request)
    {
        return Order::with([
            'orderItems',
            'address.country',
            'address.city',
            'address.district',
            'currency',
        ])
                    ->where('user_id', $request['user_id'])
                    ->orderBy('created_at', 'desc');
    }


    public function findOrderById($id)
    {
        return Order::with([
            'orderItems',
            'user',
            'address.country',
            'address.city',
            'address.district',
            'currency',
        ])
                    ->find($id);
    }

    public function createOrder(array $data, $cart)
    {
        $subtotal       = (float)($cart->total_price ?? 0);
        $discountAmount = (float)($cart->discount_amount ?? 0);
        $shipping       = (float)($data['shipping_price'] ?? 0);
        $total          = max(0, $subtotal - $discountAmount + $shipping);

        return Order::create([
            'user_id'         => $data['user_id'],
            'address_id'      => $data['address_id'],
            'order_number'    => $data['order_number'],
            'subtotal'        => $subtotal,
            'discount_amount' => $discountAmount,
            'shipping_price'  => $shipping,
            'total_price'     => $total,
            'payment_method'  => $data['payment_method'],         // card | cod
            'payment_status'  => 'pending',                       // 👈 جديد
            'status'          => $data['payment_method'] === 'cod' ? 'processing' : 'pending',
            'coupon_code'     => $cart->coupon_code,
            'currency_id'     => $cart->currency_id,
        ]);
    }

    public function delete($id)
    {
        $order = Order::find($id);
        if ($order) {
            $order->delete();
        }
        return $order;
    }

}
