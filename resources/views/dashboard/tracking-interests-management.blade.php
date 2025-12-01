<x-layouts.app :title="__('Gestione Tracking Interests')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 rounded-xl border border-neutral-200 dark:border-neutral-700 p-6">
        <div class="mb-6">
            <h2 class="text-xl font-semibold">{{ __('Gestione Tracking Interests') }}</h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Crea e gestisci i tracking interests del sistema') }}</p>
        </div>

        <!-- Tracking Interests Management Table -->
        <livewire:admin.tracking-interest-management />
    </div>
</x-layouts.app>
