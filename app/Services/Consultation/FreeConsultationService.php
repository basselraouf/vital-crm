<?php

namespace App\Services\Consultation;

use App\Models\FreeConsultation;
use Illuminate\Support\Facades\Response;

class FreeConsultationService
{
    /**
     * Get a paginated list of consultations (Dashboard).
     */
    public function index(array $filters)
    {
        try {
            $query = FreeConsultation::with('service:id,name,slug,image')
                ->sorted($filters['sort_by'] ?? 'created_at', $filters['sort_dir'] ?? 'desc');

            if (!empty($filters['search'])) {
                $query->search($filters['search']);
            }

            if (!empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (!empty($filters['service_id'])) {
                $query->where('service_id', $filters['service_id']);
            }

            $consultations = $query->paginate($filters['per_page'] ?? 15);

            return Response::successResponse([
                'consultations' => $consultations->items(),
                'meta' => [
                    'current_page' => $consultations->currentPage(),
                    'last_page'    => $consultations->lastPage(),
                    'per_page'     => $consultations->perPage(),
                    'total'        => $consultations->total(),
                ]
            ]);
        } catch (\Exception $e) {
            return Response::handleException($e, 'fetch consultations');
        }
    }

    /**
     * Show a single consultation (Dashboard).
     */
    public function show(int $id)
    {
        try {
            $consultation = FreeConsultation::with('service:id,name,slug,image')->findOrFail($id);
            return Response::successResponse($consultation);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::handleModelNotFoundException($e, 'Free Consultation');
        } catch (\Exception $e) {
            return Response::handleException($e, 'fetch consultation');
        }
    }

    /**
     * Store a new consultation request (Public).
     */
    public function store(array $data)
    {
        try {
            $consultation = FreeConsultation::create($data);
            $consultation->load('service:id,name,slug,image');

            return Response::successResponse(
                $consultation,
                'Your consultation request has been submitted successfully. We will get back to you soon!',
                201
            );
        } catch (\Exception $e) {
            return Response::handleException($e, 'submit consultation request');
        }
    }

    /**
     * Update the status of a consultation (Dashboard).
     */
    public function updateStatus(int $id, string $status)
    {
        try {
            $consultation = FreeConsultation::with('service:id,name,slug,image')->findOrFail($id);
            $consultation->update(['status' => $status]);

            return Response::successResponse($consultation, 'Status updated successfully.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::handleModelNotFoundException($e, 'Free Consultation');
        } catch (\Exception $e) {
            return Response::handleException($e, 'update consultation status');
        }
    }

    /**
     * Delete a consultation (Dashboard).
     */
    public function destroy(int $id)
    {
        try {
            $consultation = FreeConsultation::findOrFail($id);
            $consultation->delete();

            return Response::successResponse(null, 'Consultation deleted successfully.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::handleModelNotFoundException($e, 'Free Consultation');
        } catch (\Exception $e) {
            return Response::handleException($e, 'delete consultation');
        }
    }
}
