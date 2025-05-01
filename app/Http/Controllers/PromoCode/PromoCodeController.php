<?php

namespace App\Http\Controllers\PromoCode;

use App\Http\Controllers\Controller;
use App\Http\Requests\PromoCode\PromoCodeRequest;
use App\Http\Services\PromoCode\PromoCodeService;

class PromoCodeController extends Controller
{
    protected $promoCodeService;

    public function __construct(PromoCodeService $promoCodeService)
    {
        $this->promoCodeService = $promoCodeService;
    }

    public function index(PromoCodeRequest $request)
    {
        return $this->promoCodeService->getAllPromoCodes($request);
    }

    public function show($id)
    {
        return $this->promoCodeService->getPromoCodeById($id);
    }

    public function store(PromoCodeRequest $request)
    {
        return $this->promoCodeService->createPromoCode($request->validated());
    }

    public function update(PromoCodeRequest $request, $id)
    {
        $validatedData = $request->validated();
        return $this->promoCodeService->updatePromoCode($id, $validatedData);
    }

    public function destroy($id)
    {
        return $this->promoCodeService->deletePromoCode($id);
    }
}
