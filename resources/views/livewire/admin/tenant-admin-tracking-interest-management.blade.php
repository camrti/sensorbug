<div class="space-y-6">
    <div class="bg-white dark:bg-zinc-900 shadow rounded-sm p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-lg font-semibold">Gestione Tracking Interest</h2>
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

        <livewire:tenant-user-tracking-interests-table/>
    </div>

    @if($showManageTIModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" x-data="{ show: @entangle('showManageTIModal') }" x-show="show">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 backdrop-blur-sm bg-gray-500/10 dark:bg-black/10 transition-opacity"
                     wire:click="closeManageTIModal"></div>

                <div class="inline-block align-bottom bg-white dark:bg-zinc-900 rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full">
                    <div class="bg-white dark:bg-zinc-900 px-4 pt-5 pb-4 sm:p-6">
                        <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100 mb-6">
                            Gestisci Tracking Interest - {{ $selectedUser?->name }}
                        </h3>

                        <div class="space-y-6">
                            <div>
                                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                                    Tracking Interest Assegnati
                                </h4>
                                @if($selectedUser && $selectedUser->trackingInterests->isNotEmpty())
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($selectedUser->trackingInterests as $ti)
                                            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-md text-sm font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">
                                                {{ $ti->interest }}
                                                <button wire:click="removeTrackingInterest({{ $ti->id }})"
                                                        class="hover:text-blue-900 dark:hover:text-blue-300">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                    </svg>
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Nessun tracking interest assegnato</p>
                                @endif
                            </div>

                            <div class="border-t border-gray-200 dark:border-zinc-700 pt-6">
                                <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
                                    Aggiungi Tracking Interest
                                </h4>
                                @if($this->availableTrackingInterests->isNotEmpty())
                                    <div class="flex gap-2">
                                        <select wire:model="selectedTrackingInterestId"
                                               class="flex-1 rounded-md border-gray-300 dark:border-zinc-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:bg-zinc-800 dark:text-gray-300">
                                            <option value="">Seleziona un tracking interest</option>
                                            @foreach($this->availableTrackingInterests as $ti)
                                                <option value="{{ $ti->id }}">{{ $ti->interest }}</option>
                                            @endforeach
                                        </select>
                                        <button wire:click="addTrackingInterest"
                                                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 rounded-md">
                                            Aggiungi
                                        </button>
                                    </div>
                                @else
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        Nessun tracking interest disponibile da assegnare
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 dark:bg-zinc-800 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button"
                                wire:click="closeManageTIModal"
                                class="w-full inline-flex justify-center rounded-md border border-gray-300 dark:border-zinc-600 shadow-sm px-4 py-2 bg-white dark:bg-zinc-800 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:w-auto sm:text-sm">
                            Chiudi
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
