<div class="space-y-6">
    <div class="bg-white dark:bg-zinc-900 shadow rounded-sm p-6">
        <div class="flex justify-between items-center mb-6">
            <h3 class="text-lg font-semibold">{{ __('News esistenti') }}</h3>
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

        <!-- Filtri e ricerca -->
        <div class="flex flex-col sm:flex-row gap-4 mb-6">
            <div class="flex-1">
                <flux:input wire:model.live="search" placeholder="{{ __('Cerca per titolo o testo...') }}" />
            </div>
            <div>
                <select wire:model.live="filter"
                        class="px-3 py-2 border border-gray-300 dark:border-zinc-600 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-zinc-800 dark:text-white">
                    <option value="all">{{ __('Tutte le news') }}</option>
                    <option value="user">{{ __('Solo per utenti') }}</option>
                    <option value="tracking_interest">{{ __('Solo per tracking interests') }}</option>
                </select>
            </div>
        </div>

        <!-- Lista delle news -->
        @if($news->count() > 0)
            <div class="space-y-4">
                @foreach($news as $newsItem)
                    <div class="border border-gray-200 dark:border-zinc-600 rounded-lg p-4 bg-gray-50 dark:bg-zinc-800">
                        <div class="flex justify-between items-start">
                            <div class="flex-1 space-y-3">
                                <div class="text-base font-bold text-gray-900 dark:text-white">{{ $newsItem->title }}</div>
                                <div class="text-sm text-gray-700 dark:text-gray-300">{{ Str::limit(strip_tags($newsItem->text), 150) }}</div>

                                <div class="space-y-2 text-xs text-zinc-500 dark:text-zinc-400">
                                    @if($newsItem->forUser)
                                        <div class="flex items-center gap-2">
                                            <span class="inline-block px-2 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded text-xs">
                                                {{ __('Utente') }}
                                            </span>
                                            <span><strong>{{ $newsItem->forUser->name }}</strong> ({{ $newsItem->forUser->email }})</span>
                                        </div>
                                    @endif

                                    @if($newsItem->forTrackingInterest)
                                        <div class="flex items-center gap-2">
                                            <span class="inline-block px-2 py-1 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 rounded text-xs">
                                                {{ __('Tracking Interest') }}
                                            </span>
                                            <span><strong>{{ $newsItem->forTrackingInterest->interest }}</strong></span>
                                        </div>
                                    @endif

                                    <div class="flex items-center gap-2">
                                        <span class="inline-block px-2 py-1 bg-zinc-100 dark:bg-zinc-700 text-zinc-800 dark:text-zinc-200 rounded text-xs">
                                            {{ __('Creata da') }}
                                        </span>
                                        <span><strong>{{ $newsItem->addedByUser->name }}</strong></span>
                                    </div>                                    <div class="flex items-center gap-2">
                                        <span class="inline-block px-2 py-1 bg-zinc-100 dark:bg-zinc-700 text-zinc-800 dark:text-zinc-200 rounded text-xs">
                                            {{ __('Data') }}
                                        </span>
                                        <span>{{ $newsItem->created_at->format('d/m/Y H:i') }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="ml-4">
                                <button wire:click="deleteNews({{ $newsItem->id }})"
                                        wire:confirm="{{ __('Sei sicuro di voler eliminare questa news?') }}"
                                        class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 p-1">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Paginazione -->
            <div class="mt-6">
                {{ $news->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <svg class="mx-auto size-12 text-zinc-400 dark:text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="mt-4 text-lg font-semibold">
                    @if($search || $filter !== 'all')
                        {{ __('Nessuna news trovata') }}
                    @else
                        {{ __('Nessuna news presente') }}
                    @endif
                </h3>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    @if($search || $filter !== 'all')
                        {{ __('Prova a modificare i filtri di ricerca.') }}
                    @else
                        {{ __('Crea la prima news utilizzando il modulo sopra.') }}
                    @endif
                </p>
            </div>
        @endif
    </div>
</div>