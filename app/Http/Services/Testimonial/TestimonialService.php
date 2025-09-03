<?php

namespace App\Http\Services\Testimonial;


use App\Http\Resources\PaginationResource\PaginationResource;
use App\Http\Resources\Testimonial\TestimonialResource;
use App\Models\Testimonial;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class TestimonialService
{
    public function getAll($request)
    {
        $testimonialQuery = Testimonial::query()
        ->with('product', 'user')
            ->when($request->product_id ?? null, function ($query, $product_id) {
                return $query->where('product_id', $product_id);
            });

        if ($request->per_page) {
            $testimonialQuery = new PaginationResource($testimonialQuery->paginate($request->per_page),TestimonialResource::class);
        } else {
            $testimonialQuery = TestimonialResource::collection($testimonialQuery->get());
        }
        return Response::successResponse($testimonialQuery, 'Testimonials Retrieved Successfully');
    }

    public function find($id)
    {
        $testimonial = Testimonial::with('product', 'user')->find($id);

        if (!$testimonial) {
            return Response::errorResponse('Testimonial not found', [], 404);
        }

        return Response::successResponse(new TestimonialResource($testimonial), 'Testimonial found successfully', 200);
    }

    public function create($request)
    {
        try {
            $user = Auth::user();
            $request['user_id'] = $user->id;
            $testimonial = Testimonial::create($request->all());

            return Response::successResponse(new TestimonialResource ($testimonial->load('product', 'user')), 'Testimonial created successfully', 201);
        } catch (\Exception $e) {
            throw new \Exception('Failed to create Testimonial: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $testimonial = Testimonial::find($id);
        if (!$testimonial) {
            return Response::errorResponse('Testimonial not found', [], 404);
        }
        $testimonial->delete();
        return Response::successResponse(['is_success' => 1] , 'Testimonial deleted successfully', 200);
    }


}
