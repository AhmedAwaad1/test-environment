<?php

namespace App\Http\Services\Product;

use App\Repositories\ProductAttributeFilter\ProductAttributeFilterRepository;
use App\Http\Resources\ProductAttributeFilter\ProductAttributeFilterResource;
use App\Http\Resources\Product\ProductResource;
use App\Http\Resources\PaginationResource\PaginationResource;
use Illuminate\Support\Facades\Response;

class ProductAttributeFilterService
{
    public function __construct(protected ProductAttributeFilterRepository $repository)
    {
    }

    public function getActiveFilters()
    {
        try {
            $attributes = $this->repository->getActiveFilters();

            $resource = ProductAttributeFilterResource::collection($attributes);

            // Filter out attributes with no values
            $filtered = collect($resource->resolve())
                ->filter(fn($attr) => !empty($attr['values']))
                ->values();

            return Response::successResponse($filtered, 'Product attribute filters retrieved successfully');
        } catch (\Exception $e) {
            return Response::handleException($e, 'Failed to retrieve product attribute filters');
        }
    }

    public function filterProducts($request)
    {
        try {
            $filters = $request->input('filters', []);
            $perPage = $request->input('per_page');

            $products = $this->repository->filterProducts($filters, $perPage);

            $resource = $perPage
                ? new PaginationResource($products, ProductResource::class)
                : ProductResource::collection($products);

            return Response::successResponse($resource, 'Products filtered successfully');
        } catch (\Exception $e) {
            return Response::handleException($e, 'Failed to filter products');
        }
    }
}
