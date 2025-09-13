<?php

namespace App\Http\Services\Product;

use App\Http\Resources\PaginationResource\PaginationResource;
use App\Http\Resources\Product\ProductResource;
use App\Http\Resources\ProductOption\ProductOptionResource;
use App\Http\Resources\ProductVariant\ProductVariantResource;
use App\Http\Services\GeoCurrency\GeoCurrencyService;
use App\Repositories\Product\ProductRepository;
use App\Repositories\ProductOption\ProductOptionRepository;
use App\Repositories\ProductOptionValue\ProductOptionValueRepository;
use App\Repositories\ProductVariant\ProductVariantRepository;
use App\Models\VariantOptionValue;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class ProductService
{
    public function __construct(
        protected ProductRepository            $productRepo,
        protected ProductOptionRepository      $productOptionRepo,
        protected ProductOptionValueRepository $productOptionValueRepo,
        protected ProductVariantRepository     $productVariantRepo,
        protected VariantOptionValue           $variantOptionValue,
        protected GeoCurrencyService           $geoCurrencyService
    )
    {
    }


    public function getAllProducts($request)
    {
        try {
            session()->forget('currency_id');

            if ($sid = $request->get('session_id')) {
                Cache::forget("currency_id_{$sid}");
            }

            $currency = $this->geoCurrencyService->getCurrencyForRequest();
            $products = $this->productRepo->getAll($request);

            $resource = $request->per_page
                ? new PaginationResource($products, ProductResource::class)
                : ProductResource::collection($products);

            return Response::successResponse(
                $resource->additional([
                    'currency' => $currency?->name,
                    'currency_id' => $currency?->id,
                ]),
                'Products retrieved successfully'
            );
        } catch (\Exception $e) {
            return Response::handleException($e, 'Failed to retrieve products');
        }
    }


    public function findProduct($id)
    {
        try {
            $currency = $this->geoCurrencyService->getCurrencyForRequest();
            $product  = $this->productRepo->findWithVariants($id);

            if (!$product) {
                return Response::errorResponse('Product not found', [], 404);
            }

            return Response::successResponse(
                (new ProductResource($product))->additional([
                    'currency' => $currency?->name,
                    'currency_id' => $currency?->id,
                ]),
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

            //Attach prices
            if (!empty($data['prices'])) {
                $this->productRepo->createProductPrices($product, $data['prices']);
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
                new ProductResource($product->load('productVariants', 'productOptions.values', 'productPrices.currency')),
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
                'sku'        => $variant['sku'],
                'quantity'   => $variant['quantity'],
                'barcode'    => $variant['barcode'] ?? null,
                'weight'     => $variant['weight'] ?? null,
                'is_active'  => $variant['is_active'] ?? true,
                'order'      => $variant['order'] ?? 1,
            ]);

            foreach ($variant['option_values'] as $value) {
                foreach ($optionValueMap as $optionType => $valuesMap) {
                    if (isset($valuesMap[strtolower($value['value'])])) {
                        $this->variantOptionValue->create([
                            'product_variant_id'      => $createdVariant->id,
                            'product_option_value_id' => $valuesMap[strtolower($value['value'])],
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

            $product = $this->productRepo->update($id, $data);

            if (isset($data['prices']) && is_array($data['prices'])) {
                $this->productRepo->updateProductPrices($product, $data['prices']);
            }


            if (isset($data['images'])) {
                $this->handleProductImages($product, $data['images']);
            }

            if (isset($data['deleted_images'])) {
                $product->images()->whereIn('id', $data['deleted_images'])->delete();
            }

            if (isset($data['main_image_id'])) {
                $product->images()->update(['is_main' => false]);
                $product->images()->where('id', $data['main_image_id'])->update(['is_main' => true]);
            }

            $this->handleProductVariantsUpdate($product, $data);

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

    private function handleProductVariantsUpdate($product, array $data): void
    {
        if (!isset($data['has_variants'])) return;

        if ($data['has_variants']) {
            $this->updateProductVariants($product->id, $data);
        } else {
            $this->deleteAllVariants($product);
        }
    }

    private function updateProductVariants(int $productId, array $data): void
    {
        if (empty($data['options']) || empty($data['variants'])) {
            return;
        }

        // First, handle options and get option value mappings
        $optionValueMap = $this->handleOptionsUpdate($productId, $data['options']);

        // Then update variants
        foreach ($data['variants'] as $variantData) {
            if (isset($variantData['id'])) {
                // Update existing variant
                $variant = $this->productVariantRepo->update($variantData['id'], [
                    'quantity'  => $variantData['quantity'],
                    'sku'       => $variantData['sku'],
                    'barcode'   => $variantData['barcode'] ?? null,
                    'weight'    => $variantData['weight'] ?? null,
                    'is_active' => $variantData['is_active'] ?? true,
                    'order'     => $variantData['order'] ?? 1,
                ]);

                // Update variant option values
                $this->updateVariantOptionValues($variant, $variantData['option_values']);
            } else {
                // Create new variant
                $variant = $this->productVariantRepo->create([
                    'product_id' => $productId,
                    'quantity'   => $variantData['quantity'],
                    'sku'        => $variantData['sku'],
                    'barcode'    => $variantData['barcode'] ?? null,
                    'weight'     => $variantData['weight'] ?? null,
                    'is_active'  => $variantData['is_active'] ?? true,
                    'order'      => $variantData['order'] ?? 1,
                ]);

                // Create variant option values
                foreach ($variantData['option_values'] as $value) {
                    foreach ($optionValueMap as $optionType => $valuesMap) {
                        if (isset($valuesMap[strtolower($value['value'])])) {
                            $this->variantOptionValue->create([
                                'product_variant_id'      => $variant->id,
                                'product_option_value_id' => $valuesMap[strtolower($value['value'])],
                            ]);
                            break;
                        }
                    }
                }
            }
        }
    }

    private function handleOptionsUpdate(int $productId, array $options): array
    {
        $map = [];

        foreach ($options as $option) {
            if (isset($option['id'])) {
                // Update existing option
                $productOption = $this->productOptionRepo->update($option['id'], [
                    'product_option_type_id' => $option['option_type_id'],
                    'order'                  => $option['order'] ?? 1,
                ]);
            } else {
                // Create new option
                $productOption = $this->productOptionRepo->create([
                    'product_id'             => $productId,
                    'product_option_type_id' => $option['option_type_id'],
                    'order'                  => $option['order'] ?? 1,
                ]);
            }

            $productOption->load('optionType');

            foreach ($option['values'] as $value) {
                if (isset($value['id'])) {
                    // Update existing value
                    $optionValue = $this->productOptionValueRepo->update($value['id'], [
                        'value'    => $value['value'],
                        'hex_code' => $value['hex_code'] ?? null,
                        'order'    => $value['order'] ?? 1,
                    ]);
                } else {
                    // Create new value
                    $optionValue = $this->productOptionValueRepo->create([
                        'product_option_id' => $productOption->id,
                        'value'             => $value['value'],
                        'hex_code'          => $value['hex_code'] ?? null,
                        'order'             => $value['order'] ?? 1,
                    ]);
                }

                // Handle images for the option value if they exist
                if (!empty($value['images'])) {
                    foreach ($value['images'] as $imageIndex => $imagePath) {
                        $optionValue->images()->create([
                            'image' => $imagePath,
                            'order' => $imageIndex + 1,
                        ]);
                    }
                }

                if (!empty($productOption->optionType) && is_string($productOption->optionType->name)) {
                    $optionTypeName                                    = strtolower($productOption->optionType->name);
                    $map[$optionTypeName][strtolower($value['value'])] = $optionValue->id;
                }
            }
        }

        return $map;
    }

    private function updateVariantOptionValues($variant, array $optionValues): void
    {
        // Get existing option value IDs
        $existingValueIds = $variant->optionValues->pluck('id')->toArray();

        // Get new option value IDs
        $newValueIds = collect($optionValues)->pluck('id')->filter()->toArray();

        // Get values to delete (existing but not in new)
        $toDelete = array_diff($existingValueIds, $newValueIds);

        // Delete removed values
        if (!empty($toDelete)) {
            $this->variantOptionValue
                ->where('product_variant_id', $variant->id)
                ->whereIn('product_option_value_id', $toDelete)
                ->delete();
        }

        // Update or create new values
        foreach ($optionValues as $value) {
            if (!isset($value['id'])) {
                continue; // Skip if no ID (should've been handled in variant creation)
            }

            $this->variantOptionValue->updateOrCreate(
                [
                    'product_variant_id'      => $variant->id,
                    'product_option_value_id' => $value['id']
                ],
                []
            );
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
            $path   = $image['path'];
            $isMain = isset($image['is_main']) ? filter_var($image['is_main'], FILTER_VALIDATE_BOOLEAN) : false;

            if ($isMain) {
                // If this is a main image, set all other images as not main
                $product->images()->update(['is_main' => false]);
            }

            $product->images()->create([
                'image'   => $path,
                'is_main' => $isMain,
            ]);
        }
    }

    private function createOptionsAndValues(int $productId, array $options): array
    {
        $map = [];

        foreach ($options as $option) {
            $createdOption = $this->productOptionRepo->create([
                'product_id'             => $productId,
                'product_option_type_id' => $option['option_type_id'],
                'order'                  => $option['order'] ?? 1,
            ]);

            $createdOption->load('optionType');

            foreach ($option['values'] as $index => $value) {
                $createdValue = $this->productOptionValueRepo->create([
                    'product_option_id' => $createdOption->id,
                    'value'             => $value['value'],
                    'hex_code'          => $value['hex_code'] ?? null,
                    'order'             => $value['order'] ?? ($index + 1),
                ]);

                // Handle images for the option value if they exist
                if (!empty($value['images'])) {
                    foreach ($value['images'] as $imageIndex => $imagePath) {
                        $createdValue->images()->create([
                            'image' => $imagePath,
                            'order' => $imageIndex + 1,
                        ]);
                    }
                }

                if (!empty($createdOption->optionType) && is_string($createdOption->optionType->name)) {
                    $optionTypeName                                    = strtolower($createdOption->optionType->name);
                    $map[$optionTypeName][strtolower($value['value'])] = $createdValue->id;
                }
            }
        }

        return $map;
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

