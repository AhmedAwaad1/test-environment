<?php

namespace App\Http\Services\Cart;

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

class CartItemService
{
    public function __construct(
        protected CartItemRepository  $cartItemRepo,
        protected CartRepository      $cartRepo,
        protected PromoCodeRepository $couponRepo,
        protected GeoCurrencyService  $geoCurrencyService,
        protected ProductPriceService $productPriceService,
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

        // Use cart currency if provided, otherwise default
        $currencyId = $cartCurrencyId
            ?? $this->geoCurrencyService->getDefaultCurrency()->id;

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

            // SAFETY FALLBACK for SETS: Try default currency if no price found
            if ($unitRaw <= 0 && $cartCurrencyId) {
                $defaultCurrency = $this->geoCurrencyService->getDefaultCurrency();
                if ($defaultCurrency && $defaultCurrency->id !== $currencyId) {
                    \Log::warning('No set price for currency ' . $currencyId . ', trying default currency ' . $defaultCurrency->id, [
                        'set_id' => $setId,
                        'original_currency' => $currencyId,
                        'partial' => !empty($selectedProductIds)
                    ]);

                    if ($selectedProductIds && !empty($selectedProductIds)) {
                        $calculated = $item->getTotalPriceForCurrency($defaultCurrency->id, $selectedProductIds);
                    } else {
                        $setPrice = $item->productSetItemPrices()->where('currency_id', $defaultCurrency->id)->first();
                        if ($setPrice) {
                            $unitRaw = $setPrice->price;
                            $unitAfter = $setPrice->price_after_discount;
                            $productPrice = $setPrice;
                            $currencyId = $defaultCurrency->id;
                        } else {
                            $calculated = $item->getTotalPriceForCurrency($defaultCurrency->id);
                        }
                    }

                    if (!isset($setPrice) || !$setPrice) {
                        if ($calculated['price'] > 0) {
                            $unitRaw = $calculated['price'];
                            $unitAfter = $calculated['price_after_discount'];
                            $currencyId = $defaultCurrency->id;
                        }
                    }

                    if ($unitRaw > 0) {
                        $cart = Cart::find($cartId);
                        if ($cart) {
                            $cart->currency_id = $defaultCurrency->id;
                            $cart->save();
                            session(['currency_id' => $defaultCurrency->id]);
                            if ($cart->session_id) {
                                Cache::put("currency_id_{$cart->session_id}", $defaultCurrency->id, now()->addDays(30));
                            }
                        }
                    }
                }
            }

            // Still no price? Try ANY currency
            if ($unitRaw <= 0) {
                 if (!$selectedProductIds || empty($selectedProductIds)) {
                     $firstPrice = $item->productSetItemPrices()->first();
                     if ($firstPrice) {
                         $unitRaw = $firstPrice->price;
                         $unitAfter = $firstPrice->price_after_discount;
                         $currencyId = $firstPrice->currency_id;
                         $productPrice = $firstPrice;

                         // Update cart currency to match the found currency
                         $cart = \App\Models\Cart::find($cartId);
                         if ($cart) {
                             $cart->currency_id = $currencyId;
                             $cart->save();
                             session(['currency_id' => $currencyId]);
                             if ($cart->session_id) {
                                 \Illuminate\Support\Facades\Cache::put("currency_id_{$cart->session_id}", $currencyId, now()->addDays(30));
                             }
                         }
                     }
                 }

                 if ($unitRaw <= 0) {
                    // Try to find ANY product price in the set (full or partial)
                    foreach (\App\Models\Currency::all() as $currency) {
                        $calculated = $item->getTotalPriceForCurrency($currency->id, $selectedProductIds ?: null);
                        if ($calculated['price'] > 0) {
                            $unitRaw = $calculated['price'];
                            $unitAfter = $calculated['price_after_discount'];
                            $currencyId = $currency->id;

                            // Update cart currency to match the found currency
                            $cart = \App\Models\Cart::find($cartId);
                            if ($cart) {
                                $cart->currency_id = $currencyId;
                                $cart->save();
                                session(['currency_id' => $currencyId]);
                                if ($cart->session_id) {
                                    \Illuminate\Support\Facades\Cache::put("currency_id_{$cart->session_id}", $currencyId, now()->addDays(30));
                                }
                            }
                            break;
                        }
                    }
                }
            }

            if ($unitRaw <= 0) {
                throw new \Exception("No price available for this product set (or selected items) in any currency.");
            }
        } else {
            $productPrice = $this->productPriceService
                ->getProductPriceByProductAndCurrency($productId, $currencyId);

            // SAFETY FALLBACK: Try default currency if no price found
            if (!$productPrice && $cartCurrencyId) {
                $defaultCurrency = $this->geoCurrencyService->getDefaultCurrency();

                if ($defaultCurrency && $defaultCurrency->id !== $currencyId) {
                    \Log::warning('No price for currency ' . $currencyId . ', trying default currency ' . $defaultCurrency->id, [
                        'product_id' => $productId,
                        'original_currency' => $currencyId
                    ]);

                    $productPrice = $this->productPriceService
                        ->getProductPriceByProductAndCurrency($productId, $defaultCurrency->id);

                    if ($productPrice) {
                        $cart = Cart::find($cartId);
                        if ($cart) {
                            $cart->currency_id = $defaultCurrency->id;
                            $cart->save();

                            session(['currency_id' => $defaultCurrency->id]);
                            if ($cart->session_id) {
                                Cache::put("currency_id_{$cart->session_id}", $defaultCurrency->id, now()->addDays(30));
                            }
                        }
                        $currencyId = $defaultCurrency->id;
                    }
                }
            }

            if (!$productPrice) {
                $itemForPrice = ($type === 'variant') ? $item->product : $item;
                $productPrice = $itemForPrice->productPrices->first();
                if ($productPrice) {
                    $currencyId = $productPrice->currency_id;
                }
            }

            if (!$productPrice) {
                throw new \Exception("No price available for this product in any currency.");
            }

            $unitRaw = $productPrice->price;
            $unitAfter = $productPrice->price_after_discount;

            // VARIANT PRICE OVERRIDE: If it's a variant, use its specific price if set
            if ($type === 'variant') {
                if ($item->price > 0) {
                    $unitRaw = $item->price;
                    $unitAfter = ($item->price_after_discount > 0) ? $item->price_after_discount : null;
                } elseif ($item->price_after_discount > 0) {
                    $unitAfter = $item->price_after_discount;
                }
            }
        }

        $perUnit = ($unitAfter !== null && (float)$unitAfter > 0) ? (float)$unitAfter : (float)$unitRaw;
        $totalPrice = round($perUnit * max(1, $quantity), 2);

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

        $perUnit = ($cartItem->unit_price_after_discount !== null && (float)$cartItem->unit_price_after_discount > 0)
            ? (float)$cartItem->unit_price_after_discount
            : (float)$cartItem->unit_price;

        $cartItem->quantity    += $added;
        $cartItem->total_price = round($perUnit * $cartItem->quantity, 2);
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
