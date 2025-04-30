<?php

namespace App\Http\Services\District;

use App\Http\Resources\PaginationResource\PaginationResource;
use App\Http\Resources\District\DistrictResource;
use App\Repositories\District\DistrictRepository;
use Illuminate\Support\Facades\Response;

class DistrictService
{
    protected $districtRepo;

    public function __construct(DistrictRepository $districtRepo)
    {
        $this->districtRepo = $districtRepo;
    }


    public function getAllDistricts($request)
    {
        $query = $this->districtRepo->getAll($request);

        if ($request->per_page) {
            $districts = new PaginationResource($query->paginate($request->per_page), DistrictResource::class);
        } else {
            $districts = DistrictResource::collection($query->get());
        }

        return Response::successResponse($districts, 'districts retrieved successfully');
    }

    public function getDistrictById($id)
    {
        try {
            $district = $this->districtRepo->find($id);

            if (!$district) {
                return Response::errorResponse('district not found', [], 404);
            }

            return Response::successResponse(new DistrictResource($district), 'district found successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            //exception if id not found
            return Response::handleModelNotFoundException($e, 'district');
        } catch (\Exception $e) {
            return Response::handleException($e, 'Failed to retrieve district');
        }
    }

    public function createDistrict($request)
    {
        try {
            $district = $this->districtRepo->create($request);

            return Response::successResponse(new DistrictResource($district), 'district created successfully', 201);

        } catch (\Illuminate\Database\QueryException $e) {
            return Response::handleDatabaseException($e, 'create district');
        } catch (\Exception $e) {
            return Response::handleException($e, 'create district');
        }
    }

    public function updateDistrict($id, array $data)
    {
        try {
            $district = $this->districtRepo->update($id, $data);

            if (!$district) {
                return Response::errorResponse('District not found', [], 404);
            }

            return Response::successResponse(new DistrictResource($district), 'district updated successfully');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            //exception if id not found
            return Response::handleModelNotFoundException($e, 'district');
        } catch (\Exception $e) {
            return Response::handleException($e, 'update district');
        }
    }

    public function deleteDistrict($id)
    {
        $district = $this->districtRepo->find($id);

        if (!$district) {
            return Response::errorResponse('district not found', [], 404);
        }

        $this->districtRepo->delete($id);

        return Response::successResponse(['is_success' => 1], 'district deleted successfully');
    }
}
