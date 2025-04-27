<?php

namespace App\Repositories\Size;

use App\Models\Size;

class SizeRepository
{
    public function getAll()
    {
        return Size::query();
    }

    public function find($id)
    {
        return Size::find($id);
    }

    public function create(array $data)
    {
        return Size::create($data);
    }

    public function update($id, array $data)
    {
        $city = $this->find($id);

        if ($city) {
            $city->update($data);
        }
        return $city;
    }

    public function delete($id)
    {
        $city = $this->find($id);
        if ($city) {
            $city->delete();
        }
        return $city;
    }
}
