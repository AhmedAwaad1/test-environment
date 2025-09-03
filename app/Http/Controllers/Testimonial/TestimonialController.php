<?php

namespace App\Http\Controllers\Testimonial;

use App\Http\Controllers\Controller;
use App\Http\Requests\Testimonial\TestimonialRequest;
use App\Http\Services\Testimonial\TestimonialService;
use Illuminate\Support\Facades\Request;

class TestimonialController extends Controller
{
    public $testimonialService;
    public function __construct(TestimonialService $testimonialService)
    {
        $this->testimonialService = $testimonialService;
    }

    public function all(TestimonialRequest $request)
    {
        return $this->testimonialService->getAll($request);
    }
    public function create(TestimonialRequest $request)
    {
        return $this->testimonialService->create($request);
    }

    public function show($id)
    {
        return $this->testimonialService->find($id);
    }

    public function delete(TestimonialRequest $request)
    {
        return $this->testimonialService->destroy($request->id);
    }
}
