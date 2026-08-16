<?php

namespace App\Http\Controllers\Blog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Blog\BlogRequest;
use App\Services\Blog\BlogService;

class BlogController extends Controller
{
    public function __construct(private BlogService $blogService) {}

    // ── Dashboard (auth protected) ────────────────────────────────────────

    /** GET /api/blogs  — all statuses, full filters */
    public function index(BlogRequest $request)
    {
        return $this->blogService->getAllBlogs($request);
    }

    /** GET /api/blogs/{id} */
    public function show(BlogRequest $request, int $id)
    {
        return $this->blogService->getBlogById($id);
    }

    /** POST /api/blogs */
    public function store(BlogRequest $request)
    {
        return $this->blogService->createBlog($request);
    }

    /** POST /api/blogs/{id}  (POST used to support multipart/form-data for image) */
    public function update(BlogRequest $request, int $id)
    {
        return $this->blogService->updateBlog($request, $id);
    }

    /** DELETE /api/blogs/{id} */
    public function destroy(BlogRequest $request, int $id)
    {
        return $this->blogService->deleteBlog($id);
    }

    // ── Public / Website (no auth) ────────────────────────────────────────

    /** GET /api/website/blogs  — published only */
    public function publicIndex(BlogRequest $request)
    {
        return $this->blogService->getPublishedBlogs($request);
    }

    /** GET /api/website/blogs/slug/{slug} */
    public function getBySlug(string $slug)
    {
        return $this->blogService->getBlogBySlug($slug);
    }
}
