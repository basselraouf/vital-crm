<?php

namespace App\Http\Controllers\Accommodation;

use App\Http\Controllers\Controller;
use App\Http\Requests\Accommodation\AccommodationRequest;
use App\Services\Accommodation\AccommodationService;
use Illuminate\Http\Request;

class AccommodationController extends Controller
{
    protected AccommodationService $service;

    public function __construct(AccommodationService $service)
    {
        $this->service = $service;
    }

    // ── Public ───────────────────────────────────────────────────────────────

    public function publicIndex(Request $request)
    {
        return $this->service->publicIndex($request);
    }

    public function publicShow(string $slug)
    {
        return $this->service->publicShow($slug);
    }

    // ── Dashboard ────────────────────────────────────────────────────────────

    public function index(AccommodationRequest $request)
    {
        return $this->service->index($request->validated());
    }

    public function show(int $id)
    {
        return $this->service->show($id);
    }

    public function store(AccommodationRequest $request)
    {
        return $this->service->store($request->validated(), $request);
    }

    public function update(AccommodationRequest $request, int $id)
    {
        return $this->service->update($id, $request->validated(), $request);
    }

    public function destroy(int $id)
    {
        return $this->service->destroy($id);
    }
}
