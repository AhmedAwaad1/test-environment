<?php

namespace App\Http\Controllers\City;

use App\Http\Controllers\Controller;
use App\Http\Requests\City\CityRequest;
use App\Http\Services\City\CityService;

class CityController extends Controller
{
    public $cityService;
    public function __construct(CityService $cityService)
    {
        $this->cityService = $cityService;
    }

    public function index(CityRequest $request)
    {
        return $this->cityService->getAllCities($request);
    }

    public function show(CityRequest $request)
    {
        return $this->cityService->getCityById($request->id);
    }

    public function store(CityRequest $request)
    {
        return $this->cityService->createCity($request->validated());
    }

    public function update(CityRequest $request, $id)
    {
        return $this->cityService->updateCity($id, $request->validated());
    }

    public function destroy(CityRequest $request)
    {
        return $this->cityService->deleteCity($request->id);
    }
}
