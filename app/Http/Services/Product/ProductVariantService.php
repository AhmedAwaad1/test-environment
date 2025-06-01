<?php

namespace App\Http\Services\Product;

use App\Http\Resources\ProductVariant\ProductVariantResource;
use App\Repositories\Product\ProductRepository;
use App\Repositories\ProductVariant\ProductVariantRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class ProductVariantService
{
    public function __construct(
        protected ProductRepository $productRepo,
        protected ProductVariantRepository $variantRepo
    ) {}

    public function getProductVariants($productId)
    {
        try {
            $product = $this->productRepo->find($productId);

            if (!$product) {
                return Response::errorResponse('Product not found', [], 404);
            }

            if (!$product->has_variants) {
                return Response::errorResponse('Product does not have variants', [], 400);
            }

            $variants = $this->variantRepo->getByProductId($productId);

            return Response::successResponse(
                ProductVariantResource::collection($variants),
                'Variants retrieved successfully'
            );
        } catch (\Exception $e) {
            return Response::handleException($e, 'Failed to retrieve variants');
        }
    }

    public function updateVariant($variantId, array $data)
    {
        try {
            DB::beginTransaction();

            $variant = $this->variantRepo->find($variantId);

            if (!$variant) {
                return Response::errorResponse('Variant not found', [], 404);
            }

            $variant = $this->variantRepo->update($variantId, $data);

            // Update option values if provided
            if (!empty($data['option_values'])) {
                $this->updateVariantOptionValues($variant, $data['option_values']);
            }

            DB::commit();

            return Response::successResponse(
                new ProductVariantResource($variant->load('optionValues')),
                'Variant updated successfully'
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return Response::handleException($e, 'Failed to update variant');
        }
    }

    public function updateVariantStock($variantId, $quantity)
    {
        try {
            $variant = $this->variantRepo->find($variantId);

            if (!$variant) {
                return Response::errorResponse('Variant not found', [], 404);
            }

            $variant = $this->variantRepo->update($variantId, ['quantity' => $quantity]);

            return Response::successResponse(
                new ProductVariantResource($variant),
                'Stock updated successfully'
            );
        } catch (\Exception $e) {
            return Response::handleException($e, 'Failed to update stock');
        }
    }

    public function toggleVariantStatus($variantId)
    {
        try {
            $variant = $this->variantRepo->find($variantId);

            if (!$variant) {
                return Response::errorResponse('Variant not found', [], 404);
            }

            $variant = $this->variantRepo->toggleStatus($variantId);

            return Response::successResponse(
                new ProductVariantResource($variant),
                'Status updated successfully'
            );
        } catch (\Exception $e) {
            return Response::handleException($e, 'Failed to toggle status');
        }
    }

    public function deleteVariant($variantId)
    {
        try {
            DB::beginTransaction();

            $variant = $this->variantRepo->find($variantId);

            if (!$variant) {
                return Response::errorResponse('Variant not found', [], 404);
            }

            // Check if this is the last variant
            $variantsCount = $variant->product->productVariants()->count();
            if ($variantsCount <= 1) {
                return Response::errorResponse('Cannot delete the last variant', [], 400);
            }

            $this->variantRepo->delete($variantId);

            DB::commit();

            return Response::successResponse(
                null,
                'Variant deleted successfully'
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return Response::handleException($e, 'Failed to delete variant');
        }
    }

    public function getVariantByOptions($productId, array $optionValues)
    {
        try {
            $product = $this->productRepo->find($productId);

            if (!$product) {
                return Response::errorResponse('Product not found', [], 404);
            }

            if (!$product->has_variants) {
                return Response::errorResponse('Product does not have variants', [], 400);
            }

            $variant = $this->variantRepo->findByOptions($productId, $optionValues);

            if (!$variant) {
                return Response::errorResponse('Variant not found', [], 404);
            }

            return Response::successResponse(
                new ProductVariantResource($variant->load(['optionValues.productOption.optionType', 'optionValues.images'])),
                'Variant found successfully'
            );
        } catch (\Exception $e) {
            return Response::handleException($e, 'Failed to find variant');
        }
    }

    private function updateVariantOptionValues($variant, array $optionValues)
    {
        // Get existing option value IDs
        $existingValueIds = $variant->optionValues->pluck('id')->toArray();

        // Get new option value IDs
        $newValueIds = collect($optionValues)->pluck('id')->filter()->toArray();

        // Get values to delete (existing but not in new)
        $toDelete = array_diff($existingValueIds, $newValueIds);

        // Delete removed values
        if (!empty($toDelete)) {
            $variant->optionValues()->detach($toDelete);
        }

        // Add new values
        $variant->optionValues()->syncWithoutDetaching($newValueIds);
    }
}
