<?php

namespace App\Http\Resources\Blog;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlogListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'title'            => $this->title,
            'slug'             => $this->slug,
            'excerpt'          => $this->excerpt,
            'featured_image'   => $this->featured_image_url,   // uses model accessor
            'category'         => $this->whenLoaded('category', fn () => [
                'id'   => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ]),
            'status'           => $this->status,
            'published_at'     => $this->published_at?->toISOString(),
            'views_count'      => $this->views_count,
            'meta_title'       => $this->meta_title,
            'meta_description' => $this->meta_description,
            'focus_keyword'    => $this->focus_keyword,
            'created_at'       => $this->created_at->toISOString(),
        ];
    }
}
