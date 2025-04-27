<?php

namespace App\Http\Controllers\Color;

use App\Http\Controllers\Controller;
use App\Http\Requests\Color\ColorRequest;
use App\Http\Services\Color\ColorService;

class ColorController extends Controller
{
    public $colorService;
    public function __construct(ColorService $colorService)
    {
        $this->colorService = $colorService;
    }

    public function index(ColorRequest $request)
    {
        return $this->colorService->getAllColors($request);
    }

    public function show(ColorRequest $request)
    {
        return $this->colorService->getColorById($request->id);
    }

    public function store(ColorRequest $request)
    {
        return $this->colorService->createColor($request->validated());
    }

    public function update(ColorRequest $request, $id)
    {
        return $this->colorService->updateColor($id, $request->validated());
    }

    public function destroy(ColorRequest $request)
    {
        return $this->colorService->deleteColor($request->id);
    }
}
