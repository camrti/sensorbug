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
            Button::add('change-password')
                ->slot('<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z" /></svg>')
                ->class('p-2 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded text-zinc-600 dark:text-zinc-400')
                ->dispatch('openChangePasswordModal', ['userId' => $row->id]),

            Button::add('delete')
                ->slot('<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>')
                ->class('p-2 hover:bg-red-100 dark:hover:bg-red-900/30 rounded text-red-600 dark:text-red-400')
                ->dispatch('confirmDelete', ['userId' => $row->id]),
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