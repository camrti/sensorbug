<?php

namespace App\Livewire;

use App\Models\User;
use App\Models\Tenant;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Rule;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;

final class UsersTable extends PowerGridComponent
{
    public string $tableName = 'users-table-bvutf4-table';

    public function setUp(): array
    {
        $this->showCheckBox();

        return [
            PowerGrid::header()
                ->showSearchInput()
                ->showToggleColumns(),
            PowerGrid::footer()
                ->showPerPage()
                ->showRecordCount(),
        ];
    }

    public function datasource(): Builder
    {
        $query = User::query();

        // Se è tenant_admin, mostra solo utenti del suo tenant
        if (auth()->user()->isTenantAdmin()) {
            $query->where('tenant_id', auth()->user()->tenant_id);
        }

        // Leggi dalla rotta
        $tenantId = request()->route('tenant');
        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        return $query;
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('name')
            ->add('email')
            ->add('tenant_name', function (User $model) {
                return $model->tenant?->name ?? '-';
            })
            ->add('user_role_formatted', function (User $model) {
                return $model->getUserType();
            })
            ->add('created_at_formatted', function (User $model) {
                return $model->created_at->locale('it')->diffForHumans();
            })
            ->add('is_enabled_formatted', function (User $model) {
                if ($model->isSuperadmin()) {
                    return '';
                }
                return view('livewire.powergrid-table-switch', [
                    'onlyAdmin' => true,
                    'checkedValue' => $model->is_enabled,
                    'handlerName' => 'toggleEnabled',
                    'handlerParam' => $model->id
                ])->render();
            })
            ->add('start_impersonate', function (User $model) {
                if(auth()->user()->id == $model->id || $model->isSuperadmin()) {
                    return '';
                }
                return view('livewire.impersonate-button', [
                    'userId' => $model->id
                ])->render();
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

            Column::make('Tenant', 'tenant_name'),



            Column::make('Ruolo', 'user_role_formatted'),

            Column::make('Abilitato', 'is_enabled_formatted'),

            Column::make('Impersona', 'start_impersonate'),

            Column::make('Creato il', 'created_at_formatted', 'created_at')
                ->sortable()
                ->searchable(),

            Column::action('Azioni')
        ];
    }


    public function actions(User $row): array
    {
        return [
            Button::add('actions-menu')
                ->slot(view('livewire.users-table.actions-menu', ['userId' => $row->id])->render())
                ->class('')
        ];
    }


    public function filters(): array
    {
        return [
            Filter::boolean('is_enabled', 'is_enabled')
                ->label('Tutti', 'Abilitato', 'Disabilitato'),
            Filter::select('tenant_name', 'tenant_id')
                ->dataSource(Tenant::orderBy('name')->get()) 
                ->optionLabel('name')
                ->optionValue('id')
        ];
    }

    public function toggleEnabled($userId)
    {
        if (!auth()->user()->isAdmin()) {
            $this->js('alert("Non hai i permessi per questa azione")');
            return;
        }

        $user = User::find($userId);
        if (!$user) {
            return;
        }

        // Se è tenant_admin, verifica che l'utente appartenga al suo tenant
        if (auth()->user()->isTenantAdmin() && $user->tenant_id !== auth()->user()->tenant_id) {
            $this->js('alert("Non puoi modificare utenti di altri tenant")');
            return;
        }

        // Se il tenant è disabilitato, non permettere di abilitare l'utente
        if ($user->tenant && !$user->tenant->is_enabled && !$user->is_enabled) {
            $this->js('alert("Non puoi abilitare l\'utente: il tenant è disabilitato")');
            return;
        }

        $user->is_enabled = !$user->is_enabled;
        $user->save();
    }
}