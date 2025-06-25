<?php

namespace App\Http\Controllers\ProductSetItems;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductSetItems\ProductSetItemsRequest;
use App\Http\Resources\PaginationResource\PaginationResource;
use App\Http\Resources\ProductSetItems\ProductSetItemsResource;
use App\Http\Services\ProductSetItems\ProductSetItemsService;

class ProductSetItemsController extends Controller
{
    public ProductSetItemsService $service;

    public function __construct(ProductSetItemsService $service) {
        $this->service = $service;
    }

    public function index(ProductSetItemsRequest $request)
    {
        return $this->service->getAllProductSetItems($request);
    }

    public function show($id)
    {
        return $this->service->findProductSetItems($id);
    }

    public function store(ProductSetItemsRequest $request)
    {
        return $this->service->createProductSetItems($request->validated());
    }

    public function update($id, ProductSetItemsRequest $request)
    {
        return $this->service->updateProductSetItems($id, $request->validated());
    }

    public function destroy($id)
    {
        return $this->service->deleteProductSetItems($id);
    }
} 