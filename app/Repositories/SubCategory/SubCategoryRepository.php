<?php

namespace App\Repositories\SubCategory;

use App\Models\SubCategory;

class SubCategoryRepository
{
    public function getAll($request)
    {
        return SubCategory::with('category')->filter($request)->orderBy('order', 'asc');
    }

    public function find($id)
    {
        return SubCategory::find($id);
    }

    public function create(array $data)
    {
        return SubCategory::create($data);
    }

    public function update($id, array $data)
    {
        $subCategory = $this->find($id);

        if ($subCategory) {
            $subCategory->update($data);
        }
        return $subCategory;
    }

    public function delete($id)
    {
        $subCategory = $this->find($id);
        if ($subCategory) {
            $subCategory->delete();
        }
        return $subCategory;
    }
}
