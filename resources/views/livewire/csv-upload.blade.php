<?php

use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\{Shop, WebDomain, Page, Seller, TicketInfo, TrackingInterest, PageFound, User, SearchQueryString};

new class extends Component {
    use WithFileUploads;

    public $csvFile;
    public $selectedTrackingInterest = '';
    public $isUploading = false;
    public $uploadResults = [];
    public $showResults = false;
    public $trackingInterests = [];

    protected $rules = [
        'csvFile' => 'required|file|mimes:csv,txt|max:10240', // 10MB max
        'selectedTrackingInterest' => 'required|exists:tracking_interests,id',
    ];

    protected $messages = [
        'csvFile.required' => 'Seleziona un file CSV da caricare.',
        'csvFile.mimes' => 'Il file deve essere in formato CSV.',
        'csvFile.max' => 'Il file non può superare i 10MB.',
        'selectedTrackingInterest.required' => 'Seleziona un interesse di tracciamento.',
        'selectedTrackingInterest.exists' => 'L\'interesse di tracciamento selezionato non è valido.',
    ];

    public function mount()
    {
        $this->selectedTrackingInterest = '';
        $this->trackingInterests = TrackingInterest::where('is_active', true)->get();
    }

    public function updatedSelectedTrackingInterest()
    {
        // Reset validation errors when tracking interest changes
        $this->resetErrorBag('selectedTrackingInterest');
    }

    public function updatedCsvFile()
    {
        // Reset validation errors when file changes
        $this->resetErrorBag('csvFile');
    }

    public function uploadCsv()
    {
        $this->validate();

        $this->isUploading = true;
        $this->uploadResults = [
            'success' => 0,
            'errors' => 0,
            'skipped' => 0,
            'details' => [],
            'skipped_details' => []
        ];

        try {
            $path = $this->csvFile->getRealPath();
            $csv = array_map('str_getcsv', file($path));
            $header = array_shift($csv); // Rimuovi l'header

            // Mappa le colonne del CSV
            $columns = [
                'page_whitelist_class' => 0,
                'page_url' => 1,
                'ticket_type' => 2,
                'search_platform' => 3,
                'selling_price' => 4,
                'seller' => 5,
                'date' => 6,
                'cleaned_domain' => 7,
                'domain' => 8,
                'shop_type' => 9,
                'company_name' => 10,
                'identification_code' => 11,
                'phone_number' => 12,
                'email' => 13,
                'address' => 14,
                'ads' => 15,
                'ticket_page_description' => 16,
                'anagraphic_description' => 17,
                'domain_country' => 18,
                'serp_ads' => 19
            ];

            DB::beginTransaction();

            // Trova il TrackingInterest selezionato
            $trackingInterest = TrackingInterest::findOrFail($this->selectedTrackingInterest);

            // Crea o trova una SearchQueryString di default per l'import CSV
            $csvSearchQuery = \App\Models\SearchQueryString::firstOrCreate([
                'tracking_interest_id' => $trackingInterest->id,
                'query_string' => 'CSV_IMPORT_' . now()->format('Y_m_d_H_i')
            ], [
                'search_intent' => 'CSV Import',
                'language_code' => 'it',
                'source' => 'csv_upload'
            ]);

            // Cache per evitare query duplicate durante l'import
            $domainCache = [];
            $shopCache = [];
            $pageCache = [];

            foreach ($csv as $rowIndex => $row) {
                try {
                    if (empty(array_filter($row))) {
                        $this->uploadResults['skipped']++;
                        $this->uploadResults['skipped_details'][] = "Riga " . ($rowIndex + 2) . ": Riga completamente vuota";
                        continue; // Salta righe vuote
                    }

                    // Estrai i dati dalla riga
                    $pageUrl = trim($row[$columns['page_url']] ?? '');
                    // this is the full domain, with subdomain if present (subdomain "www" is included)
                    $domain = trim($row[$columns['domain']] ?? '');
                    // this is the cleaned domain, without subdomain (e.g. "example.com" instead of "www.example.com")
                    $cleanedDomain = trim($row[$columns['cleaned_domain']] ?? '');
                    $shopType = trim($row[$columns['shop_type']] ?? '');
                    $companyName = trim($row[$columns['company_name']] ?? '');
                    $identificationCode = trim($row[$columns['identification_code']] ?? '');
                    $sellingPrice = $this->parsePrice($row[$columns['selling_price']] ?? '');
                    $ticketType = trim($row[$columns['ticket_type']] ?? '');
                    $priceDate = trim($row[$columns['date']] ?? '');

                    if (empty($pageUrl) || empty($domain)) {
                        $this->uploadResults['skipped']++;
                        $this->uploadResults['skipped_details'][] = "Riga " . ($rowIndex + 2) . ": URL pagina o dominio mancante (URL: '" . $pageUrl . "', Dominio: '" . $domain . "')";
                        continue;
                    }

                    // 1. Find the web domain or create a new one (using cache)
                    $domainKey = $cleanedDomain ?: $domain;
                    if (isset($domainCache[$domainKey])) {
                        $webDomain = $domainCache[$domainKey];
                    } else {
                        $webDomain = WebDomain::firstOrCreate([
                            'domain' => $domainKey
                        ], [
                            'country' => trim($row[$columns['domain_country']] ?? '')
                        ]);
                        $domainCache[$domainKey] = $webDomain;
                    }

                    // 2. Find existing shop using multiple strategies with caching
                    $shop = null;
                    $shopKey = null;
                    $foundByCompany = false;

                    // Strategy 1: If we have a company name, try to find an existing shop with that name
                    if (!empty($companyName)) {
                        $shopKey = 'company_' . $companyName;
                        if (isset($shopCache[$shopKey])) {
                            $shop = $shopCache[$shopKey];
                        } else {
                            $shop = Shop::where('company_name', $companyName)->first();
                            if ($shop) {
                                $shopCache[$shopKey] = $shop;
                            }
                        }
                        $foundByCompany = ($shop !== null);
                    }

                    // Strategy 2: If no shop found by company name, try to find by domain
                    if (!$shop) {
                        $domainShopKey = 'domain_' . $webDomain->id;
                        if (isset($shopCache[$domainShopKey])) {
                            $shop = $shopCache[$domainShopKey];
                        } else {
                            $shop = Shop::whereHas('webDomains', function ($query) use ($webDomain) {
                                $query->where('web_domain_id', $webDomain->id);
                            })->first();
                            if ($shop) {
                                $shopCache[$domainShopKey] = $shop;
                            }
                        }
                    }

                    // Critical: If we found a shop (by any method), ensure this domain is associated
                    if ($shop) {
                        // Check if domain association already exists (using cache for performance)
                        $domainAssocKey = 'shop_' . $shop->id . '_domain_' . $webDomain->id;
                        $isDomainAssociated = false;

                        if (isset($shopCache[$domainAssocKey])) {
                            $isDomainAssociated = $shopCache[$domainAssocKey];
                        } else {
                            $isDomainAssociated = $shop->webDomains()->where('web_domain_id', $webDomain->id)->exists();
                            $shopCache[$domainAssocKey] = $isDomainAssociated;
                        }

                        // If domain not associated, add it
                        if (!$isDomainAssociated) {
                            $shop->webDomains()->attach($webDomain->id);
                            // Update cache to reflect new association
                            $shopCache[$domainAssocKey] = true;
                            $shopCache['domain_' . $webDomain->id] = $shop;

                            Log::info("Associated domain {$webDomain->domain} with existing shop", [
                                'shop_id' => $shop->id,
                                'company_name' => $shop->company_name,
                                'domain_id' => $webDomain->id,
                                'found_by' => $foundByCompany ? 'company_name' : 'domain',
                                'row' => $rowIndex + 2
                            ]);
                        }
                    }

                    // Strategy 3: If still no shop found, create a new one
                    if (!$shop) {
                        try {
                            $shop = Shop::create([
                                'shop_type' => $shopType ?: 'OTA',
                                'company_name' => $companyName ?: '',
                                'email' => trim($row[$columns['email']] ?? ''),
                                'phone_number' => trim($row[$columns['phone_number']] ?? ''),
                                'identification_number' => $identificationCode ?: '',
                                'address' => trim($row[$columns['address']] ?? ''),
                            ]);

                            // Associate the domain to the new shop
                            $shop->webDomains()->attach($webDomain->id);

                            // Update cache with new shop and association
                            if (!empty($companyName)) {
                                $shopCache['company_' . $companyName] = $shop;
                            }
                            $shopCache['domain_' . $webDomain->id] = $shop;
                            $shopCache['shop_' . $shop->id . '_domain_' . $webDomain->id] = true;

                        } catch (\Illuminate\Database\QueryException $e) {
                            // If creation fails due to unique constraint, try to find existing shop again
                            if (str_contains($e->getMessage(), 'company_name')) {
                                $shop = Shop::where('company_name', $companyName)->first();
                                if ($shop) {
                                    // Update cache with found shop
                                    $shopCache['company_' . $companyName] = $shop;

                                    // Check if domain association exists
                                    $domainAssocExists = $shop->webDomains()->where('web_domain_id', $webDomain->id)->exists();
                                    if (!$domainAssocExists) {
                                        $shop->webDomains()->attach($webDomain->id);
                                        Log::info("Associated domain {$webDomain->domain} with recovered shop after constraint error", [
                                            'shop_id' => $shop->id,
                                            'company_name' => $shop->company_name,
                                            'domain_id' => $webDomain->id,
                                            'row' => $rowIndex + 2
                                        ]);
                                    }

                                    // Update caches
                                    $shopCache['domain_' . $webDomain->id] = $shop;
                                    $shopCache['shop_' . $shop->id . '_domain_' . $webDomain->id] = true;
                                }
                            }
                            if (!$shop) {
                                throw $e; // Re-throw if we couldn't resolve the issue
                            }
                        }
                    } else {
                        // If the shop exists, update the information if not already present
                        $updateData = [];
                        if (empty($shop->company_name) && !empty($companyName)) {
                            $updateData['company_name'] = $companyName;
                        }
                        if (empty($shop->email) && !empty(trim($row[$columns['email']] ?? ''))) {
                            $updateData['email'] = trim($row[$columns['email']] ?? '');
                        }
                        if (empty($shop->phone_number) && !empty(trim($row[$columns['phone_number']] ?? ''))) {
                            $updateData['phone_number'] = trim($row[$columns['phone_number']] ?? '');
                        }
                        if (empty($shop->address) && !empty(trim($row[$columns['address']] ?? ''))) {
                            $updateData['address'] = trim($row[$columns['address']] ?? '');
                        }
                        if (empty($shop->identification_number) && !empty($identificationCode)) {
                            $updateData['identification_number'] = $identificationCode;
                        }

                        if (!empty($updateData)) {
                            try {
                                $shop->update($updateData);
                            } catch (\Illuminate\Database\QueryException $e) {
                                // If update fails due to unique constraint on company_name, skip the update
                                if (!str_contains($e->getMessage(), 'company_name')) {
                                    throw $e; // Re-throw if it's not a company_name issue
                                }
                                // Log the issue but continue processing
                                Log::warning("Could not update shop company_name due to unique constraint", [
                                    'shop_id' => $shop->id,
                                    'attempted_company_name' => $companyName,
                                    'row' => $rowIndex + 2
                                ]);
                            }
                        }
                    }

                    // 3. Find or create the Seller if specified
                    $seller = null;
                    $sellerName = trim($row[$columns['seller']] ?? '');
                    if (!empty($sellerName)) {
                        $seller = Seller::firstOrCreate([
                            'name' => $sellerName,
                            'found_on_domain_id' => $webDomain->id
                        ], [
                            'is_certified' => false, // Default non certificato
                        ]);
                    }

                    // 4. Find or create the page (using cache)
                    if (isset($pageCache[$pageUrl])) {
                        $page = $pageCache[$pageUrl];
                    } else {
                        $page = Page::firstOrCreate([
                            'page_url' => $pageUrl
                        ], [
                            'shop_id' => $shop->id,
                            'whitelist_class' => trim($row[$columns['page_whitelist_class']] ?? 'Non Certificato'),
                            'currently_sells' => !empty($sellingPrice),
                            'is_selling_page' => !empty($sellingPrice),
                            'seller_id' => $seller?->id,
                            'notes' => trim($row[$columns['ticket_page_description']] ?? '')
                        ]);
                        $pageCache[$pageUrl] = $page;
                    }

                    // 5. If a ticker price and a ticket type are specified, create a TicketInfo
                    if (!empty($sellingPrice) && !empty($ticketType)) {
                        TicketInfo::firstOrCreate([
                            'page_id' => $page->id,
                            'price_at' => $priceDate ?: '',
                            'ticket_type' => $ticketType
                        ], [
                            'currency' => 'EUR',
                            'selling_price' => $sellingPrice,
                            'description' => trim($row[$columns['ticket_page_description']] ?? '')
                        ]);
                    }

                    // 6. Create a PageFound record to link the page to the selected tracking interest
                    PageFound::firstOrCreate([
                        'page_id' => $page->id,
                        'tracking_interest_id' => $trackingInterest->id
                    ], [
                        'search_query_string_id' => $csvSearchQuery->id,
                        'search_platform' => trim($row[$columns['search_platform']] ?? 'CSV Import'),
                        'serp_ads' => trim($row[$columns['serp_ads']] ?? '') === 'SI',
                        'serp_position' => 1
                    ]);

                    $this->uploadResults['success']++;

                    // Log successful processing for debugging
                    Log::info("CSV Import Success on row " . ($rowIndex + 2), [
                        'shop_id' => $shop->id,
                        'company_name' => $shop->company_name,
                        'domain' => $domain,
                        'page_url' => $pageUrl
                    ]);

                } catch (\Exception $e) {
                    $this->uploadResults['errors']++;
                    $errorMessage = $e->getMessage();

                    // Provide more user-friendly error messages
                    if (str_contains($errorMessage, 'company_name')) {
                        $errorMessage = "Errore di duplicazione nome azienda: '{$companyName}'. Verifica che il nome sia univoco nel database.";
                    } elseif (str_contains($errorMessage, 'Duplicate entry')) {
                        $errorMessage = "Errore di duplicazione dati. Alcuni record potrebbero già esistere nel database.";
                    }

                    $this->uploadResults['details'][] = "Riga " . ($rowIndex + 2) . ": " . $errorMessage;

                    Log::error("CSV Import Error on row " . ($rowIndex + 2), [
                        'error' => $e->getMessage(),
                        'company_name' => $companyName,
                        'domain' => $domain,
                        'page_url' => $pageUrl,
                        'row_data' => [
                            'shop_type' => $shopType,
                            'identification_code' => $identificationCode,
                            'selling_price' => $sellingPrice
                        ]
                    ]);
                }
            }

            DB::commit();

            // Add summary information about domain-shop associations
            $this->uploadResults['summary'] = [
                'unique_domains_processed' => count($domainCache),
                'unique_shops_involved' => count(array_unique(array_filter($shopCache, function($item) {
                    return $item instanceof \App\Models\Shop;
                }))),
                'tracking_interest' => $trackingInterest->interest
            ];

            $this->showResults = true;

        } catch (\Exception $e) {
            DB::rollback();
            $this->uploadResults['details'][] = "Errore generale: " . $e->getMessage();
            Log::error("CSV Import General Error", ['error' => $e->getMessage()]);
        }

        $this->isUploading = false;
        $this->csvFile = null;
    }

    private function parsePrice($priceString)
    {
        if (empty($priceString)) {
            return null;
        }

        // Rimuovi simboli e spazi, mantieni solo numeri, virgola e punto
        $cleaned = preg_replace('/[^\d,.]/', '', $priceString);

        // Gestisci formati europei (1.234,56) e anglosassoni (1,234.56)
        if (strpos($cleaned, ',') !== false && strpos($cleaned, '.') !== false) {
            // Se ci sono entrambi, il punto è il separatore delle migliaia se viene prima
            if (strpos($cleaned, '.') < strpos($cleaned, ',')) {
                // Formato europeo: 1.234,56
                $cleaned = str_replace('.', '', $cleaned);
                $cleaned = str_replace(',', '.', $cleaned);
            } else {
                // Formato anglosassone: 1,234.56
                $cleaned = str_replace(',', '', $cleaned);
            }
        } elseif (strpos($cleaned, ',') !== false) {
            // Solo virgola: potrebbe essere decimale europeo
            $cleaned = str_replace(',', '.', $cleaned);
        }

        return (float) $cleaned;
    }

    public function resetUpload()
    {
        $this->showResults = false;
        $this->uploadResults = [];
        $this->csvFile = null;
        $this->selectedTrackingInterest = '';
        $this->resetErrorBag();
    }
}; ?>

<div class="space-y-6">
    <!-- Form di caricamento -->
    <div class="bg-white dark:bg-zinc-900 shadow rounded-lg p-6">
        <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-4">
            {{ __('Carica File CSV') }}
        </h2>

        <form wire:submit="uploadCsv" class="space-y-4">
            <!-- Selezione Tracking Interest -->
            <div>
                <flux:field>
                    <flux:label>{{ __('Interesse di Tracciamento') }}</flux:label>
                    <flux:select wire:model.live="selectedTrackingInterest" placeholder="Seleziona un interesse di tracciamento">
                        @forelse ($trackingInterests as $ti)
                            <option value="{{ $ti->id }}">{{ $ti->interest }}</option>
                        @empty
                            <option disabled>{{ __('Nessun interesse attivo disponibile') }}</option>
                        @endforelse
                    </flux:select>
                    <flux:error name="selectedTrackingInterest" />
                </flux:field>

                @if ($trackingInterests->isEmpty())
                    <div class="mt-2 p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-md">
                        <div class="text-sm text-yellow-800 dark:text-yellow-200">
                            {{ __('Non hai ancora creato nessun interesse di tracciamento attivo. ') }}
                            <a href="{{ route('dashboard') }}" class="font-medium underline hover:no-underline" wire:navigate>
                                {{ __('Vai alla dashboard per crearne uno.') }}
                            </a>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Selezione File -->
            <div>
                <flux:field>
                    <flux:label>{{ __('Seleziona file CSV') }}</flux:label>
                    <flux:input type="file" wire:model.live="csvFile" accept=".csv,.txt" />
                    <flux:error name="csvFile" />
                </flux:field>

                @if ($csvFile)
                    <div class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
                        {{ __('File selezionato: :name (:size)', [
                            'name' => $csvFile->getClientOriginalName(),
                            'size' => number_format($csvFile->getSize() / 1024, 2) . ' KB'
                        ]) }}
                    </div>
                @endif
            </div>

            <div class="flex items-center gap-4">
                <flux:button
                    type="submit"
                    variant="primary"
                    :disabled="!$csvFile || !$selectedTrackingInterest || $isUploading || $trackingInterests->isEmpty()"
                    class="w-full sm:w-auto"
                >
                    @if ($isUploading)
                        <flux:icon.arrow-path class="animate-spin size-4 mr-2" />
                        {{ __('Caricamento in corso...') }}
                    @else
                        <flux:icon.arrow-up-tray class="size-4 mr-2" />
                        {{ __('Carica CSV') }}
                    @endif
                </flux:button>

                @if ($showResults)
                    <flux:button
                        type="button"
                        variant="filled"
                        wire:click="resetUpload"
                    >
                        {{ __('Carica altro file') }}
                    </flux:button>
                @endif
            </div>
        </form>
    </div>

    <!-- Pannello Debug Admin -->
    <div class="bg-gray-50 dark:bg-gray-800 border-2 border-dashed border-gray-300 dark:border-gray-600 rounded-lg p-4">
        <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">
            🔧 Debug Panel (Admin)
        </h3>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-xs">
            <div class="bg-white dark:bg-gray-700 p-2 rounded border">
                <div class="font-medium text-gray-600 dark:text-gray-400">Selected TI:</div>
                <div class="text-blue-600 dark:text-blue-400">{{ $selectedTrackingInterest ?: 'NULL' }}</div>
            </div>
            <div class="bg-white dark:bg-gray-700 p-2 rounded border">
                <div class="font-medium text-gray-600 dark:text-gray-400">CSV File:</div>
                <div class="text-green-600 dark:text-green-400">{{ $csvFile ? 'Selected' : 'NULL' }}</div>
            </div>
            <div class="bg-white dark:bg-gray-700 p-2 rounded border">
                <div class="font-medium text-gray-600 dark:text-gray-400">TI Count:</div>
                <div class="text-purple-600 dark:text-purple-400">{{ $trackingInterests->count() }}</div>
            </div>
            <div class="bg-white dark:bg-gray-700 p-2 rounded border">
                <div class="font-medium text-gray-600 dark:text-gray-400">Button Disabled:</div>
                <div class="text-red-600 dark:text-red-400">
                    {{ (!$csvFile || !$selectedTrackingInterest || $isUploading || $trackingInterests->isEmpty()) ? 'YES' : 'NO' }}
                </div>
            </div>
        </div>
        @if ($showResults)
            <div class="mt-3 grid grid-cols-3 gap-3 text-xs">
                <div class="bg-green-100 dark:bg-green-900/30 p-2 rounded border">
                    <div class="font-medium text-green-700 dark:text-green-400">Elaborati:</div>
                    <div class="text-green-800 dark:text-green-300 font-bold">{{ $uploadResults['success'] ?? 0 }}</div>
                </div>
                <div class="bg-yellow-100 dark:bg-yellow-900/30 p-2 rounded border">
                    <div class="font-medium text-yellow-700 dark:text-yellow-400">Saltati:</div>
                    <div class="text-yellow-800 dark:text-yellow-300 font-bold">{{ $uploadResults['skipped'] ?? 0 }}</div>
                </div>
                <div class="bg-red-100 dark:bg-red-900/30 p-2 rounded border">
                    <div class="font-medium text-red-700 dark:text-red-400">Errori:</div>
                    <div class="text-red-800 dark:text-red-300 font-bold">{{ $uploadResults['errors'] ?? 0 }}</div>
                </div>
            </div>
        @endif
        @if ($csvFile)
            <div class="mt-2 bg-white dark:bg-gray-700 p-2 rounded border">
                <div class="font-medium text-gray-600 dark:text-gray-400 text-xs">File Info:</div>
                <div class="text-xs text-gray-800 dark:text-gray-200">
                    {{ $csvFile->getClientOriginalName() }} ({{ number_format($csvFile->getSize() / 1024, 2) }} KB)
                </div>
            </div>
        @endif
    </div>

    <!-- Risultati dell'upload -->
    @if ($showResults)
        <div class="bg-white dark:bg-zinc-900 shadow rounded-lg p-6">
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100 mb-4">
                {{ __('Risultati Caricamento') }}
            </h2>

            <div class="mb-4 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                <div class="text-blue-800 dark:text-blue-200 text-sm">
                    <strong>{{ __('Interesse di tracciamento:') }}</strong>
                    {{ $trackingInterests->find($selectedTrackingInterest)?->interest }}
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-lg">
                    <div class="text-green-800 dark:text-green-300 text-2xl font-bold">
                        {{ $uploadResults['success'] }}
                    </div>
                    <div class="text-green-600 dark:text-green-400 text-sm">
                        {{ __('Record elaborati con successo') }}
                    </div>
                </div>

                <div class="bg-yellow-50 dark:bg-yellow-900/20 p-4 rounded-lg">
                    <div class="text-yellow-800 dark:text-yellow-300 text-2xl font-bold">
                        {{ $uploadResults['skipped'] }}
                    </div>
                    <div class="text-yellow-600 dark:text-yellow-400 text-sm">
                        {{ __('Record saltati') }}
                    </div>
                </div>

                <div class="bg-red-50 dark:bg-red-900/20 p-4 rounded-lg">
                    <div class="text-red-800 dark:text-red-300 text-2xl font-bold">
                        {{ $uploadResults['errors'] }}
                    </div>
                    <div class="text-red-600 dark:text-red-400 text-sm">
                        {{ __('Errori') }}
                    </div>
                </div>
            </div>

            <!-- Riepilogo associazioni domini-shop -->
            @if (!empty($uploadResults['summary']))
                <div class="mb-6 p-4 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg">
                    <h3 class="font-medium text-indigo-900 dark:text-indigo-100 mb-2">
                        {{ __('Riepilogo Elaborazione:') }}
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm">
                        <div class="text-indigo-700 dark:text-indigo-300">
                            <strong>{{ __('Domini unici processati:') }}</strong>
                            {{ $uploadResults['summary']['unique_domains_processed'] }}
                        </div>
                        <div class="text-indigo-700 dark:text-indigo-300">
                            <strong>{{ __('Shop coinvolti:') }}</strong>
                            {{ $uploadResults['summary']['unique_shops_involved'] }}
                        </div>
                        <div class="text-indigo-700 dark:text-indigo-300">
                            <strong>{{ __('Tracking Interest:') }}</strong>
                            {{ $uploadResults['summary']['tracking_interest'] }}
                        </div>
                    </div>
                </div>
            @endif

            @if (!empty($uploadResults['details']))
                <div class="space-y-2">
                    <h3 class="font-medium text-zinc-900 dark:text-zinc-100">
                        {{ __('Dettagli degli errori:') }}
                    </h3>
                    <div class="bg-red-50 dark:bg-red-900/20 p-4 rounded-lg max-h-64 overflow-y-auto">
                        @foreach ($uploadResults['details'] as $detail)
                            <div class="text-sm text-red-700 dark:text-red-300 mb-1">
                                {{ $detail }}
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if (!empty($uploadResults['skipped_details']))
                <div class="space-y-2">
                    <h3 class="font-medium text-zinc-900 dark:text-zinc-100">
                        🔧 Dettagli righe saltate (Debug Admin):
                    </h3>
                    <div class="bg-yellow-50 dark:bg-yellow-900/20 p-4 rounded-lg max-h-64 overflow-y-auto">
                        @foreach ($uploadResults['skipped_details'] as $detail)
                            <div class="text-sm text-yellow-700 dark:text-yellow-300 mb-1">
                                {{ $detail }}
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    @endif

    <!-- Informazioni sul formato CSV -->
    <div class="bg-blue-50 dark:bg-blue-900/20 p-6 rounded-lg">
        <h3 class="text-lg font-medium text-blue-900 dark:text-blue-100 mb-3">
            {{ __('Formato CSV Supportato') }}
        </h3>
        <div class="text-blue-800 dark:text-blue-200 text-sm space-y-2">
            <p>{{ __('Il file CSV deve contenere le seguenti colonne nell\'ordine specificato:') }}</p>
            <ul class="list-disc list-inside space-y-1 ml-4">
                <li>page_whitelist_class</li>
                <li>page_url</li>
                <li>ticket_type</li>
                <li>search_platform</li>
                <li>selling_price</li>
                <li>seller</li>
                <li>date</li>
                <li>cleaned_domain</li>
                <li>domain</li>
                <li>shop_type</li>
                <li>company_name</li>
                <li>identification_code</li>
                <li>phone_number</li>
                <li>email</li>
                <li>address</li>
                <li>ads</li>
                <li>ticket_page_description</li>
                <li>anagraphic_description</li>
                <li>domain_country</li>
                <li>serp_ads</li>
            </ul>
            <p class="mt-3">{{ __('I campi obbligatori sono: page_url e domain. Gli altri campi possono essere vuoti.') }}</p>
            <p class="mt-2 text-blue-700 dark:text-blue-300 font-medium">
                {{ __('Tutti i dati importati verranno collegati all\'interesse di tracciamento selezionato.') }}
            </p>
        </div>
    </div>
</div>