<?php

namespace App\Http\Services\Product;

use App\Http\Resources\PaginationResource\PaginationResource;
use App\Http\Resources\Product\ProductResource;
use App\Http\Resources\ProductOption\ProductOptionResource;
use App\Http\Resources\ProductVariant\ProductVariantResource;
use App\Models\ProductImage;
use App\Repositories\Product\ProductRepository;
use App\Repositories\ProductOption\ProductOptionRepository;
use App\Repositories\ProductOptionValue\ProductOptionValueRepository;
use App\Repositories\ProductVariant\ProductVariantRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use App\Models\VariantOptionValue;

class ProductService
{
    public function __construct(
        protected ProductRepository $productRepo,
        protected ProductOptionRepository $productOptionRepo,
        protected ProductOptionValueRepository $productOptionValueRepo,
        protected ProductVariantRepository $productVariantRepo
    ) {}

    public function getAllProducts($request)
    {
        try {
            $products = $this->productRepo->getAll($request);

            $response = $request->per_page
                ? new PaginationResource($products, ProductResource::class)
                : ProductResource::collection($products);

            return Response::successResponse($response, 'Products retrieved successfully');
        } catch (\Exception $e) {
            return Response::handleException($e, 'Failed to retrieve products');
        }
    }

    public function getProductById($id)
    {
        try {
            $product = $this->productRepo->findWithVariants($id);

            if (!$product) {
                return Response::errorResponse('Product not found', [], 404);
            }

            return Response::successResponse(
                new ProductResource($product),
                'Product found successfully'
            );
        } catch (\Exception $e) {
            return Response::handleException($e, 'Failed to retrieve product');
        }
    }

    public function createProduct($request)
    {
        try {
            DB::beginTransaction();

            // Create the product
            $product = $this->productRepo->create($request);

            // Handle images
            if (isset($request['images'])) {
                $this->handleProductImages($product, $request['images']);
            }

            // Handle variants if product has variants
            if (isset($request['has_variants']) && $request['has_variants']) {
                $this->createProductVariants($product->id, $request);
            }

            // Fetch the complete product with all relationships
            $product = $this->productRepo->findWithVariants($product->id);

            DB::commit();

            return Response::successResponse(
                new ProductResource($product),
                'Product created successfully',
                201
            );
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            return Response::handleDatabaseException($e, 'create product');
        } catch (\Exception $e) {
            DB::rollBack();
            return Response::handleException($e, 'Failed to create product');
        }
    }

    public function updateProduct($id, array $data)
    {
        try {
            DB::beginTransaction();

            $product = $this->productRepo->find($id);

            if (!$product) {
                return Response::errorResponse('Product not found', [], 404);
            }

            // Update basic product info
            $product = $this->productRepo->update($id, $data);

            // Handle images
            if (isset($data['images'])) {
                $this->handleProductImages($product, $data['images']);
            }

            // Handle variants
            if (isset($data['has_variants'])) {
                if ($data['has_variants']) {
                    $this->updateProductVariants($product->id, $data);
                } else {
                    $this->deleteAllVariants($product);
                }
            }

            // Fetch the complete product with all relationships
            $product = $this->productRepo->findWithVariants($product->id);

            DB::commit();

            return Response::successResponse(
                new ProductResource($product),
                'Product updated successfully'
            );
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            return Response::handleDatabaseException($e, 'update product');
        } catch (\Exception $e) {
            DB::rollBack();
            return Response::handleException($e, 'Failed to update product');
        }
    }

    public function deleteProduct($id)
    {
        try {
            $isDeleted = $this->productRepo->delete($id);

            if (!$isDeleted) {
                return Response::errorResponse('Failed to delete product', [], 400);
            }

            return Response::successResponse(
                ['is_success' => true],
                'Product deleted successfully'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::errorResponse('Product not found', [], 404);
        } catch (\Exception $e) {
            return Response::handleException($e, 'Failed to delete product');
        }
    }

    public function getProductOptions($id)
    {
        try {
            $product = $this->productRepo->find($id);

            if (!$product) {
                return Response::errorResponse('Product not found', [], 404);
            }

            if (!$product->has_variants) {
                return Response::errorResponse('Product does not have variants', [], 400);
            }

            $options = $this->productOptionRepo->getByProductId($id);

            return Response::successResponse(
                ProductOptionResource::collection($options),
                'Product options retrieved successfully'
            );
        } catch (\Exception $e) {
            return Response::handleException($e, 'Failed to retrieve product options');
        }
    }

    public function getVariantByOptions($productId, $selectedOptions)
    {
        try {
            $product = $this->productRepo->find($productId);

            if (!$product) {
                return Response::errorResponse('Product not found', [], 404);
            }

            if (!$product->has_variants) {
                return Response::errorResponse('Product does not have variants', [], 400);
            }

            $variant = $this->productVariantRepo->findByOptions($productId, $selectedOptions);

            if (!$variant) {
                return Response::errorResponse('Variant not found', [], 404);
            }

            return Response::successResponse(
                new ProductVariantResource($variant),
                'Variant found successfully'
            );
        } catch (\Exception $e) {
            return Response::handleException($e, 'Failed to find variant');
        }
    }

    // Private helper methods
    private function handleProductImages($product, $images)
    {
        foreach ($images as $image) {
            $path = $image['path'];
            $isMain = isset($image['is_main']) ? filter_var($image['is_main'], FILTER_VALIDATE_BOOLEAN) : false;

            if ($isMain) {
                // If this is a main image, set all other images as not main
                $product->images()->update(['is_main' => false]);
            }

            $product->images()->create([
                'image' => $path,
                'is_main' => $isMain,
            ]);
        }
    }

    private function createProductVariants($productId, array $data)
    {
        // Validate required data
        if (!isset($data['options']) || !isset($data['variants'])) {
            throw new \Exception('Options and variants are required for products with variants');
        }

        // Create options and their values
        foreach ($data['options'] as $optionData) {
            $this->productOptionRepo->createWithValues($productId, $optionData);
        }

        // Create variants
        foreach ($data['variants'] as $variantData) {
            $this->productVariantRepo->createWithOptions($productId, $variantData);
        }
    }

    private function updateProductVariants($productId, array $data)
    {
        // First delete all existing variants and options
        $this->deleteAllVariants($this->productRepo->find($productId));

        // Then create new ones
        $this->createProductVariants($productId, $data);
    }

    private function deleteAllVariants($product)
    {
        foreach ($product->productVariants as $variant) {
            $this->productVariantRepo->delete($variant->id);
        }

        foreach ($product->productOptions as $option) {
            $this->productOptionRepo->delete($option->id);
        }
    }
}

