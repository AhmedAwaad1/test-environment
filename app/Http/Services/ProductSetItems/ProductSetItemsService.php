<?php

namespace App\Http\Services\ProductSetItems;

use App\Http\Resources\PaginationResource\PaginationResource;
use App\Http\Resources\ProductSetItems\ProductSetItemsResource;
use App\Repositories\ProductSetItems\ProductSetItemsRepository;
use Illuminate\Support\Facades\Response;

class ProductSetItemsService
{
    protected ProductSetItemsRepository $productSetItemsRepo;
    public function __construct(ProductSetItemsRepository $productSetItemsRepo)
    {
        $this->productSetItemsRepo = $productSetItemsRepo;
    }

    public function getAllProductSetItems($request)
    {
        try {
            $productSetItems = $this->productSetItemsRepo->getAll($request);

            if ($request->per_page) {
                $response = new PaginationResource($productSetItems, ProductSetItemsResource::class);
            } else {
                $response = ProductSetItemsResource::collection($productSetItems);
            }

            return Response::successResponse($response, 'Product set items retrieved successfully');
        } catch (\Exception $e) {
            return Response::handleException($e, 'Failed to retrieve product set items');
        }
    }

    public function findProductSetItems($id)
    {
        try {
            $productSetItem = $this->productSetItemsRepo->find($id);

            if (!$productSetItem) {
                return Response::errorResponse('Product set item not found', [], 404);
            }

            return Response::successResponse(
                new ProductSetItemsResource($productSetItem),
                'Product set item found successfully'
            );
        } catch (\Exception $e) {
            return Response::handleException($e, 'Failed to retrieve product set item');
        }
    }

    public function createProductSetItems(array $data)
    {
        try {
            $productSetItem = $this->productSetItemsRepo->create($data);

            return Response::successResponse(
                new ProductSetItemsResource($productSetItem),
                'Product set item created successfully',
                201
            );
        } catch (\Exception $e) {
            return Response::handleException($e, 'Failed to create product set item');
        }
    }

    public function updateProductSetItems($id, array $data)
    {
        try {
            $productSetItem = $this->productSetItemsRepo->find($id);

            if (!$productSetItem) {
                return Response::errorResponse('Product set item not found', [], 404);
            }

            $productSetItem = $this->productSetItemsRepo->update($id, $data);

            return Response::successResponse(
                new ProductSetItemsResource($productSetItem),
                'Product set item updated successfully'
            );
        } catch (\Exception $e) {
            return Response::handleException($e, 'Failed to update product set item');
        }
    }

    public function deleteProductSetItems($id)
    {
        try {
            $productSetItem = $this->productSetItemsRepo->find($id);

            if (!$productSetItem) {
                return Response::errorResponse('Product set item not found', [], 404);
            }

            $this->productSetItemsRepo->delete($id);

            return Response::successResponse(
                ['is_success' => true],
                'Product set item deleted successfully'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::errorResponse('Product set item not found', [], 404);
        } catch (\Exception $e) {
            return Response::handleException($e, 'Failed to delete product set item');
        }
    }
} 