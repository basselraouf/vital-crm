<?php

namespace App\Http\Controllers\Journey;

use App\Http\Controllers\Controller;
use App\Http\Requests\Journey\JourneyRequest;
use App\Services\Journey\JourneyService;

class JourneyController extends Controller
{
    protected JourneyService $service;

    public function __construct(JourneyService $service)
    {
        $this->service = $service;
    }

    // ── Public ───────────────────────────────────────────────────────────────

    public function store(JourneyRequest $request)
    {
        return $this->service->store($request->validated(), $request);
    }

    // ── Dashboard ────────────────────────────────────────────────────────────

    public function index(JourneyRequest $request)
    {
        return $this->service->index($request->validated());
    }

    public function show(int $id)
    {
        return $this->service->show($id);
    }

    public function updateStatus(JourneyRequest $request, int $id)
    {
        return $this->service->updateStatus($id, $request->validated());
    }

    public function destroy(int $id)
    {
        return $this->service->destroy($id);
    }
}
