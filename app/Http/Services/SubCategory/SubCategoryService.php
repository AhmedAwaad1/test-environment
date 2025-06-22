<?php

namespace App\Http\Services\SubCategory;

use App\Http\Resources\PaginationResource\PaginationResource;
use App\Http\Resources\SubCategory\SubCategoryResource;
use App\Repositories\SubCategory\SubCategoryRepository;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class SubCategoryService
{
    protected $subCategoryRepo;

    public function __construct(SubCategoryRepository $subCategoryRepo)
    {
        $this->subCategoryRepo = $subCategoryRepo;
    }


    public function getAllSubCategories($request)
    {
        $query = $this->subCategoryRepo->getAll($request->all());

        if ($request->per_page) {
            $subCategorys = new PaginationResource($query->paginate($request->per_page), SubCategoryResource::class);
        } else {
            $subCategorys = SubCategoryResource::collection($query->get());
        }

        return Response::successResponse($subCategorys, 'sub categories retrieved successfully');
    }

    public function getSubCategoryById($id)
    {
        try {
            $subCategory = $this->subCategoryRepo->find($id);

            if (!$subCategory) {
                return Response::errorResponse('sub category not found', [], 404);
            }

            return Response::successResponse(new SubCategoryResource($subCategory), 'sub category found successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            //exception if id not found
            return Response::handleModelNotFoundException($e, 'sub category');
        } catch (\Exception $e) {
            return Response::handleException($e, 'Failed to retrieve sub category');
        }
    }

    public function createSubCategory($request)
    {
        try {
            if (empty($request['slug']) && $request['name_en']) {
                $request['slug'] = str_replace(' ', '-', $request['name_en']);
            }

            $subCategory = $this->subCategoryRepo->create($request);

            return Response::successResponse(new SubCategoryResource($subCategory), 'sub category created successfully', 201);

        } catch (\Illuminate\Database\QueryException $e) {
            return Response::handleDatabaseException($e, 'create sub category');
        } catch (\Exception $e) {
            return Response::handleException($e, 'create sub category');
        }
    }

    public function updateSubCategory($id, array $data)
    {
        try {
            if ((!isset($data['slug']) || empty($data['slug'])) && isset($data['name_en'])) {
                $data['slug'] = str_replace(' ', '-', $data['name_en']);
            }

            $subCategory = $this->subCategoryRepo->update($id, $data);

            if (!$subCategory) {
                return Response::errorResponse('sub category not found', [], 404);
            }

            return Response::successResponse(new SubCategoryResource($subCategory), 'sub category updated successfully');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            //exception if id not found
            return Response::handleModelNotFoundException($e, 'sub category');
        } catch (\Exception $e) {
            return Response::handleException($e, 'update sub category');
        }
    }

    public function deleteSubCategory($id)
    {
        $subCategory = $this->subCategoryRepo->find($id);

        if (!$subCategory) {
            return Response::errorResponse('sub category not found', [], 404);
        }

        if ($subCategory->image) {
            Storage::delete($subCategory->image);
        }

        $this->subCategoryRepo->delete($id);

        return Response::successResponse(['is_success' => 1], 'sub category deleted successfully');
    }
}
