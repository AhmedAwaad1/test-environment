<?php

namespace App\Http\Controllers\Cart;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cart\ApplyCouponRequest;
use App\Http\Requests\Cart\CartRequest;
use App\Http\Services\Cart\CartService;

class CartController extends Controller
{
    public $cartService;
    public function __construct(CartService $cartService)
    {
        $this->middleware('auth:api');
        $this->cartService = $cartService;
    }

    public function addtoCart(CartRequest $request)
    {
        return $this->cartService->addToCart($request->validated());
    }

    public function updateCartItemQuantity(CartRequest $request, $id)
    {
        return $this->cartService->updateCartItemQuantity($id, $request->validated());
    }

    public function getUserCart()
    {
        return $this->cartService->getUserCart();
    }

    public function deleteCartItem($id)
    {
        return $this->cartService->deleteCartItem($id);
    }

}
