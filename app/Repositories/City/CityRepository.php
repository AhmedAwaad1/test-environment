<?php

namespace App\Repositories\City;

use App\Models\City;

class CityRepository
{
    public function getAll()
    {
        return City::with('country');
    }

    public function find($id)
    {
        return City::with('country')->find($id);
    }

    public function create(array $data)
    {
        return City::create($data);
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
