<?php

namespace Database\Factories;

use App\Models\SearchQueryString;
use App\Models\TrackingInterest;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SearchQueryString>
 */
class SearchQueryStringFactory extends Factory
{
    protected $model = SearchQueryString::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tracking_interest_id' => TrackingInterest::factory(),
            'search_intent' => fake()->randomElement(['informational', 'commercial', 'transactional', 'navigational']),
            'query_string' => fake()->unique()->words(fake()->numberBetween(1, 4), true),
            'language_code' => fake()->randomElement(['it', 'en', 'es', 'fr', 'de']),
            'source' => fake()->randomElement(['keyword_research', 'competitor_analysis', 'user_input', 'automated']),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Generate realistic search queries.
     */
    private function generateSearchQuery(): string
    {
        $templates = [
            // E-commerce queries
            'acquistare {product}',
            'miglior {product}',
            '{product} prezzo',
            '{product} offerta',
            'dove comprare {product}',
            '{product} online',
            '{product} scontato',
            'negozio {product}',

            // Fashion queries
            'abbigliamento {category}',
            'vestiti {style}',
            'scarpe {type}',
            'borse {brand}',

            // Electronics queries
            'smartphone {brand}',
            'laptop {category}',
            'televisore {size}',
            'cuffie wireless',

            // General product queries
            '{adjective} {product}',
            'recensioni {product}',
            'confronto {product}',
        ];

        $products = [
            'smartphone', 'laptop', 'tablet', 'scarpe', 'borsa', 'vestito', 'giacca',
            'televisore', 'cuffie', 'orologio', 'zaino', 'jeans', 'maglietta', 'telefono'
        ];

        $categories = [
            'uomo', 'donna', 'bambini', 'casual', 'elegante', 'sportivo', 'estivo', 'invernale'
        ];

        $styles = [
            'casual', 'elegante', 'sportivo', 'vintage', 'moderno', 'classico'
        ];

        $types = [
            'running', 'eleganti', 'casual', 'sportive', 'estive', 'invernali'
        ];

        $brands = [
            'Nike', 'Adidas', 'Prada', 'Gucci', 'Zara', 'H&M', 'Apple', 'Samsung'
        ];

        $adjectives = [
            'migliore', 'economico', 'qualità', 'nuovo', 'usato', 'scontato', 'originale'
        ];

        $template = fake()->randomElement($templates);

        $query = str_replace('{product}', fake()->randomElement($products), $template);
        $query = str_replace('{category}', fake()->randomElement($categories), $query);
        $query = str_replace('{style}', fake()->randomElement($styles), $query);
        $query = str_replace('{type}', fake()->randomElement($types), $query);
        $query = str_replace('{brand}', fake()->randomElement($brands), $query);
        $query = str_replace('{size}', fake()->randomElement(['32"', '42"', '55"', '65"']), $query);
        $query = str_replace('{adjective}', fake()->randomElement($adjectives), $query);

        return $query;
    }

    /**
     * Indicate that the search query string is for a specific tracking interest.
     */
    public function forTrackingInterest(TrackingInterest $trackingInterest): static
    {
        return $this->state(fn (array $attributes) => [
            'tracking_interest_id' => $trackingInterest->id,
        ]);
    }

    /**
     * Indicate that the search query string is commercial intent.
     */
    public function commercial(): static
    {
        return $this->state(fn (array $attributes) => [
            'search_intent' => 'commercial',
        ]);
    }

    /**
     * Indicate that the search query string is transactional intent.
     */
    public function transactional(): static
    {
        return $this->state(fn (array $attributes) => [
            'search_intent' => 'transactional',
        ]);
    }

    /**
     * Indicate that the search query string is in Italian.
     */
    public function italian(): static
    {
        return $this->state(fn (array $attributes) => [
            'language_code' => 'it',
        ]);
    }
}
