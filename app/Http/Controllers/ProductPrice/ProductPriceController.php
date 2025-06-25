<?php

namespace App\Http\Controllers\ProductPrice;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductPrice\ProductPriceRequest;
use App\Http\Resources\PaginationResource\PaginationResource;
use App\Http\Resources\ProductPrice\ProductPriceResource;
use App\Http\Services\ProductPrice\ProductPriceService;

class ProductPriceController extends Controller
{
    public ProductPriceService $service;

    public function __construct(ProductPriceService $service) {
        $this->service = $service;
    }

    public function index(ProductPriceRequest $request)
    {
        return $this->service->getAllProductPrices($request);
    }

    public function show($id)
    {
        return $this->service->findProductPrice($id);
    }

    public function store(ProductPriceRequest $request)
    {
        return $this->service->createProductPrice($request->validated());
    }

    public function update($id, ProductPriceRequest $request)
    {
        return $this->service->updateProductPrice($id, $request->validated());
    }

    public function destroy($id)
    {
        return $this->service->deleteProductPrice($id);
    }
} 