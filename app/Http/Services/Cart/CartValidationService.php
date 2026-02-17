<?php

namespace App\Http\Services\Cart;

use App\Repositories\Cart\CartRepositoryInterface;
use App\Repositories\CartItem\CartItemRepository;
use App\Repositories\Product\ProductRepositoryInterface;
use App\Repositories\ProductVariant\ProductVariantRepository;
use App\Repositories\ProductSetItems\ProductSetItemsRepository;

class CartValidationService
{
    public function __construct(
        protected CartItemRepository $cartItemRepo,
        protected ProductRepositoryInterface $productRepo,
        protected ProductVariantRepository $productVarRepo,
        protected ProductValidatorService $productValidator,
        protected ProductSetItemsRepository $productSetRepo
    ) {}

    /**
     * Get cart item data and validate availability.
     */
    public function getCartItemData(array $data, $cartId): array
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
            $type       = 'set';
            $validation = true; 
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
}
