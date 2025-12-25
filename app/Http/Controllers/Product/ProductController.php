<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\ProductRequest;
use App\Http\Services\Product\ProductService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(protected ProductService $service)
    {
    }

    /**
     * Get all products (with optional filters)
     * ?is_best_seller=1
     * ?is_new_arrival=1
     * ?per_page=10
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

        // Ensure boolean flags are stored correctly
        $data['is_best_seller'] = $request->boolean('is_best_seller');
        $data['is_new_arrival'] = $request->boolean('is_new_arrival');

        return $this->service->createProduct($data);
    }

    /**
     * Update product
     */
    public function update($id, ProductRequest $request)
    {
        $data = $request->validated();

        if ($request->has('is_best_seller')) {
            $data['is_best_seller'] = $request->boolean('is_best_seller');
        }

        if ($request->has('is_new_arrival')) {
            $data['is_new_arrival'] = $request->boolean('is_new_arrival');
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
     * /api/products/best-sellers
     */
    public function bestSellers(Request $request)
    {
        return $this->service->getBestSellers($request);
    }

    /**
     * New arrivals endpoint
     * /api/products/new-arrivals
     */
    public function newArrivals(Request $request)
    {
        return $this->service->getNewArrivals($request);
    }
}
