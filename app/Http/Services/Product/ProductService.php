<?php

namespace App\Http\Services\Product;

use App\Http\Resources\PaginationResource\PaginationResource;
use App\Http\Resources\Product\ProductResource;
use App\Http\Resources\ProductOption\ProductOptionResource;
use App\Http\Resources\ProductVariant\ProductVariantResource;
use App\Repositories\Product\ProductRepository;
use App\Repositories\ProductOption\ProductOptionRepository;
use App\Repositories\ProductOptionValue\ProductOptionValueRepository;
use App\Repositories\ProductVariant\ProductVariantRepository;
use App\Models\VariantOptionValue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class ProductService
{
    public function __construct(
        protected ProductRepository $productRepo,
        protected ProductOptionRepository $productOptionRepo,
        protected ProductOptionValueRepository $productOptionValueRepo,
        protected ProductVariantRepository $productVariantRepo,
        protected VariantOptionValue $variantOptionValue
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

    public function findProduct($id)
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

    public function createProduct(array $data)
    {
        DB::beginTransaction();
        try {
            //Create product
            $product = $this->productRepo->create($data);

            //Attach images
            if (!empty($data['images'])) {
                $this->handleProductImages($product, $data['images']);
            }

            //Create options & values
            $optionValueMap = [];
            if (!empty($data['options'])) {
                $optionValueMap = $this->createOptionsAndValues($product->id, $data['options']);
            }

            //Create variants & attach values
            if (!empty($data['variants'])) {
                $this->createVariants($product->id, $data['variants'], $optionValueMap);
            }

            DB::commit();
            return Response::successResponse(
                new ProductResource($product->load('productVariants', 'productOptions.values')),
                'Product created successfully',
                201
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return Response::handleException($e, 'Failed to create product');
        }
    }

    private function createVariants(int $productId, array $variants, array $optionValueMap): void
    {
        foreach ($variants as $variant) {
            $createdVariant = $this->productVariantRepo->create([
                'product_id' => $productId,
                'sku' => $variant['sku'],
                'price' => $variant['price'],
                'price_after_discount' => $variant['price_after_discount'] ?? null,
                'quantity' => $variant['quantity'],
                'barcode' => $variant['barcode'] ?? null,
                'weight' => $variant['weight'] ?? null,
                'is_active' => $variant['is_active'] ?? true,
                'order' => $variant['order'] ?? 1,
            ]);

            foreach ($variant['option_values'] as $value) {
                $valueKey = strtolower($value); // lowercase for consistency

                foreach ($optionValueMap as $optionType => $valuesMap) {
                    if (isset($valuesMap[$valueKey])) {
                        $this->variantOptionValue->create([
                            'product_variant_id' => $createdVariant->id,
                            'product_option_value_id' => $valuesMap[$valueKey],
                        ]);
                        break;
                    }
                }
            }
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

    public function getVariantByOptions($productId, array $selectedOptions)
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

    private function createOptionsAndValues(int $productId, array $options): array
    {
        $map = [];

        foreach ($options as $option) {
            $createdOption = $this->productOptionRepo->create([
                'product_id' => $productId,
                'product_option_type_id' => $option['option_type_id'],
                'order' => $option['order'] ?? 1,
            ]);


            // Eager load the 'type' relation to avoid null issue
            $createdOption->load('optionType');

            foreach ($option['values'] as $index => $value) {
                $createdValue = $this->productOptionValueRepo->create([
                    'product_option_id' => $createdOption->id,
                    'value' => $value['value'],
                    'hex_code' => $value['hex_code'] ?? null,
                    'order' => $index + 1,
                ]);

                // Safe mapping with lowercase keys for consistency
                if (!empty($createdOption->type) && is_string($createdOption->type->name)) {
                    $optionTypeName = strtolower($createdOption->type->name);
                    $valueKey = strtolower($value['value']); // ensure lowercase

                    $map[$optionTypeName][$valueKey] = $createdValue->id;
                }
            }
        }

        logger()->info('Creating value', [
            'index' => $index,
            'value' => $value,
            'createdOption' => $createdOption,
        ]);

        
        dd($map);

        return $map;
    }


    private function updateProductVariants($productId, array $data)
    {
        // First delete all existing variants and options
        $this->deleteAllVariants($this->productRepo->find($productId));

        // Create new options and values
        $optionValueMap = [];
        if (!empty($data['options'])) {
            $optionValueMap = $this->createOptionsAndValues($productId, $data['options']);
        }

        // Create new variants
        if (!empty($data['variants'])) {
            $this->createVariants($productId, $data['variants'], $optionValueMap);
        }
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

