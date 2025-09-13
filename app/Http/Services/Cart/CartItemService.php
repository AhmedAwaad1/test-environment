<?php

namespace App\Http\Services\Cart;

use App\Http\Services\GeoCurrency\GeoCurrencyService;
use App\Http\Services\ProductPrice\ProductPriceService;
use App\Models\Cart;
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

    public function createNewCartItem(
        int $cartId,
            $item,
        int $quantity,
        string $type,
        ?int $cartCurrencyId = null
    ) {
        $productId  = $item->id;

        // اعتمد على عملة الكارت لو موجودة، وإلا الافتراضي
        $currencyId = $cartCurrencyId
            ?? $this->geoCurrencyService->getDefaultCurrency()->id;

        $productPrice = $this->productPriceService
            ->getProductPriceByProductAndCurrency($productId, $currencyId);

        if (!$productPrice) {
            throw new \Exception("No price available for this product in the cart currency.");
        }

        $unitRaw   = (float) $productPrice->price;

        // 0 يعني مفيش خصم
        $unitAfter = $productPrice->price_after_discount;
        $unitAfter = ($unitAfter !== null && (float)$unitAfter > 0) ? (float)$unitAfter : null;

        $perUnit   = $unitAfter ?? $unitRaw;

        $existingSamePrice = $this->cartItemRepo
            ->findByCartProductAndPrice($cartId, $productId, $productPrice->id);

        if ($existingSamePrice) {
            $existingSamePrice->quantity    += max(1, $quantity);
            $existingSamePrice->total_price  = round($perUnit * $existingSamePrice->quantity, 2);
            $existingSamePrice->save();
            return $existingSamePrice;
        }

        return $this->cartItemRepo->create([
            'cart_id'                    => $cartId,
            'product_id'                 => $productId,
            'product_variant_id'         => null,
            'quantity'                   => max(1, $quantity),
            'product_price_id'           => $productPrice->id,
            'currency_id'                => $currencyId,
            'unit_price'                 => $unitRaw,
            'unit_price_after_discount'  => $unitAfter, // null لو مفيش خصم
            'total_price'                => round($perUnit * max(1, $quantity), 2),
        ]);
    }

    public function updateQuantityAndPrice(CartItem $cartItem, $addedQty)
    {
        $added = max(1, (int)$addedQty);

        $perUnit = $cartItem->unit_price_after_discount ?? $cartItem->unit_price;
        $cartItem->quantity    += $added;
        $cartItem->total_price  = round($perUnit * $cartItem->quantity, 2);
        $cartItem->save();

        return $cartItem;
    }



    public function updateCartItemQuantity($cartItemId, array $data)
    {
        $item = $this->cartItemRepo->findById((int)$cartItemId);
        if (!$item) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('Cart item not found');
        }

        $newQty = max(1, (int)($data['quantity'] ?? 1));

        // برضه هنا: 0 = مفيش خصم
        $perUnit = ($item->unit_price_after_discount !== null && (float)$item->unit_price_after_discount > 0)
            ? (float)$item->unit_price_after_discount
            : (float)$item->unit_price;

        $item->quantity    = $newQty;
        $item->total_price = round($perUnit * $newQty, 2);
        $item->save();

        return $item;
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
