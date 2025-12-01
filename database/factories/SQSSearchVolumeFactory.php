<?php

namespace Database\Factories;

use App\Models\SQSSearchVolume;
use App\Models\SearchQueryString;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SQSSearchVolume>
 */
class SQSSearchVolumeFactory extends Factory
{
    protected $model = SQSSearchVolume::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $dataSources = ['Google Ads', 'SEMrush', 'Ahrefs', 'Keyword Planner', 'Manual Research'];
        $fromDate = fake()->dateTimeBetween('-1 year', '-1 month');
        $toDate = fake()->dateTimeBetween($fromDate, 'now');

        return [
            'search_query_string_id' => SearchQueryString::factory(),
            'volume' => fake()->numberBetween(10, 50000),
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'data_source' => fake()->randomElement($dataSources),
            'description' => fake()->optional(0.4)->sentence(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the volume is for a specific search query string.
     */
    public function forSearchQuery(SearchQueryString $searchQuery): static
    {
        return $this->state(fn (array $attributes) => [
            'search_query_string_id' => $searchQuery->id,
        ]);
    }

    /**
     * Create high volume search data.
     */
    public function highVolume(): static
    {
        return $this->state(fn (array $attributes) => [
            'volume' => fake()->numberBetween(10000, 100000),
        ]);
    }

    /**
     * Create low volume search data.
     */
    public function lowVolume(): static
    {
        return $this->state(fn (array $attributes) => [
            'volume' => fake()->numberBetween(10, 1000),
        ]);
    }

    /**
     * Create recent data.
     */
    public function recent(): static
    {
        $fromDate = fake()->dateTimeBetween('-2 months', '-1 week');
        return $this->state(fn (array $attributes) => [
            'from_date' => $fromDate,
            'to_date' => fake()->dateTimeBetween($fromDate, 'now'),
        ]);
    }

    /**
     * Create old data.
     */
    public function old(): static
    {
        $fromDate = fake()->dateTimeBetween('-1 year', '-6 months');
        return $this->state(fn (array $attributes) => [
            'from_date' => $fromDate,
            'to_date' => fake()->dateTimeBetween($fromDate, '-3 months'),
        ]);
    }

    /**
     * Create data from a specific source.
     */
    public function fromSource(string $source): static
    {
        return $this->state(fn (array $attributes) => [
            'data_source' => $source,
        ]);
    }

    /**
     * Create monthly volume data.
     */
    public function monthlyData(): static
    {
        $startOfMonth = fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-01');
        $endOfMonth = date('Y-m-t', strtotime($startOfMonth));

        return $this->state(fn (array $attributes) => [
            'from_date' => $startOfMonth,
            'to_date' => $endOfMonth,
            'description' => 'Dati mensili di ricerca',
        ]);
    }
}
