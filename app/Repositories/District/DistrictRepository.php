<?php

namespace App\Repositories\District;

use App\Models\District;

class DistrictRepository
{
    public function getAll($request)
    {
        return District::with('city')->filter($request);;
    }

    public function find($id)
    {
        return District::with('city')->find($id);
    }

    public function create(array $data)
    {
        return District::create($data);
    }

    public function update($id, array $data)
    {
        $district = $this->find($id);

        if ($district) {
            $district->update($data);
        }
        return $district;
    }

    public function delete($id)
    {
        $district = $this->find($id);
        if ($district) {
            $district->delete();
        }
        return $district;
    }
}
