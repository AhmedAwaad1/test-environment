<?php

namespace App\Http\Services\Product;

use App\Models\Product;
use App\Models\VariantOptionValue;
use App\Http\Resources\ProductVariant\ProductVariantResource;
use App\Repositories\ProductOption\ProductOptionRepository;
use App\Repositories\ProductOptionValue\ProductOptionValueRepository;
use App\Repositories\ProductVariant\ProductVariantRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Response;

class ProductVariantService
{
    public function __construct(
        protected ProductOptionRepository      $productOptionRepo,
        protected ProductOptionValueRepository $productOptionValueRepo,
        protected ProductVariantRepository     $productVariantRepo,
        protected VariantOptionValue           $variantOptionValue
    ) {}

    /**
     * Create variants for a product
     */
    public function createVariants(int $productId, array $variants, array $optionValueMap): void
    {
        foreach ($variants as $variant) {
            $defaultPrice = 0;
            $defaultPriceAfterDiscount = null;

            if (!empty($variant['prices'])) {
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

    /**
     * Update product variants
     */
    public function updateProductVariants(int $productId, array $data): void
    {
        $optionValueMap = [];
        if (!empty($data['options'])) {
            $optionValueMap = $this->updateOptionsAndValues($productId, $data['options']);
        } else {
            $optionValueMap = $this->getExistingOptionValueMap($productId);
        }

        $existingVariants = $this->productVariantRepo->getByProductId($productId);
        $processedIds = [];

        if (!empty($data['variants'])) {
            foreach ($data['variants'] as $variantData) {
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
                    $variant = $this->productVariantRepo->update($variantData['id'], $variantFields);
                    $processedIds[] = $variant->id;
                } else {
                    $variantFields['product_id'] = $productId;
                    $variant = $this->productVariantRepo->create($variantFields);
                    $processedIds[] = $variant->id;
                }

                if (!empty($variantData['prices'])) {
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

                if (!empty($variantData['option_values'])) {
                    $this->syncVariantOptionValues($variant, $variantData['option_values'], $optionValueMap);
                }
            }
        }

        foreach ($existingVariants as $existingVariant) {
            if (!in_array($existingVariant->id, $processedIds)) {
                $this->productVariantRepo->delete($existingVariant->id);
            }
        }
    }

    /**
     * Create options and values
     */
    public function createOptionsAndValues(int $productId, array $options): array
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

    /**
     * Delete all variants and options for a product
     */
    public function deleteAllVariants(Product $product): void
    {
        foreach ($product->productVariants as $variant) {
            $this->productVariantRepo->delete($variant->id);
        }

        foreach ($product->productOptions as $option) {
            $this->productOptionRepo->delete($option->id);
        }
    }

    /**
     * Get variant by selected options
     */
    public function getVariantByOptions($productId, array $selectedOptions)
    {
        try {
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

    /**
     * Handle variant updates during product update.
     */
    public function handleProductVariantsUpdate(Product $product, array $data): void
    {
        // If has_variants is not provided, we don't change the variant structure
        if (!isset($data['has_variants'])) {
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

    protected function updateOptionsAndValues(int $productId, array $options): array
    {
        $map = [];
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

    protected function getExistingOptionValueMap(int $productId): array
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

    protected function syncVariantOptionValues($variant, array $optionValues, array $optionValueMap): void
    {
        $this->variantOptionValue->where('product_variant_id', $variant->id)->delete();

        foreach ($optionValues as $valueData) {
            $valueId = null;
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
}
