<?php

namespace App\Http\Services\Cart;

use App\Http\Resources\Cart\CartResource;
use App\Http\Services\GeoCurrency\GeoCurrencyService;
use App\Http\Services\ProductPrice\ProductPriceService;
use App\Models\Cart;
use App\Models\User;
use App\Repositories\Cart\CartRepository;
use App\Repositories\Cart\CartRepositoryInterface;
use App\Repositories\CartItem\CartItemRepository;
use App\Repositories\Product\ProductRepository;
use App\Repositories\Product\ProductRepositoryInterface;
use App\Repositories\ProductVariant\ProductVariantRepository;
use App\Repositories\ProductSetItems\ProductSetItemsRepository;
use App\Repositories\PromoCode\PromoCodeRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Tymon\JWTAuth\Facades\JWTAuth;


class CartService
{
    public function __construct(
        protected CartRepositoryInterface   $cartRepo,
        protected CartItemRepository        $cartItemRepo,
        protected ProductRepositoryInterface $productRepo,
        protected ProductVariantRepository  $productVarRepo,
        protected ProductValidatorService   $productValidator,
        protected CartItemService           $cartItemService,
        protected CouponService             $couponService,
        protected GeoCurrencyService        $geoCurrencyService,
        protected ProductPriceService       $productPriceService,
        protected ProductSetItemsRepository $productSetRepo,
        protected CartCalculationService    $cartCalculationService,
        protected CartCurrencyService       $cartCurrencyService,
        protected CartUserResolverService   $cartUserResolverService,
        protected CartValidationService      $cartValidationService
    ) {}

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
            
            // Normalize input: Convert single product format to items array for unified processing
            if (isset($data['items']) && is_array($data['items'])) {
                // Bulk format - use items array as-is
                $items = $data['items'];
                $sessionId = $data['session_id'] ?? null;
            } else {
                // Single format (backward compatible) - wrap into items array
                $items = [[
                    'product_id' => $data['product_id'] ?? null,
                    'product_variant_id' => $data['product_variant_id'] ?? null,
                    'product_set_item_id' => $data['product_set_item_id'] ?? null,
                    'selected_product_ids' => $data['selected_product_ids'] ?? null,
                    'quantity' => $data['quantity'] ?? 1,
                ]];
                $sessionId = $data['session_id'] ?? null;
            }

            $user = $this->cartUserResolverService->resolveUserFromRequest();

            if ($user) {
                $cart = $this->cartRepo->findOrCreateUserCart($user->id);
            } else {
                if (empty($sessionId)) {
                    DB::rollBack();
                    return Response::errorResponse('Session ID is required for guest cart.', [], 400);
                }
                $cart = $this->cartRepo->findOrCreateBySessionId($sessionId);
            }

            $this->cartCurrencyService->handleCartCurrency($cart, $sessionId);

            // Process each item in the loop
            foreach ($items as $itemData) {
                $itemDataWithSession = array_merge($itemData, ['session_id' => $sessionId]);
                
                // Use validation service
                $cartItemData = $this->cartValidationService->getCartItemData($itemDataWithSession, $cart->id);
                
                if ($cartItemData['error']) {
                    DB::rollBack();
                    return Response::errorResponse($cartItemData['error'], [], 400);
                }

                $item = $cartItemData['item'];
                $type = $cartItemData['type'];
                $quantity = max(1, (int)($itemData['quantity'] ?? 1));

                // Use existing createNewCartItem service
                $this->cartItemService->createNewCartItem(
                    $cart->id,
                    $item,
                    $quantity,
                    $type,
                    $cart->currency_id,
                    $itemData['selected_product_ids'] ?? null
                );
            }

            // Calculate totals and apply coupon ONLY ONCE after all items are added
            $this->cartCalculationService->recalculateCart($cart);

            DB::commit();

            $cart->refresh()->load([
                'cartItems.product.images',
                'cartItems.productVariant.optionValues.productOption.optionType',
                'cartItems.productSetItem.products.images',
                'cartItems.productSetItem.products.productPrices',
                'cartItems.currency',
            ]);

            $message = count($items) === 1 ? 'Item added to cart' : 'Items added to cart';
            return Response::successResponse(new CartResource($cart), $message, 201);

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
            
            // Recalculate cart
            $this->cartCalculationService->recalculateCart($updatedItem->cart);

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

            $this->cartCalculationService->recalculateCart($cart);

            DB::commit();

            return Response::successResponse(new CartResource($cart), 'Item deleted successfully', 200);
        } catch (\Throwable $e) {
            DB::rollBack();
            return Response::handleException($e, 'Error deleting item');
        }
    }


}
