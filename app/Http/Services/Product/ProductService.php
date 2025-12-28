<?php

namespace App\Http\Services\Product;

use App\Http\Resources\PaginationResource\PaginationResource;
use App\Http\Resources\Product\ProductResource;
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
    ) {}

    /**
     * Get all products (with optional filters)
     */
    public function getAllProducts($request)
    {
        try {
            session()->forget('currency_id');

            if ($sid = $request->get('session_id')) {
                Cache::forget("currency_id_{$sid}");
            }

            $currency = $this->geoCurrencyService->getCurrencyForRequest();

            // Apply filters only if set
            $products = $this->productRepo->getAll($request, $request->all());

            $resource = $request->per_page
                ? new PaginationResource($products, ProductResource::class)
                : ProductResource::collection($products);

            return Response::successResponse(
                $resource->additional([
                    'currency'    => $currency?->name,
                    'currency_id' => $currency?->id,
                ]),
                'Products retrieved successfully'
            );
        } catch (\Exception $e) {
            return Response::handleException($e, 'Failed to retrieve products');
        }
    }

    /**
     * Best sellers endpoint
     */
    public function getBestSellers($request)
    {
        $request->merge(['is_best_seller' => 1]);
        return $this->getAllProducts($request);
    }

    /**
     * New arrivals endpoint
     */
    public function getNewArrivals($request)
    {
        $request->merge(['is_new_arrival' => 1]);
        return $this->getAllProducts($request);
    }

    /**
     * Find product by ID
     */
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
                    'currency'    => $currency?->name,
                    'currency_id' => $currency?->id,
                ]),
                'Product found successfully'
            );
        } catch (\Exception $e) {
            return Response::handleException($e, 'Failed to retrieve product');
        }
    }

    /**
     * Create product
     */
    public function createProduct(array $data)
    {
        DB::beginTransaction();
        try {
            $product = $this->productRepo->create($data);

            if (!empty($data['images'])) {
                $this->handleProductImages($product, $data['images']);
            }

            if (!empty($data['prices'])) {
                $this->productRepo->createProductPrices($product, $data['prices']);
            }

            $optionValueMap = [];
            if (!empty($data['options'])) {
                $optionValueMap = $this->createOptionsAndValues($product->id, $data['options']);
            }

            if (!empty($data['variants'])) {
                $this->createVariants($product->id, $data['variants'], $optionValueMap);
            }

            DB::commit();

            return Response::successResponse(
                new ProductResource(
                    $product->load('productVariants', 'productOptions.values', 'productPrices.currency')
                ),
                'Product created successfully',
                201
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return Response::handleException($e, 'Failed to create product');
        }
    }

    /**
     * Update product
     */
    public function updateProduct($id, array $data)
    {
        DB::beginTransaction();
        try {
            $product = $this->productRepo->find($id);
            if (!$product) {
                return Response::errorResponse('Product not found', [], 404);
            }

            $product = $this->productRepo->update($id, $data);

            if (isset($data['prices'])) {
                $this->productRepo->updateProductPrices($product, $data['prices']);
            }

            if (isset($data['images'])) {
                $this->handleProductImages($product, $data['images']);
            }

            if (isset($data['deleted_images'])) {
                $product->images()->whereIn('id', $data['deleted_images'])->delete();
            }

            $this->handleProductVariantsUpdate($product, $data);

            DB::commit();

            return Response::successResponse(
                new ProductResource($this->productRepo->findWithVariants($product->id)),
                'Product updated successfully'
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return Response::handleException($e, 'Failed to update product');
        }
    }

    /**
     * Delete product
     */
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
        } catch (\Exception $e) {
            return Response::handleException($e, 'Failed to delete product');
        }
    }

    /**
     * Get variant by selected options
     */
    public function getVariantByOptions($productId, array $selectedOptions)
    {
        try {
            $product = $this->productRepo->find($productId);

            if (!$product || !$product->has_variants) {
                return Response::errorResponse('Invalid product', [], 400);
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

    /* ===================== PRIVATE HELPERS ===================== */

    private function handleProductImages($product, $images)
    {
        foreach ($images as $image) {
            $isMain = isset($image['is_main']) && filter_var($image['is_main'], FILTER_VALIDATE_BOOLEAN);

            if ($isMain) {
                $product->images()->update(['is_main' => false]);
            }

            $product->images()->create([
                'image'   => $image['path'],
                'is_main' => $isMain,
            ]);
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
                foreach ($optionValueMap as $valuesMap) {
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

                if (!empty($createdOption->optionType)) {
                    $map[strtolower($createdOption->optionType->name)][strtolower($value['value'])] = $createdValue->id;
                }
            }
        }

        return $map;
    }

    private function handleProductVariantsUpdate($product, array $data): void
    {
        if (!isset($data['has_variants'])) return;

        $data['has_variants']
            ? $this->updateProductVariants($product->id, $data)
            : $this->deleteAllVariants($product);
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
