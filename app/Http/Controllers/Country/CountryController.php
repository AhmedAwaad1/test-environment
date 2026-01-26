<?php

namespace App\Http\Controllers\Country;

use App\Http\Controllers\Controller;
use App\Http\Requests\Country\CountryRequest;
use App\Http\Services\Country\CountryService;

class CountryController extends Controller
{
    protected CountryService $countryService;
    public function __construct(
        CountryService $countryService
    )
    {
        $this->countryService = $countryService;
    }

    public function index(CountryRequest $request)
    {
        return $this->countryService->getAllCountries($request);
    }

    public function show(CountryRequest $request)
    {
        return $this->countryService->getCountryById($request->id);
    }

    public function store(CountryRequest $request)
    {
        return $this->countryService->createCountry($request->validated());
    }

    public function update(CountryRequest $request, $id)
    {
        return $this->countryService->updateCountry($id, $request->validated());
    }

    public function destroy(CountryRequest $request)
    {
        return $this->countryService->deleteCountry($request->id);
    }

    public function getCountriesByIp(CountryRequest $request)
    {
        return $this->countryService->detectCountryByIp();
    }
}
