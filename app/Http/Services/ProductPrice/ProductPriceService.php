<?php

namespace App\Http\Services\ProductPrice;

use App\Http\Resources\PaginationResource\PaginationResource;
use App\Http\Resources\ProductPrice\ProductPriceResource;
use App\Repositories\ProductPrice\ProductPriceRepository;
use Illuminate\Support\Facades\Response;

class ProductPriceService
{
    protected ProductPriceRepository $productPriceRepo;
    public function __construct(ProductPriceRepository $productPriceRepo)
    {
        $this->productPriceRepo = $productPriceRepo;
    }

    public function getAllProductPrices($request)
    {
        try {
            $productPrices = $this->productPriceRepo->getAll($request);

            if ($request->per_page) {
                $response = new PaginationResource($productPrices, ProductPriceResource::class);
            } else {
                $response = ProductPriceResource::collection($productPrices);
            }

            return Response::successResponse($response, 'Product prices retrieved successfully');
        } catch (\Exception $e) {
            return Response::handleException($e, 'Failed to retrieve product prices');
        }
    }

    public function findProductPrice($id)
    {
        try {
            $productPrice = $this->productPriceRepo->find($id);

            if (!$productPrice) {
                return Response::errorResponse('Product price not found', [], 404);
            }

            return Response::successResponse(
                new ProductPriceResource($productPrice),
                'Product price found successfully'
            );
        } catch (\Exception $e) {
            return Response::handleException($e, 'Failed to retrieve product price');
        }
    }

    public function createProductPrice(array $data)
    {
        try {
            $productPrice = $this->productPriceRepo->create($data);
            $productPrice->load(['currency', 'product']);

            return Response::successResponse(
                new ProductPriceResource($productPrice),
                'Product price created successfully',
                201
            );
        } catch (\Exception $e) {
            return Response::handleException($e, 'Failed to create product price');
        }
    }

    public function updateProductPrice($id, array $data)
    {
        try {
            $productPrice = $this->productPriceRepo->find($id);

            if (!$productPrice) {
                return Response::errorResponse('Product price not found', [], 404);
            }

            $productPrice = $this->productPriceRepo->update($id, $data);

            return Response::successResponse(
                new ProductPriceResource($productPrice),
                'Product price updated successfully'
            );
        } catch (\Exception $e) {
            return Response::handleException($e, 'Failed to update product price');
        }
    }

    public function deleteProductPrice($id)
    {
        try {
            $productPrice = $this->productPriceRepo->find($id);

            if (!$productPrice) {
                return Response::errorResponse('Product price not found', [], 404);
            }

            $this->productPriceRepo->delete($id);

            return Response::successResponse(
                ['is_success' => true],
                'Product price deleted successfully'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::errorResponse('Product price not found', [], 404);
        } catch (\Exception $e) {
            return Response::handleException($e, 'Failed to delete product price');
        }
    }

    public function getProductPriceByProductAndCurrency($productId, $currencyId)
    {
        return $this->productPriceRepo->getByProductAndCurrency($productId, $currencyId);
    }

} 