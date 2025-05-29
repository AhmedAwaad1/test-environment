<?php

namespace App\Repositories\CartItem;

use App\Models\CartItem;
use Illuminate\Support\Facades\DB;

class CartItemRepository
{
    public function create(array $data)
    {
        return CartItem::create($data);
    }

    public function update(CartItem $item, array $data)
    {
        $item->update($data);
        return $item;
    }

    public function findById($id)
    {
        $cartItem = CartItem::where('id', $id)->first();

        if (!$cartItem) {
            return null;
        }
        return $cartItem;
    }

    public function delete(CartItem $item)
    {
        return $item->delete();
    }

    public function variantExistsInCart($cartId, $variantId)
    {
        return CartItem::where('cart_id', $cartId)
            ->where('product_variant_id', $variantId)
            ->first();
    }
    public function productExistsInCart($cartId, $productId)
    {
        return CartItem::where('cart_id', $cartId)
            ->where('product_id', $productId)
            ->first();
    }
}
