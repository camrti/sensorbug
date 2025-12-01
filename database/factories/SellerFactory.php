<?php

namespace Database\Factories;

use App\Models\Seller;
use App\Models\WebDomain;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Seller>
 */
class SellerFactory extends Factory
{
    protected $model = Seller::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'found_on_domain_id' => WebDomain::factory(),
            'name' => fake()->company(),
            'is_certified' => fake()->boolean(30), // 30% chance di essere certificato
            'affiliated_with_seller_id' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the seller is found on a specific web domain.
     */
    public function foundOnDomain(WebDomain $webDomain): static
    {
        return $this->state(fn (array $attributes) => [
            'found_on_domain_id' => $webDomain->id,
        ]);
    }

    /**
     * Indicate that the seller is affiliated with another seller.
     */
    public function affiliatedWith(Seller $parentSeller): static
    {
        return $this->state(fn (array $attributes) => [
            'affiliated_with_seller_id' => $parentSeller->id,
        ]);
    }

    /**
     * Create a certified seller.
     */
    public function certified(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_certified' => true,
        ]);
    }

    /**
     * Create a non-certified seller.
     */
    public function notCertified(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_certified' => false,
        ]);
    }
}