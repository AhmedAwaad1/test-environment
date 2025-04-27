<?php

namespace App\Repositories\Country;

use App\Models\Country;

class CountryRepository
{
    public function getAll()
    {
        return Country::with('cities');
    }

    public function find($id)
    {
        return Country::find($id);
    }

    public function create(array $data)
    {
        return Country::create($data);
    }

    public function update($id, array $data)
    {
        $country = $this->find($id);

        if ($country) {
            $country->update($data);
        }
        return $country;
    }

    public function delete($id)
    {
        $country = $this->find($id);
        if ($country) {
            $country->delete();
        }
        return $country;
    }
}
