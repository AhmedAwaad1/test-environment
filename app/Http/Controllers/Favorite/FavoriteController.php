<?php

namespace App\Http\Controllers\Favorite;

use App\Http\Controllers\Controller;
use App\Http\Requests\Favorite\FavoriteRequest;
use App\Http\Services\Favorite\FavoriteService;

class FavoriteController extends Controller
{
    public $favoriteService;
    public function __construct(FavoriteService $favoriteService)
    {
        $this->middleware('auth:api');
        $this->favoriteService = $favoriteService;
    }

    public function index(FavoriteRequest $request)
    {
        return $this->favoriteService->getAllFavorites($request);
    }

    public function show(FavoriteRequest $request)
    {
        return $this->favoriteService->getFavoriteById($request->id);
    }

    public function store(FavoriteRequest $request)
    {
        return $this->favoriteService->createFavorite($request->validated());
    }

    public function destroy(FavoriteRequest $request)
    {
        return $this->favoriteService->deleteFavorite($request->id);
    }
}
