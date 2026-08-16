<?php

namespace App\Services\Blog;

use App\Http\Resources\Blog\BlogCategoryResource;
use App\Models\BlogCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;

class BlogCategoryService
{
    // ── Index ─────────────────────────────────────────────────────────────

    public function getAllCategories(Request $request)
    {
        try {
            $query = BlogCategory::withCount('blogs');

            if ($request->boolean('only_active')) {
                $query->active();
            }

            $categories = $query->ordered()->get();

            return Response::successResponse(
                BlogCategoryResource::collection($categories)
            );
        } catch (\Exception $e) {
            return Response::handleException($e, 'fetch blog categories');
        }
    }

    // ── Show ──────────────────────────────────────────────────────────────

    public function getCategoryById(int $id)
    {
        try {
            $category = BlogCategory::withCount('blogs')
                                    ->with('children')
                                    ->findOrFail($id);

            return Response::successResponse(new BlogCategoryResource($category));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::handleModelNotFoundException($e, 'Blog Category');
        } catch (\Exception $e) {
            return Response::handleException($e, 'fetch blog category');
        }
    }

    // ── Create ────────────────────────────────────────────────────────────

    public function createCategory(Request $request)
    {
        try {
            $data = $request->validated();

            if (empty($data['slug'])) {
                $data['slug'] = Str::slug($data['name']);
            }

            $category = BlogCategory::create($data);

            return Response::successResponse(
                new BlogCategoryResource($category),
                'Category created successfully.',
                201
            );
        } catch (\Illuminate\Database\QueryException $e) {
            return Response::handleDatabaseException($e, 'create blog category');
        } catch (\Exception $e) {
            return Response::handleException($e, 'create blog category');
        }
    }

    // ── Update ────────────────────────────────────────────────────────────

    public function updateCategory(Request $request, int $id)
    {
        try {
            $category = BlogCategory::findOrFail($id);
            $data     = $request->validated();

            // Prevent category from being its own parent
            if (isset($data['parent_id']) && (int) $data['parent_id'] === $id) {
                return Response::errorResponse('A category cannot be its own parent.', [], 422);
            }

            $category->update($data);

            return Response::successResponse(
                new BlogCategoryResource($category),
                'Category updated successfully.'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::handleModelNotFoundException($e, 'Blog Category');
        } catch (\Illuminate\Database\QueryException $e) {
            return Response::handleDatabaseException($e, 'update blog category');
        } catch (\Exception $e) {
            return Response::handleException($e, 'update blog category');
        }
    }

    // ── Delete ────────────────────────────────────────────────────────────

    public function deleteCategory(int $id)
    {
        try {
            $category = BlogCategory::withCount('blogs')->findOrFail($id);

            if ($category->blogs_count > 0) {
                return Response::errorResponse(
                    "Cannot delete category with {$category->blogs_count} blog(s). Re-assign them first.",
                    ['blogs_count' => $category->blogs_count],
                    422
                );
            }

            $category->delete();

            return Response::successResponse(null, 'Category deleted successfully.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::handleModelNotFoundException($e, 'Blog Category');
        } catch (\Exception $e) {
            return Response::handleException($e, 'delete blog category');
        }
    }
}
