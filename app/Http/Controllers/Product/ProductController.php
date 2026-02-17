<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Traits\CanCastBooleans;
use App\Http\Requests\Product\ProductRequest;
use App\Http\Services\Product\ProductService;
use App\Http\Services\Product\ProductVariantService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    use CanCastBooleans;

    public function __construct(
        protected ProductService $service,
        protected ProductVariantService $variantService
    )
    {
    }

    /**
     * Get all products (with optional filters)
     */
    public function index(ProductRequest $request)
    {
        return $this->service->getAllProducts($request);
    }

    /**
     * Get single product
     */
    public function show($id)
    {
        return $this->service->findProduct($id);
    }

    /**
     * Store product
     */
    public function store(ProductRequest $request)
    {
        $data = $request->validated();

        $this->castBooleansInArray($data, ['is_best_seller', 'is_new_arrival', 'status']);

        return $this->service->createProduct($data);
    }

    /**
     * Update product
     */
    public function update($id, ProductRequest $request)
    {
        $data = $request->validated();

        $this->castBooleansInArray($data, ['is_best_seller', 'is_new_arrival', 'has_variants']);

        if ($request->has('status')) {
            $data['is_active'] = $this->castToBoolean($request->input('status'));
        }

        // Ensure variants and options are passed if they exist in the request
        // even if not explicitly in validated() (though they should be)
        if ($request->has('variants')) {
            $data['variants'] = $request->input('variants');
        }
        if ($request->has('options')) {
            $data['options'] = $request->input('options');
        }
        if ($request->has('images')) {
            $data['images'] = $request->allFiles()['images'] ?? $request->input('images');
        }

        return $this->service->updateProduct($id, $data);
    }

    /**
     * Delete product
     */
    public function destroy($id)
    {
        return $this->service->deleteProduct($id);
    }

    /**
     * Best sellers endpoint
     */
    public function bestSellers(Request $request)
    {
        $request->merge(['is_best_seller' => 1]);
        return $this->service->getAllProducts($request);
    }

    /**
     * New arrivals endpoint
     */
    public function newArrivals(Request $request)
    {
        $request->merge(['is_new_arrival' => 1]);
        return $this->service->getAllProducts($request);
    }
}