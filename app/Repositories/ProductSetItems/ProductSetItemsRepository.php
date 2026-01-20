<?php

namespace App\Repositories\ProductSetItems;

use App\Models\ProductSetItems;
use Illuminate\Support\Facades\DB;

class ProductSetItemsRepository
{
    public function __construct(protected ProductSetItems $model) {

    }

    public function getAll($request)
    {
        $query = $this->model->with(['category', 'subCategory', 'products.productPrices.currency', 'products.images'])->filter($request);

        if ($request->has('per_page')) {
            return $query->paginate($request->per_page);
        }

        return $query->get();
    }

    public function find($id)
    {
        return $this->model
            ->with(['category', 'subCategory', 'products.productPrices.currency', 'products.images', 'products.category', 'products.subCategory'])
            ->findOrFail($id);
    }

    public function create(array $data)
    {
        try {
            DB::beginTransaction();

            $productSetItem = $this->model->create([
                'category_id' => $data['category_id'],
                'sub_category_id' => $data['sub_category_id'] ?? null,
                'sku' => $data['sku'] ?? null,
                'quantity' => $data['quantity'] ?? 0,
                'is_active' => $data['is_active'] ?? true,
                'name_en' => $data['name_en'] ?? null,
                'name_ar' => $data['name_ar'] ?? null,
                'description_en' => $data['description_en'] ?? null,
                'description_ar' => $data['description_ar'] ?? null,
                'image' => $data['image'] ?? null,
            ]);

            // Attach products to the set
            if (!empty($data['product_ids']) && is_array($data['product_ids'])) {
                $productSetItem->products()->attach($data['product_ids']);
            }

            DB::commit();
            return $productSetItem->load(['products.productPrices.currency', 'products.images']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update($id, array $data)
    {
        try {
            DB::beginTransaction();

            $productSetItem = $this->model->findOrFail($id);

            $updateData = array_filter([
                'category_id' => $data['category_id'] ?? null,
                'sub_category_id' => $data['sub_category_id'] ?? null,
                'sku' => $data['sku'] ?? null,
                'name_en' => $data['name_en'] ?? null,
                'name_ar' => $data['name_ar'] ?? null,
                'description_en' => $data['description_en'] ?? null,
                'description_ar' => $data['description_ar'] ?? null,
                'image' => $data['image'] ?? null,
            ], fn($value) => $value !== null);

            // Handle quantity and is_active separately as they can be 0/false
            if (array_key_exists('quantity', $data)) {
                $updateData['quantity'] = $data['quantity'];
            }
            if (array_key_exists('is_active', $data)) {
                $updateData['is_active'] = $data['is_active'];
            }

            $productSetItem->update($updateData);

            // Update products if provided
            if (isset($data['product_ids']) && is_array($data['product_ids'])) {
                $productSetItem->products()->sync($data['product_ids']);
            }

            DB::commit();
            return $productSetItem->fresh()->load(['products.productPrices.currency', 'products.images']);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function delete($id)
    {
        try {
            DB::beginTransaction();

            $productSetItem = $this->model->findOrFail($id);

            $isDeleted = $productSetItem->delete();

            DB::commit();
            return $isDeleted;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
} 