<?php

namespace Database\Factories;

use App\Models\Shop;
use App\Models\WebDomain;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Shop>
 */
class ShopFactory extends Factory
{
    protected $model = Shop::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $shopTypes = ['OTA', 'Personal Website'];

        return [
            'shop_type' => fake()->randomElement($shopTypes),
            'company_name' => fake()->unique()->company(),
            'email' => fake()->companyEmail(),
            'phone_number' => fake()->phoneNumber(),
            'identification_number' => fake()->numerify('########'),
            'address' => fake()->address(),
            'notes' => fake()->optional(0.3)->sentence(), // 30% chance di avere note
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Create a shop with Italian details.
     */
    public function italian(): static
    {
        return $this->state(fn (array $attributes) => [
            'identification_number' => fake()->numerify('IT########'),
        ]);
    }

    /**
     * Create a shop without notes.
     */
    public function withoutNotes(): static
    {
        return $this->state(fn (array $attributes) => [
            'notes' => null,
        ]);
    }

    /**
     * Attach web domains to the shop after creation.
     */
    public function withWebDomains($webDomains): static
    {
        return $this->afterCreating(function (Shop $shop) use ($webDomains) {
            if (is_array($webDomains) || is_object($webDomains)) {
                $shop->webDomains()->attach($webDomains);
            } else {
                $shop->webDomains()->attach([$webDomains]);
            }
        });
    }
}
