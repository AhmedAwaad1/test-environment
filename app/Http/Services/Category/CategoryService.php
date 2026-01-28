<?php

namespace App\Http\Services\Category;

use App\Http\Resources\PaginationResource\PaginationResource;
use App\Http\Resources\Category\CategoryResource;
use App\Repositories\Category\CategoryRepository;
use App\Helpers\CacheHelper;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class CategoryService
{
    protected $categoryRepo;

    public function __construct(CategoryRepository $categoryRepo)
    {
        $this->categoryRepo = $categoryRepo;
    }


    public function getAllCategories($request)
    {
        // Force pagination and limit per_page to prevent memory issues
        if ($request->filled('per_page')) {
            $perPage = min((int) $request->per_page, 100);
            $request->merge(['per_page' => $perPage]);
        } else {
            $request->merge(['per_page' => 15]);
        }

        $cacheKey = CacheHelper::generateKey('categories', $request->all());

        $data = Cache::remember($cacheKey, now()->addHours(24), function () use ($request) {
            $query = $this->categoryRepo->getAll($request->all());

            if ($request->per_page) {
                return (new PaginationResource($query->paginate($request->per_page), CategoryResource::class))->resolve();
            } else {
                return CategoryResource::collection($query->get())->resolve();
            }
        });

        return Response::successResponse($data, 'categories retrieved successfully');
    }

    public function getCategoryById($id)
    {
        $cacheKey = CacheHelper::generateKey('categories', ['id' => $id]);

        $data = Cache::remember($cacheKey, now()->addHours(24), function () use ($id) {
            $category = $this->categoryRepo->find($id);

            if (!$category) {
                return null;
            }

            return (new CategoryResource($category))->resolve();
        });

        if (!$data) {
            return Response::errorResponse('category not found', [], 404);
        }

        return Response::successResponse($data, 'category found successfully');
    }

    public function createCategory($request)
    {
        if (empty($request['slug'])) {
            $request['slug'] = str_replace(' ', '-', $request['name_en']);
        }

        $category = $this->categoryRepo->create($request);

        return Response::successResponse(new CategoryResource($category), 'category created successfully', 201);
    }

    public function updateCategory($id, array $data)
    {
        if (!isset($data['slug']) || empty($data['slug']) && isset($data['name_en'])) {
            $data['slug'] = str_replace(' ', '-', $data['name_en']);
        }

        $category = $this->categoryRepo->update($id, $data);

        if (!$category) {
            return Response::errorResponse('category not found', [], 404);
        }

        return Response::successResponse(new CategoryResource($category), 'category updated successfully');
    }

    public function deleteCategory($id)
    {
        $category = $this->categoryRepo->find($id);

        if (!$category) {
            return Response::errorResponse('category not found', [], 404);
        }

        if ($category->image) {
            Storage::delete($category->image);
        }

        $this->categoryRepo->delete($id);

        return Response::successResponse(['is_success' => 1], 'category deleted successfully');
    }
}
