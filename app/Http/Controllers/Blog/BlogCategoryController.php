<?php

namespace App\Http\Controllers\Blog;

use App\Http\Controllers\Controller;
use App\Http\Requests\Blog\BlogCategoryRequest;
use App\Services\Blog\BlogCategoryService;

class BlogCategoryController extends Controller
{
    public function __construct(private BlogCategoryService $categoryService) {}

    /** GET /api/blog-categories */
    public function index(BlogCategoryRequest $request)
    {
        return $this->categoryService->getAllCategories($request);
    }

    /** GET /api/blog-categories/{id} */
    public function show(BlogCategoryRequest $request, int $id)
    {
        return $this->categoryService->getCategoryById($id);
    }

    /** POST /api/blog-categories */
    public function store(BlogCategoryRequest $request)
    {
        return $this->categoryService->createCategory($request);
    }

    /** PUT /api/blog-categories/{id} */
    public function update(BlogCategoryRequest $request, int $id)
    {
        return $this->categoryService->updateCategory($request, $id);
    }

    /** DELETE /api/blog-categories/{id} */
    public function destroy(BlogCategoryRequest $request, int $id)
    {
        return $this->categoryService->deleteCategory($id);
    }
}
