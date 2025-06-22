<?php

namespace App\Http\Services\Review;


use App\Http\Resources\PaginationResource\PaginationResource;
use App\Http\Resources\Review\ReviewResource;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class ReviewService
{
    public function getAll($request)
    {
        $reviewQuery = Review::query()
        ->with('product', 'user')
            ->when($request->product_id ?? null, function ($query, $product_id) {
                return $query->where('product_id', $product_id);
            });

        if ($request->per_page) {
            $reviewQuery = new PaginationResource($reviewQuery->paginate($request->per_page),ReviewResource::class);
        } else {
            $reviewQuery = ReviewResource::collection($reviewQuery->get());
        }
        return Response::successResponse($reviewQuery, 'Reviews Retrieved Successfully');
    }

    public function find($id)
    {
        $review = Review::with('product', 'user')->find($id);

        if (!$review) {
            return Response::errorResponse('Review not found', [], 404);
        }

        return Response::successResponse(new ReviewResource($review), 'Review found successfully', 200);
    }

    public function create($request)
    {
        try {
            $user = Auth::user();
            $request['user_id'] = $user->id;
            $review = Review::create($request->all());

            return Response::successResponse(new ReviewResource ($review->load('product', 'user')), 'Review created successfully', 201);
        } catch (\Exception $e) {
            throw new \Exception('Failed to create Review: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $review = Review::find($id);
        if (!$review) {
            return Response::errorResponse('Review not found', [], 404);
        }
        $review->delete();
        return Response::successResponse(['is_success' => 1] , 'Review deleted successfully', 200);
    }


}
