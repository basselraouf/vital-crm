<?php

namespace App\Services\Blog;

use App\Http\Resources\Blog\BlogDetailResource;
use App\Http\Resources\Blog\BlogListResource;
use App\Http\Resources\PaginationResource\PaginationResource;
use App\Models\Blog;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BlogService
{
    // ── Public: Dashboard (all statuses, all filters) ─────────────────────

    public function getAllBlogs(Request $request)
    {
        try {
            $query   = $this->buildBlogQuery($request, publishedOnly: false);
            $perPage = min((int) ($request->per_page ?? 15), 100);
            $blogs   = $query->paginate($perPage);

            return Response::successResponse(
                new PaginationResource($blogs, BlogListResource::class)
            );
        } catch (\Exception $e) {
            return Response::handleException($e, 'fetch blogs');
        }
    }

    // ── Public: Website (published only) ─────────────────────────────────

    public function getPublishedBlogs(Request $request)
    {
        try {
            $query   = $this->buildBlogQuery($request, publishedOnly: true);
            $perPage = min((int) ($request->per_page ?? 12), 50);
            $blogs   = $query->paginate($perPage);

            return Response::successResponse(
                new PaginationResource($blogs, BlogListResource::class)
            );
        } catch (\Exception $e) {
            return Response::handleException($e, 'fetch published blogs');
        }
    }

    // ── Show by ID (dashboard) ────────────────────────────────────────────

    public function getBlogById(int $id)
    {
        try {
            $blog = Blog::with(['category', 'author'])->findOrFail($id);
            return Response::successResponse(new BlogDetailResource($blog));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::handleModelNotFoundException($e, 'Blog');
        } catch (\Exception $e) {
            return Response::handleException($e, 'fetch blog');
        }
    }

    // ── Show by Slug (website) ────────────────────────────────────────────

    public function getBlogBySlug(string $slug)
    {
        try {
            $blog = Blog::with(['category', 'author'])
                        ->published()
                        ->where('slug', $slug)
                        ->firstOrFail();

            // Increment views
            $blog->increment('views_count');

            return Response::successResponse(new BlogDetailResource($blog));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::handleModelNotFoundException($e, 'Blog');
        } catch (\Exception $e) {
            return Response::handleException($e, 'fetch blog by slug');
        }
    }

    // ── Create ────────────────────────────────────────────────────────────

    public function createBlog(Request $request)
    {
        try {
            $data = $request->except('featured_image');

            // Auto-generate slug if not provided
            if (empty($data['slug'])) {
                $data['slug'] = $this->uniqueSlug($data['title']);
            }

            // Auto-set published_at when status is published
            if (($data['status'] ?? 'draft') === 'published' && empty($data['published_at'])) {
                $data['published_at'] = now();
            }

            // Handle image upload
            if ($request->hasFile('featured_image')) {
                $data['featured_image'] = $request->file('featured_image')
                    ->store('blogs', 'public');
            }

            $data['author_id'] = Auth::id();

            $blog = Blog::create($data);
            $blog->load(['category', 'author']);

            return Response::successResponse(
                new BlogDetailResource($blog),
                'Blog created successfully.',
                201
            );
        } catch (\Illuminate\Database\QueryException $e) {
            return Response::handleDatabaseException($e, 'create blog');
        } catch (\Exception $e) {
            return Response::handleException($e, 'create blog');
        }
    }

    // ── Update ────────────────────────────────────────────────────────────

    public function updateBlog(Request $request, int $id)
    {
        try {
            $blog = Blog::findOrFail($id);
            $data = $request->except('featured_image');

            // Auto-set published_at when transitioning to published
            if (
                isset($data['status']) &&
                $data['status'] === 'published' &&
                $blog->status !== 'published' &&
                empty($data['published_at'])
            ) {
                $data['published_at'] = now();
            }

            // Handle image upload — delete old local file if replaced
            if ($request->hasFile('featured_image')) {
                if ($blog->featured_image && !str_starts_with($blog->featured_image, 'http')) {
                    Storage::disk('public')->delete($blog->featured_image);
                }
                $data['featured_image'] = $request->file('featured_image')
                    ->store('blogs', 'public');
            }

            $blog->update($data);
            $blog->load(['category', 'author']);

            return Response::successResponse(
                new BlogDetailResource($blog),
                'Blog updated successfully.'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::handleModelNotFoundException($e, 'Blog');
        } catch (\Illuminate\Database\QueryException $e) {
            return Response::handleDatabaseException($e, 'update blog');
        } catch (\Exception $e) {
            return Response::handleException($e, 'update blog');
        }
    }

    // ── Delete ────────────────────────────────────────────────────────────

    public function deleteBlog(int $id)
    {
        try {
            $blog = Blog::findOrFail($id);

            // Delete local image if not an external URL
            if ($blog->featured_image && !str_starts_with($blog->featured_image, 'http')) {
                Storage::disk('public')->delete($blog->featured_image);
            }

            $blog->delete();

            return Response::successResponse(null, 'Blog deleted successfully.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::handleModelNotFoundException($e, 'Blog');
        } catch (\Exception $e) {
            return Response::handleException($e, 'delete blog');
        }
    }

    // ── Private: Shared Query Builder ────────────────────────────────────
    // Single source of truth for all filters — reused by dashboard & website

    private function buildBlogQuery(Request $request, bool $publishedOnly): Builder
    {
        $query = Blog::with(['category', 'author']);

        // ── Scope: published only (website) ───────────────────────────────
        if ($publishedOnly) {
            $query->published();
        }

        // ── Filter: status (dashboard only) ───────────────────────────────
        if (!$publishedOnly && $request->filled('status')) {
            $query->where('status', $request->status);
        }

        // ── Filter: search (title | excerpt | focus_keyword) ──────────────
        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // ── Filter: category ──────────────────────────────────────────────
        if ($request->filled('category_id')) {
            $query->where('category_id', (int) $request->category_id);
        }

        // ── Filter: date range ────────────────────────────────────────────
        if ($request->filled('date_from')) {
            $query->whereDate('published_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('published_at', '<=', $request->date_to);
        }

        // ── Sort ──────────────────────────────────────────────────────────
        $query->sorted(
            $request->sort_by  ?? 'published_at',
            $request->sort_dir ?? 'desc'
        );

        return $query;
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    private function uniqueSlug(string $title): string
    {
        $slug  = Str::slug($title);
        $count = Blog::where('slug', 'LIKE', "{$slug}%")->count();
        return $count ? "{$slug}-{$count}" : $slug;
    }
}
