<?php

namespace App\Http\Services\Category;

use App\Http\Resources\PaginationResource\PaginationResource;
use App\Http\Resources\Category\CategoryResource;
use App\Repositories\Category\CategoryRepository;
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
        $query = $this->categoryRepo->getAll($request->all());

        if ($request->per_page) {
            $categories = new PaginationResource($query->paginate($request->per_page), CategoryResource::class);
        } else {
            $categories = CategoryResource::collection($query->get());
        }

        return Response::successResponse($categories, 'categories retrieved successfully');
    }

    public function getCategoryById($id)
    {
        try {
            $category = $this->categoryRepo->find($id);

            if (!$category) {
                return Response::errorResponse('category not found', [], 404);
            }

            return Response::successResponse(new CategoryResource($category), 'category found successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            //exception if id not found
            return Response::handleModelNotFoundException($e, 'category');
        } catch (\Exception $e) {
            return Response::handleException($e, 'Failed to retrieve category');
        }
    }

    public function createCategory($request)
    {
        try {
            if (empty($request['slug'])) {
                $request['slug'] = str_replace(' ', '-', $request['name_en']);
            }

            $category = $this->categoryRepo->create($request);

            return Response::successResponse(new CategoryResource($category), 'category created successfully', 201);

        } catch (\Illuminate\Database\QueryException $e) {
            return Response::handleDatabaseException($e, 'create category');
        } catch (\Exception $e) {
            return Response::handleException($e, 'create category');
        }
    }

    public function updateCategory($id, array $data)
    {
        try {
            if ((!isset($data['slug']) || empty($data['slug'])) && isset($data['name_en'])) {
                $data['slug'] = str_replace(' ', '-', $data['name_en']);
            }


            $category = $this->categoryRepo->update($id, $data);

            if (!$category) {
                return Response::errorResponse('category not found', [], 404);
            }

            return Response::successResponse(new CategoryResource($category), 'category updated successfully');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            //exception if id not found
            return Response::handleModelNotFoundException($e, 'category');
        } catch (\Exception $e) {
            return Response::handleException($e, 'update category');
        }
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
