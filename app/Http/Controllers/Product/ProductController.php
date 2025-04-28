<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\ProductRequest;
use App\Http\Services\Product\ProductService;

class ProductController extends Controller
{
    public $productService;
    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function index(ProductRequest $request)
    {
        return $this->productService->getAllProducts($request);
    }

    public function show(ProductRequest $request)
    {
        return $this->productService->getProductById($request->id);
    }

    public function store(ProductRequest $request)
    {
        dd('a');
        return $this->productService->createProduct($request->validated());
    }

    public function update(ProductRequest $request, $id)
    {
        return $this->productService->updateProduct($id, $request->validated());
    }

    public function destroy(ProductRequest $request)
    {
        return $this->productService->deleteProduct($request->id);
    }
}
