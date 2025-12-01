<div class="space-y-6">
    <div class="bg-white dark:bg-zinc-900 shadow rounded-sm p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-lg font-semibold">Lista utenti {{ auth()->user()->isTenantAdmin() ? 'del tuo tenant' : 'del sistema' }}</h2>
            @if (auth()->user()->isSuperadmin())
                <flux:button wire:click="openCreateModal" variant="primary" icon="user-plus">
                    Crea nuovo utente
                </flux:button>
            @endif
        </div>

        @if (session()->has('success'))
            <div class="mb-6 rounded-md bg-green-50 dark:bg-green-900/50 p-4 border border-green-200 dark:border-green-800">
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

        @if (session()->has('error'))
            <div class="mb-6 rounded-md bg-red-50 dark:bg-red-900/50 p-4 border border-red-200 dark:border-red-800">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800 dark:text-red-200">
                            {{ session('error') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <livewire:users-table/>
    </div>

    @if($showCreateModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" x-data="{ show: @entangle('showCreateModal') }" x-show="show">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 backdrop-blur-sm bg-gray-500/10 dark:bg-black/10 transition-opacity"
                     wire:click="closeCreateModal"></div>

                <div class="inline-block align-bottom bg-white dark:bg-zinc-900 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form wire:submit.prevent="createUser">
                        <div class="bg-white dark:bg-zinc-900 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100 mb-4">
                                        Crea Nuovo Utente
                                    </h3>

                                    <div class="space-y-4">
                                        <div>
                                            <label for="first_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                Nome
                                            </label>
                                            <flux:input wire:model="first_name"
                                                       id="first_name"
                                                       placeholder="Mario" />
                                            @error('first_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                        </div>

                                        <div>
                                            <label for="last_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                Cognome
                                            </label>
                                            <flux:input wire:model="last_name"
                                                       id="last_name"
                                                       placeholder="Rossi" />
                                            @error('last_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                        </div>

                                        <div>
                                            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                Email
                                            </label>
                                            <flux:input wire:model="email"
                                                       type="email"
                                                       id="email"
                                                       placeholder="mario.rossi@esempio.it" />
                                            @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                        </div>

                                        <div>
                                            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                Password
                                            </label>
                                            <flux:input wire:model="password"
                                                       type="password"
                                                       id="password"
                                                       placeholder="Inserisci la password"
                                                       viewable />
                                            @error('password') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                        </div>

                                        <div>
                                            <label for="tenant_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                Tenant
                                            </label>
                                            <select wire:model.live="tenant_id"
                                                   id="tenant_id"
                                                   class="w-full rounded-md border-gray-300 dark:border-zinc-600 shadow-sm focus:border-zinc-500 focus:ring-zinc-500 dark:bg-zinc-800 dark:text-gray-300">
                                                <option value="">Seleziona un tenant</option>
                                                @foreach($tenants as $tenant)
                                                    <option value="{{ $tenant->id }}">{{ $tenant->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('tenant_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                        </div>

                                        <div>
                                            <label for="user_role" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                Ruolo
                                            </label>
                                            <select wire:model="user_role"
                                                   id="user_role"
                                                   class="w-full rounded-md border-gray-300 dark:border-zinc-600 shadow-sm focus:border-zinc-500 focus:ring-zinc-500 dark:bg-zinc-800 dark:text-gray-300">
                                                @foreach($this->availableRoles as $value => $label)
                                                    <option value="{{ $value }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                            @error('user_role') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                        </div>

                                        <div class="space-y-2 pt-2">
                                            <div class="flex items-center">
                                                <label class="flex items-center">
                                                    <input type="checkbox"
                                                           wire:model="is_enabled"
                                                           class="rounded border-gray-300 text-zinc-700 shadow-sm focus:ring-zinc-500 dark:bg-zinc-800 dark:border-zinc-600 dark:text-zinc-400">
                                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Utente abilitato</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 dark:bg-zinc-800 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit"
                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-zinc-700 text-base font-medium text-white hover:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-zinc-500 dark:bg-zinc-600 dark:hover:bg-zinc-700 sm:ml-3 sm:w-auto sm:text-sm">
                                Crea Utente
                            </button>
                            <button type="button"
                                    wire:click="closeCreateModal"
                                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-zinc-600 shadow-sm px-4 py-2 bg-white dark:bg-zinc-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Annulla
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    @if($showEditModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" x-data="{ show: @entangle('showEditModal') }" x-show="show">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 backdrop-blur-sm bg-gray-500/10 dark:bg-black/10 transition-opacity"
                     wire:click="closeEditModal"></div>

                <div class="inline-block align-bottom bg-white dark:bg-zinc-900 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                    <form wire:submit.prevent="updateUser">
                        <div class="bg-white dark:bg-zinc-900 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100 mb-4">
                                        Modifica Utente
                                    </h3>

                                    <div class="space-y-4">
                                        <div>
                                            <label for="edit_first_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                Nome
                                            </label>
                                            <flux:input wire:model="first_name"
                                                       id="edit_first_name"
                                                       placeholder="Mario" />
                                            @error('first_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                        </div>

                                        <div>
                                            <label for="edit_last_name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                Cognome
                                            </label>
                                            <flux:input wire:model="last_name"
                                                       id="edit_last_name"
                                                       placeholder="Rossi" />
                                            @error('last_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                        </div>

                                        <div>
                                            <label for="edit_email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                Email
                                            </label>
                                            <flux:input wire:model="email"
                                                       type="email"
                                                       id="edit_email"
                                                       placeholder="mario.rossi@esempio.it" />
                                            @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                        </div>

                                        <div>
                                            <label for="edit_password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                Password
                                            </label>
                                            <flux:input wire:model="password"
                                                       type="password"
                                                       id="edit_password"
                                                       placeholder="Lascia vuoto per non modificare"
                                                       viewable />
                                            @error('password') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                        </div>

                                        <div>
                                            <label for="edit_tenant_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                Tenant
                                            </label>
                                            <select wire:model.live="tenant_id"
                                                   id="edit_tenant_id"
                                                   class="w-full rounded-md border-gray-300 dark:border-zinc-600 shadow-sm focus:border-zinc-500 focus:ring-zinc-500 dark:bg-zinc-800 dark:text-gray-300">
                                                <option value="">Seleziona un tenant</option>
                                                @foreach($tenants as $tenant)
                                                    <option value="{{ $tenant->id }}">{{ $tenant->name }}</option>
                                                @endforeach
                                            </select>
                                            @error('tenant_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                        </div>

                                        <div>
                                            <label for="edit_user_role" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                Ruolo
                                            </label>
                                            <select wire:model="user_role"
                                                   id="edit_user_role"
                                                   class="w-full rounded-md border-gray-300 dark:border-zinc-600 shadow-sm focus:border-zinc-500 focus:ring-zinc-500 dark:bg-zinc-800 dark:text-gray-300">
                                                @foreach($this->availableRoles as $value => $label)
                                                    <option value="{{ $value }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                            @error('user_role') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                                        </div>

                                        <div class="space-y-2 pt-2">
                                            <div class="flex items-center">
                                                <label class="flex items-center">
                                                    <input type="checkbox"
                                                           wire:model="is_enabled"
                                                           class="rounded border-gray-300 text-zinc-700 shadow-sm focus:ring-zinc-500 dark:bg-zinc-800 dark:border-zinc-600 dark:text-zinc-400">
                                                    <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Utente abilitato</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 dark:bg-zinc-800 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit"
                                    class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-zinc-700 text-base font-medium text-white hover:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-zinc-500 dark:bg-zinc-600 dark:hover:bg-zinc-700 sm:ml-3 sm:w-auto sm:text-sm">
                                Salva Modifiche
                            </button>
                            <button type="button"
                                    wire:click="closeEditModal"
                                    class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-zinc-600 shadow-sm px-4 py-2 bg-white dark:bg-zinc-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Annulla
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <script>
        document.addEventListener('livewire:initialized', () => {
            Livewire.on('confirmDelete', (event) => {
                if (confirm('Sei sicuro di voler eliminare questo utente?')) {
                    Livewire.dispatch('deleteUser', { userId: event.userId });
                }
            });
        });
    </script>
</div>