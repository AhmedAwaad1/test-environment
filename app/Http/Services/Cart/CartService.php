<?php

namespace App\Http\Services\Cart;

use App\Http\Resources\Cart\CartResource;
use App\Http\Services\GeoCurrency\GeoCurrencyService;
use App\Http\Services\ProductPrice\ProductPriceService;
use App\Models\Cart;
use App\Repositories\Cart\CartRepository;
use App\Repositories\CartItem\CartItemRepository;
use App\Repositories\Product\ProductRepository;
use App\Repositories\ProductVariant\ProductVariantRepository;
use App\Repositories\PromoCode\PromoCodeRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;


class CartService
{
    protected $cartRepo, $cartItemRepo, $productVarRepo, $couponService,
        $productRepo, $productValidator, $cartItemService, $geoCurrencyService, $productPriceService;

    public function __construct(
        CartRepository           $cartRepo,
        CartItemRepository       $cartItemRepo,
        ProductRepository        $productRepo,
        ProductVariantRepository $productVarRepo,
        ProductValidatorService  $productValidator,
        CartItemService          $cartItemService,
        CouponService            $couponService,
        GeoCurrencyService       $geoCurrencyService,
        ProductPriceService      $productPriceService

    )
    {
        $this->cartRepo            = $cartRepo;
        $this->cartItemRepo        = $cartItemRepo;
        $this->productVarRepo      = $productVarRepo;
        $this->productRepo         = $productRepo;
        $this->productValidator    = $productValidator;
        $this->cartItemService     = $cartItemService;
        $this->couponService       = $couponService;
        $this->geoCurrencyService  = $geoCurrencyService;
        $this->productPriceService = $productPriceService;
    }

    public function getUserCart()
    {
        try {
            $user = Auth::user();

            $cart = $this->cartRepo->findUserCart($user->id);

            if (!$cart) {
                return Response::successResponse([], 'Cart is empty', 200);
            }

            return Response::successResponse(new CartResource($cart), 'Cart retrieved successfully', 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            //exception if id not found
            return Response::handleModelNotFoundException($e, 'cart');
        } catch (\Exception $e) {
            return Response::handleException($e, 'Failed to retrieve cart');
        }
    }

    public function getGuestCart($request)
    {
        try {
            if (!$request->has('session_id')) {
                return Response::errorResponse('Session ID is required', [], 400);
            }

            $cart = $this->cartRepo->findBySessionId($request->session_id);

            if (!$cart) {
                return Response::successResponse([], 'Guest cart is empty', 200);
            }

            return Response::successResponse(new CartResource($cart), 'Guest cart retrieved successfully', 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::handleModelNotFoundException($e, 'cart');
        } catch (\Exception $e) {
            return Response::handleException($e, 'Failed to retrieve guest cart');
        }
    }


    public function addToCart(array $data)
    {
        try {
            DB::beginTransaction();

            if (Auth::check()) {
                $userId = Auth::id();
                $cart   = $this->cartRepo->findOrCreateUserCart($userId);
            } else {
                if (empty($data['session_id'])) {
                    DB::rollBack();
                    return Response::errorResponse('Session ID is required for guest cart.', [], 400);
                }
                $cart = $this->cartRepo->findOrCreateBySessionId($data['session_id']);
            }

            $xForwardedFor = request()->header('X-Forwarded-For');

            if (!$cart->currency_id) {
                if (empty($xForwardedFor)) {
                    session()->forget(['currency_id', 'country_id']);
                    if (!empty($data['session_id'])) {
                        Cache::forget("currency_id_{$data['session_id']}");
                        Cache::forget("country_id_{$data['session_id']}");
                    }
                }

                $geo      = app(GeoCurrencyService::class);
                $currency = !empty($xForwardedFor)
                    ? ($geo->getCurrencyForRequest() ?? $geo->getDefaultCurrency())
                    : $geo->getDefaultCurrency();

                if (!$currency) {
                    $currency = $geo->getDefaultCurrency();
                }

                $cart->currency_id = $currency->id;
                $cart->save();

                session(['currency_id' => $currency->id]);
                if (!empty($data['session_id'])) {
                    Cache::put("currency_id_{$data['session_id']}", $currency->id, now()->addDays(30));
                }
            }

            $itemData = $this->getCartItemData($data, $cart->id);
            if ($itemData['error']) {
                DB::rollBack();
                return Response::errorResponse($itemData['error'], [], 400);
            }

            $item     = $itemData['item'];
            $type     = $itemData['type'];
            $quantity = max(1, (int)($data['quantity'] ?? 1));

            $this->cartItemService->createNewCartItem($cart->id, $item, $quantity, $type);

            $this->calculateTotalPrice($cart);
            $this->couponService->checkAndApplyCouponIfExists($cart);

            DB::commit();

            $cart->refresh()->load([
                'cartItems.product.images',
                'cartItems.currency',
            ]);

            return Response::successResponse(new CartResource($cart), 'Item added to cart', 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            return Response::handleException($e, 'Error adding item to cart');
        }
    }



    public function updateCartItemQuantity($cartItemId, $data)
    {
        try {
            DB::beginTransaction();

            $updatedItem = $this->cartItemService->updateCartItemQuantity($cartItemId, $data);
            // Update total cart price
            $this->calculateTotalPrice($updatedItem->cart);

            // Apply coupon discount
            $this->couponService->checkAndApplyCouponIfExists($updatedItem->cart);

            DB::commit();

            return Response::successResponse(new CartResource($updatedItem->cart), 'Item quantity updated successfully', 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return Response::handleException($e, 'Error updating item quantity');
        }
    }

    public function deleteCartItem($cartItemId, $sessionId = null)
    {
        try {
            DB::beginTransaction();

            if (Auth::check()) {
                $userId = Auth::id();
                $cart   = $this->cartRepo->findUserCart($userId);
            } else {
                if (empty($sessionId)) {
                    return Response::errorResponse('Session ID is required for guest cart.', [], 400);
                }

                $cart = $this->cartRepo->findBySessionId($sessionId);
            }

            if (!$cart) {
                return Response::errorResponse('Cart not found', [], 404);
            }

            $deletedItem = $this->cartItemService->deleteCartItem($cartItemId, $cart->id);

            if (!$deletedItem) {
                return Response::errorResponse('Item not found', [], 404);
            }

            $this->calculateTotalPrice($cart);
            $this->couponService->checkAndApplyCouponIfExists($cart);

            $cart->refresh();

            DB::commit();

            return Response::successResponse(new CartResource($cart), 'Item deleted successfully', 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return Response::handleException($e, 'Error deleting item');
        }
    }


    public function calculateItemPrice($unitPrice, $quantity)
    {
        return $unitPrice * $quantity;
    }

    public function calculateTotalPrice(Cart $cart)
    {
        $total = $cart->cartItems()->sum('total_price');

        $cart->total_price = $total;
        $cart->save();

        return $total;
    }

    private function getCartItemData(array $data, $cartId): array
    {
        if (!empty($data['product_variant_id'])) {
            $item       = $this->productVarRepo->find($data['product_variant_id']);
            $existing   = $this->cartItemRepo->variantExistsInCart($cartId, $item->id);
            $currentQty = $existing ? $existing->quantity : 0;
            $totalQty   = $currentQty + $data['quantity'];
            $validation = $this->productValidator->validateVariant($item, $totalQty);
            $type       = 'variant';
            $price      = $item->price ?? 0;
        } else {
            $item = $this->productRepo->find($data['product_id']);
            if ($item->has_variants) {
                return ['error' => 'Product has variants and cannot be added to cart'];
            }

            $existing   = $this->cartItemRepo->productExistsInCart($cartId, $item->id);
            $currentQty = $existing ? $existing->quantity : 0;
            $totalQty   = $currentQty + $data['quantity'];
            $validation = $this->productValidator->validateProduct($item, $totalQty);
            $type       = 'product';
        }

        if ($validation !== true) {
            return ['error' => $validation];
        }

        return ['item' => $item, 'existing' => $existing, 'type' => $type, 'error' => false];
    }


}
