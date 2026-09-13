<?php

namespace App\Http\Controllers\ERP;

use App\Http\Controllers\Controller;
use App\Http\Requests\ERP\ErpProductStoreRequest;
use App\Http\Requests\ERP\ErpProductUpdateRequest;
use App\Http\Requests\ERP\ErpProductIndexRequest;
use App\Http\Requests\ERP\ErpBulkProductUpsertRequest;
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

    public function bulkUpsert(ErpBulkProductUpsertRequest $request)
    {
        return $this->erpProductService->bulkUpsertProducts($request->validated());
    }

    public function update(ErpProductUpdateRequest $request, string $externalId)
    {
        return $this->erpProductService->updateProduct($externalId, $request->validated());
    }

    public function index(ErpProductIndexRequest $request)
    {
        return $this->erpProductService->listProducts($request->integer('per_page', 15));
    }

    public function show(string $externalId)
    {
        return $this->erpProductService->getProduct($externalId);
    }

    public function destroy(string $externalId)
    {
        return $this->erpProductService->deleteProduct($externalId);
    }
}
