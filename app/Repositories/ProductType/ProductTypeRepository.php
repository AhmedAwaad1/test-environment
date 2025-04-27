<?php

namespace App\Repositories\ProductType;

use App\Models\ProductType;

class ProductTypeRepository
{
    public function getAll()
    {
        return ProductType::query();
    }

    public function find($id)
    {
        return ProductType::find($id);
    }

    public function create(array $data)
    {
        return ProductType::create($data);
    }

    public function update($id, array $data)
    {
        $productType = $this->find($id);

        if ($productType) {
            $productType->update($data);
        }
        return $productType;
    }

    public function delete($id)
    {
        $productType = $this->find($id);
        if ($productType) {
            $productType->delete();
        }
        return $productType;
    }
}
