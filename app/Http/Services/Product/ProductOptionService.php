<?php

namespace App\Http\Services\Product;

use App\Http\Resources\ProductOption\ProductOptionResource;
use App\Http\Resources\ProductOptionValue\ProductOptionValueResource;
use App\Repositories\Product\ProductRepository;
use App\Repositories\ProductOption\ProductOptionRepository;
use App\Repositories\ProductOptionValue\ProductOptionValueRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class ProductOptionService
{
    public function __construct(
        protected ProductRepository $productRepo,
        protected ProductOptionRepository $optionRepo,
        protected ProductOptionValueRepository $optionValueRepo
    ) {}

    public function getProductOptions($productId)
    {
        try {
            $product = $this->productRepo->find($productId);

            if (!$product) {
                return Response::errorResponse('Product not found', [], 404);
            }

            if (!$product->has_variants) {
                return Response::errorResponse('Product does not have variants', [], 400);
            }

            $options = $this->optionRepo->getByProductId($productId);

            return Response::successResponse(
                ProductOptionResource::collection($options),
                'Product options retrieved successfully'
            );
        } catch (\Exception $e) {
            return Response::handleException($e, 'Failed to retrieve product options');
        }
    }

    public function addProductOption($productId, array $data)
    {
        try {
            DB::beginTransaction();

            $product = $this->productRepo->find($productId);

            if (!$product) {
                return Response::errorResponse('Product not found', [], 404);
            }

            if (!$product->has_variants) {
                return Response::errorResponse('Product does not have variants', [], 400);
            }

            $option = $this->optionRepo->create([
                'product_id' => $productId,
                'product_option_type_id' => $data['option_type_id'],
                'order' => $data['order'] ?? 1,
            ]);

            // Add option values if provided
            if (!empty($data['values'])) {
                foreach ($data['values'] as $valueData) {
                    $value = $this->optionValueRepo->create([
                        'product_option_id' => $option->id,
                        'value' => $valueData['value'],
                        'hex_code' => $valueData['hex_code'] ?? null,
                        'order' => $valueData['order'] ?? 1,
                    ]);

                    // Handle images if they exist
                    if (!empty($valueData['images'])) {
                        foreach ($valueData['images'] as $imageIndex => $imagePath) {
                            $value->images()->create([
                                'image' => $imagePath,
                                'order' => $imageIndex + 1,
                            ]);
                        }
                    }
                }
            }

            $option->load(['values.images', 'optionType']);

            DB::commit();

            return Response::successResponse(
                new ProductOptionResource($option),
                'Product option added successfully',
                201
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return Response::handleException($e, 'Failed to add product option');
        }
    }

    public function updateOption($optionId, array $data)
    {
        try {
            DB::beginTransaction();

            $option = $this->optionRepo->update($optionId, [
                'product_option_type_id' => $data['option_type_id'],
                'order' => $data['order'] ?? 1,
            ]);

            if (!$option) {
                return Response::errorResponse('Option not found', [], 404);
            }

            $option->load(['values.images', 'optionType']);

            DB::commit();

            return Response::successResponse(
                new ProductOptionResource($option),
                'Product option updated successfully'
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return Response::handleException($e, 'Failed to update product option');
        }
    }

    public function deleteOption($optionId)
    {
        try {
            $isDeleted = $this->optionRepo->delete($optionId);

            if (!$isDeleted) {
                return Response::errorResponse('Failed to delete option', [], 400);
            }

            return Response::successResponse(
                ['is_success' => true],
                'Product option deleted successfully'
            );
        } catch (\Exception $e) {
            return Response::handleException($e, 'Failed to delete product option');
        }
    }

    public function addOptionValue($optionId, array $data)
    {
        try {
            DB::beginTransaction();

            $value = $this->optionValueRepo->create([
                'product_option_id' => $optionId,
                'value' => $data['value'],
                'hex_code' => $data['hex_code'] ?? null,
                'order' => $data['order'] ?? 1,
            ]);

            // Handle images if they exist
            if (!empty($data['images'])) {
                foreach ($data['images'] as $imageIndex => $imagePath) {
                    $value->images()->create([
                        'image' => $imagePath,
                        'order' => $imageIndex + 1,
                    ]);
                }
            }

            $value->load(['images', 'productOption.optionType']);

            DB::commit();

            return Response::successResponse(
                new ProductOptionValueResource($value),
                'Option value added successfully',
                201
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return Response::handleException($e, 'Failed to add option value');
        }
    }

    public function updateOptionValue($valueId, array $data)
    {
        try {
            DB::beginTransaction();

            $value = $this->optionValueRepo->update($valueId, [
                'value' => $data['value'],
                'hex_code' => $data['hex_code'] ?? null,
                'order' => $data['order'] ?? 1,
            ]);

            if (!$value) {
                return Response::errorResponse('Option value not found', [], 404);
            }

            // Handle images if they exist
            if (!empty($data['images'])) {
                // Remove existing images
                $value->images()->delete();

                // Add new images
                foreach ($data['images'] as $imageIndex => $imagePath) {
                    $value->images()->create([
                        'image' => $imagePath,
                        'order' => $imageIndex + 1,
                    ]);
                }
            }

            $value->load(['images', 'productOption.optionType']);

            DB::commit();

            return Response::successResponse(
                new ProductOptionValueResource($value),
                'Option value updated successfully'
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return Response::handleException($e, 'Failed to update option value');
        }
    }

    public function deleteOptionValue($valueId)
    {
        try {
            $isDeleted = $this->optionValueRepo->delete($valueId);

            if (!$isDeleted) {
                return Response::errorResponse('Option value not found', [], 404);
            }

            return Response::successResponse(
                ['is_success' => true],
                'Option value deleted successfully'
            );
        } catch (\Exception $e) {
            return Response::handleException($e, 'Failed to delete option value');
        }
    }

    public function reorderOptionValues($optionId, array $order)
    {
        try {
            DB::beginTransaction();

            foreach ($order as $valueId => $position) {
                $this->optionValueRepo->update($valueId, ['order' => $position]);
            }

            $option = $this->optionRepo->getById($optionId);
            $option->load(['values.images', 'optionType']);

            DB::commit();

            return Response::successResponse(
                new ProductOptionResource($option),
                'Option values reordered successfully'
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return Response::handleException($e, 'Failed to reorder option values');
        }
    }
}
