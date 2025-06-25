<?php

namespace App\Repositories\ProductPrice;

use App\Models\ProductPrice;
use Illuminate\Support\Facades\DB;

class ProductPriceRepository
{
    public function __construct(protected ProductPrice $model) {

    }

    public function getAll($request)
    {
        $query = $this->model->with(['product', 'currency'])->filter($request);

        if ($request->has('per_page')) {
            return $query->paginate($request->per_page);
        }

        return $query->get();
    }

    public function find($id)
    {
        return $this->model
            ->with(['product', 'currency'])
            ->findOrFail($id);
    }

    public function create(array $data)
    {
        try {
            DB::beginTransaction();

            $productPrice = $this->model->create([
                'product_id' => $data['product_id'],
                'currency_id' => $data['currency_id'],
                'price' => $data['price'],
                'price_after_discount' => $data['price_after_discount'] ?? null,
            ]);

            DB::commit();
            return $productPrice;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function update($id, array $data)
    {
        try {
            DB::beginTransaction();

            $productPrice = $this->model->findOrFail($id);

            $productPrice->update(array_filter([
                'product_id' => $data['product_id'] ?? null,
                'currency_id' => $data['currency_id'] ?? null,
                'price' => $data['price'] ?? null,
                'price_after_discount' => $data['price_after_discount'] ?? null,
            ]));

            DB::commit();
            return $productPrice->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function delete($id)
    {
        try {
            DB::beginTransaction();

            $productPrice = $this->model->findOrFail($id);

            $isDeleted = $productPrice->delete();

            DB::commit();
            return $isDeleted;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
} 