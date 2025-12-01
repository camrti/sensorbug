<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('components.layouts.auth')] class extends Component {
    public string $first_name = '';
    public string $last_name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    /**
     * Handle an incoming registration request.
     */
    public function register(): void
    {
        $validated = $this->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
        ]);

        $validated['name'] = $validated['first_name'] . ' ' . $validated['last_name'];
        unset($validated['first_name'], $validated['last_name']);
        $validated['password'] = Hash::make($validated['password']);

        event(new Registered(($user = User::create($validated))));

        Auth::login($user);

        $this->redirectIntended(route('dashboard', absolute: false), navigate: true);
    }
}; ?>

<div class="flex flex-col gap-6">
    <x-auth-header :title="__('Crea un account')" :description="__('Inserisci i tuoi dati qui sotto per creare il tuo account')" />

    <!-- Session Status -->
    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit="register" class="flex flex-col gap-6">
        <!-- First Name -->
        <flux:input
            wire:model="first_name"
            :label="__('Nome')"
            type="text"
            required
            autofocus
            autocomplete="given-name"
            placeholder="Mario"
        />

        <!-- Last Name -->
        <flux:input
            wire:model="last_name"
            :label="__('Cognome')"
            type="text"
            required
            autocomplete="family-name"
            placeholder="Rossi"
        />

        <!-- Email Address -->
        <flux:input
            wire:model="email"
            :label="__('Indirizzo email')"
            type="email"
            required
            autocomplete="email"
            placeholder="mario.rossi@esempio.it"
        />

        <!-- Password -->
        <div>
            <flux:input
                wire:model="password"
                :label="__('Password')"
                type="password"
                required
                autocomplete="new-password"
                placeholder=""
                viewable
            />
            <div class="mt-2 text-xs text-zinc-600 dark:text-zinc-400 space-y-1">
                <p>La password deve contenere:</p>
                <ul class="list-disc list-inside space-y-0.5 ml-2">
                    <li>Almeno 8 caratteri</li>
                    <li>Almeno una lettera maiuscola</li>
                    <li>Almeno un numero</li>
                    <li>Almeno un simbolo (!@#$%^&*)</li>
                </ul>
            </div>
        </div>

        <!-- Confirm Password -->
        <flux:input
            wire:model="password_confirmation"
            :label="__('Conferma password')"
            type="password"
            required
            autocomplete="new-password"
            placeholder=""
            viewable
        />

        <div class="flex items-center justify-end">
            <flux:button type="submit" variant="primary" class="w-full">
                {{ __('Crea un account') }}
            </flux:button>
        </div>
    </form>

    <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
        {{ __('Hai già un account?') }}
        <flux:link :href="route('login')" wire:navigate>{{ __('Accedi') }}</flux:link>
    </div>
</div>
