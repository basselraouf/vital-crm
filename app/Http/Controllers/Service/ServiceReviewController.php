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

    /** POST /api/public/services/{slug}/reviews */
    public function publicStore(ServiceReviewRequest $request, string $slug)
    {
        try {
            $service = Service::active()->where('slug', $slug)->firstOrFail();

            $review = $service->reviews()->create([
                'reviewer_name'     => $request->reviewer_name,
                'reviewer_location' => $request->reviewer_location,
                'rating'            => $request->rating,
                'content'           => $request->content,
                'source'            => 'website',
                'status'            => 'pending',
            ]);

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
            $review = ServiceReview::where('service_id', $id)->findOrFail($reviewId);
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
            $review = ServiceReview::where('service_id', $id)->findOrFail($reviewId);

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
}
