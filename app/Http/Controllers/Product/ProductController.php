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

        // Consistent Fix: Ensure strings "1"/"0" from FormData are handled as booleans
        $data['is_best_seller'] = $request->input('is_best_seller') == '1' || $request->input('is_best_seller') === 'true';
        $data['is_new_arrival'] = $request->input('is_new_arrival') == '1' || $request->input('is_new_arrival') === 'true';
        $data['status'] = $request->input('status') == '1' || $request->input('status') === 'true';

        return $this->service->createProduct($data);
    }

    /**
     * Update product
     */
    public function update($id, ProductRequest $request)
    {
        $data = $request->validated();

        // Fix: Manual boolean casting for multipart/form-data updates
        if ($request->has('is_best_seller')) {
            $data['is_best_seller'] = $request->input('is_best_seller') == '1' || $request->input('is_best_seller') === 'true';
        }

        if ($request->has('is_new_arrival')) {
            $data['is_new_arrival'] = $request->input('is_new_arrival') == '1' || $request->input('is_new_arrival') === 'true';
        }

        if ($request->has('status')) {
            $data['status'] = $request->input('status') == '1' || $request->input('status') === 'true';
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
        return $this->service->getBestSellers($request);
    }

    /**
     * New arrivals endpoint
     */
    public function newArrivals(Request $request)
    {
        return $this->service->getNewArrivals($request);
    }
}