<?php

namespace App\Http\Services\Blog;

use App\Http\Resources\PaginationResource\PaginationResource;
use App\Http\Resources\Blog\BlogResource;
use App\Models\Blog;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class BlogService
{
    public function getAllBlogs($request)
    {
        $blog = Blog::query();

        if ($request->per_page) {
            $blog = new PaginationResource($blog->paginate($request->per_page), BlogResource::class);
        } else {
            $blog = BlogResource::collection($blog->get());
        }
        return Response::successResponse($blog, 'Blog Retrieved Successfully');

    }

    public function getBlogBySlug($slug)
    {
        $blog = Blog::where('slug', $slug)->first();

        if (!$blog) {
            return Response::errorResponse('Blog not found', [], 404);
        }

        return Response::successResponse(new BlogResource($blog), 'Blog found successfully', 200);
    }

    public function createBlog($request)
    {
        $blog = Blog::create($request->all());

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('blog', 'public');

            $blog->image = $path;
            $blog->save();
        }

        return Response::successResponse(new BlogResource($blog), 'Blog created successfully', 201);
    }

    public function updateBlog($request)
    {
        $blog = Blog::find($request->id);

        if (!$blog) {
            return Response::errorResponse('Blog not found', [], 404);
        }
        $blog->update($request->all());

        if ($request->hasFile('image')) {
            if ($blog->image) {
                Storage::delete($blog->image);
            }
            $path = $request->file('image')->store('blog', 'public');

            $blog->image = $path;
            $blog->save();
        }

        return Response::successResponse(new BlogResource($blog), 'Blog updated successfully', 200);
    }

    public function deleteBlog($id)
    {
        $blog = Blog::find($id);

        if (!$blog) {
            return Response::errorResponse('Blog not found', [], 404);
        }

        $blog->delete();
        return Response::successResponse(['is_success' => 1], 'Blog deleted successfully', 200);
    }
}
