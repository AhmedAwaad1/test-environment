<?php

namespace App\Repositories\Color;

use App\Models\Color;

class ColorRepository
{
    public function getAll()
    {
        return Color::query();
    }

    public function find($id)
    {
        return Color::find($id);
    }

    public function create(array $data)
    {
        return Color::create($data);
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
