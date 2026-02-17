<?php

namespace App\Http\Services\Product;

use App\Http\Resources\PaginationResource\PaginationResource;
use App\Http\Resources\Product\ProductResource;
use App\Http\Resources\Product\ProductListResource;
use App\Http\Resources\ProductVariant\ProductVariantResource;
use App\Http\Services\GeoCurrency\GeoCurrencyService;
use App\Repositories\Product\ProductRepositoryInterface;
use App\Repositories\ProductVariant\ProductVariantRepository;
use App\Helpers\CacheHelper;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class ProductService
{
    public function __construct(
        protected ProductRepositoryInterface $productRepo,
        protected ProductVariantRepository $productVariantRepo,
        protected GeoCurrencyService     $geoCurrencyService,
        protected ProductVariantService  $productVariantService,
        protected ProductImageService    $productImageService,
        protected ProductCacheService    $productCacheService,
        protected ProductFilterService   $productFilterService
    ) {}

    /**
     * Get all products (with optional filters)
     */
    public function getAllProducts($request)
    {
        session()->forget('currency_id');

        if ($sid = $request->get('session_id')) {
            Cache::forget("currency_id_{$sid}");
        }

        $currency = $this->geoCurrencyService->getCurrencyForRequest();
        $filters = $this->productFilterService->getRelevantFilters($request);

        $data = $this->productCacheService->remember($filters, function () use ($currency, $filters) {
            $products = $this->productRepo->getAll($filters);

            if ($filters['is_list_resource'] ?? false) {
                return [
                    'data' => ProductListResource::collection($products)->resolve(),
                    'meta' => [
                        'current_page' => $products->currentPage(),
                        'last_page' => $products->lastPage(),
                        'per_page' => $products->perPage(),
                        'total' => $products->total(),
                    ],
                    'currency' => $currency?->name,
                    'currency_id' => $currency?->id,
                ];
            }

            if ($filters['should_paginate'] ?? false) {
                $resource = new PaginationResource($products, ProductResource::class);
            } else {
                $resource = ProductResource::collection($products);
            }

            return $resource->additional([
                'currency'    => $currency?->name,
                'currency_id' => $currency?->id,
            ])->resolve();
        });

        return Response::successResponse($data, 'Products retrieved successfully');
    }

    /**
     * Find product by ID
     */
    public function findProduct($id)
    {
        $currency = $this->geoCurrencyService->getCurrencyForRequest();
        
        $data = $this->productCacheService->remember(['id' => $id], function () use ($id, $currency) {
            $product = $this->productRepo->findWithVariants($id);

            if (!$product) {
                return null;
            }

            return (new ProductResource($product))->additional([
                'currency'    => $currency?->name,
                'currency_id' => $currency?->id,
            ])->resolve();
        });

        if (!$data) {
            return Response::errorResponse('Product not found', [], 404);
        }

        return Response::successResponse($data, 'Product found successfully');
    }

    /**
     * Create product
     */
    public function createProduct(array $data)
    {
        return DB::transaction(function () use ($data) {
            $product = $this->productRepo->create($data);

            if (!empty($data['images'])) {
                $this->productImageService->handleProductImages($product, $data['images']);
            }

            if (!empty($data['prices'])) {
                $this->productRepo->createProductPrices($product, $data['prices']);
            }

            $optionValueMap = [];
            if (!empty($data['options'])) {
                $optionValueMap = $this->productVariantService->createOptionsAndValues($product->id, $data['options']);
            }

            if (!empty($data['variants'])) {
                $this->productVariantService->createVariants($product->id, $data['variants'], $optionValueMap);
            }

            return Response::successResponse(
                new ProductResource(
                    $product->load('productVariants', 'productOptions.values', 'productPrices.currency')
                ),
                'Product created successfully',
                201
            );
        });
    }

    /**
     * Update product
     */
    public function updateProduct($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            $product = $this->productRepo->find($id);
            if (!$product) {
                return Response::errorResponse('Product not found', [], 404);
            }

            $product = $this->productRepo->update($id, $data);

            if (isset($data['prices'])) {
                $this->productRepo->updateProductPrices($product, $data['prices']);
            }

            if (isset($data['images'])) {
                $this->productImageService->handleProductImages($product, $data['images']);
            }

            if (isset($data['deleted_images'])) {
                $this->productImageService->deleteProductImages($product, $data['deleted_images']);
            }

            $this->productVariantService->handleProductVariantsUpdate($product, $data);

            // Fetch a completely fresh product with all relationships loaded for the response
            $freshProduct = $this->productRepo->findWithVariants($product->id);

            return Response::successResponse(
                new ProductResource($freshProduct),
                'Product updated successfully'
            );
        });
    }

    /**
     * Delete product
     */
    public function deleteProduct($id)
    {
        $isDeleted = $this->productRepo->delete($id);

        if (!$isDeleted) {
            return Response::errorResponse('Failed to delete product', [], 400);
        }

        return Response::successResponse(
            ['is_success' => true],
            'Product deleted successfully'
        );
    }
}
