<?php

namespace App\Services\Journey;

use App\Models\JourneyRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class JourneyService
{
    // ── Dashboard: paginated list ────────────────────────────────────────────

    public function index(array $filters)
    {
        try {
            $query = JourneyRequest::with('accommodation')
                ->sorted($filters['sort_by'] ?? 'created_at', $filters['sort_dir'] ?? 'desc');

            if (!empty($filters['search'])) {
                $query->search($filters['search']);
            }

            if (!empty($filters['status'])) {
                $query->where('status', $filters['status']);
            }

            if (!empty($filters['accommodation_id'])) {
                $query->where('accommodation_id', $filters['accommodation_id']);
            }

            if (!empty($filters['country_of_residence'])) {
                $query->where('country_of_residence', 'LIKE', '%' . $filters['country_of_residence'] . '%');
            }

            if (!empty($filters['arrival_from'])) {
                $query->whereDate('arrival_date', '>=', $filters['arrival_from']);
            }

            if (!empty($filters['arrival_to'])) {
                $query->whereDate('arrival_date', '<=', $filters['arrival_to']);
            }

            if (isset($filters['fast_track_clearance'])) {
                $query->where('fast_track_clearance', filter_var($filters['fast_track_clearance'], FILTER_VALIDATE_BOOLEAN));
            }

            $requests = $query->paginate($filters['per_page'] ?? 15);

            return Response::successResponse([
                'journey_requests' => $requests->items(),
                'meta' => [
                    'current_page' => $requests->currentPage(),
                    'last_page'    => $requests->lastPage(),
                    'per_page'     => $requests->perPage(),
                    'total'        => $requests->total(),
                ],
            ], 'Journey requests retrieved successfully.');
        } catch (\Exception $e) {
            return Response::handleException($e, 'fetch journey requests');
        }
    }

    // ── Dashboard: show single ───────────────────────────────────────────────

    public function show(int $id)
    {
        try {
            $journey = JourneyRequest::with('accommodation')->findOrFail($id);
            return Response::successResponse($journey, 'Journey request retrieved successfully.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::handleModelNotFoundException($e, 'Journey Request');
        } catch (\Exception $e) {
            return Response::handleException($e, 'fetch journey request');
        }
    }

    // ── Public: submit the full journey form ─────────────────────────────────

    public function store(array $data, Request $request)
    {
        try {
            // Handle file uploads
            if ($request->hasFile('passport')) {
                $data['passport_path'] = $request->file('passport')->store('journeys/passports', 'public');
            }

            if ($request->hasFile('flight_ticket')) {
                $data['flight_ticket_path'] = $request->file('flight_ticket')->store('journeys/flight-tickets', 'public');
            }

            // Remove raw file inputs from validated data — we already stored paths above
            unset($data['passport'], $data['flight_ticket']);

            $journey = JourneyRequest::create($data);
            $journey->load('accommodation');

            return Response::successResponse(
                $journey,
                'Your journey request has been submitted successfully. Our team will be in touch with you shortly!',
                201
            );
        } catch (\Exception $e) {
            return Response::handleException($e, 'submit journey request');
        }
    }

    // ── Dashboard: update status (and optional internal note) ────────────────

    public function updateStatus(int $id, array $data)
    {
        try {
            $journey = JourneyRequest::with('accommodation')->findOrFail($id);
            $journey->update($data);

            return Response::successResponse($journey, 'Journey request status updated successfully.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::handleModelNotFoundException($e, 'Journey Request');
        } catch (\Exception $e) {
            return Response::handleException($e, 'update journey status');
        }
    }

    // ── Dashboard: delete ────────────────────────────────────────────────────

    public function destroy(int $id)
    {
        try {
            $journey = JourneyRequest::findOrFail($id);

            // Clean up uploaded files
            if ($journey->passport_path) {
                Storage::disk('public')->delete($journey->passport_path);
            }
            if ($journey->flight_ticket_path) {
                Storage::disk('public')->delete($journey->flight_ticket_path);
            }

            $journey->delete();

            return Response::successResponse(null, 'Journey request deleted successfully.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return Response::handleModelNotFoundException($e, 'Journey Request');
        } catch (\Exception $e) {
            return Response::handleException($e, 'delete journey request');
        }
    }
}
