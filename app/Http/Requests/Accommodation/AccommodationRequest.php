<?php

namespace App\Http\Requests\Accommodation;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AccommodationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // ── Index (Dashboard listing) ────────────────────────────────────
        if ($this->routeIs('*.index')) {
            return [
                'search'   => ['nullable', 'string', 'max:255'],
                'status'   => ['nullable', 'string', 'in:active,inactive'],
                'sort_by'  => ['nullable', 'in:sort_order,name,price_per_night,rating,created_at'],
                'sort_dir' => ['nullable', 'in:asc,desc'],
                'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            ];
        }

        // ── Store / Update ──────────────────────────────────────────────
        $isUpdate = $this->isMethod('post') && $this->route('id');

        return [
            'name'            => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:255'],
            'slug'            => [
                'nullable', 
                'string', 
                'max:255', 
                $isUpdate ? 'unique:accommodations,slug,' . $this->route('id') : 'unique:accommodations,slug'
            ],
            'description'     => ['nullable', 'string', 'max:5000'],
            'rating'          => ['nullable', 'numeric', 'min:0', 'max:5'],
            'distance_text'   => ['nullable', 'string', 'max:255'],
            'bedrooms'        => ['nullable', 'integer', 'min:0', 'max:20'],
            'max_guests'      => ['nullable', 'integer', 'min:1', 'max:50'],
            'area_sqm'        => ['nullable', 'integer', 'min:1'],
            'price_per_night' => [$isUpdate ? 'sometimes' : 'required', 'numeric', 'min:0'],
            //currency should be required if price_per_night is not zero
            'currency'        => ['required_with:price_per_night', 'nullable', 'string', 'max:10'],
            'amenities'       => ['nullable', 'array'],
            'amenities.*'     => ['string', 'max:100'],
            'status'          => ['nullable', 'in:active,inactive'],
            'sort_order'      => ['nullable', 'integer', 'min:0'],

            // Images (uploaded with store/update)
            'images'          => ['nullable', 'array'],
            'images.*'        => ['file', 'image', 'mimes:jpeg,png,webp', 'max:5120'],

            // Delete images on update
            'delete_image_ids'   => ['nullable', 'array'],
            'delete_image_ids.*' => ['integer', 'exists:accommodation_images,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $isUpdate = $this->isMethod('post') && $this->route('id');

        if (!$isUpdate) {
            // Create
            if ($this->filled('name') && !$this->filled('slug')) {
                $this->merge(['slug' => \Illuminate\Support\Str::slug($this->name)]);
            }
        } else {
            // Update: Only generate slug if name changed and slug is not provided
            if ($this->filled('name') && !$this->filled('slug')) {
                $accommodation = \App\Models\Accommodation::find($this->route('id'));
                if ($accommodation && $accommodation->name !== $this->name) {
                    $this->merge(['slug' => \Illuminate\Support\Str::slug($this->name)]);
                }
            }
        }

        // Support single values or comma-separated strings for delete_image_ids (e.g. from postman form-data)
        if ($this->has('delete_image_ids')) {
            $val = $this->delete_image_ids;
            if (!is_array($val)) {
                if (is_string($val) && str_contains($val, ',')) {
                    $this->merge(['delete_image_ids' => explode(',', $val)]);
                } else {
                    $this->merge(['delete_image_ids' => array_filter([$val])]);
                }
            }
        }
    }
}
