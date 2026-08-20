<?php

namespace App\Http\Controllers\Consultation;

use App\Http\Controllers\Controller;
use App\Http\Requests\Consultation\FreeConsultationRequest;
use App\Services\Consultation\FreeConsultationService;
use Illuminate\Http\Request;

class FreeConsultationController extends Controller
{
    protected FreeConsultationService $service;

    public function __construct(FreeConsultationService $service)
    {
        $this->service = $service;
    }

    public function index(FreeConsultationRequest $request)
    {
        return $this->service->index($request->validated());
    }

    public function store(FreeConsultationRequest $request)
    {
        return $this->service->store($request->validated());
    }

    public function show($id)
    {
        return $this->service->show($id);
    }

    public function updateStatus(FreeConsultationRequest $request, $id)
    {
        return $this->service->updateStatus($id, $request->validated('status'));
    }

    public function destroy($id)
    {
        return $this->service->destroy($id);
    }
}
