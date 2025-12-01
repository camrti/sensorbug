<?php

namespace Database\Factories;

use App\Models\PageFound;
use App\Models\Page;
use App\Models\TrackingInterest;
use App\Models\SearchQueryString;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PageFound>
 */
class PageFoundFactory extends Factory
{
    protected $model = PageFound::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'page_id' => Page::factory(),
            'tracking_interest_id' => TrackingInterest::factory(),
            'search_query_string_id' => SearchQueryString::factory(),
            'search_platform' => fake()->randomElement(['Google', 'Bing', 'Yahoo', 'DuckDuckGo']),
            'serp_position' => fake()->numberBetween(1, 100),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the page was found in top positions.
     */
    public function topPosition(): static
    {
        return $this->state(fn (array $attributes) => [
            'serp_position' => fake()->numberBetween(1, 10),
        ]);
    }

    /**
     * Indicate that the page was found on Google.
     */
    public function onGoogle(): static
    {
        return $this->state(fn (array $attributes) => [
            'search_platform' => 'Google',
        ]);
    }

    /**
     * Indicate that the page belongs to a specific tracking interest.
     */
    public function forTrackingInterest(TrackingInterest $trackingInterest): static
    {
        return $this->state(fn (array $attributes) => [
            'tracking_interest_id' => $trackingInterest->id,
        ]);
    }

    /**
     * Indicate that the page belongs to a specific page.
     */
    public function forPage(Page $page): static
    {
        return $this->state(fn (array $attributes) => [
            'page_id' => $page->id,
        ]);
    }

    /**
     * Indicate that the page was found using a specific search query string.
     */
    public function withSearchQuery(SearchQueryString $searchQueryString): static
    {
        return $this->state(fn (array $attributes) => [
            'search_query_string_id' => $searchQueryString->id,
        ]);
    }
}
