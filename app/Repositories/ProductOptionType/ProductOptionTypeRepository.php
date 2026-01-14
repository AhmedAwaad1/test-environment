<?php

namespace App\Repositories\ProductOptionType;

use App\Models\ProductOptionType;

class ProductOptionTypeRepository
{
    public function getAll($request)
    {
        $query = ProductOptionType::query();
        
        if ($request->has('name')) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        return $request->per_page 
            ? $query->paginate($request->per_page) 
            : $query->get();
    }

    public function find($id)
    {
        return ProductOptionType::find($id);
    }

    public function create(array $data)
    {
        return ProductOptionType::create($data);
    }

    public function update($id, array $data)
    {
        $type = $this->find($id);
        if ($type) {
            $type->update($data);
        }
        return $type;
    }

    public function delete($id)
    {
        $type = $this->find($id);
        if ($type) {
            $type->delete();
        }
        return $type;
    }
}
