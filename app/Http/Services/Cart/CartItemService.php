<?php

namespace App\Http\Services\Cart;

use App\Http\Services\GeoCurrency\GeoCurrencyService;
use App\Http\Services\ProductPrice\ProductPriceService;
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
        protected GeoCurrencyService $geoCurrencyService,
        protected ProductPriceService $productPriceService,
    ) {}

    public function createNewCartItem($cartId, $item, $quantity, $type)
    {
        $productId = $item->id;

        $currency = $this->geoCurrencyService->getCurrencyForRequest();
        \Log::channel('product_price')->info('Detected currency from IP/session', [
            'currency_id' => $currency->id,
            'currency_code' => $currency->name,
        ]);

        $productPrice = $this->productPriceService->getProductPriceByProductAndCurrency($productId, $currency->id);
        \Log::channel('product_price')->info('Price fetched with detected currency', [
            'found' => (bool)$productPrice,
            'product_id' => $productId,
            'currency_id' => $currency->id,
        ]);

        if (!$productPrice) {
            $defaultCurrency = $this->geoCurrencyService->getDefaultCurrency();
            \Log::channel('product_price')->warning('Fallback to default currency', [
                'default_currency_id' => $defaultCurrency->id,
                'default_currency_code' => $defaultCurrency->name,
            ]);

            $productPrice = $this->productPriceService->getProductPriceByProductAndCurrency($productId, $defaultCurrency->id);

            \Log::channel('product_price')->info('Price fetched with default currency', [
                'found' => (bool)$productPrice,
                'product_id' => $productId,
                'currency_id' => $defaultCurrency->id,
            ]);

            if (!$productPrice) {
                \Log::channel('product_price')->error('No price available for product in any currency', [
                    'product_id' => $productId,
                ]);

                throw new \Exception("No price available for this product.");
            }
        }

        $price = $productPrice->price_after_discount ?? $productPrice->price;

        \Log::channel('product_price')->info('Final price selected for cart item', [
            'price' => $price,
            'product_price_id' => $productPrice->id,
            'product_id' => $productId,
            'currency_id' => $productPrice->currency_id,
        ]);


        return $this->cartItemRepo->create([
            'cart_id'            => $cartId,
            'product_id'         => $type === 'product' ? $item->id : null,
            'product_variant_id' => $type === 'variant' ? $item->id : null,
            'quantity'           => $quantity,
            'price'              => $price,
            'total_price'        => $this->calculateItemPrice($price, $quantity),
        ]);
    }




    public function updateQuantityAndPrice(CartItem $cartItem, $item, $addedQty, $type)
    {
        $productId = $item->id;

        $currency = $this->geoCurrencyService->getCurrencyForRequest();
        $productPrice = $this->productPriceService->getProductPriceByProductAndCurrency($productId, $currency->id)
            ?? $this->productPriceService->getProductPriceByProductAndCurrency($productId, $this->geoCurrencyService->getDefaultCurrency()->id);

        if (!$productPrice) {
            throw new \Exception("No price available for this product.");
        }

        $price = $productPrice->price_after_discount ?? $productPrice->price;

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
        if (Auth::check()) {
            if ($cartItem->cart->user_id !== Auth::id()) {
                return Response::errorResponse('Unauthorized access to cart item', [], 403);
            }
        } else {
            if (empty($data['session_id']) || $cartItem->cart->session_id !== $data['session_id']) {
                return Response::errorResponse('Unauthorized guest access to cart item', [], 403);
            }
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
