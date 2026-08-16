<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\BlogCategory>
 */
class BlogCategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'        => fake()->words(3, true),
            'slug'        => fake()->unique()->slug(3),
            'description' => fake()->optional()->sentence(),
            'parent_id'   => null,
            'sort_order'  => fake()->numberBetween(0, 10),
            'is_active'   => true,
        ];
    }
}
