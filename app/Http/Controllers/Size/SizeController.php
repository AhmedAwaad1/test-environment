<?php

namespace App\Http\Controllers\Size;

use App\Http\Controllers\Controller;
use App\Http\Requests\Size\SizeRequest;
use App\Http\Services\Size\SizeService;

class SizeController extends Controller
{
    public $sizeService;
    public function __construct(SizeService $sizeService)
    {
        $this->sizeService = $sizeService;
    }

    public function index(SizeRequest $request)
    {
        return $this->sizeService->getAllSizes($request);
    }

    public function show(SizeRequest $request)
    {
        return $this->sizeService->getSizeById($request->id);
    }

    public function store(SizeRequest $request)
    {
        return $this->sizeService->createSize($request->validated());
    }

    public function update(SizeRequest $request, $id)
    {
        return $this->sizeService->updateSize($id, $request->validated());
    }

    public function destroy(SizeRequest $request)
    {
        return $this->sizeService->deleteSize($request->id);
    }
}
