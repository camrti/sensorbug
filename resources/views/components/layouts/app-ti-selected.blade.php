<div x-data="{
    hasSelectedInterest: {{ Session::has('selected_tracking_interest') ? 'true' : 'false' }},
    init() {
        Livewire.on('tracking-interest-selected', () => {
            this.hasSelectedInterest = true;
        });
        Livewire.on('tracking-interest-cleared', () => {
            this.hasSelectedInterest = false;
        });
    }
}">
    <x-layouts.app.sidebar :title="$title" class="flex h-full w-full flex-1 min-h-screen">
        <flux:main x-show="hasSelectedInterest" class="flex h-full w-full flex-1 min-h-screen">
            {{ $slot }}
        </flux:main>

        <flux:main x-show="!hasSelectedInterest" class="flex h-full w-full flex-1 min-h-screen">
            <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl border border-neutral-200 dark:border-neutral-700 p-6">
                <div class="text-center">
                    <p class="text-gray-500 dark:text-gray-400">
                        {{ __('Seleziona un interesse per visualizzare i dati') }}
                    </p>
                </div>
            </div>
        </flux:main>
    </x-layouts.app.sidebar>
</div>
