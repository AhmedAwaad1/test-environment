<?php

namespace App\Http\Services\Banner;

use App\Http\Resources\PaginationResource\PaginationResource;
use App\Http\Resources\Banner\BannerResource;
use App\Repositories\Banner\BannerRepository;
use Illuminate\Support\Facades\Response;

class BannerService
{
    protected $bannerRepo;

    public function __construct(BannerRepository $bannerRepo)
    {
        $this->bannerRepo = $bannerRepo;
    }


    public function getAllBanners($request)
    {
        $query = $this->bannerRepo->getAll();

        if ($request->per_page) {
            $banners = new PaginationResource($query->paginate($request->per_page), BannerResource::class);
        } else {
            $banners = BannerResource::collection($query->get());
        }

        return Response::successResponse($banners, 'banners retrieved successfully');
    }

    public function getBannerById($id)
    {
        try {
            $banner = $this->bannerRepo->find($id);

            if (!$banner) {
                return Response::errorResponse('banner not found', [], 404);
            }

            return Response::successResponse(new BannerResource($banner), 'banner found successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            //exception if id not found
            return Response::handleModelNotFoundException($e, 'banner');
        } catch (\Exception $e) {
            return Response::handleException($e, 'Failed to retrieve banner');
        }
    }

    public function createBanner($request)
    {
        try {
            $relations = [
                $request['product_id'] ?? null,
                $request['category_id'] ?? null,
                $request['product_type_id'] ?? null,
            ];

            $nonNullRelations = array_filter($relations);

            if (count($nonNullRelations) !== 1) {
                return Response::errorResponse('You must provide exactly one of product_id, category_id, or product_type_id', [], 422);
            }

            $banner = $this->bannerRepo->create($request);

            return Response::successResponse(new BannerResource($banner), 'banner created successfully', 201);

        } catch (\Illuminate\Database\QueryException $e) {
            return Response::handleDatabaseException($e, 'create banner');
        } catch (\Exception $e) {
            return Response::handleException($e, 'create banner');
        }
    }

    public function updateBanner($id, array $data)
    {
        try {
            $relations = [
                $data['product_id'] ?? null,
                $data['category_id'] ?? null,
                $data['product_type_id'] ?? null,
            ];

            $nonNullRelations = array_filter($relations);
            if (count($nonNullRelations) !== 1) {
                return Response::errorResponse('You must provide exactly one of product_id, category_id, or product_type_id', [], 422);
            }
            
            $banner = $this->bannerRepo->update($id, $data);

            if (!$banner) {
                return Response::errorResponse('Banner not found', [], 404);
            }

            return Response::successResponse(new BannerResource($banner), 'banner updated successfully');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            //exception if id not found
            return Response::handleModelNotFoundException($e, 'banner');
        } catch (\Exception $e) {
            return Response::handleException($e, 'update banner');
        }
    }

    public function deleteBanner($id)
    {
        $banner = $this->bannerRepo->find($id);

        if (!$banner) {
            return Response::errorResponse('banner not found', [], 404);
        }

        $this->bannerRepo->delete($id);

        return Response::successResponse(['is_success' => 1], 'banner deleted successfully');
    }
}
