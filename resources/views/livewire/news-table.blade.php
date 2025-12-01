<div class="bg-white dark:bg-zinc-800 rounded-lg shadow-sm border border-zinc-200 dark:border-zinc-700 p-6 space-y-6">
    <div class="flex justify-between items-center">
        <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ __('Le tue notizie') }}</h3>
        <flux:button wire:click="loadNews" variant="ghost" size="sm" icon="arrow-path">
            {{ __('Aggiorna') }}
        </flux:button>
    </div>

    @if($news->count() > 0)
        <div class="space-y-4">
            @foreach($news as $newsItem)
                <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg p-4 border-l-4 border-l-blue-500 dark:border-l-blue-400 shadow-sm">
                    <div class="flex justify-between items-start gap-4">
                        <div class="flex-1 space-y-3">
                            <h4 class="text-xl font-bold text-zinc-900 dark:text-zinc-100">{{ $newsItem->title }}</h4>
                            <div class="text-zinc-700 dark:text-zinc-300 leading-relaxed prose prose-sm dark:prose-invert max-w-none">
                                {!! $newsItem->text !!}
                            </div>

                            <div class="flex flex-wrap gap-2 text-sm">
                                @if($newsItem->forUser)
                                    <div class="flex items-center gap-1">
                                        <span class="text-zinc-600 dark:text-zinc-400">{{ __('Per utente:') }}</span>
                                        <span class="bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300 px-2 py-1 rounded-full text-xs border border-green-200 dark:border-green-700">
                                            {{ $newsItem->forUser->name }}
                                        </span>
                                    </div>
                                @endif

                                @if($newsItem->forTrackingInterest)
                                    <div class="flex items-center gap-1">
                                        <span class="text-zinc-600 dark:text-zinc-400">{{ __('Per interesse:') }}</span>
                                        <span class="bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 px-2 py-1 rounded-full text-xs border border-blue-200 dark:border-blue-700">
                                            {{ $newsItem->forTrackingInterest->interest }}
                                        </span>
                                    </div>
                                @endif

                                @if($newsItem->addedByUser)
                                    <div class="flex items-center gap-1">
                                        <span class="text-zinc-600 dark:text-zinc-400">{{ __('Aggiunto da:') }}</span>
                                        <span class="text-zinc-700 dark:text-zinc-300 font-medium">{{ $newsItem->addedByUser->name }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="text-sm text-zinc-500 dark:text-zinc-400 whitespace-nowrap">
                            {{ $newsItem->created_at->diffForHumans() }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Paginazione --}}
        <div class="flex justify-between items-center pt-4 border-t border-zinc-200 dark:border-zinc-700">
            <div class="text-sm text-zinc-600 dark:text-zinc-400">
                {{ __('Mostrando') }} {{ $news->firstItem() }} - {{ $news->lastItem() }} {{ __('di') }} {{ $news->total() }} {{ __('notizie') }}
            </div>
            <div class="flex gap-2">
                @if($news->onFirstPage())
                    <span class="px-3 py-1 text-sm text-zinc-400 dark:text-zinc-500 cursor-not-allowed">
                        {{ __('Precedente') }}
                    </span>
                @else
                    <button wire:click="previousPage" class="px-3 py-1 text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 transition-colors">
                        {{ __('Precedente') }}
                    </button>
                @endif

                <span class="px-3 py-1 text-sm text-zinc-700 dark:text-zinc-300">
                    {{ $news->currentPage() }} / {{ $news->lastPage() }}
                </span>

                @if($news->hasMorePages())
                    <button wire:click="nextPage" class="px-3 py-1 text-sm text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 transition-colors">
                        {{ __('Successivo') }}
                    </button>
                @else
                    <span class="px-3 py-1 text-sm text-zinc-400 dark:text-zinc-500 cursor-not-allowed">
                        {{ __('Successivo') }}
                    </span>
                @endif
            </div>
        </div>
    @else
        <div class="bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-lg p-12 text-center">
            <div class="space-y-4">
                <div class="text-zinc-400 dark:text-zinc-500">
                    <svg class="mx-auto h-16 w-16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 7.5h1.5m-1.5 3h1.5m-7.5 3h7.5m-7.5 3h7.5m3-9h3.375c.621 0 1.125.504 1.125 1.125V18a2.25 2.25 0 01-2.25 2.25M16.5 7.5V18a2.25 2.25 0 002.25 2.25M16.5 7.5V4.875c0-.621-.504-1.125-1.125-1.125H4.125C3.504 3.75 3 4.254 3 4.875V18a2.25 2.25 0 002.25 2.25h6.75" />
                    </svg>
                </div>
                <div class="space-y-2">
                    <h4 class="text-lg font-medium text-zinc-900 dark:text-zinc-100">{{ __('Nessuna notizia') }}</h4>
                    <p class="text-zinc-500 dark:text-zinc-400">{{ __('Non ci sono notizie per te al momento.') }}</p>
                </div>
            </div>
        </div>
    @endif
</div>