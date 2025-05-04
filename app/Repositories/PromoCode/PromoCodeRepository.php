<?php

namespace App\Repositories\PromoCode;

use App\Models\PromoCode;

class PromoCodeRepository
{
    public function getAll($request)
    {
        return PromoCode::query();
    }

    public function find($id)
    {
        return PromoCode::find($id);
    }

    public function findByCode($code)
    {
        return PromoCode::where('code', $code)->first();
    }
    
    public function create(array $data)
    {
        return PromoCode::create($data);
    }

    public function update($id, array $data)
    {
        $promoCode = $this->find($id);

        if ($promoCode) {
            $promoCode->update($data);
        }
        return $promoCode;
    }

    public function delete($id)
    {
        $promoCode = $this->find($id);
        if ($promoCode) {
            $promoCode->delete();
        }
        return $promoCode;
    }
}
