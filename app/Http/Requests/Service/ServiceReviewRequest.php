<?php

namespace App\Http\Requests\Service;

use Illuminate\Foundation\Http\FormRequest;

class ServiceReviewRequest extends FormRequest
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
     */
    public function rules(): array
    {
        if ($this->routeIs('public.reviews.all')) {
            return [
                'per_page'     => ['nullable', 'integer', 'min:1', 'max:100'],
                'website_page' => ['nullable', 'integer', 'min:1'],
                'admin_page'   => ['nullable', 'integer', 'min:1'],
            ];
        }

        if ($this->routeIs('*.reviews.index')) {
            return [
                'status'   => ['nullable', 'string', 'in:pending,rejected,selected,drafted'],
                'source'   => ['nullable', 'string', 'in:website,admin'],
                'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            ];
        }

        if ($this->routeIs('*.reviews.status')) {
            return [
                'status' => ['required', 'string', 'in:rejected,selected,drafted'],
            ];
        }

        // Store (public & admin)
        $rules = [
            'reviewer_name'     => ['nullable', 'string', 'max:150'],
            'reviewer_location' => ['nullable', 'string', 'max:100'],
            'rating'            => ['required', 'integer', 'min:1', 'max:5'],
            'content'           => ['required', 'string', 'max:2000'],
        ];

        // Flat public route: service_id is optional (generic review when omitted)
        if ($this->routeIs('public.reviews.store')) {
            $rules['service_id'] = ['nullable', 'integer', 'exists:services,id'];
        }

        // Admin can upload media
        if ($this->routeIs('*.dashboard.reviews.store')) {
            $rules['media'] = [
                'nullable', 
                'file', 
                'mimetypes:image/jpeg,image/png,image/webp,video/mp4,video/quicktime,video/x-msvideo', 
                'max:20480'
            ];
        }

        return $rules;
    }
}
