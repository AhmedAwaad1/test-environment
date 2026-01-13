<?php

namespace App\Http\Services\Cart;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Response;

class ProductValidatorService
{
    public function validateProduct(?Product $product, int $quantity)
    {
        if (!$product) {
            return 'Product not found';
        }
        // Lock the product for update (pessimistic locking)
        $product = Product::where('id', $product->id)->lockForUpdate()->first();

        if ($product->quantity < $quantity) {
            return 'Insufficient stock for the product';
        }

        if (!$product->is_active) {
            return 'Product is not active';
        }

        return true;
    }

    public function validateVariant(?ProductVariant $variant, int $quantity)
    {
        if (!$variant) {
            return 'Product variant not found';
        }

        // Lock the variant for update (pessimistic locking)
        $variant = ProductVariant::where('id', $variant->id)->lockForUpdate()->first();
        
        if ($variant->quantity < $quantity) {
            return 'Insufficient stock for the product variant';
        }

        if (!$variant->is_active) {
            return 'Product variant is not active';
        }

        return true;
    }
}
