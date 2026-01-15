<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Http\Services\Product\ProductAttributeFilterService;
use Illuminate\Http\Request;

class ProductAttributeFilterController extends Controller
{
    public function __construct(protected ProductAttributeFilterService $service)
    {
    }

    /**
     * Get all active product attributes and their unique values
     * that are currently assigned to available ProductVariants.
     */
    public function index()
    {
        return $this->service->getActiveFilters();
    }

    /**
     * Filter products by attribute name and value(s)
     * 
     * Request body example:
     * {
     *   "filters": [
     *     { "attribute_name": "Size", "value": "Earthenware" },
     *     { "attribute_name": "Color", "value": "White" }
     *   ],
     *   "per_page": 10
     * }
     */
    public function filter(Request $request)
    {
        return $this->service->filterProducts($request);
    }
}
