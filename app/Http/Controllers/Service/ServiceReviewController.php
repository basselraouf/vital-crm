<?php

namespace App\Http\Controllers\Service;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceReview;
use App\Http\Requests\Service\ServiceReviewRequest;
use Illuminate\Support\Facades\Response;

class ServiceReviewController extends Controller
{
    // ── Public: visitor submits review ────────────────────────────────────────

    /** POST /api/public/reviews  (service_id optional)
     *  POST /api/public/services/{slug}/reviews  (backward-compat, slug takes priority) */
    public function publicStore(ServiceReviewRequest $request, string $slug = null)
    {
        try {
            // Resolve service — optional for generic reviews
            $service = null;

            if ($slug) {
                // Old slug-based route: service is required
                $service = Service::active()->where('slug', $slug)->firstOrFail();
            } elseif ($request->filled('service_id')) {
                // New flat route: caller may optionally pass service_id in body
                $service = Service::active()->findOrFail($request->service_id);
            }

            $data = [
                'service_id'        => $service?->id,   // null for generic reviews
                'reviewer_name'     => $request->reviewer_name,
                'reviewer_location' => $request->reviewer_location,
                'rating'            => $request->rating,
                'content'           => $request->content,
                'source'            => 'website',
                'status'            => 'pending',
            ];

            $review = ServiceReview::create($data);

            return Response::successResponse($review, 'Review submitted successfully. It will be visible after approval.', 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return Response::errorResponse('Validation failed.', $e->errors(), 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::handleModelNotFoundException($e, 'Service');
        } catch (\Exception $e) {
            return Response::handleException($e, 'submit review');
        }
    }

    // ── Dashboard: list reviews for a service ─────────────────────────────────

    /** GET /api/services/{id}/reviews */
    public function index(ServiceReviewRequest $request, int $id)
    {
        try {
            $service = Service::findOrFail($id);

            $query = $service->reviews();

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('source')) {
                $query->where('source', $request->source);
            }

            $reviews = $query->paginate($request->per_page ?? 15);

            return Response::successResponse([
                'reviews' => $reviews->items(),
                'meta'    => [
                    'current_page' => $reviews->currentPage(),
                    'last_page'    => $reviews->lastPage(),
                    'per_page'     => $reviews->perPage(),
                    'total'        => $reviews->total(),
                ],
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::handleModelNotFoundException($e, 'Service');
        } catch (\Exception $e) {
            return Response::handleException($e, 'fetch reviews');
        }
    }

    // ── Dashboard: admin creates review ───────────────────────────────────────

    /** POST /api/services/{id}/reviews */
    public function store(ServiceReviewRequest $request, int $id)
    {
        try {
            $service = Service::findOrFail($id);

            $data = [
                'reviewer_name'     => $request->reviewer_name,
                'reviewer_location' => $request->reviewer_location,
                'rating'            => $request->rating,
                'content'           => $request->content,
                'source'            => 'admin',
                'status'            => 'selected',           // admin reviews are published immediately
            ];

            if ($request->hasFile('media')) {
                $file = $request->file('media');
                $data['media_path'] = $file->store('reviews', 'public');
                $data['media_type'] = str_starts_with($file->getMimeType(), 'video') ? 'video' : 'image';
            }

            $review = $service->reviews()->create($data);

            return Response::successResponse($review, 'Review created successfully.', 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return Response::errorResponse('Validation failed.', $e->errors(), 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::handleModelNotFoundException($e, 'Service');
        } catch (\Exception $e) {
            return Response::handleException($e, 'create review');
        }
    }

    // ── Dashboard: update status ───────────────────────────────────────────────

    /** PATCH /api/services/{id}/reviews/{reviewId}/status */
    public function updateStatus(ServiceReviewRequest $request, int $id, int $reviewId)
    {
        try {
            // Look up by review ID only — service_id may be null for generic reviews
            $review = ServiceReview::findOrFail($reviewId);
            $review->update(['status' => $request->status]);

            return Response::successResponse($review, 'Review status updated successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return Response::errorResponse('Validation failed.', $e->errors(), 422);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::handleModelNotFoundException($e, 'Review');
        } catch (\Exception $e) {
            return Response::handleException($e, 'update review status');
        }
    }

    // ── Dashboard: delete review ─────────────────────────────────────────────

    /** DELETE /api/services/{id}/reviews/{reviewId} */
    public function destroy(int $id, int $reviewId)
    {
        try {
            // Look up by review ID only — service_id may be null for generic reviews
            $review = ServiceReview::findOrFail($reviewId);

            if ($review->media_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($review->media_path);
            }

            $review->delete();

            return Response::successResponse(null, 'Review deleted successfully.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::handleModelNotFoundException($e, 'Review');
        } catch (\Exception $e) {
            return Response::handleException($e, 'delete review');
        }
    }

    // ── Public: all selected reviews split by source ───────────────────────────

    /** GET /api/public/reviews
     *  Query params:
     *    per_page    (int, optional) — enables pagination; limit applied independently per source
     *    website_page (int, optional, default 1) — page for website reviews
     *    admin_page   (int, optional, default 1) — page for admin reviews */
    public function allReviews(ServiceReviewRequest $request)
    {
        try {
            $baseQuery = ServiceReview::where('status', 'selected');

            // ── No per_page ─ return everything flat ─────────────────────────
            if (!$request->filled('per_page')) {
                $reviews = (clone $baseQuery)->latest()->get();

                return Response::successResponse([
                    'website' => $reviews->where('source', 'website')->values(),
                    'admin'   => $reviews->where('source', 'admin')->values(),
                ]);
            }

            // ── per_page present ─ paginate each source independently ─────────
            $perPage = (int) $request->per_page;

            $websitePaginator = (clone $baseQuery)
                ->where('source', 'website')
                ->latest()
                ->paginate($perPage, ['*'], 'website_page');

            $adminPaginator = (clone $baseQuery)
                ->where('source', 'admin')
                ->latest()
                ->paginate($perPage, ['*'], 'admin_page');

            return Response::successResponse([
                'website' => [
                    'data' => $websitePaginator->items(),
                    'meta' => [
                        'current_page' => $websitePaginator->currentPage(),
                        'last_page'    => $websitePaginator->lastPage(),
                        'per_page'     => $websitePaginator->perPage(),
                        'total'        => $websitePaginator->total(),
                    ],
                ],
                'admin' => [
                    'data' => $adminPaginator->items(),
                    'meta' => [
                        'current_page' => $adminPaginator->currentPage(),
                        'last_page'    => $adminPaginator->lastPage(),
                        'per_page'     => $adminPaginator->perPage(),
                        'total'        => $adminPaginator->total(),
                    ],
                ],
            ]);
        } catch (\Exception $e) {
            return Response::handleException($e, 'fetch all reviews');
        }
    }
}
