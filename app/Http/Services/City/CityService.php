<?php

namespace App\Http\Services\City;

use App\Http\Resources\PaginationResource\PaginationResource;
use App\Http\Resources\City\CityResource;
use App\Http\Services\Country\CountryService;
use App\Repositories\City\CityRepository;
use Illuminate\Support\Facades\Response;

class CityService
{
    protected CityRepository $cityRepo;
    protected CountryService $countryService;

    public function __construct(
        CityRepository $cityRepo,
        CountryService $countryService
    )
    {
        $this->cityRepo = $cityRepo;
        $this->countryService = $countryService;
    }


    public function getAllCities($request)
    {
        $query = $this->cityRepo->getAll($request);

        if ($request->per_page) {
            $cities = new PaginationResource($query->paginate($request->per_page), CityResource::class);
        } else {
            $cities = CityResource::collection($query->get());
        }

        return Response::successResponse($cities, 'cities retrieved successfully');
    }

    public function getCityById($id)
    {
        try {
            $city = $this->cityRepo->find($id);

            if (!$city) {
                return Response::errorResponse('city not found', [], 404);
            }

            return Response::successResponse(new CityResource($city), 'city found successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            //exception if id not found
            return Response::handleModelNotFoundException($e, 'city');
        } catch (\Exception $e) {
            return Response::handleException($e, 'Failed to retrieve city');
        }
    }

    public function createCity($request)
    {
        try {
            $city = $this->cityRepo->create($request);

            return Response::successResponse(new CityResource($city), 'city created successfully', 201);

        } catch (\Illuminate\Database\QueryException $e) {
            return Response::handleDatabaseException($e, 'create city');
        } catch (\Exception $e) {
            return Response::handleException($e, 'create city');
        }
    }

    public function updateCity($id, array $data)
    {
        try {
            $city = $this->cityRepo->update($id, $data);

            if (!$city) {
                return Response::errorResponse('City not found', [], 404);
            }

            return Response::successResponse(new CityResource($city), 'city updated successfully');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            //exception if id not found
            return Response::handleModelNotFoundException($e, 'city');
        } catch (\Exception $e) {
            return Response::handleException($e, 'update city');
        }
    }

    public function deleteCity($id)
    {
        $city = $this->cityRepo->delete($id);

        if (!$city) {
            return Response::errorResponse('city not found', [], 404);
        }

        return Response::successResponse(['is_success' => 1], 'city deleted successfully');
    }


    public function getAllCitiesByIp($request)
    {
        $filters = $request->all();

        if (empty($filters['country_id'])) {
            $country = $this->countryService->getCountryObjectByIp();
            if ($country) {
                $filters['country_id'] = $country->id;
            }
        }

        $query = $this->cityRepo->getAll($filters);

        if ($request->per_page) {
            $cities = new PaginationResource($query->paginate($request->per_page), CityResource::class);
        } else {
            $cities = CityResource::collection($query->get());
        }

        return Response::successResponse($cities, 'تم جلب المدن بنجاح');
    }

}
