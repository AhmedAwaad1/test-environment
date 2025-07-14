<?php

namespace App\Http\Services\Favorite;

use App\Http\Resources\PaginationResource\PaginationResource;
use App\Http\Resources\Favorite\FavoriteResource;
use App\Repositories\Favorite\FavoriteRepository;
use Illuminate\Support\Facades\Response;

class FavoriteService
{
    protected $favoriteRepo;

    public function __construct(FavoriteRepository $favoriteRepo)
    {
        $this->favoriteRepo = $favoriteRepo;
    }

    public function getAllFavorites($request)
    {
        $query = $this->favoriteRepo->getAll();

        if ($request->per_page) {
            $favorites = new PaginationResource($query->paginate($request->per_page), FavoriteResource::class);
        } else {
            $favorites = FavoriteResource::collection($query->get());
        }

        return Response::successResponse($favorites, 'favorites retrieved successfully');
    }

    public function getFavoriteById($id)
    {
        try {
            $favorite = $this->favoriteRepo->find($id);

            if (!$favorite) {
                return Response::successResponse([], 'favorite not found', 200);
            }

            return Response::successResponse(new FavoriteResource($favorite), 'favorite found successfully');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            //exception if id not found
            return Response::handleModelNotFoundException($e, 'favorite');
        } catch (\Exception $e) {
            return Response::handleException($e, 'Failed to retrieve favorite');
        }
    }

    public function createFavorite($request)
    {
        try {
            $userId = auth()->id();
            $request['user_id'] = $userId;

            $favorite = $this->favoriteRepo->create($request);

            return Response::successResponse(new FavoriteResource($favorite), 'favorite created successfully', 201);

        } catch (\Illuminate\Database\QueryException $e) {
            return Response::handleDatabaseException($e, 'create favorite');
        } catch (\Exception $e) {
            return Response::handleException($e, 'create favorite');
        }
    }

    public function deleteFavorite($id)
    {
        $favorite = $this->favoriteRepo->delete($id);

        if (!$favorite) {
            return Response::errorResponse('favorite not found', [], 404);
        }

        return Response::successResponse(['is_success' => 1], 'favorite deleted successfully');
    }

    public function deleteAllFavorite()
    {
        try {
            $userId = auth()->id();
            $favorites = $this->favoriteRepo->getAll()->where('user_id', $userId)->get();

            if ($favorites->isEmpty()) {
                return Response::successResponse([], 'No favorites found for deletion', 200);
            }

            foreach ($favorites as $favorite) {
                $favorite->delete();
            }

            return Response::successResponse(['is_success' => 1], 'All favorites deleted successfully');
        } catch (\Exception $e) {
            return Response::handleException($e, 'delete all favorites');
        }
    }

}
