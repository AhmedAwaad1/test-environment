<?php

namespace App\Http\Controllers\Country;

use App\Http\Controllers\Controller;
use App\Http\Requests\Country\CountryRequest;
use App\Http\Services\Country\CountryService;
use App\Http\Services\GeoCurrency\GeoCurrencyService;

class CountryController extends Controller
{
    protected GeoCurrencyService $geoCurrencyService;
    protected CountryService $countryService;
    public function __construct(
        GeoCurrencyService $geoCurrencyService,
        CountryService $countryService
    )
    {
        $this->countryService = $countryService;
        $this->geoCurrencyService = $geoCurrencyService;

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
        return $this->countryService->getCountryByIp();
    }
}
