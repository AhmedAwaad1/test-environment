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
            // CRITICAL: Use the values already calculated in memory (CreateOrderAction)
            // to ensure the snapshot matches the validated price.
            $unit      = (float) ($item->unit_price ?? 0);
            $unitAfter = $item->unit_price_after_discount !== null
                ? (float) $item->unit_price_after_discount
                : null;

            $totalLine = round(((float)($unitAfter ?? $unit)) * (int)$item->quantity, 2);

            // Use the product name snapshot from memory if available, otherwise fallback
            $productName = $item->product_name_snapshot ?? null;

            if (!$productName) {
                $productName = optional($item->product)->name_en;
                if ($item->product_variant_id && $item->productVariant) {
                    $variantTitle = $item->productVariant->getTitle();
                    if ($variantTitle) {
                        $productName .= " ({$variantTitle})";
                    }
                } elseif ($item->product_set_item_id && $item->productSetItem) {
                    $productName = $item->productSetItem->name_en;
                }
            }

            OrderItem::create([
                'order_id'                  => $orderId,
                'product_id'                => $item->product_id ?? null,
                'product_variant_id'        => $item->product_variant_id ?? null,
                'product_set_item_id'       => $item->product_set_item_id ?? null,
                'product_name'              => $productName,
                'product_price_id'          => $item->product_price_id ?? null,
                'unit_price'                => $unit,
                'unit_price_after_discount' => $unitAfter,
                'quantity'                  => (int) $item->quantity,
                'total_price'               => $totalLine,
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
