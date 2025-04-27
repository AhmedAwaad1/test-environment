<?php

namespace App\Http\Services\Size;

use App\Http\Resources\PaginationResource\PaginationResource;
use App\Http\Resources\Size\SizeResource;
use App\Repositories\Size\SizeRepository;
use Illuminate\Support\Facades\Response;

class SizeService
{
    protected $sizeRepo;

    public function __construct(SizeRepository $sizeRepo)
    {
        $this->sizeRepo = $sizeRepo;
    }


    public function getAllSizes($request)
    {
        $query = $this->sizeRepo->getAll();

        if ($request->per_page) {
            $sizes = new PaginationResource($query->paginate($request->per_page), SizeResource::class);
        } else {
            $sizes = SizeResource::collection($query->get());
        }

        return Response::successResponse($sizes, 'sizes retrieved successfully');
    }

    public function getSizeById($id)
    {
        try {
            $size = $this->sizeRepo->find($id);

            if (!$size) {
                return Response::errorResponse('size not found', [], 404);
            }

            return Response::successResponse(new SizeResource($size), 'size found successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            //exception if id not found
            return Response::handleModelNotFoundException($e, 'size');
        } catch (\Exception $e) {
            return Response::handleException($e, 'Failed to retrieve size');
        }
    }

    public function createSize($request)
    {
        try {
            $size = $this->sizeRepo->create($request);

            return Response::successResponse(new SizeResource($size), 'size created successfully', 201);

        } catch (\Illuminate\Database\QueryException $e) {
            return Response::handleDatabaseException($e, 'create size');
        } catch (\Exception $e) {
            return Response::handleException($e, 'create size');
        }
    }

    public function updateSize($id, array $data)
    {
        try {
            $size = $this->sizeRepo->update($id, $data);

            if (!$size) {
                return Response::errorResponse('Size not found', [], 404);
            }

            return Response::successResponse(new SizeResource($size), 'size updated successfully');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            //exception if id not found
            return Response::handleModelNotFoundException($e, 'size');
        } catch (\Exception $e) {
            return Response::handleException($e, 'update size');
        }
    }

    public function deleteSize($id)
    {
        $size = $this->sizeRepo->find($id);

        if (!$size) {
            return Response::errorResponse('size not found', [], 404);
        }

        $this->sizeRepo->delete($id);

        return Response::successResponse(['is_success' => 1], 'size deleted successfully');
    }
}
