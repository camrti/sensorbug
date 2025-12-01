<?php

namespace Database\Factories;

use App\Models\News;
use App\Models\User;
use App\Models\TrackingInterest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\News>
 */
class NewsFactory extends Factory
{
    protected $model = News::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(4),
            'text' => fake()->paragraph(),
            'for_user_id' => User::factory(),
            'for_tracking_interest_id' => null, // Nullable per default
            'added_by_user_id' => User::factory(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the news is for a specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'for_user_id' => $user->id,
        ]);
    }

    /**
     * Indicate that the news is for a specific tracking interest.
     */
    public function forTrackingInterest(TrackingInterest $trackingInterest): static
    {
        return $this->state(fn (array $attributes) => [
            'for_tracking_interest_id' => $trackingInterest->id,
        ]);
    }

    /**
     * Indicate that the news was added by a specific user.
     */
    public function addedBy(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'added_by_user_id' => $user->id,
        ]);
    }

    /**
     * Create news with specific title.
     */
    public function withTitle(string $title): static
    {
        return $this->state(fn (array $attributes) => [
            'title' => $title,
        ]);
    }

    /**
     * Create news with specific text.
     */
    public function withText(string $text): static
    {
        return $this->state(fn (array $attributes) => [
            'text' => $text,
        ]);
    }

    /**
     * Create short news.
     */
    public function short(): static
    {
        return $this->state(fn (array $attributes) => [
            'text' => fake()->sentence(rand(8, 15)),
        ]);
    }

    /**
     * Create long news.
     */
    public function long(): static
    {
        return $this->state(fn (array $attributes) => [
            'text' => fake()->paragraphs(rand(2, 4), true),
        ]);
    }

    /**
     * Create news with a random tracking interest.
     */
    public function withTrackingInterest(): static
    {
        return $this->state(fn (array $attributes) => [
            'for_tracking_interest_id' => TrackingInterest::factory(),
        ]);
    }
}