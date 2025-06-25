<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\ProductRequest;
use App\Http\Resources\PaginationResource\PaginationResource;
use App\Http\Resources\Product\ProductResource;
use App\Http\Services\Product\ProductService;

class ProductController extends Controller
{
    public function __construct(protected ProductService $service) {

    }

    public function index(ProductRequest $request)
    {
        return $this->service->getAllProducts($request);
    }

    public function show($id)
    {
        return $this->service->findProduct($id);
    }

    public function store(ProductRequest $request)
    {
        return $this->service->createProduct($request->validated());
    }

    public function update($id, ProductRequest $request)
    {
        return $this->service->updateProduct($id, $request->validated());
    }

    public function destroy($id)
    {
        return $this->service->deleteProduct($id);
    }
}

