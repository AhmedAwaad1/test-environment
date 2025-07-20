<?php

namespace App\Http\Services\Country;

use App\Http\Resources\PaginationResource\PaginationResource;
use App\Http\Resources\Country\CountryResource;
use App\Http\Services\GeoCurrency\GeoCurrencyService;
use App\Repositories\Country\CountryRepository;
use Illuminate\Support\Facades\Response;

class CountryService
{
    protected CountryRepository $countryRepo;
    protected GeoCurrencyService $geoCurrencyService;

    public function __construct(
        CountryRepository $countryRepo,
        GeoCurrencyService $geoCurrencyService
    ) {
        $this->countryRepo = $countryRepo;
        $this->geoCurrencyService = $geoCurrencyService;
    }



    public function getAllCountries($request)
    {
        $query = $this->countryRepo->getAll();

        if ($request->per_page) {
            $countries = new PaginationResource($query->paginate($request->per_page), CountryResource::class);
        } else {
            $countries = CountryResource::collection($query->get());
        }

        return Response::successResponse($countries, 'Countries retrieved successfully');
    }

    public function getCountryById($id)
    {
        try {
            $country = $this->countryRepo->find($id);

            if (!$country) {
                return Response::errorResponse('country not found', [], 404);
            }

            return Response::successResponse(new CountryResource($country), 'country found successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            //exception if id not found
            return Response::handleModelNotFoundException($e, 'country');
        } catch (\Exception $e) {
            return Response::handleException($e, 'Failed to retrieve country');
        }
    }

    public function createCountry($request)
    {
        try {
            $country = $this->countryRepo->create($request);

            return Response::successResponse(new CountryResource($country), 'country created successfully', 201);

        } catch (\Illuminate\Database\QueryException $e) {
            return Response::handleDatabaseException($e, 'create country');
        } catch (\Exception $e) {
            return Response::handleException($e, 'create country');
        }
    }

    public function updateCountry($id, array $data)
    {
        try {
            $country = $this->countryRepo->update($id, $data);

            if (!$country) {
                return Response::errorResponse('country not found', [], 404);
            }

            return Response::successResponse(new CountryResource($country), 'country updated successfully');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            //exception if id not found
            return Response::handleModelNotFoundException($e, 'country');
        } catch (\Exception $e) {
            return Response::handleException($e, 'update country');
        }
    }

    public function deleteCountry($id)
    {
        $country = $this->countryRepo->find($id);

        if (!$country) {
            return Response::errorResponse('country not found', [], 404);
        }

        $this->countryRepo->delete($id);

        return Response::successResponse(['is_success' => 1], 'country deleted successfully');
    }

    public function getCountryByIp()
    {
        try {
            $code = $this->geoCurrencyService->getCountryCodeFromIp();

            if (!$code) {
                return Response::errorResponse('Unable to detect country from IP', [], 404);
            }

            $country = $this->countryRepo->findByCountryCode($code);

            if (!$country) {
                return Response::errorResponse("Country not found for code: $code", [], 404);
            }

            return Response::successResponse(
                new CountryResource($country),
                'Country retrieved successfully'
            );

        } catch (\Exception $e) {
            return Response::handleException($e, 'Failed to retrieve country by IP');
        }
    }
}
