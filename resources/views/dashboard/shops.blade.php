<x-layouts.app-ti-selected :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl border border-neutral-200 dark:border-neutral-700 p-6">
        <div class="">
            <div class="flex justify-between items-center">
                <h2 class="text-lg font-semibold">{{ __('Piattaforme di vendita') }}</h2>
            </div>
        </div>

        <livewire:shops-table></livewire:shops-table>
    </div>
</x-layouts.app>
