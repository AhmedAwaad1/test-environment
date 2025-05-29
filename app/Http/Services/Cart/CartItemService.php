<?php

namespace App\Http\Services\Cart;

use App\Models\CartItem;
use App\Repositories\CartItem\CartItemRepository;
use Illuminate\Support\Facades\Response;
use App\Repositories\Cart\CartRepository;
use App\Repositories\Coupon\CouponRepository;
use App\Repositories\PromoCode\PromoCodeRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartItemService
{
    public function __construct(
        protected CartItemRepository $cartItemRepo,
        protected CartRepository $cartRepo,
        protected PromoCodeRepository $couponRepo,
    ) {}

    public function createNewCartItem($cartId, $item, $quantity, $type)
    {
        return $this->cartItemRepo->create([
            'cart_id'            => $cartId,
            'product_variant_id' => $type === 'variant' ? $item->id : null,
            'product_id'         => $type === 'product' ? $item->id : null,
            'quantity'           => $quantity,
            'price'              => $item->price_after_discount ?? $item->price,
            'total_price'        => $this->calculateItemPrice($item->price_after_discount ?? $item->price, $quantity),
        ]);
    }

    public function updateQuantityAndPrice(CartItem $cartItem, $price, $addedQty)
    {
        $cartItem->quantity += $addedQty;
        $cartItem->price = $price;
        $cartItem->total_price = $price * $cartItem->quantity;
        $cartItem->save();

        return $cartItem;
    }

    public function updateCartItemQuantity($cartItemId, $data)
    {
        $cartItem = $this->cartItemRepo->findById($cartItemId);

        if (!$cartItem) {
            return Response::errorResponse('Cart item not found', [], 404);
        }

        $cartItem->quantity = $data['quantity'];
        $cartItem->total_price = $this->calculateItemPrice($cartItem->price, $data['quantity']);
        $cartItem->save();

        return $cartItem;
    }

    public function deleteCartItem($cartItemId, $cartId)
    {
        try {
            DB::beginTransaction();

            $cartItem = $this->cartItemRepo->findById($cartItemId);

            if (!$cartItem || $cartItem->cart_id !== $cartId) {
                return null;
            }

            $this->cartItemRepo->delete($cartItem);

            DB::commit();

            return Response::successResponse(null, 'Cart item deleted successfully', 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return Response::handleException($e, 'Error deleting cart item');
        }
    }

    public function calculateItemPrice(float $price, int $quantity): float
    {
        return $price * $quantity;
    }

    protected function calculateTotalPriceAfterDiscount(float $total, $coupon)
    {
        return $total - ($total * ($coupon->discount / 100));
    }
}
