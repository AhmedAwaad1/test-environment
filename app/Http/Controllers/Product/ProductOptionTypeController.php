<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\ProductOptionTypeRequest;
use App\Http\Services\Product\ProductOptionTypeService;

class ProductOptionTypeController extends Controller
{
    public function __construct(protected ProductOptionTypeService $service)
    {
    }

    /**
     * Get all available product option types (e.g., Color, Size).
     */
    public function index(ProductOptionTypeRequest $request)
    {
        return $this->service->getAll($request);
    }

    /**
     * Store a new product option type.
     */
    public function store(ProductOptionTypeRequest $request)
    {
        return $this->service->create($request->validated());
    }

    /**
     * Get a specific product option type.
     */
    public function show($id)
    {
        return $this->service->find($id);
    }

    /**
     * Update a specific product option type.
     */
    public function update($id, ProductOptionTypeRequest $request)
    {
        return $this->service->update($id, $request->validated());
    }

    /**
     * Delete a specific product option type.
     */
    public function destroy($id)
    {
        return $this->service->delete($id);
    }
}
