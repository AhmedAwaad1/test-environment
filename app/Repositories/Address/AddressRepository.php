<?php

namespace App\Repositories\Address;

use App\Models\Address;

class AddressRepository
{
    public function getAll($request)
    {
        return Address::with(['country', 'city.country', 'district', 'user'])
                      ->filter($request);
    }

    public function find($id, $userId)
    {
        return Address::with(['country', 'city.country', 'district', 'user'])
                      ->where('user_id', $userId)
                      ->where('id', $id)
                      ->first();
    }


    public function create(array $data)
    {
        return Address::create($data);
    }

    public function update($id, array $data)
    {
        $address = $this->find($id, $data['user_id']);

        if ($address) {
            $address->update($data);
        }
        return $address;
    }

    public function delete($id, $userId)
    {
        $address = $this->find($id, $userId);
        if ($address) {
            $address->delete();
        }
        return $address;
    }

    public function updateDefaultAddress($userId, $addressId)
    {
        $address = Address::where('user_id', $userId)
                          ->where('id', '!=', $addressId)
                          ->update(['is_default' => false]);

        return $address;
    }

}
