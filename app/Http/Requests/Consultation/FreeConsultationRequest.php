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
        if ($this->isMethod('patch') || $this->isMethod('put')) {
            // Update Status (Dashboard)
            return [
                'status' => ['required', 'string', 'in:pending,contacted,resolved'],
            ];
        }

        // Store (Public)
        return [
            'name'        => ['required', 'string', 'max:255'],
            'email'       => ['required', 'email', 'max:255'],
            'description' => ['required', 'string', 'max:1000'],
        ];
    }
}
