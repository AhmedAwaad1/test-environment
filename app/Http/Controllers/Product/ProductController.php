<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\ProductRequest;
use App\Http\Resources\PaginationResource\PaginationResource;
use App\Http\Resources\Product\ProductResource;
use App\Services\Product\ProductService;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function __construct(
        protected ProductService $service
    ) {}

    public function index(ProductRequest $request): JsonResponse
    {
        $products = $this->service->getAll($request);
        return response()->json(new PaginationResource($products, ProductResource::class));
    }

    public function show($id): JsonResponse
    {
        $product = $this->service->find($id);
        return response()->json(new ProductResource($product));
    }

    public function store(ProductRequest $request): JsonResponse
    {
        $product = $this->service->create($request);
        return response()->json(new ProductResource($product), 201);
    }

    public function update($id, ProductRequest $request): JsonResponse
    {
        $product = $this->service->update($id, $request);
        return response()->json(new ProductResource($product));
    }

    public function destroy($id): JsonResponse
    {
        $this->service->delete($id);
        return response()->json(null, 204);
    }
}

