<?php

namespace App\Http\Services\Product;

use App\Repositories\ProductOptionType\ProductOptionTypeRepository;
use App\Http\Resources\ProductOptionType\ProductOptionTypeResource;
use App\Http\Resources\PaginationResource\PaginationResource;
use Illuminate\Support\Facades\Response;

class ProductOptionTypeService
{
    public function __construct(protected ProductOptionTypeRepository $repository)
    {
    }

    public function getAll($request)
    {
        try {
            $types = $this->repository->getAll($request);

            $resource = $request->per_page
                ? new PaginationResource($types, ProductOptionTypeResource::class)
                : ProductOptionTypeResource::collection($types);

            return Response::successResponse($resource, 'Option types retrieved successfully');
        } catch (\Exception $e) {
            return Response::handleException($e, 'Failed to retrieve option types');
        }
    }

    public function find($id)
    {
        try {
            $type = $this->repository->find($id);

            if (!$type) {
                return Response::errorResponse('Option type not found', [], 404);
            }

            return Response::successResponse(new ProductOptionTypeResource($type), 'Option type retrieved successfully');
        } catch (\Exception $e) {
            return Response::handleException($e, 'Failed to retrieve option type');
        }
    }

    public function create(array $data)
    {
        try {
            $type = $this->repository->create($data);
            return Response::successResponse(new ProductOptionTypeResource($type), 'Option type created successfully', 201);
        } catch (\Exception $e) {
            return Response::handleException($e, 'Failed to create option type');
        }
    }

    public function update($id, array $data)
    {
        try {
            $type = $this->repository->update($id, $data);

            if (!$type) {
                return Response::errorResponse('Option type not found', [], 404);
            }

            return Response::successResponse(new ProductOptionTypeResource($type), 'Option type updated successfully');
        } catch (\Exception $e) {
            return Response::handleException($e, 'Failed to update option type');
        }
    }

    public function delete($id)
    {
        try {
            $type = $this->repository->delete($id);

            if (!$type) {
                return Response::errorResponse('Option type not found', [], 404);
            }

            return Response::successResponse(null, 'Option type deleted successfully');
        } catch (\Exception $e) {
            return Response::handleException($e, 'Failed to delete option type');
        }
    }
}
