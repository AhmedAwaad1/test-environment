<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\ProductOptionRequest;
use App\Http\Requests\Product\ProductOptionValueRequest;
use App\Http\Services\Product\ProductOptionService;
use Illuminate\Http\Request;

class ProductOptionController extends Controller
{
    public function __construct(
        protected ProductOptionService $optionService
    ) {}

    /**
     * Get all options for a product
     */
    public function index($productId)
    {
        return $this->optionService->getProductOptions($productId);
    }

    /**
     * Add new option to product
     */
    public function store($productId, ProductOptionRequest $request)
    {
        return $this->optionService->addProductOption($productId, $request->validated());
    }

    /**
     * Update an existing option
     */
    public function update($optionId, ProductOptionRequest $request)
    {
        return $this->optionService->updateOption($optionId, $request->validated());
    }

    /**
     * Delete an option
     */
    public function destroy($optionId)
    {
        return $this->optionService->deleteOption($optionId);
    }

    /**
     * Add value to an option
     */
    public function addValue($optionId, ProductOptionValueRequest $request)
    {
        return $this->optionService->addOptionValue($optionId, $request->validated());
    }

    /**
     * Update option value
     */
    public function updateValue($valueId, ProductOptionValueRequest $request)
    {
        return $this->optionService->updateOptionValue($valueId, $request->validated());
    }

    /**
     * Delete option value
     */
    public function deleteValue($valueId)
    {
        return $this->optionService->deleteOptionValue($valueId);
    }

    /**
     * Reorder option values
     */
    public function reorderValues($optionId, Request $request)
    {
        return $this->optionService->reorderOptionValues($optionId, $request->order);
    }
}
