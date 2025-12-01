<x-layouts.app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl border border-neutral-200 dark:border-neutral-700 p-6">
        {{-- begin::News-block --}}
        <div class="mb-6">
            <livewire:news-table/>
        </div>
        {{-- end::News-block --}}

        @if(auth()->user()->isSuperadmin())
        {{-- begin::TI-block --}}
        <div class="">
            <div class="mb-6">
                <div class="flex justify-between items-center">
                    <h2 class="text-lg font-semibold">{{ __('Gestione Interessi di Tracciamento') }}</h2>
                </div>
            </div>
            {{-- <livewire:add-tracking-interests /> --}}
            <livewire:tracking-interest-table/>
        </div>
        {{-- end::TI-block --}}
        @endif

    </div>
</x-layouts.app>
