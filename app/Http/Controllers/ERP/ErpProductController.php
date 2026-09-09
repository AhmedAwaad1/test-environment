<?php

namespace App\Http\Controllers\ERP;

use App\Http\Controllers\Controller;
use App\Http\Requests\ERP\ErpProductStoreRequest;
use App\Http\Requests\ERP\ErpProductUpdateRequest;
use App\Http\Requests\ERP\ErpProductIndexRequest;
use App\Http\Services\ERP\ErpProductService;

class ErpProductController extends Controller
{
    public function __construct(
        protected ErpProductService $erpProductService
    ) {}

    public function store(ErpProductStoreRequest $request)
    {
        return $this->erpProductService->storeProduct($request->validated());
    }

    public function update(ErpProductUpdateRequest $request, string $sku)
    {
        return $this->erpProductService->updateProduct($sku, $request->validated());
    }

    public function index(ErpProductIndexRequest $request)
    {
        return $this->erpProductService->listProducts($request->integer('per_page', 15));
    }

    public function show(string $sku)
    {
        return $this->erpProductService->getProduct($sku);
    }

    public function destroy(string $sku)
    {
        return $this->erpProductService->deleteProduct($sku);
    }
}
