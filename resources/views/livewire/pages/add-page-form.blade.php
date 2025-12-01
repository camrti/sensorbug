<?php

use App\Models\Page;
use App\Models\Shop;
use App\Models\Seller;
use App\Models\WebDomain;
use App\Models\PageFound;
use App\Models\SearchQueryString;
use Livewire\Volt\Component;

new class extends Component {
    // Form fields
    public $page_url = '';
    public $whitelist_class = 'Unknown';
    public $currently_sells = true;
    public $is_selling_page = false;
    public $page_notes = '';
    public $custom_search_query = '';
    public $search_platform = 'Manual Entry';

    // Shop fields
    public $shop_type = '';
    public $company_name = '';
    public $shop_email = '';
    public $shop_phone_number = '';
    public $shop_identification_number = '';
    public $shop_address = '';
    public $shop_notes = '';

    // Seller fields
    public $seller_name = '';
    public $seller_domain = '';

    // Control flags
    public $showForm = false;
    public $existingShop = null;
    public $existingShops = [];
    public $useExistingShop = false;
    public $selectedShopId = null;

    // Seller autocomplete
    public $useExistingSeller = false;
    public $selectedSellerId = null;
    public $existingSellers = [];

    // Ticket Info fields
    public $add_ticket_info = false;
    public $ticket_currency = 'EUR';
    public $ticket_type = '';
    public $selling_price = '';
    public $ticket_description = '';
    public $price_date = '';

    protected $rules = [
        'page_url' => 'required|string|max:500|unique:pages,page_url',
        'whitelist_class' => 'required|string|max:30',
        'currently_sells' => 'boolean',
        'is_selling_page' => 'boolean',
        'page_notes' => 'nullable|string|max:255',
        'custom_search_query' => 'nullable|string|max:255',
        'search_platform' => 'required|string|max:100',

        'shop_type' => 'required|string|max:20',
        'company_name' => 'required|string|max:255|unique:shops,company_name',
        'shop_email' => 'nullable|email|max:255',
        'shop_phone_number' => 'nullable|string|max:30',
        'shop_identification_number' => 'required|string|max:50',
        'shop_address' => 'nullable|string|max:255',
        'shop_notes' => 'nullable|string|max:255',

        'seller_name' => 'nullable|string|max:255',
        'seller_domain' => 'nullable|string|max:255',

        'selectedShopId' => 'nullable|exists:shops,id',
        'selectedSellerId' => 'nullable|exists:sellers,id',

        // Ticket Info validation
        'add_ticket_info' => 'boolean',
        'ticket_currency' => 'required_if:add_ticket_info,true|string|max:20',
        'ticket_type' => 'nullable|string|max:20',
        'selling_price' => 'required_if:add_ticket_info,true|numeric|min:0',
        'ticket_description' => 'nullable|string|max:255',
        'price_date' => 'required_if:add_ticket_info,true|date',
    ];

    protected $messages = [
        'page_url.required' => 'L\'URL della pagina è obbligatorio.',
        'page_url.unique' => 'Questa URL è già presente nel database.',
        'page_url.max' => 'L\'URL non può superare i :max caratteri.',

        'shop_type.required' => 'Il tipo di negozio è obbligatorio.',
        'company_name.required' => 'Il nome dell\'azienda è obbligatorio.',
        'company_name.unique' => 'Questo nome azienda è già registrato.',
        'shop_identification_number.required' => 'Il numero di identificazione del negozio è obbligatorio.',

        'selectedShopId.exists' => 'Il negozio selezionato non esiste.',
        'selectedSellerId.exists' => 'Il venditore selezionato non esiste.',
        'search_platform.required' => 'La piattaforma di ricerca è obbligatoria.',

        // Ticket Info messages
        'ticket_currency.required_if' => 'La valuta è obbligatoria quando si aggiungono informazioni sui biglietti.',
        'selling_price.required_if' => 'Il prezzo di vendita è obbligatorio quando si aggiungono informazioni sui biglietti.',
        'selling_price.numeric' => 'Il prezzo deve essere un numero.',
        'selling_price.min' => 'Il prezzo deve essere maggiore o uguale a 0.',
        'price_date.required_if' => 'La data del prezzo è obbligatoria quando si aggiungono informazioni sui biglietti.',
        'price_date.date' => 'La data del prezzo deve essere una data valida.',
    ];

    public function mount()
    {
        $this->existingShops = Shop::select('id', 'company_name', 'shop_type')
            ->orderBy('company_name')
            ->get()
            ->toArray();

        $this->existingSellers = Seller::select('id', 'name')
            ->whereNotNull('name')
            ->orderBy('name')
            ->get()
            ->toArray();

        // Set default price date to today
        $this->price_date = now()->format('Y-m-d');
    }

    public function getCurrentTrackingInterest()
    {
        $selectedTI = session('selected_tracking_interest');
        if ($selectedTI) {
            return \App\Models\TrackingInterest::find($selectedTI);
        }
        return null;
    }

    public function getEffectiveSearchQuery()
    {
        return $this->custom_search_query ?: 'Manual Entry';
    }

    public function updatedUseExistingShop()
    {
        if ($this->useExistingShop) {
            $this->resetShopValidation();
        } else {
            $this->selectedShopId = null;
            $this->resetValidation(['selectedShopId']);
        }
    }

    public function updatedUseExistingSeller()
    {
        if ($this->useExistingSeller) {
            $this->resetSellerValidation();
        } else {
            $this->selectedSellerId = null;
            $this->resetValidation(['selectedSellerId']);
        }
    }

    public function updatedSelectedSellerId()
    {
        if ($this->selectedSellerId && $this->useExistingSeller) {
            $seller = collect($this->existingSellers)->firstWhere('id', $this->selectedSellerId);
            if ($seller) {
                // Auto-fill seller fields
                $this->seller_name = $seller['name'];
            }
        }
    }

    public function resetShopValidation()
    {
        $this->resetValidation([
            'company_name', 'shop_type', 'shop_identification_number',
            'shop_email', 'shop_phone_number', 'shop_address', 'shop_notes'
        ]);
    }

    public function resetSellerValidation()
    {
        $this->resetValidation([
            'seller_name', 'seller_domain'
        ]);
    }

    public function toggleForm()
    {
        $this->showForm = !$this->showForm;
        if (!$this->showForm) {
            $this->resetForm();
        }
    }

    public function resetForm()
    {
        $this->reset([
            'page_url', 'whitelist_class', 'currently_sells', 'is_selling_page', 'page_notes',
            'custom_search_query', 'search_platform',
            'shop_type', 'company_name', 'shop_email', 'shop_phone_number',
            'shop_identification_number', 'shop_address', 'shop_notes',
            'seller_name', 'seller_domain',
            'useExistingShop', 'selectedShopId', 'useExistingSeller', 'selectedSellerId',
            'add_ticket_info', 'ticket_currency', 'ticket_type', 'selling_price',
            'ticket_description', 'price_date'
        ]);
        $this->whitelist_class = 'Unknown';
        $this->currently_sells = true;
        $this->is_selling_page = false;
        $this->search_platform = 'Manual Entry';
        $this->ticket_currency = 'EUR';
        $this->price_date = now()->format('Y-m-d');
        $this->resetValidation();
    }

    public function save()
    {
        // Check if a tracking interest is selected
        $selectedTI = session('selected_tracking_interest');
        if (!$selectedTI) {
            $this->addError('general', 'Nessun interesse di tracciamento selezionato. Seleziona un interesse prima di aggiungere una pagina.');
            return;
        }

        // Adjust validation rules based on form state
        $rules = $this->rules;
        if ($this->useExistingShop) {
            unset($rules['company_name'], $rules['shop_type'], $rules['shop_identification_number'],
                  $rules['shop_email'], $rules['shop_phone_number'], $rules['shop_address'], $rules['shop_notes']);
            $rules['selectedShopId'] = 'required|exists:shops,id';
        } else {
            unset($rules['selectedShopId']);
        }

        if ($this->useExistingSeller) {
            unset($rules['seller_name'], $rules['seller_domain']);
            $rules['selectedSellerId'] = 'required|exists:sellers,id';
        } else {
            unset($rules['selectedSellerId']);
        }

        $this->validate($rules);

        try {
            \DB::transaction(function () use ($selectedTI) {
                // Extract domain from page URL
                $parsedUrl = parse_url($this->page_url);
                if (!$parsedUrl || !isset($parsedUrl['host'])) {
                    throw new \Exception('URL non valido: impossibile estrarre il dominio.');
                }

                $domainName = $parsedUrl['host'];
                // Remove www. prefix if present
                $domainName = preg_replace('/^www\./', '', $domainName);

                // Create or get the web domain
                $webDomain = WebDomain::firstOrCreate(['domain' => $domainName]);

                // Handle Shop
                if ($this->useExistingShop) {
                    $shop = Shop::findOrFail($this->selectedShopId);
                } else {
                    $shop = Shop::create([
                        'shop_type' => $this->shop_type,
                        'company_name' => $this->company_name,
                        'email' => $this->shop_email ?: null,
                        'phone_number' => $this->shop_phone_number ?: null,
                        'identification_number' => $this->shop_identification_number,
                        'address' => $this->shop_address ?: null,
                        'notes' => $this->shop_notes ?: null,
                    ]);
                }

                // Link the web domain to the shop (if not already linked)
                if (!$shop->webDomains()->where('web_domain_id', $webDomain->id)->exists()) {
                    $shop->webDomains()->attach($webDomain->id);
                }

                // Handle Seller (optional)
                $seller = null;
                if ($this->useExistingSeller && $this->selectedSellerId) {
                    $seller = Seller::findOrFail($this->selectedSellerId);
                } elseif (!$this->useExistingSeller && ($this->seller_name || $this->seller_domain)) {

                    // Handle seller domain
                    $sellerWebDomain = null;
                    if ($this->seller_domain) {
                        // Clean domain (remove http/https and www)
                        $cleanDomain = preg_replace('/^(https?:\/\/)?(www\.)?/', '', trim($this->seller_domain));
                        $sellerWebDomain = WebDomain::firstOrCreate(['domain' => $cleanDomain]);
                    }

                    $seller = Seller::create([
                        'found_on_domain_id' => $sellerWebDomain ? $sellerWebDomain->id : null,
                        'name' => $this->seller_name ?: null,
                        'is_certified' => false, // Default non certificato
                    ]);
                }

                // Create Page
                $page = Page::create([
                    'shop_id' => $shop->id,
                    'whitelist_class' => $this->whitelist_class,
                    'currently_sells' => $this->currently_sells,
                    'is_selling_page' => $this->is_selling_page,
                    'seller_id' => $seller ? $seller->id : null,
                    'page_url' => $this->page_url,
                    'notes' => $this->page_notes ?: null,
                ]);

                // Create a default search query string for manually added pages
                $queryString = $this->custom_search_query ?: 'Manual Entry';
                $searchQueryString = SearchQueryString::firstOrCreate([
                    'tracking_interest_id' => $selectedTI,
                    'query_string' => $queryString,
                    'language_code' => 'it',
                ], [
                    'search_intent' => $this->custom_search_query ? 'custom' : 'manual',
                    'source' => 'manual_entry',
                ]);

                // Create PageFound record to link the page to the selected tracking interest
                PageFound::create([
                    'page_id' => $page->id,
                    'tracking_interest_id' => $selectedTI,
                    'search_query_string_id' => $searchQueryString->id,
                    'search_platform' => $this->search_platform,
                    'serp_ads' => false,
                    'serp_position' => 0, // Position 0 indicates manual entry
                ]);

                // Create TicketInfo record if requested
                if ($this->add_ticket_info && $this->selling_price && $this->price_date) {
                    \App\Models\TicketInfo::create([
                        'page_id' => $page->id,
                        'price_at' => $this->price_date,
                        'currency' => $this->ticket_currency,
                        'ticket_type' => $this->ticket_type ?: null,
                        'selling_price' => $this->selling_price,
                        'description' => $this->ticket_description ?: null,
                    ]);
                }
            });

            $this->resetForm();
            $this->showForm = false;
            $this->dispatch('page-saved', ['message' => 'Pagina salvata con successo!']);

            // Show success message to user
            session()->flash('page-success', 'Pagina salvata con successo e collegata al dominio e interesse di tracciamento!');

        } catch (\Exception $e) {
            $this->addError('general', 'Errore durante il salvataggio: ' . $e->getMessage());
        }
    }
}; ?>

<div class="mb-6">
    <!-- Success Message -->
    @if(session()->has('page-success'))
        <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-md">
            <p class="text-green-600 dark:text-green-400 text-sm">{{ session('page-success') }}</p>
        </div>
    @endif

    <!-- Toggle Button -->
    <div class="mb-4">
        <flux:button
            wire:click="toggleForm"
            variant="primary"
            class="w-full md:w-auto"
        >
            @if($showForm)
                {{ __('Nascondi Form') }}
            @else
                {{ __('Aggiungi pagina manualmente') }}
            @endif
        </flux:button>
    </div>

    <!-- Form -->
    @if($showForm)
    <div class="bg-white dark:bg-zinc-900 shadow rounded-lg border border-neutral-200 dark:border-neutral-700">
        <div class="p-6">
            <h3 class="text-lg font-semibold mb-4 text-zinc-900 dark:text-zinc-100">
                {{ __('Aggiungi Nuova Pagina') }}
            </h3>

            <!-- Tracking Interest Info -->
            @php
                $currentTI = $this->getCurrentTrackingInterest();
            @endphp

            @if($currentTI)
                <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-md">
                    <p class="text-blue-700 dark:text-blue-300 text-sm">
                        <strong>{{ __('Interesse di tracciamento selezionato:') }}</strong> {{ $currentTI->interest }}
                    </p>
                    <p class="text-blue-600 dark:text-blue-400 text-xs mt-1">
                        {{ __('La pagina verrà automaticamente collegata a questo interesse di tracciamento.') }}
                    </p>
                    @if($custom_search_query || $search_platform !== 'Manual Entry')
                        <div class="mt-3 pt-2 border-t border-blue-200 dark:border-blue-700">
                            <p class="text-blue-600 dark:text-blue-400 text-xs">
                                <strong>{{ __('Query di ricerca:') }}</strong>
                                <span class="font-mono">{{ $this->getEffectiveSearchQuery() }}</span>
                            </p>
                            <p class="text-blue-600 dark:text-blue-400 text-xs">
                                <strong>{{ __('Piattaforma:') }}</strong> {{ $search_platform }}
                            </p>
                        </div>
                    @endif
                </div>
            @else
                <div class="mb-6 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-md">
                    <p class="text-yellow-700 dark:text-yellow-300 text-sm">
                        <strong>{{ __('Attenzione:') }}</strong> {{ __('Nessun interesse di tracciamento selezionato. Seleziona un interesse prima di procedere.') }}
                    </p>
                </div>
            @endif

            @if($errors->has('general'))
                <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md">
                    <p class="text-red-600 dark:text-red-400 text-sm">{{ $errors->first('general') }}</p>
                </div>
            @endif

            <form wire:submit="save" class="space-y-6" @if(!$currentTI) style="pointer-events: none; opacity: 0.6;" @endif>

                <!-- Page Information -->
                <div class="bg-gray-50 dark:bg-zinc-800 p-4 rounded-lg">
                    <h4 class="text-md font-medium mb-4 text-zinc-800 dark:text-zinc-200">
                        {{ __('Informazioni Pagina') }}
                    </h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <flux:input
                                wire:model="page_url"
                                :label="__('URL Pagina') . ' *'"
                                type="url"
                                required
                                :error="$errors->first('page_url')"
                                placeholder="https://esempio.com/pagina"
                            />
                        </div>

                        <div>
                            <flux:input
                                wire:model="whitelist_class"
                                :label="__('Classe Whitelist') . ' *'"
                                type="text"
                                required
                                :error="$errors->first('whitelist_class')"
                            />
                        </div>

                        <div class="space-y-3">
                            <flux:field variant="inline">
                                <flux:label>{{ __('Vende attualmente') }}</flux:label>
                                <flux:switch wire:model.live="currently_sells" />
                                <flux:error name="currently_sells" />
                            </flux:field>

                            <flux:field variant="inline">
                                <flux:label>{{ __('È una pagina di vendita') }}</flux:label>
                                <flux:switch wire:model.live="is_selling_page" />
                                <flux:error name="is_selling_page" />
                            </flux:field>
                        </div>

                        <div class="md:col-span-2">
                            <flux:textarea
                                wire:model="page_notes"
                                :label="__('Note Pagina')"
                                rows="3"
                                :error="$errors->first('page_notes')"
                            />
                        </div>

                        <div>
                            <flux:input
                                wire:model.live="custom_search_query"
                                :label="__('Query di Ricerca Personalizzata')"
                                type="text"
                                :error="$errors->first('custom_search_query')"
                                placeholder="es. biglietti eventi roma, hotel milano..."
                            />
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                {{ __('Lascia vuoto per usare "Manual Entry" come default') }}
                            </p>
                        </div>

                        <div>
                            <flux:select
                                wire:model.live="search_platform"
                                :label="__('Piattaforma di Ricerca') . ' *'"
                                required
                                :error="$errors->first('search_platform')"
                            >
                                <option value="Manual Entry">{{ __('Inserimento Manuale') }}</option>
                                <option value="Google">Google</option>
                                <option value="Bing">Bing</option>
                                <option value="Yahoo">Yahoo</option>
                                <option value="DuckDuckGo">DuckDuckGo</option>
                                <option value="Yandex">Yandex</option>
                                <option value="Baidu">Baidu</option>
                                <option value="Other">{{ __('Altro') }}</option>
                            </flux:select>
                        </div>
                    </div>
                </div>

                <!-- Shop Information -->
                <div class="bg-gray-50 dark:bg-zinc-800 p-4 rounded-lg">
                    <h4 class="text-md font-medium mb-4 text-zinc-800 dark:text-zinc-200">
                        {{ __('Informazioni Negozio') }}
                    </h4>

                    <!-- Toggle existing shop -->
                    <div class="mb-4">
                        <flux:field variant="inline">
                            <flux:label>{{ __('Usa negozio esistente') }}</flux:label>
                            <flux:switch wire:model.live="useExistingShop" />
                            <flux:error name="useExistingShop" />
                        </flux:field>
                    </div>

                    @if($useExistingShop)
                        <div>
                            <flux:select
                                wire:model="selectedShopId"
                                :label="__('Seleziona Negozio') . ' *'"
                                required
                                :error="$errors->first('selectedShopId')"
                            >
                                <option value="">{{ __('Seleziona un negozio...') }}</option>
                                @foreach($existingShops as $shop)
                                    <option value="{{ $shop['id'] }}">
                                        {{ $shop['company_name'] }} ({{ $shop['shop_type'] }})
                                    </option>
                                @endforeach
                            </flux:select>
                        </div>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <flux:input
                                    wire:model="shop_type"
                                    :label="__('Tipo Negozio') . ' *'"
                                    type="text"
                                    required
                                    :error="$errors->first('shop_type')"
                                    placeholder="es. OTA, Personal Website"
                                />
                            </div>

                            <div>
                                <flux:input
                                    wire:model="company_name"
                                    :label="__('Nome Azienda') . ' *'"
                                    type="text"
                                    required
                                    :error="$errors->first('company_name')"
                                />
                            </div>

                            <div>
                                <flux:input
                                    wire:model="shop_email"
                                    :label="__('Email Negozio')"
                                    type="email"
                                    :error="$errors->first('shop_email')"
                                />
                            </div>

                            <div>
                                <flux:input
                                    wire:model="shop_phone_number"
                                    :label="__('Telefono Negozio')"
                                    type="tel"
                                    :error="$errors->first('shop_phone_number')"
                                />
                            </div>

                            <div>
                                <flux:input
                                    wire:model="shop_identification_number"
                                    :label="__('Codice Identificazione') . ' *'"
                                    type="text"
                                    required
                                    :error="$errors->first('shop_identification_number')"
                                    placeholder="P.IVA, Codice Fiscale, ecc."
                                />
                            </div>

                            <div>
                                <flux:input
                                    wire:model="shop_address"
                                    :label="__('Indirizzo Negozio')"
                                    type="text"
                                    :error="$errors->first('shop_address')"
                                />
                            </div>

                            <div class="md:col-span-2">
                                <flux:textarea
                                    wire:model="shop_notes"
                                    :label="__('Note Negozio')"
                                    rows="2"
                                    :error="$errors->first('shop_notes')"
                                />
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Seller Information (Optional) -->
                <div class="bg-gray-50 dark:bg-zinc-800 p-4 rounded-lg">
                    <h4 class="text-md font-medium mb-4 text-zinc-800 dark:text-zinc-200">
                        {{ __('Informazioni Venditore') }} <span class="text-sm text-gray-500 dark:text-gray-400">({{ __('Opzionale') }})</span>
                    </h4>

                    <!-- Toggle existing seller -->
                    <div class="mb-4">
                        <flux:field variant="inline">
                            <flux:label>{{ __('Usa venditore esistente') }}</flux:label>
                            <flux:switch wire:model.live="useExistingSeller" />
                            <flux:error name="useExistingSeller" />
                        </flux:field>
                    </div>

                    @if($useExistingSeller)
                        <div class="mb-4">
                            <flux:select
                                wire:model.live="selectedSellerId"
                                :label="__('Seleziona Venditore')"
                                :error="$errors->first('selectedSellerId')"
                            >
                                <option value="">{{ __('Seleziona un venditore...') }}</option>
                                @foreach($existingSellers as $seller)
                                    <option value="{{ $seller['id'] }}">
                                        {{ $seller['name'] }}
                                    </option>
                                @endforeach
                            </flux:select>
                        </div>
                    @endif

                    @if(!$useExistingSeller)
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <flux:input
                                wire:model="seller_name"
                                :label="__('Nome Venditore')"
                                type="text"
                                :error="$errors->first('seller_name')"
                            />
                        </div>

                        <div>
                            <flux:input
                                wire:model="seller_domain"
                                :label="__('Dominio di Riferimento')"
                                type="text"
                                :error="$errors->first('seller_domain')"
                                placeholder="esempio.com"
                            />
                        </div>
                    </div>
                    @endif
                </div>

                <!-- Ticket Information (Optional) -->
                <div class="bg-gray-50 dark:bg-zinc-800 p-4 rounded-lg">
                    <h4 class="text-md font-medium mb-4 text-zinc-800 dark:text-zinc-200">
                        {{ __('Informazioni Biglietto/Prezzo') }} <span class="text-sm text-gray-500 dark:text-gray-400">({{ __('Opzionale') }})</span>
                    </h4>

                    <!-- Toggle ticket info -->
                    <div class="mb-4">
                        <flux:field variant="inline">
                            <flux:label>{{ __('Aggiungi informazioni prezzo') }}</flux:label>
                            <flux:switch wire:model.live="add_ticket_info" />
                            <flux:error name="add_ticket_info" />
                        </flux:field>
                    </div>

                    @if($add_ticket_info)
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <flux:input
                                    wire:model="selling_price"
                                    :label="__('Prezzo di Vendita') . ' *'"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    required
                                    :error="$errors->first('selling_price')"
                                    placeholder="0.00"
                                />
                            </div>

                            <div>
                                <flux:select
                                    wire:model="ticket_currency"
                                    :label="__('Valuta') . ' *'"
                                    required
                                    :error="$errors->first('ticket_currency')"
                                >
                                    <option value="EUR">EUR (€)</option>
                                    <option value="USD">USD ($)</option>
                                    <option value="GBP">GBP (£)</option>
                                    <option value="JPY">JPY (¥)</option>
                                    <option value="CHF">CHF</option>
                                    <option value="CAD">CAD</option>
                                    <option value="AUD">AUD</option>
                                </flux:select>
                            </div>

                            <div>
                                <flux:input
                                    wire:model="ticket_type"
                                    :label="__('Tipo Biglietto')"
                                    type="text"
                                    :error="$errors->first('ticket_type')"
                                    placeholder="es. Standard, VIP, Premium..."
                                />
                            </div>

                            <div>
                                <flux:input
                                    wire:model="price_date"
                                    :label="__('Data Prezzo') . ' *'"
                                    type="date"
                                    required
                                    :error="$errors->first('price_date')"
                                />
                            </div>

                            <div class="md:col-span-2">
                                <flux:textarea
                                    wire:model="ticket_description"
                                    :label="__('Descrizione')"
                                    rows="2"
                                    :error="$errors->first('ticket_description')"
                                    placeholder="Descrizione del biglietto/servizio..."
                                />
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Form Actions -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200 dark:border-gray-700">
                    <flux:button
                        type="button"
                        variant="filled"
                        wire:click="resetForm"
                        class="min-w-[100px]"
                        :disabled="!$currentTI"
                    >
                        {{ __('Annulla') }}
                    </flux:button>

                    <flux:button
                        type="submit"
                        variant="primary"
                        class="min-w-[100px]"
                        :disabled="!$currentTI"
                    >
                        {{ __('Salva') }}
                    </flux:button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>