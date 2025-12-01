<x-layouts.app :title="__('Gestione News')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl border border-neutral-200 dark:border-neutral-700 p-6">
        <div class="mb-6">
            <h2 class="text-xl font-semibold">{{ __('Gestione News') }}</h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Crea e gestisci le news per utenti e tracking interests') }}</p>
        </div>

        <!-- Form per creare nuova news -->
        <livewire:admin.news-form />

        <!-- Lista delle news esistenti -->
        <livewire:admin.news-list />
    </div>
</x-layouts.app>
