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
                if ($model->isSuperadmin() || $model->isTenantAdmin()) {
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
                ->slot('<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-5"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>')
                ->class('p-2 hover:bg-zinc-100 dark:hover:bg-zinc-800 rounded text-zinc-600 dark:text-zinc-400')
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
