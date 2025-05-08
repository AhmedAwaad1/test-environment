<?php

namespace App\Repositories\OrderItem;

use App\Models\Order;
use App\Models\OrderItem;

class OrderItemRepository
{
    public function getAll($request)
    {
        return OrderItem::filter($request);
    }

    public function find($id)
    {
        return OrderItem::find($id);
    }

    public function createOrderItems($orderId, $cartItems)
    {
        foreach ($cartItems as $item) {
            OrderItem::create([
                'order_id' => $orderId,
                'product_variant_id' => $item->product_variant_id,
                'product_name' => $item->productVariant->product->name_en,
                'quantity' => $item->quantity,
                'price' => $item->price,
                'total' => $item->total_price,
            ]);
        }
    }

    public function delete($id)
    {
        $order = $this->find($id);
        if ($order) {
            $order->delete();
        }
        return $order;
    }
}
