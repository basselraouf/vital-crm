<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Blog>
 */
class BlogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement(['draft', 'published', 'archived']);

        return [
            'title'            => fake()->sentence(6),
            'slug'             => fake()->unique()->slug(4),
            'content'          => fake()->paragraphs(5, true),
            'excerpt'          => fake()->optional()->paragraph(),
            'featured_image'   => fake()->optional()->imageUrl(1200, 630, 'medical'),
            'category_id'      => null,
            'meta_title'       => fake()->optional()->sentence(4),
            'meta_description' => fake()->optional()->sentence(10),
            'focus_keyword'    => fake()->optional()->words(3, true),
            'status'           => $status,
            'published_at'     => $status === 'published' ? fake()->dateTimeBetween('-1 year', 'now') : null,
            'views_count'      => fake()->numberBetween(0, 5000),
            'author_id'        => null,
        ];
    }

    /** State: force published */
    public function published(): static
    {
        return $this->state(fn () => [
            'status'       => 'published',
            'published_at' => fake()->dateTimeBetween('-1 year', 'now'),
        ]);
    }

    /** State: force draft */
    public function draft(): static
    {
        return $this->state(fn () => [
            'status'       => 'draft',
            'published_at' => null,
        ]);
    }
}
