<?php

namespace App\Http\Requests\Service;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class ServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        if ($this->routeIs('*.packages')) {
            return $this->packagesRules();
        }

        if ($this->routeIs('*.price-items')) {
            return $this->priceItemsRules();
        }

        if ($this->routeIs('*.faqs')) {
            return $this->faqsRules();
        }

        if ($this->routeIs('*.index') || $this->routeIs('*.public-index')) {
            return $this->indexRules();
        }

        $isUpdate = $this->isMethod('post') && $this->route('id');
        return $this->serviceRules($isUpdate);
    }

    // ── Service (store/update) — includes optional procedures & packages ───────

    private function serviceRules(bool $isUpdate): array
    {
        $slugUnique = 'unique:services,slug';
        if ($isUpdate && $this->route('id')) {
            $slugUnique .= ',' . $this->route('id');
        }

        return [
            // Core fields
            'name'              => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:255'],
            'slug'              => [$isUpdate ? 'sometimes' : 'nullable', 'string', 'max:255', $slugUnique],
            'tagline'           => ['nullable', 'string', 'max:500'],
            'short_description' => ['nullable', 'string'],
            'image'             => ['nullable', 'file', 'image', 'max:5120'],    // 5MB max
            'benefits'          => ['nullable', 'array'],
            'benefits.*'        => ['string', 'max:500'],
            'why_us_points'     => ['nullable', 'array'],
            'why_us_points.*'   => ['string', 'max:500'],
            'packages_tagline'  => ['nullable', 'string', 'max:255'],
            'packages_description' => ['nullable', 'string', 'max:500'],
            'packages_include'  => ['nullable', 'array'],
            'packages_include.*'=> ['string', 'max:500'],
            'sort_order'        => ['nullable', 'integer', 'min:0', 'max:255'],
            'status'            => ['nullable', 'in:active,in_active,coming_soon'],

            // Nested: procedures (optional — if present, replaces all)
            // Nested: procedures (optional — if present, replaces all)
            // Expecting simple array of strings: ["Procedure 1", "Procedure 2"]
            'procedures'   => ['nullable', 'array'],
            'procedures.*' => ['required_with:procedures', 'string', 'max:255'],
        ];
    }

    // ── Packages (sync) ───────────────────────────────────────────────────────

    private function packagesRules(): array
    {
        return [
            'packages'               => ['required', 'array'],
            'packages.*.name'        => ['required', 'string', 'max:255'],
            'packages.*.description' => ['nullable', 'string'],
            'packages.*.content'     => ['nullable', 'string'],
            'packages.*.sort_order'  => ['nullable', 'integer', 'min:0'],
        ];
    }

    // ── Price Items (sync) ────────────────────────────────────────────────

    private function priceItemsRules(): array
    {
        return [
            'price_items'              => ['required', 'array'],
            'price_items.*.name'       => ['required', 'string', 'max:255'],
            'price_items.*.price'      => ['nullable', 'string', 'max:150'],
            'price_items.*.note'       => ['nullable', 'string', 'max:255'],
            'price_items.*.sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    // ── FAQs (sync) ──────────────────────────────────────────────────────────

    private function faqsRules(): array
    {
        return [
            'faqs'              => ['required', 'array'],
            'faqs.*.question'   => ['required', 'string', 'max:500'],
            'faqs.*.answer'     => ['nullable', 'string'],
            'faqs.*.sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }

    // ── Index (filters/search/sort) ───────────────────────────────────────────

    private function indexRules(): array
    {
        return [
            'search'    => ['nullable', 'string', 'max:255'],
            'status'    => ['nullable', 'in:active,in_active,coming_soon'],
            'sort_by'   => ['nullable', 'in:sort_order,name,created_at'],
            'sort_dir'  => ['nullable', 'in:asc,desc'],
            'per_page'  => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    // ── Auto-generate slug if not provided ────────────────────────────────────

    protected function prepareForValidation(): void
    {
        if ($this->filled('name') && !$this->filled('slug')) {
            $this->merge(['slug' => Str::slug($this->name)]);
        }
    }
}

