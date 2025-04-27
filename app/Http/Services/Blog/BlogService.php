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

    public function getBlogById($id)
    {
        $blog = Blog::find($id);

        if (!$blog) {
            return Response::errorResponse('Blog not found', [], 404);
        }

        return Response::successResponse(new BlogResource($blog), 'Blog found successfully', 200);
    }

    public function createBlog($request)
    {
        try {
            $blog = Blog::create($request->all());

            if ($request->hasFile('cover_image')) {
                $path = $request->file('cover_image')->store('blog', 'public');

                $blog->cover_image = $path;
                $blog->save();
            }
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('blog', 'public');

                $blog->image = $path;
                $blog->save();
            }

            return Response::successResponse(new BlogResource($blog), 'Blog created successfully', 201);
        } catch (\Exception $e) {
            throw new \Exception('Failed to create blog: ' . $e->getMessage());
        }
    }

    public function updateBlog($request)
    {
        try {
            $blog = Blog::find($request->id);

            if (!$blog) {
                return Response::errorResponse('Blog not found', [], 404);
            }
            $blog->update($request->all());

            if ($request->hasFile('cover_image')) {
                if ($blog->cover_image) {
                    Storage::delete($blog->cover_image);
                }
                $path = $request->file('cover_image')->store('blog', 'public');

                $blog->cover_image = $path;
                $blog->save();
            }

            if ($request->hasFile('image')) {
                if ($blog->image) {
                    Storage::delete($blog->image);
                }
                $path = $request->file('image')->store('blog', 'public');

                $blog->image = $path;
                $blog->save();
            }

            return Response::successResponse(new BlogResource($blog), 'Blog updated successfully', 200);
        } catch (\Exception $e) {
            throw new \Exception('Failed to update blog: ' . $e->getMessage());
        }
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
