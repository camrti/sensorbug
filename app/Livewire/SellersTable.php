<?php

namespace App\Livewire;

use App\Models\Seller;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use Livewire\Attributes\On;

final class SellersTable extends PowerGridComponent
{
    public string $tableName = 'sellers-table-jnt1af-table';

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
        $selectedTrackingInterest = session('selected_tracking_interest_' . auth()->id());

        if ($selectedTrackingInterest) {
            return Seller::query()
                ->whereHas('pages.trackingInterests', function ($query) use ($selectedTrackingInterest) {
                    $query->where('tracking_interests.id', $selectedTrackingInterest);
                });
        }

        return Seller::query()->where('id', '=', 0);
    }

    public function relationSearch(): array
    {
        return [
            'foundOnDomain' => [
                'domain'
            ]
        ];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('name')
            ->add('is_certified_formatted', function (Seller $model) {
                return $model->is_certified ? 'Sì' : 'No';
            })
            ->add('found_on_domain', function (Seller $model) {
                return $model->foundOnDomain ? $model->foundOnDomain->domain : '';
            });
    }

    public function columns(): array
    {
        return [
            Column::make('Nome', 'name')
                ->sortable()
                ->searchable(),

            Column::make('Certificato', 'is_certified_formatted', 'is_certified')
                ->sortable(),

            Column::make('Trovato sul dominio', 'found_on_domain')
                ->sortable()
                ->searchable(),
        ];
    }

    public function filters(): array
    {
        return [
        ];
    }

    // send notification to admin (?)
    #[\Livewire\Attributes\On('reportSeller')]
    public function reportSeller($rowId): void
    {
        $seller = Seller::find($rowId);

        if ($seller) {
            $seller->report();
            $this->js('alert("Segnalazione della piattaforma: ' . $seller->id . ' effettuata con successo!");');
        } else {
            $this->js('alert("Piattaforma non trovata!");');
        }
    }

    // public function actions(Seller $row): array
    // {
    //     return [
    //         Button::add('reportSeller')
    //             ->slot('Segnala')
    //             ->id()
    //             ->class('inline-flex items-center px-3 py-1.5 text-sm font-medium text-red-700 dark:text-red-300 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/30 focus:outline-none focus:ring-2 focus:ring-red-500 dark:focus:ring-red-400 focus:ring-offset-2 dark:focus:ring-offset-zinc-800 transition-colors duration-200 shadow-sm')
    //             ->dispatch('reportSeller', ['rowId' => $row->id])
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