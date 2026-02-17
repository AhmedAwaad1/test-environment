<?php

namespace App\Http\Services\Cart;

use App\Http\Services\Cart\ProductValidatorService;
use App\Http\Services\GeoCurrency\GeoCurrencyService;
use App\Http\Services\ProductPrice\ProductPriceService;
use App\Models\Cart;
use App\Models\CartItem;
use App\Repositories\CartItem\CartItemRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;
use App\Repositories\Cart\CartRepository;
use App\Repositories\Coupon\CouponRepository;
use App\Repositories\PromoCode\PromoCodeRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Services\Cart\CartCalculationService;

class CartItemService
{
    public function __construct(
        protected CartItemRepository  $cartItemRepo,
        protected CartRepository      $cartRepo,
        protected PromoCodeRepository $couponRepo,
        protected GeoCurrencyService  $geoCurrencyService,
        protected ProductPriceService $productPriceService,
        protected ProductValidatorService $productValidator,
        protected CartCalculationService $cartCalculationService,
    )
    {
    }

    public function createNewCartItem(
        int $cartId,
            $item,
        int $quantity,
        string $type,
        ?int $cartCurrencyId = null,
        ?array $selectedProductIds = null
    ) {
        $productId  = ($type === 'variant') ? $item->product_id : (($type === 'set') ? null : $item->id);
        $variantId  = ($type === 'variant') ? $item->id : null;
        $setId      = ($type === 'set') ? $item->id : null;

        // Use cart currency if provided, otherwise default to EGP
        $currencyId = $cartCurrencyId;
        
        if (!$currencyId) {
            $egpCurrency = \App\Models\Currency::where('code', 'EGP')->first();
            $currencyId = $egpCurrency ? $egpCurrency->id : $this->geoCurrencyService->getDefaultCurrency()->id;
        }

        $productPrice = null;
        $unitRaw = 0;
        $unitAfter = null;

        if ($type === 'set') {
            if ($selectedProductIds && !empty($selectedProductIds)) {
                // PARTIAL SET: Calculate price from selected products only
                $calculated = $item->getTotalPriceForCurrency($currencyId, $selectedProductIds);
                $unitRaw = $calculated['price'];
                $unitAfter = $calculated['price_after_discount'];
                $productPrice = null; // No single price record for partial sets
            } else {
                // FULL SET: Use set price if exists, otherwise sum all products
                $setPrice = $item->productSetItemPrices()->where('currency_id', $currencyId)->first();
                if ($setPrice) {
                    $unitRaw = $setPrice->price;
                    $unitAfter = $setPrice->price_after_discount;
                    $productPrice = $setPrice;
                } else {
                    $calculated = $item->getTotalPriceForCurrency($currencyId);
                    $unitRaw = $calculated['price'];
                    $unitAfter = $calculated['price_after_discount'];
                }
            }

            if ($unitRaw <= 0) {
                throw new \Exception("No price available for this product set in the required currency.");
            }
        } else {
            // Fetch product price (for base currency/discount info)
            $productPrice = $this->productPriceService
                ->getProductPriceByProductAndCurrency($productId, $currencyId);

            if ($type === 'variant') {
                // For variants, we use the price from the variant model itself
                $unitRaw = $item->price;
                $unitAfter = ($item->price_after_discount > 0) ? $item->price_after_discount : null;

                // Only throw exception if variant price is missing AND base product price is missing
                if ($unitRaw <= 0 && (!$productPrice || $productPrice->price <= 0)) {
                    throw new \Exception("No price available for this variant in the required currency.");
                }

                // If variant doesn't have a price but product does, fallback (optional, but safer)
                if ($unitRaw <= 0 && $productPrice) {
                    $unitRaw = $productPrice->price;
                    $unitAfter = $productPrice->price_after_discount;
                }
            } else {
                // For simple products, base price is mandatory
                if (!$productPrice) {
                    throw new \Exception("No price available for this product in the required currency.");
                }

                $unitRaw = $productPrice->price;
                $unitAfter = $productPrice->price_after_discount;
            }
        }

        $totalPrice = $this->cartCalculationService->calculateItemPrice((float)$unitRaw, max(1, $quantity));

        // Check if item already exists in cart to update quantity
        if ($type === 'variant') {
            $existingItem = $this->cartItemRepo->findByCartAndVariantAndPrice($cartId, $variantId, $productPrice?->id);
        } elseif ($type === 'set') {
            $existingItem = $this->cartItemRepo->findByCartSetItemAndPrice($cartId, $setId, $productPrice?->id, $selectedProductIds);
        } else {
            $existingItem = $this->cartItemRepo->findByCartProductAndPrice($cartId, $productId, $productPrice?->id);
        }

        if ($existingItem) {
            return $this->cartItemRepo->incrementQuantity($existingItem, $quantity);
        }

        return $this->cartItemRepo->create([
            'cart_id'                   => $cartId,
            'product_id'                => $productId,
            'product_variant_id'        => $variantId,
            'product_set_item_id'       => $setId,
            'selected_product_ids'      => $selectedProductIds,
            'quantity'                  => max(1, $quantity),
            'product_price_id'          => $productPrice?->id,
            'currency_id'               => $currencyId,
            'unit_price'                => $unitRaw,
            'unit_price_after_discount' => $unitAfter,
            'total_price'               => $totalPrice,
        ]);
    }
    public function updateQuantityAndPrice(CartItem $cartItem, $addedQty)
    {
        $added = max(1, (int)$addedQty);

        $cartItem->quantity    += $added;
        $cartItem->total_price = $this->cartCalculationService->calculateItemPrice((float)$cartItem->unit_price, $cartItem->quantity);
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

        // --- UNIFIED STOCK VALIDATION ---
        if ($item->product_variant_id) {
            $validation = $this->productValidator->validateVariant($item->productVariant, $newQty);
        } elseif ($item->product_id) {
            $validation = $this->productValidator->validateProduct($item->product, $newQty);
        } elseif ($item->product_set_item_id) {
            $validation = $this->productValidator->validateProductSetItem($item->productSetItem, $newQty);
        } else {
            $validation = 'Product type not supported for stock validation';
        }

        if ($validation !== true) {
            throw new \Exception($validation);
        }
        // --------------------------------

        $item->quantity    = $newQty;
        $item->total_price = $this->cartCalculationService->calculateItemPrice((float)$item->unit_price, $newQty);
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
}
