<?php

namespace App\Livewire;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;

final class TenantUserTrackingInterestsTable extends PowerGridComponent
{
    public string $tableName = 'tenant-user-tracking-interests-table';

    public function setUp(): array
    {
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
        if (!auth()->user()->isTenantAdmin()) {
            abort(403, 'Accesso non autorizzato');
        }

        return User::query()
            ->where('tenant_id', auth()->user()->tenant_id)
            ->with('trackingInterests');
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('name')
            ->add('email')
            ->add('tracking_interests', function (User $model) {
                if ($model->trackingInterests->isEmpty()) {
                    return '<span class="text-zinc-400 dark:text-zinc-500 text-sm">Nessuno</span>';
                }

                return $model->trackingInterests->map(function($ti) {
                    return '<span class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400">'
                        . e($ti->interest) .
                        '</span>';
                })->join(' ');
            })
            ->add('user_role_formatted', function (User $model) {
                return $model->getUserType();
            })
            ->add('is_enabled_formatted', function (User $model) {
                if ($model->isSuperadmin()) {
                    return '';
                }
                return view('livewire.powergrid-table-switch', [
                    'onlyAdmin' => false,
                    'checkedValue' => $model->is_enabled,
                    'handlerName' => 'toggleEnabled',
                    'handlerParam' => $model->id
                ])->render();
            })
            ->add('created_at_formatted', function (User $model) {
                return $model->created_at->locale('it')->diffForHumans();
            });
    }

    public function columns(): array
    {
        return [
            Column::make('Nome', 'name')
                ->sortable()
                ->searchable(),

            Column::make('Email', 'email')
                ->sortable()
                ->searchable(),

            Column::make('Tracking Interest', 'tracking_interests'),

            Column::make('Ruolo', 'user_role_formatted'),

            Column::make('Abilitato', 'is_enabled_formatted'),

            Column::make('Creato il', 'created_at_formatted', 'created_at')
                ->sortable(),

            Column::action('Azioni')
        ];
    }

    public function actions(User $row): array
    {
        return [
            Button::add('manage-ti')
                ->slot('Gestisci TI')
                ->class('px-3 py-1.5 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 rounded-md')
                ->dispatch('openManageTIModal', ['userId' => $row->id]),
        ];
    }

    public function toggleEnabled($userId)
    {
        $user = User::find($userId);
        if (!$user || $user->tenant_id !== auth()->user()->tenant_id) {
            $this->js('alert("Operazione non consentita")');
            return;
        }

        if ($user->tenant && !$user->tenant->is_enabled && !$user->is_enabled) {
            $this->js('alert("Non puoi abilitare l\'utente: il tenant è disabilitato")');
            return;
        }

        $user->is_enabled = !$user->is_enabled;
        $user->save();
    }
}
