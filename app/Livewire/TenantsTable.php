<?php

namespace App\Livewire;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Builder;
use PowerComponents\LivewirePowerGrid\Button;
use PowerComponents\LivewirePowerGrid\Column;
use PowerComponents\LivewirePowerGrid\Facades\Filter;
use PowerComponents\LivewirePowerGrid\Facades\PowerGrid;
use PowerComponents\LivewirePowerGrid\PowerGridFields;
use PowerComponents\LivewirePowerGrid\PowerGridComponent;

final class TenantsTable extends PowerGridComponent
{
    public string $tableName = 'tenants-table';

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

    // funzione che definisce la query di base per ottenere i dati
    public function datasource(): Builder
    {
        return Tenant::query()->withCount('users');
    }

    public function relationSearch(): array
    {
        return [];
    }

    public function fields(): PowerGridFields
    {
        return PowerGrid::fields()
            ->add('id')
            ->add('name', function (Tenant $model) {
                $url = route('users-list') . '?tenant_id=' . $model->id;
                return "<a href='{$url}'
                           wire:navigate
                           class='hover:underline cursor-pointer text-blue-600 dark:text-blue-400'>
                            {$model->name}
                        </a>";
            })
            ->add('users_count')
            ->add('created_at_formatted', function (Tenant $model) {
                return $model->created_at->locale('it')->diffForHumans();
            })
            ->add('is_enabled_formatted', function (Tenant $model) {
                // Tenant di sistema non ha toggle
                if ($model->is_system) {
                    return '<span class="text-sm text-gray-500 dark:text-gray-400">-</span>';
                }

                $checked = $model->is_enabled ? 'checked' : '';
                return "
                    <label class='relative inline-flex items-center cursor-pointer'>
                        <input type='checkbox'
                               wire:click='toggleEnabled({$model->id})'
                               {$checked}
                               class='sr-only peer'>
                        <div class='w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-zinc-500 dark:peer-focus:ring-zinc-600 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[\"\"] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-zinc-600'></div>
                    </label>
                ";
            });
    }

    public function columns(): array
    {
        return [
            Column::make('Nome', 'name')
                ->sortable()
                ->searchable(),

            Column::make('N° Utenti', 'users_count')
                ->sortable(),

            Column::make('Abilitato', 'is_enabled_formatted'),

            Column::make('Creato il', 'created_at_formatted', 'created_at')
                ->sortable()
                ->searchable(),

            Column::action('Azioni')
        ];
    }

    public function actions(Tenant $row): array
    {
        $actions = [];

        // Non mostrare i pulsanti per il tenant di sistema
        if (!$row->is_system) {
            $actions[] = Button::add('edit')
                ->slot('<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>')
                ->class('p-2 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded text-zinc-600 dark:text-zinc-400')
                ->dispatch('editTenant', ['tenantId' => $row->id]);

            $actions[] = Button::add('delete')
                ->slot('<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>')
                ->class('p-2 hover:bg-red-100 dark:hover:bg-red-900/30 rounded text-red-600 dark:text-red-400')
                ->dispatch('confirmDeleteTenant', ['tenantId' => $row->id, 'usersCount' => $row->users_count]);
        }

        return $actions;
    }

    public function filters(): array
    {
        return [
            Filter::boolean('is_enabled', 'is_enabled')
                ->label('Tutti', 'Abilitato', 'Disabilitato'),
    ];
}

    public function toggleEnabled($tenantId)
    {
        if (!auth()->user()->isSuperadmin()) {
            $this->js('alert("Non hai i permessi per questa azione")');
            return;
        }

        $tenant = Tenant::find($tenantId);
        if ($tenant) {
            // Non permettere di disabilitare il tenant di sistema
            if ($tenant->is_system) {
                $this->js('alert("Non puoi disabilitare il tenant di sistema")');
                return;
            }

            $tenant->is_enabled = !$tenant->is_enabled;
            $tenant->save();

            // Se disabilitato, disabilita anche tutti gli utenti
            if (!$tenant->is_enabled) {
                $tenant->users()->update(['is_enabled' => false]);
            }
        }
    }
}