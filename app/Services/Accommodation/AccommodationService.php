<?php

namespace App\Services\Accommodation;

use App\Models\Accommodation;
use App\Models\AccommodationImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AccommodationService
{
    // ── Dashboard: paginated list ────────────────────────────────────────────

    public function index(array $filters)
    {
        try {
            $query = Accommodation::with(['images'])
                ->sorted($filters['sort_by'] ?? 'sort_order', $filters['sort_dir'] ?? 'asc');

            if (!empty($filters['search'])) {
                $query->search($filters['search']);
            }

            if (!empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            $accommodations = $query->paginate($filters['per_page'] ?? 15);

            return Response::successResponse([
                'accommodations' => $accommodations->items(),
                'meta' => [
                    'current_page' => $accommodations->currentPage(),
                    'last_page'    => $accommodations->lastPage(),
                    'per_page'     => $accommodations->perPage(),
                    'total'        => $accommodations->total(),
                ],
            ], 'Accommodations retrieved successfully.');
        } catch (\Exception $e) {
            return Response::handleException($e, 'fetch accommodations');
        }
    }

    // ── Public: active only, lightweight ─────────────────────────────────────

    public function publicIndex(Request $request)
    {
        try {
            $query = Accommodation::with(['images'])
                ->select('id', 'name', 'slug', 'rating', 'distance_text', 'price_per_night', 'currency', 'amenities', 'status')
                ->active()
                ->sorted('sort_order', 'asc');

            $accommodations = $query->get();

            return Response::successResponse($accommodations, 'Accommodations retrieved successfully.');
        } catch (\Exception $e) {
            return Response::handleException($e, 'fetch public accommodations');
        }
    }

    // ── Dashboard: full detail ──────────────────────────────────────────────

    public function show(int $id)
    {
        try {
            $accommodation = Accommodation::with(['images'])->findOrFail($id);
            return Response::successResponse($accommodation, 'Accommodation retrieved successfully.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::handleModelNotFoundException($e, 'Accommodation');
        } catch (\Exception $e) {
            return Response::handleException($e, 'fetch accommodation');
        }
    }

    // ── Public: full detail by slug ─────────────────────────────────────────

    public function publicShow(string $slug)
    {
        try {
            $accommodation = Accommodation::with(['images'])
                ->active()
                ->where('slug', $slug)
                ->firstOrFail();

            return Response::successResponse($accommodation, 'Accommodation retrieved successfully.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::handleModelNotFoundException($e, 'Accommodation');
        } catch (\Exception $e) {
            return Response::handleException($e, 'fetch accommodation');
        }
    }

    // ── Store ───────────────────────────────────────────────────────────────

    public function store(array $data, Request $request)
    {
        try {
            $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

            $accommodation = Accommodation::create($data);

            // Handle image uploads
            $this->handleImageUploads($accommodation, $request);

            $accommodation->load('images');

            return Response::successResponse($accommodation, 'Accommodation created successfully.', 201);
        } catch (\Exception $e) {
            return Response::handleException($e, 'create accommodation');
        }
    }

    // ── Update ──────────────────────────────────────────────────────────────

    public function update(int $id, array $data, Request $request)
    {
        try {
            $accommodation = Accommodation::findOrFail($id);

            if (isset($data['name']) && $data['name'] !== $accommodation->name && !isset($data['slug'])) {
                $data['slug'] = Str::slug($data['name']);
            }

            $accommodation->update($data);

            // Delete specified images
            if ($request->filled('delete_image_ids')) {
                $toDelete = AccommodationImage::where('accommodation_id', $id)
                    ->whereIn('id', $request->delete_image_ids)
                    ->get();

                foreach ($toDelete as $img) {
                    Storage::disk('public')->delete($img->image);
                    $img->delete();
                }
            }

            // Handle new image uploads
            $this->handleImageUploads($accommodation, $request);

            $accommodation->load('images');

            return Response::successResponse($accommodation, 'Accommodation updated successfully.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::handleModelNotFoundException($e, 'Accommodation');
        } catch (\Exception $e) {
            return Response::handleException($e, 'update accommodation');
        }
    }

    // ── Destroy ─────────────────────────────────────────────────────────────

    public function destroy(int $id)
    {
        try {
            $accommodation = Accommodation::with('images')->findOrFail($id);

            // Clean up all image files
            foreach ($accommodation->images as $image) {
                Storage::disk('public')->delete($image->image);
            }

            $accommodation->delete(); // cascade deletes images rows

            return Response::successResponse(null, 'Accommodation deleted successfully.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::handleModelNotFoundException($e, 'Accommodation');
        } catch (\Exception $e) {
            return Response::handleException($e, 'delete accommodation');
        }
    }

    // ── Private: handle image uploads ───────────────────────────────────────

    private function handleImageUploads(Accommodation $accommodation, Request $request): void
    {
        if (!$request->hasFile('images')) return;

        $maxSort = $accommodation->images()->max('sort_order') ?? 0;

        foreach ($request->file('images') as $index => $file) {
            $path = $file->store('accommodations', 'public');
            $accommodation->images()->create([
                'image'      => $path,
                'sort_order' => $maxSort + $index + 1,
            ]);
        }
    }
}
