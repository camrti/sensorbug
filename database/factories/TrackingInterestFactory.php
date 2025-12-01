<?php

namespace Database\Factories;

use App\Models\TrackingInterest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TrackingInterest>
 */
class TrackingInterestFactory extends Factory
{
    protected $model = TrackingInterest::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'interest' => fake()->words(3, true),
            'is_active' => fake()->boolean(80), // 80% chance di essere attivo
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the tracking interest is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the tracking interest is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
