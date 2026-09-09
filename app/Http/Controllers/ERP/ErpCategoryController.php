<?php

namespace App\Http\Controllers\ERP;

use App\Http\Controllers\Controller;
use App\Http\Requests\ERP\ErpCategoryRequest;
use App\Http\Services\ERP\ErpCategoryService;

class ErpCategoryController extends Controller
{
    public function __construct(
        protected ErpCategoryService $erpCategoryService
    ) {}

    public function store(ErpCategoryRequest $request)
    {
        return $this->erpCategoryService->syncCategories($request->validated('categories'));
    }

    public function index()
    {
        return $this->erpCategoryService->listCategories();
    }
}
