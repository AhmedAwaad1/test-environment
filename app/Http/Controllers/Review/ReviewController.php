<?php

namespace App\Http\Controllers\Review;

use App\Http\Controllers\Controller;
use App\Http\Requests\Review\ReviewRequest;
use App\Http\Services\Review\ReviewService;
use Illuminate\Support\Facades\Request;

class ReviewController extends Controller
{
    public $reviewService;
    public function __construct(ReviewService $reviewService)
    {
        $this->reviewService = $reviewService;
    }

    public function all(ReviewRequest $request)
    {
        return $this->reviewService->getAll($request);
    }
    public function create(ReviewRequest $request)
    {
        return $this->reviewService->create($request);
    }

    public function show($id)
    {
        return $this->reviewService->find($id);
    }

    public function delete(ReviewRequest $request)
    {
        return $this->reviewService->destroy($request->id);
    }
}
