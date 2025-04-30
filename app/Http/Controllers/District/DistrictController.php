<?php

namespace App\Http\Controllers\District;

use App\Http\Controllers\Controller;
use App\Http\Requests\District\DistrictRequest;
use App\Http\Services\District\DistrictService;

class DistrictController extends Controller
{
    public $districtService;
    public function __construct(DistrictService $districtService)
    {
        $this->districtService = $districtService;
    }

    public function index(DistrictRequest $request)
    {
        return $this->districtService->getAllDistricts($request);
    }

    public function show(DistrictRequest $request)
    {
        return $this->districtService->getDistrictById($request->id);
    }

    public function store(DistrictRequest $request)
    {
        return $this->districtService->createDistrict($request->validated());
    }

    public function update(DistrictRequest $request, $id)
    {
        return $this->districtService->updateDistrict($id, $request->validated());
    }

    public function destroy(DistrictRequest $request)
    {
        return $this->districtService->deleteDistrict($request->id);
    }
}
