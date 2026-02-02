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
            ->whereNull('product_variant_id')
            ->whereNull('product_set_item_id')
            ->first();
    }

    public function setItemExistsInCart($cartId, $setId)
    {
        return CartItem::where('cart_id', $cartId)
            ->where('product_set_item_id', $setId)
            ->first();
    }

    public function findByCartAndVariantAndPrice(int $cartId, int $variantId, ?int $productPriceId): ?CartItem
    {
        $query = CartItem::where('cart_id', $cartId)
                       ->where('product_variant_id', $variantId);

        if (is_null($productPriceId)) {
            $query->whereNull('product_price_id');
        } else {
            $query->where('product_price_id', $productPriceId);
        }

        return $query->first();
    }

    public function findByCartProductAndPrice(int $cartId, int $productId, ?int $productPriceId): ?CartItem
    {
        $query = CartItem::where('cart_id', $cartId)
                       ->where('product_id', $productId)
                       ->whereNull('product_variant_id')
                       ->whereNull('product_set_item_id');

        if (is_null($productPriceId)) {
            $query->whereNull('product_price_id');
        } else {
            $query->where('product_price_id', $productPriceId);
        }

        return $query->first();
    }

    public function findByCartSetItemAndPrice(int $cartId, int $setId, ?int $productPriceId, ?array $selectedProductIds = null): ?CartItem
    {
        $query = CartItem::where('cart_id', $cartId)
                       ->where('product_set_item_id', $setId);

        if (is_null($productPriceId)) {
            $query->whereNull('product_price_id');
        } else {
            $query->where('product_price_id', $productPriceId);
        }

        if ($selectedProductIds) {
            // Sort to ensure consistent comparison
            sort($selectedProductIds);
            $query->whereJsonContains('selected_product_ids', $selectedProductIds)
                  ->whereRaw('JSON_LENGTH(selected_product_ids) = ?', [count($selectedProductIds)]);
        } else {
            $query->whereNull('selected_product_ids');
        }

        return $query->first();
    }

    public function incrementQuantity(CartItem $item, int $by = 1): CartItem
    {
        $item->quantity += max(1, $by);
        $item->total_price = round((float)$item->unit_price * $item->quantity, 2);
        $item->save();

        return $item;
    }

}
