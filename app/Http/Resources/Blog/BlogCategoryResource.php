<?php

namespace App\Http\Resources\Blog;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlogCategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'slug'        => $this->slug,
            'description' => $this->description,
            'parent_id'   => $this->parent_id,
            'sort_order'  => $this->sort_order,
            'is_active'   => $this->is_active,
            'blogs_count' => $this->whenCounted('blogs'),
            'children'    => BlogCategoryResource::collection($this->whenLoaded('children')),
            'created_at'  => $this->created_at->toISOString(),
        ];
    }
}
