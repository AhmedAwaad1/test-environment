<?php

namespace App\Http\Controllers\Favorite;

use App\Http\Controllers\Controller;
use App\Http\Requests\Favorite\FavoriteRequest;
use App\Http\Services\Favorite\FavoriteService;
use Illuminate\Support\Facades\Request;

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

    public function userFavorites(FavoriteRequest $request)
    {
        return $this->favoriteService->getUserFavorites($request);
    }

    public function show($id)
    {
        return $this->favoriteService->getFavoriteById($id);
    }

    public function store(FavoriteRequest $request)
    {
        return $this->favoriteService->createFavorite($request->validated());
    }

    public function destroy($id)
    {
        return $this->favoriteService->deleteFavorite($id);
    }

    public function deleteAllFavorite()
    {
        return $this->favoriteService->deleteAllFavorite();
    }
}
