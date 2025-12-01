<?php

use Livewire\Volt\Component;

new class extends Component {
    public $userId;
    public $currentParentId;
    public $currentParentName;
    public $onlyAdmin;
    public $availableUsers;
}; ?>

<div class="relative" x-data="{
    open: false,
    search: '',
    currentParent: {{ $currentParentId ?? 'null' }},
    users: @js($availableUsers->map(function($u) { 
        return [
            'id' => $u->id, 
            'full_name' => trim($u->first_name . ' ' . $u->last_name),
            'email' => $u->email
        ]; 
    })->toArray())
}">
    @if($onlyAdmin && !auth()->user()->isAdmin())
        <span class="text-zinc-500 dark:text-zinc-400 text-sm">{{ $currentParentName ?? 'N/A' }}</span>
    @else
        <div class="relative min-w-[200px]">
            <button
                x-ref="button"
                @click="open = !open"
                class="relative w-full px-3 py-2 text-left bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg text-sm text-zinc-900 dark:text-zinc-100 transition-colors duration-200 hover:border-zinc-300 dark:hover:border-zinc-600 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 focus:border-transparent min-h-[2.5rem] flex items-center justify-between"
            >
                <span class="truncate">
                    {{ $currentParentName ?? 'Seleziona...' }}
                </span>
                <flux:icon.chevron-down class="size-4 text-zinc-400 dark:text-zinc-500 flex-shrink-0 ml-2" />
            </button>

            <div x-show="open"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="transform opacity-0 scale-95"
                 x-transition:enter-end="transform opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="transform opacity-100 scale-100"
                 x-transition:leave-end="transform opacity-0 scale-95"
                 @click.away="open = false"
                 class="fixed w-64 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg shadow-xl ring-1 ring-black/5 dark:ring-white/10 backdrop-blur-sm"
                 style="z-index: 99999 !important;"
                 x-cloak
                 x-init="$watch('open', value => {
                     if (value) {
                         const rect = $refs.button.getBoundingClientRect();
                         $el.style.left = rect.left + 'px';
                         $el.style.top = (rect.bottom + 8) + 'px';

                         // Se c'è overflow verso il basso, posiziona sopra
                         const dropdownRect = $el.getBoundingClientRect();
                         if (dropdownRect.bottom > window.innerHeight) {
                             $el.style.top = (rect.top - $el.offsetHeight - 8) + 'px';
                         }

                         // Se c'è overflow verso destra, aggiusta
                         if (dropdownRect.right > window.innerWidth) {
                             $el.style.left = (rect.right - $el.offsetWidth) + 'px';
                         }
                     }
                 })">

                <!-- Campo di ricerca -->
                <div class="p-3 border-b border-zinc-200 dark:border-zinc-700">
                    <flux:input
                        icon="magnifying-glass"
                        placeholder="Cerca utente enterprise"
                        x-model="search"
                        size="sm"
                        />
                </div>

                <!-- Lista opzioni -->
                <div class="max-h-48 overflow-y-auto">
                    <!-- Lista utenti enterprise filtrati -->
                    <template x-for="user in users.filter(u =>
                        u.full_name.toLowerCase().includes(search.toLowerCase()) ||
                        u.email.toLowerCase().includes(search.toLowerCase())
                    )" :key="user.id">
                        <div class="px-3 py-2.5 cursor-pointer text-sm transition-colors duration-150 flex items-start justify-between gap-2"
                             :class="user.id === currentParent ?
                                'bg-indigo-50 dark:bg-indigo-500/10 border-l-2 border-indigo-500 dark:border-indigo-400' :
                                'hover:bg-zinc-50 dark:hover:bg-zinc-700/50'">
                            <div class="flex-1" @click="$wire.call('updateParentAccount', {{ $userId }}, user.id); open = false">
                                <div class="font-medium text-zinc-900 dark:text-zinc-100" x-text="user.full_name"></div>
                                <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5" x-text="user.email"></div>
                                <div class="inline-flex items-center gap-1 mt-1">
                                    <flux:badge size="sm" color="indigo" variant="solid">Enterprise</flux:badge>
                                </div>
                            </div>
                            <button x-show="user.id === currentParent"
                                    @click.stop="$wire.call('updateParentAccount', {{ $userId }}, null); open = false"
                                    class="p-1.5 hover:bg-red-100 dark:hover:bg-red-900/30 rounded text-red-600 dark:text-red-400 flex-shrink-0">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                </svg>
                            </button>
                        </div>
                    </template>

                    <!-- No enterprise account selectable message -->
                    <div x-show="users.filter(u =>
                        u.name.toLowerCase().includes(search.toLowerCase()) ||
                        u.email.toLowerCase().includes(search.toLowerCase())
                    ).length === 0"
                         class="px-4 py-8 text-sm text-zinc-500 dark:text-zinc-400 text-center">
                        <flux:icon.user-group class="mx-auto size-8 text-zinc-300 dark:text-zinc-600 mb-2" />
                        <div class="font-medium mb-1">Nessun utente enterprise trovato</div>
                        <div class="text-xs">Solo utenti enterprise possono essere parent account</div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
