<?php

namespace App\Http\Services\Product;

use App\Models\Cart;
use Illuminate\Support\Facades\Log;

class StockService
{
    /**
     * Decrement stock for all items in the cart.
     *
     * @param Cart $cart
     * @return void
     */
    public function decrementStockFromCart(Cart $cart): void
    {
        // Eager load relationships for performance
        $cart->load(['cartItems.productVariant', 'cartItems.product', 'cartItems.productSetItem']);

        foreach ($cart->cartItems as $item) {
            $decremented = false;
            $type = 'unknown';
            $targetId = null;

            try {
                // Case 1 (Variant)
                if ($item->product_variant_id) {
                    $type = 'Variant';
                    $targetId = $item->product_variant_id;
                    if ($item->productVariant) {
                        $item->productVariant->decrement('quantity', (int)$item->quantity);
                        $decremented = true;
                    }
                } 
                // Case 2 (Simple Product)
                elseif ($item->product_id) {
                    $type = 'Simple Product';
                    $targetId = $item->product_id;
                    if ($item->product) {
                        $item->product->decrement('quantity', (int)$item->quantity);
                        $decremented = true;
                    }
                } 
                // Case 3 (Set Item)
                elseif ($item->product_set_item_id) {
                    $type = 'Set Item';
                    $targetId = $item->product_set_item_id;
                    if ($item->productSetItem) {
                        $item->productSetItem->decrement('quantity', (int)$item->quantity);
                        $decremented = true;
                    }
                }

                if ($decremented) {
                    Log::info("Successfully decremented stock for {$type} ID: {$targetId}, Quantity: {$item->quantity}");
                } else {
                    Log::warning("Failed to decrement stock for {$type} ID: {$targetId}: Relationship not found.");
                }

            } catch (\Exception $e) {
                Log::error("Error decrementing stock for item ID: {$item->id}", [
                    'type' => $type,
                    'target_id' => $targetId,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }
}
