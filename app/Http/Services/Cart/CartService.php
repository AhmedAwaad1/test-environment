<?php

namespace App\Http\Services\Cart;

use App\Http\Resources\PaginationResource\PaginationResource;
use App\Http\Resources\Cart\CartResource;
use App\Models\Cart;
use App\Repositories\Cart\CartRepository;
use App\Repositories\CartItem\CartItemRepository;
use App\Repositories\ProductVariant\ProductVariantRepository;
use App\Repositories\PromoCode\PromoCodeRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class CartService
{
    protected $cartRepo, $cartItemRepo, $productVarRepo, $couponRepo;

    public function __construct(
        CartRepository $cartRepo,
        CartItemRepository $cartItemRepo,
        ProductVariantRepository $productVarRepo,
        PromoCodeRepository $couponRepo
    )
    {
        $this->cartRepo = $cartRepo;
        $this->cartItemRepo = $cartItemRepo;
        $this->productVarRepo = $productVarRepo;
        $this->couponRepo = $couponRepo;
    }

    // public function getCartByUserId($id)
    // {
    //     try {
    //         $cart = $this->cartRepo->find($id);

    //         if (!$cart) {
    //             return Response::errorResponse('cart not found', [], 404);
    //         }

    //         return Response::successResponse(new CartResource($cart), 'cart found successfully');
    //     } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
    //         //exception if id not found
    //         return Response::handleModelNotFoundException($e, 'cart');
    //     } catch (\Exception $e) {
    //         return Response::handleException($e, 'Failed to retrieve cart');
    //     }
    // }

    public function addToCart($data)
    {
        try {
            DB::beginTransaction();

            $user = Auth::user();

            $cart = $this->cartRepo->findOrCreateUserCart($user->id);

            $productVariant = $this->productVarRepo->findVariantByProductId($data);

            //
            $validateProductVariant = $this->validateProductVariant($productVariant, $data['quantity']);

            if ($validateProductVariant !== true) {
                return $validateProductVariant;
            }

            $unitPrice = $productVariant->price_after_discount ?? $productVariant->price;

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
                    'product_variant_id' => $productVariant->id,
                    'quantity'           => $data['quantity'],
                    'price'              => $productVariant->price_after_discount ?? $productVariant->price,
                    'total_price'        => $totalPrice,
                ]);
            }

            $this->calculateTotalPrice($cart);

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

            DB::commit();
            return Response::successResponse(new CartResource($cart), 'Cart item quantity updated successfully');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            //exception if id not found
            return Response::handleModelNotFoundException($e, 'cart');
        } catch (\Exception $e) {
            return Response::handleException($e, 'update cart');
        }
    }

    // public function deleteCart($id)
    // {
    //     $cart = $this->cartRepo->delete($id);

    //     if (!$cart) {
    //         return Response::errorResponse('cart not found', [], 404);
    //     }

    //     return Response::successResponse(['is_success' => 1], 'cart deleted successfully');
    // }

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
        $user = Auth::user();

        $cart = $this->cartRepo->findUserCart($user->id);
        if (!$cart) {
            return Response::errorResponse('Cart not found', [], 404);
        }

        $total = $cart->total_price;

        if ($cart->coupon_code) {
            $coupon = $this->couponRepo->findByCode($cart->coupon_code);

            if ($coupon && $coupon->is_active) {
                $cart->discount_amount = $coupon->discount_percentage ? ($total * $coupon->discount_percentage) / 100 : null;
                $cart->total_price_after_discount = $total - $cart->discount_amount;
            } else {
                $cart->discount_amount = 0;
                $cart->total_price_after_discount = null;
            }
        } else {
            $cart->discount_amount = 0;
            $cart->total_price_after_discount = null;
        }

        $cart->save();
    }

}
