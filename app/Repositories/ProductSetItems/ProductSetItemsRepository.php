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
        $query = $this->model->with(['product'])->filter($request);

        if ($request->has('per_page')) {
            return $query->paginate($request->per_page);
        }

        return $query->get();
    }

    public function find($id)
    {
        return $this->model
            ->with(['product'])
            ->findOrFail($id);
    }

    public function create(array $data)
    {
        try {
            DB::beginTransaction();

            $productSetItem = $this->model->create([
                'product_id' => $data['product_id'],
                'name_en' => $data['name_en'] ?? null,
                'name_ar' => $data['name_ar'] ?? null,
                'description_en' => $data['description_en'] ?? null,
                'description_ar' => $data['description_ar'] ?? null,
                'how_to_use_en' => $data['how_to_use_en'] ?? null,
                'how_to_use_ar' => $data['how_to_use_ar'] ?? null,
                'features_en' => $data['features_en'] ?? null,
                'features_ar' => $data['features_ar'] ?? null,
                'image' => $data['image'] ?? null,
            ]);

            DB::commit();
            return $productSetItem;
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

            $productSetItem->update(array_filter([
                'product_id' => $data['product_id'] ?? null,
                'name_en' => $data['name_en'] ?? null,
                'name_ar' => $data['name_ar'] ?? null,
                'description_en' => $data['description_en'] ?? null,
                'description_ar' => $data['description_ar'] ?? null,
                'how_to_use_en' => $data['how_to_use_en'] ?? null,
                'how_to_use_ar' => $data['how_to_use_ar'] ?? null,
                'features_en' => $data['features_en'] ?? null,
                'features_ar' => $data['features_ar'] ?? null,
                'image' => $data['image'] ?? null,
            ]));

            DB::commit();
            return $productSetItem->fresh();
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