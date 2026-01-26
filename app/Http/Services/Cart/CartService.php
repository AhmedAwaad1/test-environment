<?php

namespace App\Http\Services\Cart;

use App\Http\Resources\Cart\CartResource;
use App\Http\Services\GeoCurrency\GeoCurrencyService;
use App\Http\Services\ProductPrice\ProductPriceService;
use App\Models\Cart;
use App\Models\User;
use App\Repositories\Cart\CartRepository;
use App\Repositories\CartItem\CartItemRepository;
use App\Repositories\Product\ProductRepository;
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
    protected $cartRepo, $cartItemRepo, $productVarRepo, $couponService,
        $productRepo, $productValidator, $cartItemService, $geoCurrencyService, $productPriceService, $productSetRepo;

    public function __construct(
        CartRepository           $cartRepo,
        CartItemRepository       $cartItemRepo,
        ProductRepository        $productRepo,
        ProductVariantRepository $productVarRepo,
        ProductValidatorService  $productValidator,
        CartItemService          $cartItemService,
        CouponService            $couponService,
        GeoCurrencyService       $geoCurrencyService,
        ProductPriceService      $productPriceService,
        ProductSetItemsRepository $productSetRepo

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
        $this->productSetRepo      = $productSetRepo;
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

            $user = $this->resolveUserFromRequest();

            if ($user) {
                $cart = $this->cartRepo->findOrCreateUserCart($user->id);
            } else {
                if (empty($sessionId)) {
                    DB::rollBack();
                    return Response::errorResponse('Session ID is required for guest cart.', [], 400);
                }
                $cart = $this->cartRepo->findOrCreateBySessionId($sessionId);
            }

            // --- REFACTOR: Remove IP/Geo logic & Force EGP (Egypt) ---
            $egpCurrency = \App\Models\Currency::where('name', 'EGP')->first();
            
            // Fallback if EGP isn't in DB (though it should be)
            if (!$egpCurrency) {
                 // Try to get default currency from Geo service as a last resort backup
                 $geo = app(GeoCurrencyService::class);
                 $egpCurrency = $geo->getDefaultCurrency();
            }

            // Always enforce EGP currency if not set or if we want to force it
            if (!$cart->currency_id || $cart->currency_id !== $egpCurrency->id) {
                $cart->currency_id = $egpCurrency->id;
                $cart->save();
            }

            // Update session/cache to reflect this forced currency
            session(['currency_id' => $cart->currency_id]);
            if (!empty($sessionId)) {
                Cache::put("currency_id_{$sessionId}", $cart->currency_id, now()->addDays(30));
            }
            // ----------------------------------------------------------

            // Process each item in the loop
            foreach ($items as $itemData) {
                $itemDataWithSession = array_merge($itemData, ['session_id' => $sessionId]);
                
                // Use existing getCartItemData for validation
                $cartItemData = $this->getCartItemData($itemDataWithSession, $cart->id);
                
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
            $this->calculateTotalPrice($cart);
            $this->couponService->checkAndApplyCouponIfExists($cart);

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
            if (!$item) {
                return ['error' => 'Product variant not found'];
            }
            $existing   = $this->cartItemRepo->variantExistsInCart($cartId, $item->id);
            $currentQty = $existing ? $existing->quantity : 0;
            $totalQty   = $currentQty + $data['quantity'];
            $validation = $this->productValidator->validateVariant($item, $totalQty);
            $type       = 'variant';
        } elseif (!empty($data['product_set_item_id'])) {
            $item       = $this->productSetRepo->find($data['product_set_item_id']);
            if (!$item) {
                return ['error' => 'Product set item not found'];
            }
            $existing   = $this->cartItemRepo->setItemExistsInCart($cartId, $item->id);
            // Assuming ProductSetItems has a quantity or we just allow adding it
            $type       = 'set';
            $validation = true; // You might want to add validation logic for sets later
        } else {
            $item = $this->productRepo->find($data['product_id']);
            if (!$item) {
                return ['error' => 'Product not found'];
            }
            if ($item->has_variants) {
                return ['error' => 'Product has variants and cannot be added to cart directly. Please select a variant.'];
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

    private function resolveUserFromRequest(): ?User
    {
        // لو فيه يوزر محمّل بالفعل
        if (auth()->check()) {
            return auth()->user();
        }

        // لو فيه Bearer token في الهيدر
        $token = request()->bearerToken();
        if (!$token) {
            return null;
        }

        try {
            return JWTAuth::setToken($token)->authenticate(); // بيرجّع User أو null
        } catch (\Throwable $e) {
            return null; // أي مشكلة في التوكن => نعامل الطلب كضيف
        }
    }


}
