<?php

namespace App\Http\Requests\Journey;

use Illuminate\Foundation\Http\FormRequest;

class JourneyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // ── Dashboard: Index filters ─────────────────────────────────────
        if ($this->routeIs('*.index')) {
            return [
                'search'              => ['nullable', 'string', 'max:255'],
                'status'              => ['nullable', 'string', 'in:new,under_review,confirmed,in_progress,completed,cancelled'],
                'accommodation_id'    => ['nullable', 'integer', 'exists:accommodations,id'],
                'country_of_residence'=> ['nullable', 'string', 'max:100'],
                'arrival_from'        => ['nullable', 'date'],
                'arrival_to'          => ['nullable', 'date', 'after_or_equal:arrival_from'],
                'fast_track_clearance'=> ['nullable', 'boolean'],
                'sort_by'             => ['nullable', 'in:full_name,created_at,arrival_date,departure_date,status,nights'],
                'sort_dir'            => ['nullable', 'in:asc,desc'],
                'per_page'            => ['nullable', 'integer', 'min:1', 'max:100'],
            ];
        }

        // ── Dashboard: Update Status ─────────────────────────────────────
        if ($this->routeIs('*.update-status')) {
            return [
                'status'         => ['required', 'string', 'in:new,under_review,confirmed,in_progress,completed,cancelled'],
                'internal_notes' => ['nullable', 'string', 'max:5000'],
            ];
        }

        // ── Public: Submit Journey Form ──────────────────────────────────
        return [
            // Step 1: Patient & Medical Details
            'full_name'            => ['required', 'string', 'max:255'],
            'email'                => ['nullable', 'email', 'max:255'],
            'phone'                => ['required', 'string', 'max:50'],
            'country_of_residence' => ['nullable', 'string', 'max:100'],
            'procedure_sought'     => ['nullable', 'string', 'max:500'],
            'medical_notes'        => ['nullable', 'string', 'max:3000'],

            // Step 2: Travel & Security Clearance
            'arrival_date'         => ['nullable', 'date'],
            'departure_date'       => ['nullable', 'date', 'after_or_equal:arrival_date'],
            'passport'             => ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:10240'],  // 10MB
            'flight_ticket'        => ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:10240'],  // 10MB
            'fast_track_clearance' => ['nullable', 'boolean'],

            // Step 3: Accommodation
            'accommodation_id'     => ['nullable', 'exists:accommodations,id'],
            'nights'               => ['nullable', 'integer', 'min:1', 'max:60'],
        ];
    }
}
