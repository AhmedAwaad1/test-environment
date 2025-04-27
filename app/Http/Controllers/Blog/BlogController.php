<?php

namespace App\Http\Controllers\Blog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Blog\BlogRequest;
use App\Http\Services\Blog\BlogService;

class BlogController extends Controller
{
    public $blogService;
    public function __construct(BlogService $blogService)
    {
        $this->blogService = $blogService;
    }

    public function index(BlogRequest $request)
    {
        return $this->blogService->getAllBlogs($request);
    }

    public function show(BlogRequest $request)
    {
        return $this->blogService->getBlogById($request->id);
    }

    public function store(BlogRequest $request)
    {
        return $this->blogService->createBlog($request);
    }

    public function update(BlogRequest $request)
    {
        return $this->blogService->updateBlog($request);
    }

    public function destroy(BlogRequest $request)
    {
        return $this->blogService->deleteBlog($request->id);
    }
}
