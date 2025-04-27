<?php

namespace App\Http\Controllers\ProductType;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductType\ProductTypeRequest;
use App\Http\Services\ProductType\ProductTypeService;

class ProductTypeController extends Controller
{
    public $productTypeService;
    public function __construct(ProductTypeService $productTypeService)
    {
        $this->productTypeService = $productTypeService;
    }

    public function index(ProductTypeRequest $request)
    {
        return $this->productTypeService->getAllProductTypes($request);
    }

    public function show(ProductTypeRequest $request)
    {
        return $this->productTypeService->getProductTypeById($request->id);
    }

    public function store(ProductTypeRequest $request)
    {
        return $this->productTypeService->createProductType($request->validated());
    }

    public function update(ProductTypeRequest $request, $id)
    {
        return $this->productTypeService->updateProductType($id, $request->validated());
    }

    public function destroy(ProductTypeRequest $request)
    {
        return $this->productTypeService->deleteProductType($request->id);
    }
}
