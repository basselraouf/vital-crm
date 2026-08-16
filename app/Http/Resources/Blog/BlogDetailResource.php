<?php

namespace App\Http\Resources\Blog;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlogDetailResource extends JsonResource
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
            'content'          => $this->content,
            'excerpt'          => $this->excerpt,
            'featured_image'   => $this->featured_image_url,
            'category'         => $this->whenLoaded('category', fn () => [
                'id'   => $this->category->id,
                'name' => $this->category->name,
                'slug' => $this->category->slug,
            ]),
            'author'           => $this->whenLoaded('author', fn () => $this->author ? [
                'id'       => $this->author->id,
                'username' => $this->author->username,
            ] : null),
            'meta_title'       => $this->meta_title,
            'meta_description' => $this->meta_description,
            'focus_keyword'    => $this->focus_keyword,
            'status'           => $this->status,
            'published_at'     => $this->published_at?->toISOString(),
            'views_count'      => $this->views_count,
            'created_at'       => $this->created_at->toISOString(),
            'updated_at'       => $this->updated_at->toISOString(),
        ];
    }
}
