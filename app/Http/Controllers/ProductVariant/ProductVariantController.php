<?php

namespace App\Http\Controllers\ProductVariant;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductVariant\ProductVariantRequest;
use App\Http\Services\ProductVariant\ProductVariantService;

class ProductVariantController extends Controller
{
    public $productVariantService;
    public function __construct(ProductVariantService $productVariantService)
    {
        $this->productVariantService = $productVariantService;
    }

    public function index(ProductVariantRequest $request)
    {
        return $this->productVariantService->getAllProductVariants($request);
    }

    public function show(ProductVariantRequest $request)
    {
        return $this->productVariantService->getProductVariantById($request->id);
    }

    public function store(ProductVariantRequest $request)
    {
        return $this->productVariantService->createProductVariant($request->validated());
    }

    public function update(ProductVariantRequest $request, $id)
    {
        return $this->productVariantService->updateProductVariant($id, $request->validated());
    }

    public function destroy(ProductVariantRequest $request)
    {
        return $this->productVariantService->deleteProductVariant($request->id);
    }
}
