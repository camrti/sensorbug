<?php

namespace App\Livewire;

use App\Models\TrackingInterest;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;

final class TrackingInterestTable extends PowerGridComponent
{
    public string $tableName = 'tracking-interest-table-tinyno-table';

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
        return TrackingInterest::query()
            ->whereHas('users', function($query) {
                $query->where('users.id', auth()->id());
            })
            ->with(['users' => function($query) {
                $query->withPivot(['assigned_by_user_id']);
            }]);
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('interest')
            ->add('is_active_formatted', function (TrackingInterest $model) {
                return $model->is_active
                    ? '✅ Attivo'
                    : '❌ Non attivo';
            })
            ->add('created_at')
            ->add('users_list', function (TrackingInterest $model) {
                $badges = $model->users->map(function ($user) {
                    return view('livewire.tracking-interests.ti-table.user-field-badge',
                    [
                        'user' => $user
                    ])->render();
                });
                return '<div class="flex flex-wrap gap-2">' . $badges->join('') . '</div>';
            })
            ->add('owner_users', function (TrackingInterest $model) {
                $badges = $model->users->where('pivot.is_owner', true)->map(function ($user) {
                    return view('livewire.tracking-interests.ti-table.user-field-badge',
                    [
                        'user' => $user
                    ])->render();
                });
                return '<div class="flex flex-wrap gap-2">' . $badges->join('') . '</div>';
            })
            ->add('creator_users', function (TrackingInterest $model) {
                $badges = $model->users->where('pivot.is_creator', true)->map(function ($user) {
                    return view('livewire.tracking-interests.ti-table.user-field-badge',
                    [
                        'user' => $user
                    ])->render();
                });
                return '<div class="flex flex-wrap gap-2">' . $badges->join('') . '</div>';
            });

    }

    public function columns(): array
    {
        return [
            Column::make('Id', 'id')
                ->sortable()
                ->searchable(),

            Column::make('Interest', 'interest')
                ->sortable()
                ->searchable(),

            Column::make('Is active', 'is_active_formatted'),

            Column::make('Users', 'users_list'),

            Column::make('Owner users', 'owner_users'),

            Column::make('Creator user', 'creator_users'),

            // Column::action('Action')
        ];
    }

    public function filters(): array
    {
        return [
        ];
    }

    #[\Livewire\Attributes\On('edit')]
    public function edit($rowId): void
    {
        $this->js('alert('.$rowId.')');
    }

    // public function actions(TrackingInterest $row): array
    // {
    //     return [
    //         // Button::add('edit')
    //         //     ->slot('Edit: '.$row->id)
    //         //     ->id()
    //         //     ->class('pg-btn-white dark:ring-pg-primary-600 dark:border-pg-primary-600 dark:hover:bg-pg-primary-700 dark:ring-offset-pg-primary-800 dark:text-pg-primary-300 dark:bg-pg-primary-700')
    //         //     ->dispatch('edit', ['rowId' => $row->id])
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
}