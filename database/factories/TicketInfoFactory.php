<?php

namespace Database\Factories;

use App\Models\TicketInfo;
use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TicketInfo>
 */
class TicketInfoFactory extends Factory
{
    protected $model = TicketInfo::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $currencies = ['EUR', 'USD', 'GBP', 'CHF'];
        $ticketTypes = ['concert', 'theater', 'sport', 'festival', 'conference', 'cinema', 'museum', 'event'];

        return [
            'page_id' => Page::factory(),
            'price_at' => fake()->dateTimeBetween('-6 months', 'now'),
            'currency' => fake()->randomElement($currencies),
            'ticket_type' => fake()->randomElement($ticketTypes),
            'selling_price' => fake()->randomFloat(2, 5.00, 500.00),
            'description' => fake()->optional(0.7)->sentence(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Indicate that the ticket is for a specific page.
     */
    public function forPage(Page $page): static
    {
        return $this->state(fn (array $attributes) => [
            'page_id' => $page->id,
        ]);
    }

    /**
     * Create expensive tickets.
     */
    public function expensive(): static
    {
        return $this->state(fn (array $attributes) => [
            'selling_price' => fake()->randomFloat(2, 100.00, 1000.00),
        ]);
    }

    /**
     * Create cheap tickets.
     */
    public function cheap(): static
    {
        return $this->state(fn (array $attributes) => [
            'selling_price' => fake()->randomFloat(2, 5.00, 50.00),
        ]);
    }

    /**
     * Create concert tickets.
     */
    public function concert(): static
    {
        return $this->state(fn (array $attributes) => [
            'ticket_type' => 'concert',
            'description' => fake()->randomElement([
                'Concerto rock',
                'Concerto pop',
                'Concerto classico',
                'Festival musicale',
                'Live performance'
            ]),
        ]);
    }

    /**
     * Create sport tickets.
     */
    public function sport(): static
    {
        return $this->state(fn (array $attributes) => [
            'ticket_type' => 'sport',
            'description' => fake()->randomElement([
                'Partita di calcio',
                'Match di tennis',
                'Gara di Formula 1',
                'Partita di basket',
                'Evento sportivo'
            ]),
        ]);
    }

    /**
     * Create tickets with specific currency.
     */
    public function withCurrency(string $currency): static
    {
        return $this->state(fn (array $attributes) => [
            'currency' => $currency,
        ]);
    }
}
