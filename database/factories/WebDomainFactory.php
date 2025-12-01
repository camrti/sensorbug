<?php

namespace Database\Factories;

use App\Models\WebDomain;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\WebDomain>
 */
class WebDomainFactory extends Factory
{
    protected $model = WebDomain::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $tlds = ['.com', '.it', '.org', '.net', '.eu', '.shop', '.store'];
        
        // Genera un dominio unico combinando parole diverse
        $domain = $this->generateUniqueDomain($tlds);

        return [
            'domain' => $domain,
            'country' => fake()->randomElement(['IT', 'US', 'DE', 'FR', 'ES', 'UK']),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * Generate a unique domain name.
     */
    private function generateUniqueDomain(array $tlds): string
    {
        $attempts = 0;
        $maxAttempts = 100;
        
        do {
            // Combina più elementi per ridurre le collisioni
            $parts = [
                fake()->randomElement([
                    fake()->company(),
                    fake()->word(),
                    fake()->firstName() . fake()->lastName(),
                    fake()->domainWord(),
                ]),
                fake()->optional(0.3)->randomElement([
                    'shop', 'store', 'online', 'web', 'digital', 'market', 'plaza'
                ])
            ];
            
            // Pulisce e combina le parti
            $domainName = strtolower(str_replace([' ', '.', ',', '&', '-'], '', implode('', array_filter($parts))));
            $domainName = substr($domainName, 0, 20); // Limita la lunghezza
            $tld = fake()->randomElement($tlds);
            $domain = $domainName . $tld;
            
            $attempts++;
            
            // Se non esiste già, restituisci il dominio
            if (!WebDomain::where('domain', $domain)->exists()) {
                return $domain;
            }
            
        } while ($attempts < $maxAttempts);
        
        // Fallback con timestamp per garantire unicità
        $timestamp = time() . rand(1000, 9999);
        return 'domain' . $timestamp . fake()->randomElement($tlds);
    }

    /**
     * Indicate that the web domain is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the web domain is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Create an Italian domain.
     */
    public function italian(): static
    {
        return $this->state(fn (array $attributes) => [
            'domain' => fake()->domainWord() . '.it',
        ]);
    }

    /**
     * Create a specific domain.
     */
    public function withDomain(string $domain): static
    {
        return $this->state(fn (array $attributes) => [
            'domain' => $domain,
        ]);
    }
}
