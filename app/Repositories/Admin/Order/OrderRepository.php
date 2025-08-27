<?php

namespace App\Repositories\Admin\Order;

use App\Models\Order;

class OrderRepository
{
    public function getAllOrdersForAdmin($request)
    {
        $query = Order::with([
            'orderItems',
            'user',
            'address.city.country',
            'address.district',
            'address.user',
        ])->filter($request->all());

        return $query->latest();
    }

    public function findOrderById($id)
    {
        return Order::with([
            'orderItems',
            'user',
            'address.city.country',
            'address.district',
            'address.user',
        ])->find($id);
    }

    public function updateOrderById($id, $data)
    {
        $order = Order::findOrFail($id);
        $order->update($data);
        return $order;
    }
}
