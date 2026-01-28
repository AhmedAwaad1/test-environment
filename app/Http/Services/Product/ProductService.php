<?php

namespace App\Http\Services\Product;

use App\Http\Resources\PaginationResource\PaginationResource;
use App\Http\Resources\Product\ProductResource;
use App\Http\Resources\Product\ProductListResource;
use App\Http\Resources\ProductVariant\ProductVariantResource;
use App\Http\Services\GeoCurrency\GeoCurrencyService;
use App\Repositories\Product\ProductRepository;
use App\Repositories\ProductOption\ProductOptionRepository;
use App\Repositories\ProductOptionValue\ProductOptionValueRepository;
use App\Repositories\ProductVariant\ProductVariantRepository;
use App\Models\VariantOptionValue;
use App\Helpers\CacheHelper;
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
        session()->forget('currency_id');

        if ($sid = $request->get('session_id')) {
            Cache::forget("currency_id_{$sid}");
        }

        $currency = $this->geoCurrencyService->getCurrencyForRequest();

        // Limit per_page to prevent memory exhaustion
        if ($request->filled('per_page')) {
            $perPage = min((int) $request->per_page, 50);
            $request->merge(['per_page' => $perPage]);
        } else {
            $request->merge(['per_page' => 10]);
        }

        $isListResource = $request->get('resource') === 'list' || $request->get('fields') === 'list';

        // Apply filters only if set
        $cacheKey = CacheHelper::generateKey('products', $request->all());

        $data = Cache::remember($cacheKey, now()->addHours(24), function () use ($request, $currency, $isListResource) {
            $products = $this->productRepo->getAll($request, $request->all());

            if ($isListResource) {
                return [
                    'data' => ProductListResource::collection($products)->resolve(),
                    'meta' => [
                        'current_page' => $products->currentPage(),
                        'last_page' => $products->lastPage(),
                        'per_page' => $products->perPage(),
                        'total' => $products->total(),
                    ],
                    'currency' => $currency?->name,
                    'currency_id' => $currency?->id,
                ];
            }

            $resource = $request->per_page
                ? new PaginationResource($products, ProductResource::class)
                : ProductResource::collection($products);

            return $resource->additional([
                'currency'    => $currency?->name,
                'currency_id' => $currency?->id,
            ])->resolve();
        });

        return Response::successResponse($data, 'Products retrieved successfully');
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
        $currency = $this->geoCurrencyService->getCurrencyForRequest();
        $cacheKey = CacheHelper::generateKey('products', ['id' => $id]);

        $data = Cache::remember($cacheKey, now()->addHours(24), function () use ($id, $currency) {
            $product = $this->productRepo->findWithVariants($id);

            if (!$product) {
                return null;
            }

            return (new ProductResource($product))->additional([
                'currency'    => $currency?->name,
                'currency_id' => $currency?->id,
            ])->resolve();
        });

        if (!$data) {
            return Response::errorResponse('Product not found', [], 404);
        }

        return Response::successResponse($data, 'Product found successfully');
    }

    /**
     * Create product
     */
    public function createProduct(array $data)
    {
        return DB::transaction(function () use ($data) {
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

            return Response::successResponse(
                new ProductResource(
                    $product->load('productVariants', 'productOptions.values', 'productPrices.currency')
                ),
                'Product created successfully',
                201
            );
        });
    }

    /**
     * Update product
     */
    public function updateProduct($id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
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

            // Fetch a completely fresh product with all relationships loaded for the response
            $freshProduct = $this->productRepo->findWithVariants($product->id);

            return Response::successResponse(
                new ProductResource($freshProduct),
                'Product updated successfully'
            );
        });
    }

    /**
     * Delete product
     */
    public function deleteProduct($id)
    {
        $isDeleted = $this->productRepo->delete($id);

        if (!$isDeleted) {
            return Response::errorResponse('Failed to delete product', [], 400);
        }

        return Response::successResponse(
            ['is_success' => true],
            'Product deleted successfully'
        );
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
            $isMain = false;
            $imageFile = null;

            if (is_array($image)) {
                $isMain = isset($image['is_main']) && ($image['is_main'] == '1' || $image['is_main'] === 'true' || $image['is_main'] === true);
                $imageFile = $image['path'] ?? null;
            } else {
                $imageFile = $image;
            }

            if ($isMain) {
                $product->images()->update(['is_main' => false]);
            }

            if ($imageFile instanceof \Illuminate\Http\UploadedFile) {
                $path = $imageFile->store('products', 'public');
            } else {
                $path = $imageFile;
            }

            if ($path && is_string($path)) {
                $product->images()->create([
                    'image'   => $path,
                    'is_main' => $isMain,
                ]);
            }
        }
    }

    private function createVariants(int $productId, array $variants, array $optionValueMap): void
    {
        foreach ($variants as $variant) {
            // Extract default price from prices array if available
            $defaultPrice = 0;
            $defaultPriceAfterDiscount = null;

            if (!empty($variant['prices'])) {
                // Look for currency_id = 1 or take the first one
                $priceData = collect($variant['prices'])->firstWhere('currency_id', 1) 
                            ?? collect($variant['prices'])->first();
                
                if ($priceData) {
                    $defaultPrice = $priceData['price'];
                    $defaultPriceAfterDiscount = $priceData['price_after_discount'] ?? null;
                }
            }

            $createdVariant = $this->productVariantRepo->create([
                'product_id'           => $productId,
                'sku'                  => $variant['sku'],
                'price'                => $defaultPrice,
                'price_after_discount' => $defaultPriceAfterDiscount,
                'quantity'             => $variant['quantity'],
                'barcode'              => $variant['barcode'] ?? null,
                'weight'               => $variant['weight'] ?? null,
                'is_active'            => $variant['is_active'] ?? true,
                'order'                => $variant['order'] ?? 1,
            ]);

            // Save multi-currency prices if provided
            if (!empty($variant['prices'])) {
                foreach ($variant['prices'] as $priceData) {
                    $createdVariant->productPrices()->create([
                        'currency_id'          => $priceData['currency_id'],
                        'price'                => $priceData['price'],
                        'price_after_discount' => $priceData['price_after_discount'] ?? null,
                    ]);
                }
            }

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
        // If has_variants is not provided, we don't change the variant structure
        // This handles simple status updates or partial updates.
        if (!isset($data['has_variants'])) {
            // Even if has_variants is not set, if variants are provided, update them
            if (!empty($data['variants'])) {
                $this->updateProductVariants($product->id, $data);
            }
            return;
        }

        if ($data['has_variants']) {
            $this->updateProductVariants($product->id, $data);
        } else {
            $this->deleteAllVariants($product);
        }
    }

    private function updateProductVariants(int $productId, array $data): void
    {
        // 1. Handle Options and build map first
        $optionValueMap = [];
        if (!empty($data['options'])) {
            $optionValueMap = $this->updateOptionsAndValues($productId, $data['options']);
        } else {
            $optionValueMap = $this->getExistingOptionValueMap($productId);
        }

        // 2. Get existing variants to handle deletions (sync)
        $existingVariants = $this->productVariantRepo->getByProductId($productId);
        $processedIds = [];

        // 3. Process variants from request
        if (!empty($data['variants'])) {
            foreach ($data['variants'] as $variantData) {
                // Extract default price from prices array if available
                $defaultPrice = 0;
                $defaultPriceAfterDiscount = null;

                if (!empty($variantData['prices'])) {
                    $priceData = collect($variantData['prices'])->firstWhere('currency_id', 1) 
                                ?? collect($variantData['prices'])->first();
                    
                    if ($priceData) {
                        $defaultPrice = $priceData['price'];
                        $defaultPriceAfterDiscount = $priceData['price_after_discount'] ?? null;
                    }
                }

                $variantFields = [
                    'sku'                  => $variantData['sku'],
                    'price'                => $defaultPrice,
                    'price_after_discount' => $defaultPriceAfterDiscount,
                    'quantity'             => $variantData['quantity'],
                    'barcode'              => $variantData['barcode'] ?? null,
                    'weight'               => $variantData['weight'] ?? null,
                    'is_active'            => $variantData['is_active'] ?? true,
                    'order'                => $variantData['order'] ?? 1,
                ];

                if (!empty($variantData['id'])) {
                    // Update existing
                    $variant = $this->productVariantRepo->update($variantData['id'], $variantFields);
                    $processedIds[] = $variant->id;
                } else {
                    // Create new
                    $variantFields['product_id'] = $productId;
                    $variant = $this->productVariantRepo->create($variantFields);
                    $processedIds[] = $variant->id;
                }

                // Save multi-currency prices if provided
                if (!empty($variantData['prices'])) {
                    // Optionally clear existing prices for update flow if needed
                    if (!empty($variantData['id'])) {
                        $variant->productPrices()->delete();
                    }
                    
                    foreach ($variantData['prices'] as $priceData) {
                        $variant->productPrices()->create([
                            'currency_id'          => $priceData['currency_id'],
                            'price'                => $priceData['price'],
                            'price_after_discount' => $priceData['price_after_discount'] ?? null,
                        ]);
                    }
                }

                // Sync option values for this variant
                if (!empty($variantData['option_values'])) {
                    $this->syncVariantOptionValues($variant, $variantData['option_values'], $optionValueMap);
                }
            }
        }

        // 4. Delete variants not present in the update request
        foreach ($existingVariants as $existingVariant) {
            if (!in_array($existingVariant->id, $processedIds)) {
                $this->productVariantRepo->delete($existingVariant->id);
            }
        }
    }

    private function updateOptionsAndValues(int $productId, array $options): array
    {
        $map = [];
        
        // When updating options, we often want to sync them. 
        // For simplicity in this flow, we'll create new ones if they don't have IDs
        // and keep track of them for the map.
        foreach ($options as $optionData) {
            $option = $this->productOptionRepo->create([
                'product_id'             => $productId,
                'product_option_type_id' => $optionData['option_type_id'],
                'order'                  => $optionData['order'] ?? 1,
            ]);
            
            $option->load('optionType');

            foreach ($optionData['values'] as $index => $valueData) {
                $value = $this->productOptionValueRepo->create([
                    'product_option_id' => $option->id,
                    'value'             => $valueData['value'],
                    'hex_code'          => $valueData['hex_code'] ?? null,
                    'order'             => $valueData['order'] ?? ($index + 1),
                ]);

                if ($option->optionType) {
                    $map[strtolower($option->optionType->name)][strtolower($value->value)] = $value->id;
                }
            }
        }

        return $map;
    }

    private function getExistingOptionValueMap(int $productId): array
    {
        $map = [];
        $options = $this->productOptionRepo->getByProductId($productId);
        foreach ($options as $option) {
            foreach ($option->values as $value) {
                if ($option->optionType) {
                    $map[strtolower($option->optionType->name)][strtolower($value->value)] = $value->id;
                }
            }
        }
        return $map;
    }

    private function syncVariantOptionValues($variant, array $optionValues, array $optionValueMap): void
    {
        // Remove existing links
        $this->variantOptionValue->where('product_variant_id', $variant->id)->delete();

        foreach ($optionValues as $valueData) {
            $valueId = null;

            // Try to find the ID in the map
            foreach ($optionValueMap as $typeName => $values) {
                if (isset($values[strtolower($valueData['value'])])) {
                    $valueId = $values[strtolower($valueData['value'])];
                    break;
                }
            }

            if ($valueId) {
                $this->variantOptionValue->create([
                    'product_variant_id'      => $variant->id,
                    'product_option_value_id' => $valueId,
                ]);
            }
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
