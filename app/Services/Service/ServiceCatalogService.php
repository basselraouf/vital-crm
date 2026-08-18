<?php

namespace App\Services\Service;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ServiceCatalogService
{
    // ── Dashboard: All services ───────────────────────────────────────────────

    public function index(Request $request)
    {
        try {
            $query = Service::with(['procedures', 'packages']);

            if ($request->filled('search')) {
                $query->search($request->search);
            }

            if ($request->filled('is_active')) {
                $query->where('is_active', filter_var($request->is_active, FILTER_VALIDATE_BOOLEAN));
            }

            $query->sorted(
                $request->sort_by  ?? 'sort_order',
                $request->sort_dir ?? 'asc'
            );

            $perPage  = min((int) ($request->per_page ?? 15), 100);
            $services = $query->paginate($perPage);

            return Response::successResponse([
                'services' => $services->items(),
                'meta'     => [
                    'current_page' => $services->currentPage(),
                    'last_page'    => $services->lastPage(),
                    'per_page'     => $services->perPage(),
                    'total'        => $services->total(),
                ],
            ]);
        } catch (\Exception $e) {
            return Response::handleException($e, 'fetch services');
        }
    }

    // ── Public: Active services only ──────────────────────────────────────────

    public function publicIndex(Request $request)
    {
        try {
            $query = Service::with(['procedures'])
                ->active()
                ->sorted($request->sort_by ?? 'sort_order', $request->sort_dir ?? 'asc');

            if ($request->filled('search')) {
                $query->search($request->search);
            }

            $services = $query->get();

            return Response::successResponse($services);
        } catch (\Exception $e) {
            return Response::handleException($e, 'fetch public services');
        }
    }

    // ── Show (Dashboard: by ID) ───────────────────────────────────────────────

    public function show(int $id)
    {
        try {
            $service = Service::with(['procedures', 'packages'])->findOrFail($id);
            return Response::successResponse($service);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::handleModelNotFoundException($e, 'Service');
        } catch (\Exception $e) {
            return Response::handleException($e, 'fetch service');
        }
    }

    // ── Show (Public: by slug) ────────────────────────────────────────────────

    public function showBySlug(string $slug)
    {
        try {
            $service = Service::with(['procedures', 'packages'])
                ->active()
                ->where('slug', $slug)
                ->firstOrFail();

            return Response::successResponse($service);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::handleModelNotFoundException($e, 'Service');
        } catch (\Exception $e) {
            return Response::handleException($e, 'fetch service by slug');
        }
    }

    // ── Store ─────────────────────────────────────────────────────

    public function store(Request $request)
    {
        try {
            $data    = $this->extractServiceData($request);
            $service = Service::create($data);

            // Sync nested relations if provided
            if ($request->filled('procedures')) {
                $this->insertProcedures($service, $request->procedures);
            }

            return Response::successResponse(
                $service->fresh(['procedures', 'packages']),
                'Service created successfully.',
                201
            );
        } catch (\Exception $e) {
            return Response::handleException($e, 'create service');
        }
    }

    // ── Update ────────────────────────────────────────────────────

    public function update(Request $request, int $id)
    {
        try {
            $service = Service::findOrFail($id);
            $data    = $this->extractServiceData($request, $service);

            $service->update($data);

            // Sync nested relations only if keys are present in the request
            if ($request->has('procedures')) {
                $service->procedures()->delete();
                if (!empty($request->procedures)) {
                    $this->insertProcedures($service, $request->procedures);
                }
            }

            return Response::successResponse(
                $service->fresh(['procedures', 'packages']),
                'Service updated successfully.'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::handleModelNotFoundException($e, 'Service');
        } catch (\Exception $e) {
            return Response::handleException($e, 'update service');
        }
    }

    // ── Delete ────────────────────────────────────────────────────────────────

    public function destroy(int $id)
    {
        try {
            $service = Service::findOrFail($id);

            // Delete image from storage if local
            if ($service->image && !str_starts_with($service->image, 'http')) {
                Storage::disk('public')->delete($service->image);
            }

            $service->delete();

            return Response::successResponse(null, 'Service deleted successfully.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::handleModelNotFoundException($e, 'Service');
        } catch (\Exception $e) {
            return Response::handleException($e, 'delete service');
        }
    }

    // ── Sync Packages ─────────────────────────────────────────────────────────

    public function syncPackages(int $id, array $packages)
    {
        try {
            $service = Service::findOrFail($id);

            // Delete all existing and re-insert
            $service->packages()->delete();

            $this->insertPackages($service, $packages);

            return Response::successResponse(
                $service->fresh('packages'),
                'Packages synced successfully.'
            );
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::handleModelNotFoundException($e, 'Service');
        } catch (\Exception $e) {
            return Response::handleException($e, 'sync packages');
        }
    }

    // ── Helpers ────────────────────────────────────────────────────

    private function extractServiceData(Request $request, ?Service $existing = null): array
    {
        $data = $request->only([
            'name', 'slug', 'tagline', 'short_description',
            'benefits', 'why_us_points', 'sort_order', 'is_active',
        ]);

        // Handle image upload
        if ($request->hasFile('image')) {
            if ($existing?->image && !str_starts_with($existing->image, 'http')) {
                Storage::disk('public')->delete($existing->image);
            }
            $data['image'] = $request->file('image')->store('services', 'public');
        }

        // Auto-generate slug from name if not provided
        if (empty($data['slug']) && !empty($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        return array_filter($data, fn($v) => $v !== null);
    }

    private function insertProcedures(Service $service, array $procedures): void
    {
        $rows = array_map(fn($procedureName, $i) => [
            'service_id'  => $service->id,
            'name'        => $procedureName, // Now it's just a string from the array
            'sort_order'  => 0,              // Hardcoded to 0 as requested
            'created_at'  => now(),
            'updated_at'  => now(),
        ], $procedures, array_keys($procedures));

        \App\Models\ServiceProcedure::insert($rows);
    }

    private function insertPackages(Service $service, array $packages): void
    {
        $rows = array_map(fn($p, $i) => [
            'service_id'  => $service->id,
            'name'        => $p['name'],
            'description' => $p['description'] ?? null,
            'content'     => $p['content'] ?? null,
            'sort_order'  => $p['sort_order'] ?? $i,
            'created_at'  => now(),
            'updated_at'  => now(),
        ], $packages, array_keys($packages));

        \App\Models\ServicePackage::insert($rows);
    }
}
