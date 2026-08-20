<?php

namespace App\Http\Requests\Consultation;

use Illuminate\Foundation\Http\FormRequest;

class FreeConsultationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        if ($this->routeIs('*.index')) {
            return [
                'search'     => ['nullable', 'string', 'max:255'],
                'status'     => ['nullable', 'string', 'in:new,contacted,scheduled,completed,cancelled,no_show,unqualified'],
                'service_id' => ['nullable', 'integer', 'exists:services,id'],
                'sort_by'    => ['nullable', 'in:full_name,created_at,preferred_date,status'],
                'sort_dir'   => ['nullable', 'in:asc,desc'],
                'per_page'   => ['nullable', 'integer', 'min:1', 'max:100'],
            ];
        }

        if ($this->isMethod('patch') || $this->isMethod('put')) {
            // Update Status (Dashboard)
            return [
                'status' => ['required', 'string', 'in:new,contacted,scheduled,completed,cancelled,no_show,unqualified'],
            ];
        }

        // Store (Public)
        return [
            'full_name'          => ['required', 'string', 'max:255'],
            'email'              => ['nullable', 'email', 'max:255'],
            'phone'              => ['required', 'string', 'max:50'],
            'preferred_date'     => ['nullable', 'date', 'after_or_equal:today'],
            'service_id'         => ['nullable', 'exists:services,id'],
            
            // Medical Fields
            'age'                => ['nullable', 'integer', 'min:1', 'max:120'],
            'weight'             => ['nullable', 'string', 'max:50'],
            'previous_surgeries' => ['nullable', 'string', 'max:1000'],
            
            // Extra
            'how_did_you_hear'   => ['nullable', 'string', 'max:255'],
            'additional_notes'   => ['nullable', 'string', 'max:2000'],
        ];
    }
}
