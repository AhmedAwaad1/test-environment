<?php

namespace App\Http\Controllers\Category;

use App\Http\Controllers\Controller;
use App\Http\Requests\Category\CategoryRequest;
use App\Http\Services\Category\CategoryService;

class CategoryController extends Controller
{
    public $categoryService;
    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    public function index(CategoryRequest $request)
    {
        return $this->categoryService->getAllCategories($request);
    }

    public function show(CategoryRequest $request)
    {
        return $this->categoryService->getCategoryById($request->id);
    }

    public function store(CategoryRequest $request)
    {
        return $this->categoryService->createCategory($request->validated());
    }

    public function update(CategoryRequest $request, $id)
    {
        return $this->categoryService->updateCategory($id, $request->validated());
    }

    public function destroy(CategoryRequest $request)
    {
        return $this->categoryService->deleteCategory($request->id);
    }
}
