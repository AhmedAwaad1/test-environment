<?php

namespace App\Http\Services\Cart;

use App\Http\Resources\Cart\CartResource;
use App\Models\Cart;
use App\Repositories\Cart\CartRepository;
use App\Repositories\CartItem\CartItemRepository;
use App\Repositories\Product\ProductRepository;
use App\Repositories\ProductVariant\ProductVariantRepository;
use App\Repositories\PromoCode\PromoCodeRepository;
use App\Repositories\VariantSize\VariantSizeRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class CartService
{
    protected $cartRepo, $cartItemRepo, $productVarRepo, $couponRepo, $variantSizeRepo, $productRepo;

    public function __construct(
        CartRepository $cartRepo,
        CartItemRepository $cartItemRepo,
        ProductVariantRepository $productVarRepo,
        VariantSizeRepository $variantSizeRepo,
        PromoCodeRepository $couponRepo,
        ProductRepository $productRepo
    )
    {
        $this->cartRepo = $cartRepo;
        $this->cartItemRepo = $cartItemRepo;
        $this->productVarRepo = $productVarRepo;
        $this->couponRepo = $couponRepo;
        $this->variantSizeRepo = $variantSizeRepo;
        $this->productRepo = $productRepo;
    }

    public function getUserCart()
    {
        try{
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

    public function addToCart($data)
    {
        try {
            DB::beginTransaction();

            $user = Auth::user();

            $cart = $this->cartRepo->findOrCreateUserCart($user->id);

            //get product data and check if it's a variant or not
            $productData = $this->getProductData($data);

            $unitPrice = $productVariant->price_after_discount ?? $productVariant->price;

            // Calculate the total price for the item based on its quantity
            $totalPrice = $this->calculateItemPrice($unitPrice, $data['quantity']);

            $existingItem = $this->cartItemRepo->findByCartAndVariant($cart->id, $productVariant->id);

            if ($existingItem) {
                //update existing item
                $existingItem->quantity += $data['quantity'];
                $existingItem->price = $unitPrice;
                $existingItem->total_price = $this->calculateItemPrice($existingItem->price, $existingItem->quantity);
                $existingItem->save();
            }else {
                //create new item
                $cartItem = $this->cartItemRepo->create([
                    'cart_id'            => $cart->id,
                    'product_variant_id' => $productData['variant_id'],
                    'product_id'         => $data['product_id'],
                    'quantity'           => $data['quantity'],
                    'price'              => $productVariant->price_after_discount ?? $productVariant->price,
                    'total_price'        => $totalPrice,
                ]);
            }

            $this->calculateTotalPrice($cart);
            // Check if the cart has a coupon code applied
            if($cart->coupon_code != null){
                $coupon = $this->couponRepo->findByCode($cart->coupon_code);
                if($coupon != null){
                    $this->calculateTotalPriceAfterDiscount($cart, $coupon);
                }
            }

            DB::commit();
            return Response::successResponse(new CartResource($cart), 'new item added to cart', 201);

        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            return Response::handleDatabaseException($e, 'create cart');
        } catch (\Exception $e) {
            DB::rollBack();
            return Response::handleException($e, 'create cart');
        }
    }

    public function updateCartItemQuantity($cartItemId, $data)
    {
        try {
            DB::beginTransaction();

            $user = Auth::user();
            $cart = $this->cartRepo->findUserCart($user->id);

            if (!$cart) {
                return Response::errorResponse('Cart not found', [], 404);
            }

            $cartItem = $this->cartItemRepo->findById($cartItemId);

            if (!$cartItem || $cartItem->cart_id !== $cart->id) {
                return Response::errorResponse('Cart item not found in user cart', [], 404);
            }

            $productVariant = $this->productVarRepo->find($cartItem->product_variant_id);

            $validationResponse = $this->validateProductVariant($productVariant, $data['quantity']);

            if ($validationResponse !== true) {
                return $validationResponse;
            }

            $cartItem->quantity = $data['quantity'];
            $cartItem->price = $productVariant->price_after_discount ?? $productVariant->price;
            $cartItem->total_price = $this->calculateItemPrice($cartItem->price, $data['quantity']);
            $cartItem->save();

            $this->calculateTotalPrice($cart);

            // Check if the cart has a coupon code applied
            if($cart->coupon_code != null){
                $coupon = $this->couponRepo->findByCode($cart->coupon_code);
                if($coupon != null){
                    $this->calculateTotalPriceAfterDiscount($cart, $coupon);
                }
            }

            DB::commit();
            return Response::successResponse(new CartResource($cart), 'Cart item quantity updated successfully');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            //exception if id not found
            return Response::handleModelNotFoundException($e, 'cart');
        } catch (\Exception $e) {
            return Response::handleException($e, 'update cart');
        }
    }

    private function getProductData($data)
    {
        // Check if the product is not a variant
        if (!$data['size_id'] && !$data['color_id']) {
            $product = $this->productRepo->find($data['product_id']);

            if ($product->quantity < $data['quantity']) {
                return Response::errorResponse('Not enough quantity in stock', [], 400);
            }

            return [
                'variant_id' => null,
                'product_id' => $product->id,
                'unit_price' => $product->price_after_discount ?? $product->price,
            ];
        } else {
            // Check if the product is a variant
            $variant = $this->productVarRepo->findVariantByProductId($data);
            if (!$variant) {
                return Response::errorResponse('Product variant not found', [], 404);
            }

            if (!$variant->is_active) {
                return Response::errorResponse('Product variant is not available', [], 400);
            }

            $variantSize = $this->variantSizeRepo->findVariantSizeByVariantId($variant->id, $data['size_id']);
            if (!$variantSize || $variantSize->quantity < $data['quantity']) {
                return Response::errorResponse('Not enough quantity in stock', [], 400);
            }

            return [
                'variant_id' => $variant->id,
                'product_id' => $variant->product_id,
                'unit_price' => $variant->price_after_discount ?? $variant->price,
            ];
        }
    }

    public function deleteCartItem($cartItemId)
    {
        $user = Auth::user();

        $cart = $this->cartRepo->findUserCart($user->id);
        if (!$cart) {
            return Response::errorResponse('Cart not found', [], 404);
        }

        $cartItem = $this->cartItemRepo->findById($cartItemId);

        if (!$cartItem || $cartItem->cart_id !== $cart->id) {
            return Response::errorResponse('Cart item not found or unauthorized', [], 403);
        }

        $cartItem->delete();

        // Recalculate the total price of the cart
        $this->calculateTotalPrice($cart);
        if($cart->coupon_code != null){
            $coupon = $this->couponRepo->findByCode($cart->coupon_code);
            if($coupon != null){
                $this->calculateTotalPriceAfterDiscount($cart, $coupon);
            }
        }
        return Response::successResponse(null, 'Cart item deleted successfully', 200);
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

    private function validateProductVariant($variant, $requestedQty)
    {
        if (!$variant) {
            return Response::errorResponse('Product variant not found', [], 404);
        }

        if (!$variant->is_active) {
            return Response::errorResponse('Product variant is not available', [], 400);
        }

        if ($variant->quantity < $requestedQty) {
            return Response::errorResponse('Not enough quantity in stock', [], 400);
        }

        return true;
    }

    public function applyCoupon($data)
    {
        try {
            $user = Auth::user();
            $cart = $this->cartRepo->findUserCart($user->id);

            if (!$cart) {
                return Response::errorResponse('Cart not found', [], 404);
            }

            $coupon = $this->couponRepo->findByCode($data['coupon_code']);

            if (!$coupon) {
                return Response::errorResponse('Coupon not found', [], 404);
            }

            if (!$coupon->is_active) {
                return Response::errorResponse('Coupon is not active', [], 400);
            }

            // Check if user used this coupon before
            // if ($user->usedCoupons()->where('coupon_id', $coupon->id)->exists()) {
            //     return Response::errorResponse('You have already used this coupon', [], 400);
            // }

            $this->calculateTotalPriceAfterDiscount($cart, $coupon);

            return Response::successResponse(new CartResource($cart), 'Coupon applied successfully');

        } catch (\Exception $e) {
            return Response::handleException($e, 'apply coupon');
        }
    }

    public function removeCoupon()
    {
        try {
            $user = Auth::user();
            $cart = $this->cartRepo->findUserCart($user->id);

            if (!$cart) {
                return Response::errorResponse('Cart not found', [], 404);
            }

            $cart->coupon_code = null;
            $cart->discount_amount = 0;
            $cart->total_price_after_discount = null;
            $cart->save();

            return Response::successResponse(new CartResource($cart), 'Coupon removed successfully');

        } catch (\Exception $e) {
            return Response::handleException($e, 'remove coupon');
        }
    }
    public function calculateTotalPriceAfterDiscount(Cart $cart, $coupon)
    {
        $total = $cart->total_price;

        if ($coupon->discount_percentage) {
            $cart->coupon_code = $coupon->code;
            $cart->discount_amount = ($total * $coupon->discount_percentage) / 100;
        } else {
            $cart->discount_amount = 0;
        }

        $cart->total_price_after_discount = $total - $cart->discount_amount;
        $cart->save();

        return $cart->total_price_after_discount;
    }
}
