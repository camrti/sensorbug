<div class="max-w-2xl mx-auto">
    <div class="bg-white dark:bg-zinc-900 shadow rounded-sm p-6 space-y-6">
        <h2 class="text-lg font-semibold">{{ __('Crea nuova News') }}</h2>

        @if (session()->has('success'))
            <div class="rounded-md bg-green-50 dark:bg-green-900/50 p-4 border border-green-200 dark:border-green-800">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800 dark:text-green-200">
                            {{ session('success') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <form wire:submit="createNews" class="space-y-6">
            <!-- Tipo di destinatario -->
            <div>
                <flux:label class="mb-3">{{ __('Destinatario') }}</flux:label>
                <div class="space-y-2">
                    <label class="flex items-center">
                        <input type="radio" wire:model.live="target_type" value="user"
                               class="mr-2 text-blue-600 border-gray-300 focus:ring-blue-500 dark:bg-zinc-800 dark:border-zinc-600">
                        <span class="text-sm">{{ __('Utente specifico') }}</span>
                    </label>
                    <label class="flex items-center">
                        <input type="radio" wire:model.live="target_type" value="tracking_interest"
                               class="mr-2 text-blue-600 border-gray-300 focus:ring-blue-500 dark:bg-zinc-800 dark:border-zinc-600">
                        <span class="text-sm">{{ __('Tracking Interest') }}</span>
                    </label>
                </div>
                <flux:error name="target_type" />
            </div>

            <!-- Selezione utente -->
            @if($target_type === 'user')
                <div>
                    <flux:label>{{ __('Seleziona Utente') }}</flux:label>
                    <select wire:model="for_user_id"
                            class="mt-1 w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-zinc-800 dark:text-white">
                        <option value="">{{ __('-- Seleziona un utente --') }}</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                        @endforeach
                    </select>
                    <!-- NOTA -->
                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-md p-3 mt-3">
                        <p class="text-sm text-blue-800 dark:text-blue-200">
                            <strong>{{ __('Nota:') }}</strong> {{ __('Selezionando per utente, la news sarà mostrata solamente all\'utente scelto.') }}<br>
                        </p>
                    </div>
                    <flux:error name="for_user_id" />
                </div>
            @endif

            <!-- Selezione tracking interest -->
            @if($target_type === 'tracking_interest')
                <div>
                    <flux:label>{{ __('Seleziona Tracking Interest') }}</flux:label>
                    <select wire:model="for_tracking_interest_id"
                            class="mt-1 w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-zinc-800 dark:text-white">
                        <option value="">{{ __('-- Seleziona un tracking interest --') }}</option>
                        @foreach($trackingInterests as $interest)
                            <option value="{{ $interest->id }}">{{ $interest->interest }}</option>
                        @endforeach
                    </select>
                    <!-- NOTA -->
                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-md p-3 mt-3">
                        <p class="text-sm text-blue-800 dark:text-blue-200">
                            <strong>{{ __('Nota:') }}</strong> {{ __('Selezionando per Tracking Interest, la news sarà mostrata a tutti gli utenti associati per i quali il Tracking Interest è attivo.') }}<br>
                        </p>
                    </div>
                    <flux:error name="for_tracking_interest_id" />
                </div>
            @endif

            <!-- Titolo della news -->
            <div>
                <flux:label>{{ __('Titolo della News') }}</flux:label>
                <input type="text" wire:model="title" maxlength="100"
                       placeholder="{{ __('es: Report Settembre 2025') }}"
                       class="mt-1 w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-zinc-800 dark:text-white">
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Massimo 100 caratteri.') }}</p>
                <flux:error name="title" />
            </div>

            <!-- Testo della news -->
            <div>
                <flux:label>{{ __('Testo della News') }}</flux:label>
                <textarea wire:model="text" rows="10"
                          placeholder="{{ __('Inserisci il testo della news (HTML supportato: <strong>, <em>, <h1>, <p>, ecc.)...') }}"
                          class="mt-1 w-full px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-zinc-800 dark:text-white font-mono text-sm"></textarea>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('Minimo 10 caratteri, massimo 20000 caratteri. Puoi usare tag HTML per la formattazione.') }}</p>
                <flux:error name="text" />
            </div>

            <!-- Pulsante submit -->
            <div class="flex justify-end">
                <flux:button type="submit" variant="primary">
                    {{ __('Crea News') }}
                </flux:button>
            </div>
        </form>
    </div>
</div>