<?php

namespace App\Livewire;

use App\Models\Shop;
use App\Models\TrackingInterest;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use Livewire\Attributes\On;

final class ShopsTable extends PowerGridComponent
{
    public string $tableName = 'shops-table-vftbfm-table';

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
        $selectedTrackingInterest = session('selected_tracking_interest');

        if ($selectedTrackingInterest) {
            return Shop::query()
                ->whereHas('pages.trackingInterests', function ($query) use ($selectedTrackingInterest) {
                    $query->where('tracking_interests.id', $selectedTrackingInterest);
                })
                ->with(['webDomains']);
        }

        return Shop::query()->where('id', '=', 0);
    }

    public function relationSearch(): array
    {
        return [
            'webDomains' => [
                'domain'
            ]
        ];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('web_domains_list', function (Shop $model) {
                if ($model->webDomains->isEmpty()) {
                    return 'Nessun dominio';
                }

                $domains = $model->webDomains->pluck('domain')->toArray();

                // Se ci sono più di 2 domini, mostra i primi 2 e aggiungi "..."
                if (count($domains) > 2) {
                    $displayed = array_slice($domains, 0, 2);
                    return implode(', ', $displayed) . ' (+' . (count($domains) - 2) . ' altri)';
                }

                return implode(', ', $domains);
            })
            ->add('shop_type')
            ->add('is_reported_formatted', function (Shop $model) {
                return $model->is_reported
                    ? '🔴 Segnalato il ' . Carbon::parse($model->reported_at)->format('d/m/Y H:i')
                    : '🟢 Non segnalato';
            })
            ->add('company_name')
            ->add('email')
            ->add('phone_number')
            ->add('identification_number')
            ->add('address')
            ->add('notes')
            ->add('created_at')
            ->add('created_at_formatted', function (Shop $model) {
                return Carbon::parse($model->created_at)->format('d/m/Y H:i');
            });
    }

    public function columns(): array
    {
        return [
            Column::make('Id', 'id')
                ->sortable()
                ->searchable(),

            Column::make('Domini Web', 'web_domains_list')
                ->sortable()
                ->searchable(),

            Column::make('Tipologia', 'shop_type')
                ->sortable()
                ->searchable(),

            // Column::make('Segnalato', 'is_reported_formatted'),

            Column::make('Azienda', 'company_name')
                ->sortable()
                ->searchable(),

            Column::make('Email', 'email')
                ->sortable()
                ->searchable(),

            Column::make('Telefono', 'phone_number')
                ->sortable()
                ->searchable(),

            Column::make('Numero Identificativo', 'identification_number')
                ->sortable()
                ->searchable(),

            Column::make('Indirizzo', 'address')
                ->sortable()
                ->searchable(),

            Column::make('Note', 'notes')
                ->sortable()
                ->searchable(),

            Column::make('Creato il', 'created_at_formatted', 'created_at')
                ->sortable(),

            // Column::action('Azioni')
        ];
    }

    public function filters(): array
    {
        return [
        ];
    }


    // TODO send notification to admin (?)
    #[\Livewire\Attributes\On('reportShop')]
    public function reportShop($rowId): void
    {
        $shop = Shop::find($rowId);

        if ($shop) {
            $shop->report();
            $this->js('alert("Segnalazione della piattaforma: ' . $shop->id . ' effettuata con successo!");');
        } else {
            $this->js('alert("Piattaforma non trovata!");');
        }
    }

    // public function actions(Shop $row): array
    // {
    //     return [
    //         Button::add('reportShop')
    //             ->slot('Segnala')
    //             ->id()
    //             ->class('inline-flex items-center px-3 py-1.5 text-sm font-medium text-red-700 dark:text-red-300 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/30 focus:outline-none focus:ring-2 focus:ring-red-500 dark:focus:ring-red-400 focus:ring-offset-2 dark:focus:ring-offset-zinc-800 transition-colors duration-200 shadow-sm')
    //             ->dispatch('reportShop', ['rowId' => $row->id])
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

    #[On('tracking-interest-selected')]
    #[On('tracking-interest-cleared')]
    public function refreshTable()
    {
        $this->refresh();
    }
}