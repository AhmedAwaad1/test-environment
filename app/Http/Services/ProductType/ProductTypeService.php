<?php

namespace App\Http\Services\ProductType;

use App\Http\Resources\PaginationResource\PaginationResource;
use App\Http\Resources\ProductType\ProductTypeResource;
use App\Repositories\ProductType\ProductTypeRepository;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class ProductTypeService
{
    protected $productTypeRepo;

    public function __construct(ProductTypeRepository $productTypeRepo)
    {
        $this->productTypeRepo = $productTypeRepo;
    }


    public function getAllProductTypes($request)
    {
        $query = $this->productTypeRepo->getAll();

        if ($request->per_page) {
            $productTypes = new PaginationResource($query->paginate($request->per_page), ProductTypeResource::class);
        } else {
            $productTypes = ProductTypeResource::collection($query->get());
        }

        return Response::successResponse($productTypes, 'product types retrieved successfully');
    }

    public function getProductTypeById($id)
    {
        try {
            $productType = $this->productTypeRepo->find($id);

            if (!$productType) {
                return Response::errorResponse('product type not found', [], 404);
            }

            return Response::successResponse(new ProductTypeResource($productType), 'product type found successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            //exception if id not found
            return Response::handleModelNotFoundException($e, 'product type');
        } catch (\Exception $e) {
            return Response::handleException($e, 'Failed to retrieve product type');
        }
    }

    public function createProductType($request)
    {
        try {
            if (empty($request['slug'])) {
                $request['slug'] = str_replace(' ', '-', $request['name_en']);
            }

            $productType = $this->productTypeRepo->create($request);

            return Response::successResponse(new ProductTypeResource($productType), 'product type created successfully', 201);

        } catch (\Illuminate\Database\QueryException $e) {
            return Response::handleDatabaseException($e, 'create product type');
        } catch (\Exception $e) {
            return Response::handleException($e, 'create product type');
        }
    }

    public function updateProductType($id, array $data)
    {
        try {
            if (empty($data['slug'])) {
                $data['slug'] = str_replace(' ', '-', $data['name_en']);
            }

            $productType = $this->productTypeRepo->update($id, $data);

            if (!$productType) {
                return Response::errorResponse('product type not found', [], 404);
            }

            return Response::successResponse(new ProductTypeResource($productType), 'product type updated successfully');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            //exception if id not found
            return Response::handleModelNotFoundException($e, 'product type');
        } catch (\Exception $e) {
            return Response::handleException($e, 'update product type');
        }
    }

    public function deleteProductType($id)
    {
        $productType = $this->productTypeRepo->find($id);

        if (!$productType) {
            return Response::errorResponse('product type not found', [], 404);
        }

        if ($productType->image) {
            Storage::delete($productType->image);
        }

        $this->productTypeRepo->delete($id);

        return Response::successResponse(['is_success' => 1], 'product type deleted successfully');
    }
}
