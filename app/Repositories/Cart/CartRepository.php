<?php

namespace App\Repositories\Cart;

use App\Models\Cart;

class CartRepository
{
    public function findOrCreateUserCart($userId)
    {
        $cart = null;
        $cart = Cart::where('user_id', $userId)->first();

        if (!$cart) {
            $cart = Cart::create(['user_id' => $userId]);
        }
        return $cart;
    }

    public function findUserCart($userId)
    {
        return Cart::where('user_id', $userId)
                   ->with([
                       'cartItems.product.images',
                       'cartItems.currency',
                       'cartItems.productVariant.product',
                       'cartItems.productPrice',
                       'cartItems.productVariant.optionValues.images',
                   ])
                   ->first();
    }

    public function findOrCreateBySessionId($sessionId)
    {
        $cart = Cart::where('session_id', $sessionId)->first();

        if (!$cart) {
            $cart = Cart::create([
                'session_id' => $sessionId
            ]);
        }

        return $cart;
    }

    public function findBySessionId($sessionId)
    {
        return Cart::where('session_id', $sessionId)
                   ->with([
                       'cartItems.product.images',
                       'cartItems.currency',
                   ])
                   ->first();
    }

    public function create(array $data)
    {
        return Cart::create($data);
    }

    public function update(Cart $cart, array $data)
    {
        return $cart->update($data);
    }

}
