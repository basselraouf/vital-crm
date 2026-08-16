<?php

namespace App\Http\Requests\Blog;

use Illuminate\Foundation\Http\FormRequest;

class BlogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $segment = $this->segment(count($this->segments()));

        return match (true) {
            $this->isMethod('POST') && !is_numeric($segment) => $this->createRules(),
            $this->isMethod('POST') && is_numeric($segment)  => $this->updateRules(),
            $this->isMethod('PUT')                           => $this->updateRules(),
            $this->isMethod('DELETE')                        => [],   // id from route param
            $this->isMethod('GET') && is_numeric($segment)   => [],   // id from route param
            default                                          => $this->indexRules(),
        };
    }

    // ── Validation Rule Sets ───────────────────────────────────────────────

    private function createRules(): array
    {
        return [
            'title'            => ['required', 'string', 'max:255'],
            'slug'             => ['nullable', 'string', 'max:200', 'unique:blogs,slug', 'regex:/^[a-z0-9\-]+$/'],
            'content'          => ['required', 'string'],
            'excerpt'          => ['nullable', 'string', 'max:1000'],
            'featured_image'   => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:5120'],
            'category_id'      => ['nullable', 'integer', 'exists:blog_categories,id'],
            'meta_title'       => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'focus_keyword'    => ['nullable', 'string', 'max:255'],
            'status'           => ['nullable', 'in:draft,published,archived'],
            'published_at'     => ['nullable', 'date'],
        ];
    }

    private function updateRules(): array
    {
        $id = $this->route('id') ?? $this->segment(count($this->segments()));

        return [
            'title'            => ['nullable', 'string', 'max:255'],
            'slug'             => ['nullable', 'string', 'max:200', "unique:blogs,slug,{$id}", 'regex:/^[a-z0-9\-]+$/'],
            'content'          => ['nullable', 'string'],
            'excerpt'          => ['nullable', 'string', 'max:1000'],
            'featured_image'   => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp,gif', 'max:5120'],
            'category_id'      => ['nullable', 'integer', 'exists:blog_categories,id'],
            'meta_title'       => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'focus_keyword'    => ['nullable', 'string', 'max:255'],
            'status'           => ['nullable', 'in:draft,published,archived'],
            'published_at'     => ['nullable', 'date'],
        ];
    }

    private function idRules(): array
    {
        // ID is passed as a route parameter — no body validation needed
        return [];
    }

    private function indexRules(): array
    {
        return [
            'search'      => ['nullable', 'string', 'max:255'],
            'status'      => ['nullable', 'in:draft,published,archived'],
            'category_id' => ['nullable', 'integer', 'exists:blog_categories,id'],
            'date_from'   => ['nullable', 'date'],
            'date_to'     => ['nullable', 'date', 'after_or_equal:date_from'],
            'sort_by'     => ['nullable', 'in:published_at,views_count,title,created_at'],
            'sort_dir'    => ['nullable', 'in:asc,desc'],
            'per_page'    => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
