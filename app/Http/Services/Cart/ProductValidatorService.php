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
            return Response::errorResponse('Product not found', 404);
        }

        if ($product->quantity < $quantity) {
            return Response::errorResponse('Insufficient stock for the product', 400);
        }

        if (!$product->is_active) {
            return Response::errorResponse('Product is not active', 400);
        }

        return true;
    }

    public function validateVariant(?ProductVariant $variant, int $quantity)
    {
        if (!$variant) {
            return Response::errorResponse('Product variant not found', 404);
        }

        if ($variant->quantity < $quantity) {
            return Response::errorResponse('Insufficient stock for the product variant', 400);
        }

        if (!$variant->is_active) {
            return Response::errorResponse('Product variant is not active', 400);
        }

        return true;
    }
}
