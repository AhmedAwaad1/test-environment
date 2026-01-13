<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\ProductVariantRequest;
use App\Http\Services\Product\ProductVariantService;
use Illuminate\Http\Request;

class ProductVariantController extends Controller
{
    public function __construct(
        protected ProductVariantService $variantService
    ) {}

    /**
     * Get all variants for a product
     */
    public function index($productId)
    {
        return $this->variantService->getProductVariants($productId);
    }

    /**
     * Store a new variant
     */
    public function store(ProductVariantRequest $request)
    {
        $productId = $request->product_id;
        return $this->variantService->createVariant($productId, $request->validated());
    }

    /**
     * Update variant details
     */
    public function update($variantId, ProductVariantRequest $request)
    {
        return $this->variantService->updateVariant($variantId, $request->validated());
    }

    /**
     * Update variant stock
     */
    public function updateStock($variantId, Request $request)
    {
        $request->validate([
            'quantity' => 'required|integer|min:0'
        ]);

        return $this->variantService->updateVariantStock($variantId, $request->quantity);
    }

    /**
     * Toggle variant status
     */
    public function toggleStatus($variantId)
    {
        return $this->variantService->toggleVariantStatus($variantId);
    }

    /**
     * Delete a variant
     */
    public function destroy($variantId)
    {
        return $this->variantService->deleteVariant($variantId);
    }

    /**
     * Get variant by selected options
     */
    public function getByOptions($productId, Request $request)
    {
        $request->validate([
            'option_values' => 'required|array',
            'option_values.*.id' => 'required|exists:product_option_values,id'
        ]);

        return $this->variantService->getVariantByOptions($productId, $request->option_values);
    }
}
