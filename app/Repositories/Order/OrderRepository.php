<?php

namespace App\Repositories\Order;

use App\Models\Order;

class OrderRepository
{
    public function getAllUserOrder($request)
    {
        return Order::with('orderItems')
            ->where('user_id', $request['user_id'])
            ->orderBy('created_at', 'desc');
    }

    public function findOrderById($id)
    {
        return Order::with('orderItems')->find($id);
    }

    public function createOrder(array $data, $cart)
    {
        if($cart->total_price_after_discount > 0) {
            $totalPrice = $cart->total_price_after_discount;
        }else{
            $totalPrice = $cart->total_price;
        }

        return Order::create([
            'user_id'       => $data['user_id'],
            'address_id'    => $data['address_id'],
            'order_number'  => $data['order_number'],
            'subtotal'      => $totalPrice,
            'shipping_price'=> $data['shipping_price'],
            'total_price'   => $totalPrice + $data['shipping_price'],
            'payment_method'=> $data['payment_method'],
            'status'        => $data['payment_method'] == 'cod' ? 'processing' : 'pending',
            'coupon_code'   => $cart->coupon_code,
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
