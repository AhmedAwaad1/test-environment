<?php

namespace App\Http\Controllers\Banner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Banner\BannerRequest;
use App\Http\Services\Banner\BannerService;

class BannerController extends Controller
{
    public $bannerService;
    public function __construct(BannerService $bannerService)
    {
        $this->bannerService = $bannerService;
    }

    public function index(BannerRequest $request)
    {
        return $this->bannerService->getAllBanners($request);
    }

    public function show(BannerRequest $request)
    {
        return $this->bannerService->getBannerById($request->id);
    }

    public function store(BannerRequest $request)
    {
        return $this->bannerService->createBanner($request->validated());
    }

    public function update(BannerRequest $request, $id)
    {
        return $this->bannerService->updateBanner($id, $request->validated());
    }

    public function destroy(BannerRequest $request)
    {
        return $this->bannerService->deleteBanner($request->id);
    }
}
