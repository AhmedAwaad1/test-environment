<?php

namespace App\Http\Services\Product;

use App\Http\Resources\PaginationResource\PaginationResource;
use App\Http\Resources\Product\ProductResource;
use App\Repositories\Product\ProductRepository;
use Illuminate\Support\Facades\Response;

class ProductService
{
    protected $productRepo;

    public function __construct(ProductRepository $productRepo)
    {
        $this->productRepo = $productRepo;
    }


    public function getAllProducts($request)
    {
        $query = $this->productRepo->getAll($request);

        if ($request->per_page) {
            $products = new PaginationResource($query->paginate($request->per_page), ProductResource::class);
        } else {
            $products = ProductResource::collection($query->get());
        }

        return Response::successResponse($products, 'products retrieved successfully');
    }

    public function getProductById($id)
    {
        try {
            $product = $this->productRepo->find($id);

            if (!$product) {
                return Response::errorResponse('product not found', [], 404);
            }

            return Response::successResponse(new ProductResource($product), 'product found successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            //exception if id not found
            return Response::handleModelNotFoundException($e, 'product');
        } catch (\Exception $e) {
            return Response::handleException($e, 'Failed to retrieve product');
        }
    }

    public function createProduct($request)
    {
        try {
            $product = $this->productRepo->create($request);

            return Response::successResponse(new ProductResource($product), 'product created successfully', 201);

        } catch (\Illuminate\Database\QueryException $e) {
            return Response::handleDatabaseException($e, 'create product');
        } catch (\Exception $e) {
            return Response::handleException($e, 'create product');
        }
    }

    public function updateProduct($id, array $data)
    {
        try {
            $product = $this->productRepo->update($id, $data);

            if (!$product) {
                return Response::errorResponse('Product not found', [], 404);
            }

            return Response::successResponse(new ProductResource($product), 'product updated successfully');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            //exception if id not found
            return Response::handleModelNotFoundException($e, 'product');
        } catch (\Exception $e) {
            return Response::handleException($e, 'update product');
        }
    }

    public function deleteProduct($id)
    {
        $product = $this->productRepo->find($id);

        if (!$product) {
            return Response::errorResponse('product not found', [], 404);
        }

        $this->productRepo->delete($id);

        return Response::successResponse(['is_success' => 1], 'product deleted successfully');
    }
}
