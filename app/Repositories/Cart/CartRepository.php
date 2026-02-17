<?php

namespace App\Repositories\Cart;

use App\Models\Cart;
use App\Repositories\BaseRepository;

class CartRepository extends BaseRepository implements CartRepositoryInterface
{
    public function __construct(Cart $model)
    {
        parent::__construct($model);
    }

    public function findOrCreateUserCart($userId)
    {
        return $this->model->firstOrCreate(['user_id' => $userId]);
    }

    public function findUserCart($userId)
    {
        return $this->model->where('user_id', $userId)
                   ->with([
                       'cartItems.product.images',
                       'cartItems.currency',
                       'cartItems.productVariant.product',
                       'cartItems.productPrice',
                       'cartItems.productVariant.optionValues.images',
                       'cartItems.productSetItem.products.images',
                       'cartItems.productSetItem.products.productPrices',
                   ])
                   ->first();
    }

    public function findOrCreateBySessionId($sessionId)
    {
        $cart = $this->model->where('session_id', $sessionId)->first();

        if (!$cart) {
            $cart = $this->model->create([
                'session_id' => $sessionId
            ]);
        }

        return $cart;
    }

    public function findBySessionId($sessionId)
    {
        return $this->model->where('session_id', $sessionId)
                   ->with([
                       'cartItems.product.images',
                       'cartItems.currency',
                       'cartItems.productVariant.product',
                       'cartItems.productPrice',
                       'cartItems.productVariant.optionValues.images',
                       'cartItems.productSetItem.products.images',
                       'cartItems.productSetItem.products.productPrices',
                   ])
                   ->first();
    }
}
