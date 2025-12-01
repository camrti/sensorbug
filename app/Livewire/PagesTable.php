<?php

namespace App\Livewire;

use App\Models\Page;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use Livewire\Attributes\On;

final class PagesTable extends PowerGridComponent
{
    public string $tableName = 'pages-table-dmfejj-table';


    public function setUp(): array
    {
        $this->showCheckBox();

        return [
            PowerGrid::header()
                ->showSearchInput(),
            PowerGrid::footer()
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        $selectedTI = session('selected_tracking_interest');

        if ($selectedTI) {
            return Page::query()
                ->select('pages.*')
                ->addSelect(DB::raw('(SELECT selling_price FROM ticket_info WHERE ticket_info.page_id = pages.id ORDER BY price_at DESC LIMIT 1) as latest_selling_price'))
                ->addSelect(DB::raw('(SELECT price_at FROM ticket_info WHERE ticket_info.page_id = pages.id ORDER BY price_at DESC LIMIT 1) as latest_price_date'))
                ->addSelect('web_domains.domain as single_domain')
                ->addSelect('shops.company_name')
                ->whereHas('trackingInterests', function ($query) use ($selectedTI) {
                    $query->where('tracking_interests.id', $selectedTI);
                })
                ->join('shops', 'shops.id', '=', 'pages.shop_id')
                ->leftJoin('shop_domain', 'shop_domain.shop_id', '=', 'shops.id')
                ->leftJoin('web_domains', 'web_domains.id', '=', 'shop_domain.web_domain_id');
        }

        return Page::query()->where('id', '=', 0);
    }

    public function relationSearch(): array
    {
        return [
            'shop' => [
                'company_name'
            ],
            'shop.webDomains' => [
                'domain'
            ]
        ];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('shop_id')
            ->add('shop_domain', function (Page $model) {
                return $model->single_domain ?? 'Nessun dominio';
            })
            ->add('company_name')
            ->add('shop_type', function (Page $model) {
                return $model->shop ? $model->shop->shop_type : '';
            })
            ->add('shop_type_db', function (Page $model) {
                return $model->shop ? $model->shop->shop_type : '';
            })
            ->add('whitelist_class')
            ->add('is_reported_formatted', function (Page $model) {
                return $model->is_reported
                    ? '🔴 Segnalato il ' . Carbon::parse($model->reported_at)->format('d/m/Y H:i')
                    : '🟢 Non segnalato';
            })
            ->add('currently_sells_formatted', function (Page $model) {
                if ($model->currently_sells) {
                    return '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-green-100 text-green-800 border border-green-200">Attivo</span>';
                } else {
                    return '<span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-semibold bg-red-100 text-red-800 border border-red-200">Non attivo</span>';
                }
            })
            ->add('latest_selling_price_formatted', function (Page $model) {
                if ($model->latest_selling_price) {
                    return '€ ' . number_format($model->latest_selling_price, 2);
                }
                return 'N/D';
            })
            ->add('latest_price_date', function (Page $model) {
                return $model->latest_price_date ?
                    Carbon::parse($model->latest_price_date)->format('d/m/Y') : 'N/D';
            })
            ->add('latest_price_date_formatted', function (Page $model) {
                return $model->latest_price_date ?
                    'Aggiornato il ' . Carbon::parse($model->latest_price_date)->format('d/m/Y H:i') : 'Mai aggiornato';
            })
            ->add('is_selling_page')
            ->add('seller_id')
            ->add('redirects_to_page_id')
            ->add('page_url_btn', function (Page $model) {
                return view('livewire.powergrid-table-link-btn', [
                    'url' => $model->page_url,
                    'btnText' => 'Vai alla pagina',
                ])->render();
            })
            ->add('ticket_name')
            ->add('notes')
            ->add('created_at_formatted', function (Page $model) {
                return $model->created_at->locale('it')->diffForHumans();
            });
    }

    public function columns(): array
    {
        return [
            // Column::make('Id', 'id')
            //     ->sortable()
            //     ->searchable(),

            Column::make('Titolo biglietto', 'ticket_name')
                ->sortable()
                ->searchable(),

            Column::make('Dominio', 'shop_domain')
                ->searchable(),

            Column::make('Tipo Piattaforma', 'shop_type'),

            Column::make('Whitelist class', 'whitelist_class')
                ->sortable()
                ->searchable(),

            // Column::make('Segnalato', 'is_reported_formatted'),

            Column::make('Stato', 'currently_sells_formatted'),

            Column::make('Ultimo prezzo', 'latest_selling_price_formatted')
                ->sortable(),

            Column::make('Data ultimo prezzo', 'latest_price_date')
                ->sortable(),

            // Column::make('Seller id', 'seller_id')
            //     ->sortable()
            //     ->searchable(),

            // Column::make('Redirects to page id', 'redirects_to_page_id')
            //     ->sortable()
            //     ->searchable(),

            Column::make('URL', 'page_url_btn'),

            Column::make('Note', 'notes')
                ->sortable()
                ->searchable(),

            Column::make('Aggiunto il', 'created_at_formatted', 'created_at')
                ->sortable()
                ->searchable(),

            // Column::action('Action')
        ];
    }

    public function filters(): array
    {
        $shopTypes = \App\Models\Shop::distinct()
            ->whereNotNull('shop_type')
            ->pluck('shop_type')
            ->filter()
            ->sort()
            ->values()
            ->map(function ($type) {
                return ['shop_type' => $type, 'label' => $type];
            })
            ->toArray();

        $companyNames = \App\Models\Shop::distinct()
            ->whereNotNull('company_name')
            ->pluck('company_name')
            ->filter()
            ->sort()
            ->values()
            ->map(function ($name) {
                return ['company_name' => $name, 'label' => $name];
            })
            ->toArray();

        return [
            Filter::select('shop_type', 'shops.shop_type')
                ->dataSource($shopTypes)
                ->optionValue('shop_type')
                ->optionLabel('label'),

            Filter::select('company_name', 'shops.company_name')
                ->dataSource($companyNames)
                ->optionValue('company_name')
                ->optionLabel('label')
        ];
    }

    // TODO send notification to admin (?)
    #[\Livewire\Attributes\On('reportPage')]
    public function reportPage($rowId)
    {
        $page = Page::find($rowId);

        if ($page) {
            $page->report();
            $this->js('alert("Segnalazione della pagina: ' . $page->id . ' effettuata con successo!");');
        } else {
            $this->js('alert("Pagina non trovata!");');
        }
    }

    //  public function actions(Page $row): array
    // {
    //     return [
    //         Button::add('reportPage')
    //             ->slot('Segnala')
    //             ->id()
    //             // ->class('inline-flex items-center px-3 py-1.5 text-sm font-medium text-zinc-700 dark:text-zinc-300 bg-white dark:bg-zinc-800 border border-zinc-300 dark:border-zinc-600 rounded-lg hover:bg-zinc-50 dark:hover:bg-zinc-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-400 focus:ring-offset-2 dark:focus:ring-offset-zinc-800 transition-colors duration-200 shadow-sm')
    //             ->class('inline-flex items-center px-3 py-1.5 text-sm font-medium text-red-700 dark:text-red-300 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/30 focus:outline-none focus:ring-2 focus:ring-red-500 dark:focus:ring-red-400 focus:ring-offset-2 dark:focus:ring-offset-zinc-800 transition-colors duration-200 shadow-sm')
    //             ->dispatch('reportPage', ['rowId' => $row->id])
    //     ];
    // }

    /*
    public function actionRules($row): array
    {
       return [
            // Hide button edit for ID 1
            Rule::button('edit')
                ->when(fn($row) => $row->id === 1)
                ->hide(),
        ];
    }
    */

    #[On('page-saved')]
    #[On('tracking-interest-selected')]
    #[On('tracking-interest-cleared')]
    public function refreshTable()
    {
        $this->refresh();
    }
}