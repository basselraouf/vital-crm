<?php

namespace App\Http\Requests\Blog;

use Illuminate\Foundation\Http\FormRequest;

class BlogCategoryRequest extends FormRequest
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
            $this->isMethod('DELETE')                        => [],  // id from route param
            $this->isMethod('GET') && is_numeric($segment)   => [],  // id from route param
            default                                          => $this->indexRules(),
        };
    }

    private function createRules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'slug'        => ['nullable', 'string', 'max:200', 'unique:blog_categories,slug', 'regex:/^[a-z0-9\-]+$/'],
            'description' => ['nullable', 'string', 'max:1000'],
            'parent_id'   => ['nullable', 'integer', 'exists:blog_categories,id'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
            'is_active'   => ['nullable', 'boolean'],
        ];
    }

    private function updateRules(): array
    {
        $id = $this->route('id') ?? $this->segment(count($this->segments()));

        return [
            'name'        => ['nullable', 'string', 'max:255'],
            'slug'        => ['nullable', 'string', 'max:200', "unique:blog_categories,slug,{$id}", 'regex:/^[a-z0-9\-]+$/'],
            'description' => ['nullable', 'string', 'max:1000'],
            'parent_id'   => ['nullable', 'integer', 'exists:blog_categories,id'],
            'sort_order'  => ['nullable', 'integer', 'min:0'],
            'is_active'   => ['nullable', 'boolean'],
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
            'with_blogs_count' => ['nullable', 'boolean'],
            'only_active'      => ['nullable', 'boolean'],
        ];
    }
}
