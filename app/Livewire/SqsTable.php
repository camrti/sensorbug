<?php

namespace App\Livewire;

use App\Models\SearchQueryString;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;
use Livewire\Attributes\On;

final class SqsTable extends PowerGridComponent
{
    public string $tableName = 'sqs-table-ddpmlc-table';

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
        $ti = session('selected_tracking_interest');

        if ($ti) {
            // Fetch search query strings related to the selected tracking interest
            return SearchQueryString::query()
                ->where('tracking_interest_id', $ti)
                ->with(['trackingInterest', 'latestSearchVolume']);
        }

        return SearchQueryString::query()->where('id', '=', 0);
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('tracking_interest_id')
            ->add('search_intent')
            ->add('query_string')
            ->add('language_code')
            ->add('source')
            ->add('volume_info', function (SearchQueryString $model) {
                if ($model->latestSearchVolume) {
                    $volume = number_format($model->latestSearchVolume->volume);
                    $toDate = Carbon::parse($model->latestSearchVolume->to_date)->format('d/m/Y');
                    $fromDate = Carbon::parse($model->latestSearchVolume->from_date)->format('d/m/Y');
                    return "{$volume} ({$fromDate} - {$toDate})";
                }
                return 'N/D';
            })
            ->add('created_at');
    }

    public function columns(): array
    {
        return [
            Column::make('Id', 'id')
                ->sortable()
                ->searchable(),

            Column::make('Search intent', 'search_intent')
                ->sortable()
                ->searchable(),

            Column::make('Query string', 'query_string')
                ->sortable()
                ->searchable(),

            Column::make('Language code', 'language_code')
                ->sortable()
                ->searchable(),

            Column::make('Source', 'source')
                ->sortable()
                ->searchable(),

            Column::make('Volume (periodo)', 'volume_info'),

            Column::make('Created at', 'created_at')
                ->sortable()
                ->searchable(),

            // Column::action('Action')
        ];
    }

    public function filters(): array
    {
        return [
        ];
    }

    // #[\Livewire\Attributes\On('edit')]
    // public function edit($rowId): void
    // {
    //     $this->js('alert('.$rowId.')');
    // }

    // public function actions(SearchQueryString $row): array
    // {
    //     return [
    //         Button::add('edit')
    //             ->slot('Edit: '.$row->id)
    //             ->id()
    //             ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
    //             ->dispatch('edit', ['rowId' => $row->id])
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
