<?php

use Livewire\Volt\Component;

new class extends Component {
    //
}; ?>

<x-layouts.app>
    <div class="p-6">
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-zinc-100">
                {{ __('Caricamento CSV') }}
            </h1>
            <p class="text-zinc-600 dark:text-zinc-400">
                {{ __('Carica un file CSV per popolare il database con nuovi dati') }}
            </p>
        </div>

        <livewire:csv-upload />
    </div>
</x-layouts.app>
