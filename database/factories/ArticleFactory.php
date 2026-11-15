<?php

namespace Database\Factories;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->unique()->sentence(6);

        return [
            'user_id' => User::factory(),
            'title' => $title,
            'slug' => Str::slug($title),
            'excerpt' => fake()->sentence(12),
            'content' => fake()->paragraphs(4, true),
            'status' => ArticleStatus::Draft,
            'published_at' => null,
            'is_featured' => false,
        ];
    }

    public function published(): static
    {
        return $this->state(fn () => [
            'status' => ArticleStatus::Published,
            'published_at' => now()->subDays(fake()->numberBetween(1, 30)),
        ]);
    }

    public function scheduled(): static
    {
        return $this->state(fn () => [
            'status' => ArticleStatus::Scheduled,
            'published_at' => now()->addDays(7),
        ]);
    }

    public function featured(): static
    {
        return $this->state(fn () => ['is_featured' => true]);
    }
}