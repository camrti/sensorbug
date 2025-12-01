<x-layouts.app :title="__('Gestione Assegnazioni Tracking Interest')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl border border-neutral-200 dark:border-neutral-700 p-6">
        <div class="mb-6">
            <h2 class="text-xl font-semibold">{{ __('Gestione Assegnazioni Tracking Interest') }}</h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Gestisci le assegnazioni dei tracking interest agli utenti secondo le regole di business') }}</p>
        </div>

        <!-- Tabella delle assegnazioni -->
        <livewire:admin.tracking-interest-assignment-table />
    </div>
</x-layouts.app>
