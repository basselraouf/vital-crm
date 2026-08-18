<?php

namespace App\Http\Controllers\Service;

use App\Http\Controllers\Controller;
use App\Http\Requests\Service\ServiceRequest;
use App\Services\Service\ServiceCatalogService;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function __construct(private ServiceCatalogService $service) {}

    // ── Dashboard ─────────────────────────────────────────────────────────────

    /** GET /api/services */
    public function index(ServiceRequest $request)
    {
        return $this->service->index($request);
    }

    /** GET /api/services/{id} */
    public function show(int $id)
    {
        return $this->service->show($id);
    }

    /** POST /api/services */
    public function store(ServiceRequest $request)
    {
        return $this->service->store($request);
    }

    /** POST /api/services/{id} — POST used to support multipart/form-data for image upload */
    public function update(ServiceRequest $request, int $id)
    {
        return $this->service->update($request, $id);
    }

    /** DELETE /api/services/{id} */
    public function destroy(int $id)
    {
        return $this->service->destroy($id);
    }

    /** POST /api/services/{id}/packages — sync all packages */
    public function syncPackages(ServiceRequest $request, int $id)
    {
        return $this->service->syncPackages($id, $request->validated('packages'));
    }

    // ── Public ────────────────────────────────────────────────────────────────

    /** GET /api/public/services */
    public function publicIndex(Request $request)
    {
        return $this->service->publicIndex($request);
    }

    /** GET /api/public/services/{slug} */
    public function showBySlug(string $slug)
    {
        return $this->service->showBySlug($slug);
    }
}
