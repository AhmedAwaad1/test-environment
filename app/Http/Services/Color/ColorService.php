<?php

namespace App\Http\Services\Color;

use App\Http\Resources\PaginationResource\PaginationResource;
use App\Http\Resources\Color\ColorResource;
use App\Repositories\Color\ColorRepository;
use Illuminate\Support\Facades\Response;

class ColorService
{
    protected $colorRepo;

    public function __construct(ColorRepository $colorRepo)
    {
        $this->colorRepo = $colorRepo;
    }


    public function getAllColors($request)
    {
        $query = $this->colorRepo->getAll();

        if ($request->per_page) {
            $colors = new PaginationResource($query->paginate($request->per_page), ColorResource::class);
        } else {
            $colors = ColorResource::collection($query->get());
        }

        return Response::successResponse($colors, 'colors retrieved successfully');
    }

    public function getColorById($id)
    {
        try {
            $color = $this->colorRepo->find($id);

            if (!$color) {
                return Response::errorResponse('color not found', [], 404);
            }

            return Response::successResponse(new ColorResource($color), 'color found successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            //exception if id not found
            return Response::handleModelNotFoundException($e, 'color');
        } catch (\Exception $e) {
            return Response::handleException($e, 'Failed to retrieve color');
        }
    }

    public function createColor($request)
    {
        try {
            $color = $this->colorRepo->create($request);

            return Response::successResponse(new ColorResource($color), 'color created successfully', 201);

        } catch (\Illuminate\Database\QueryException $e) {
            return Response::handleDatabaseException($e, 'create color');
        } catch (\Exception $e) {
            return Response::handleException($e, 'create color');
        }
    }

    public function updateColor($id, array $data)
    {
        try {
            $color = $this->colorRepo->update($id, $data);

            if (!$color) {
                return Response::errorResponse('Color not found', [], 404);
            }

            return Response::successResponse(new ColorResource($color), 'color updated successfully');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            //exception if id not found
            return Response::handleModelNotFoundException($e, 'color');
        } catch (\Exception $e) {
            return Response::handleException($e, 'update color');
        }
    }

    public function deleteColor($id)
    {
        $color = $this->colorRepo->find($id);

        if (!$color) {
            return Response::errorResponse('color not found', [], 404);
        }

        $this->colorRepo->delete($id);

        return Response::successResponse(['is_success' => 1], 'color deleted successfully');
    }
}
