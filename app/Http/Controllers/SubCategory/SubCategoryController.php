<?php

namespace App\Http\Controllers\SubCategory;

use App\Http\Controllers\Controller;
use App\Http\Requests\SubCategory\SubCategoryRequest;
use App\Http\Services\SubCategory\SubCategoryService;

class SubCategoryController extends Controller
{
    public $subCategoryService;
    public function __construct(SubCategoryService $subCategoryService)
    {
        $this->subCategoryService = $subCategoryService;
    }

    public function index(SubCategoryRequest $request)
    {
        return $this->subCategoryService->getAllSubCategories($request);
    }

    public function show(SubCategoryRequest $request)
    {
        return $this->subCategoryService->getSubCategoryById($request->id);
    }

    public function store(SubCategoryRequest $request)
    {
        return $this->subCategoryService->createSubCategory($request->validated());
    }

    public function update(SubCategoryRequest $request, $id)
    {
        return $this->subCategoryService->updateSubCategory($id, $request->validated());
    }

    public function destroy(SubCategoryRequest $request)
    {
        return $this->subCategoryService->deleteSubCategory($request->id);
    }
}
