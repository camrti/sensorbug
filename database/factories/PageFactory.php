<?php

namespace Database\Factories;

use App\Models\Page;
use App\Models\Shop;
use App\Models\Seller;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Page>
 */
class PageFactory extends Factory
{
    protected $model = Page::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'shop_id' => Shop::factory(),
            'whitelist_class' => fake()->randomElement(['A', 'B', 'C', 'D']),
            'currently_sells' => fake()->boolean(70), // 70% chance di vendere attualmente
            'is_selling_page' => fake()->boolean(80), // 80% chance di essere una pagina di vendita
            'seller_id' => null, // Può essere null
            'redirects_to_page_id' => null, // Può essere null
            'page_url' => fake()->url(),
            'ticket_name' => fake()->sentence(3), // Nome del biglietto
            'notes' => fake()->optional(0.3)->sentence(), // 30% chance di avere note aggiuntive
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the page is currently selling.
     */
    public function selling(): static
    {
        return $this->state(fn (array $attributes) => [
            'currently_sells' => true,
            'is_selling_page' => true,
        ]);
    }

    /**
     * Indicate that the page is not selling.
     */
    public function notSelling(): static
    {
        return $this->state(fn (array $attributes) => [
            'currently_sells' => false,
            'is_selling_page' => false,
        ]);
    }

    /**
     * Indicate that the page has notes.
     */
    public function withNotes(): static
    {
        return $this->state(fn (array $attributes) => [
            'notes' => fake()->paragraph(),
        ]);
    }

    /**
     * Indicate that the page belongs to a specific shop.
     */
    public function forShop(Shop $shop): static
    {
        return $this->state(fn (array $attributes) => [
            'shop_id' => $shop->id,
        ]);
    }
}