<?php

namespace App\Livewire\Admin;

use App\Models\Tenant;
use Livewire\Component;

class TenantsManagement extends Component
{
    public $showCreateModal = false;
    public $showEditModal = false;

    public $name = '';
    public $is_enabled = true;

    public $selectedTenantId = null;

    protected $listeners = [
        'deleteTenant' => 'deleteTenant',
        'editTenant' => 'editTenant'
    ];

    public function openCreateModal()
    {
        $this->showCreateModal = true;
    }

    public function closeCreateModal()
    {
        $this->showCreateModal = false;
        $this->reset(['name', 'is_enabled']);
        $this->resetValidation();
    }

    public function createTenant()
    {
        $this->validate([
            'name' => 'required|string|min:2|max:255',
            'is_enabled' => 'boolean',
        ], [
            'name.required' => 'Il nome del tenant è obbligatorio.',
            'name.min' => 'Il nome deve avere almeno 2 caratteri.',
        ]);

        Tenant::create([
            'name' => $this->name,
            'is_enabled' => $this->is_enabled,
        ]);

        $this->closeCreateModal();
        $this->dispatch('pg:eventRefresh-tenants-table');
        session()->flash('success', 'Tenant creato con successo!');
    }

    public function editTenant($tenantId)
    {
        $tenant = Tenant::find($tenantId);
        if (!$tenant) {
            return;
        }

        // Non permettere di modificare il tenant di sistema
        if ($tenant->is_system) {
            session()->flash('error', 'Non puoi modificare il tenant di sistema');
            return;
        }

        $this->selectedTenantId = $tenant->id;
        $this->name = $tenant->name;
        $this->is_enabled = $tenant->is_enabled;
        $this->showEditModal = true;
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->reset(['selectedTenantId', 'name', 'is_enabled']);
        $this->resetValidation();
    }

    public function updateTenant()
    {
        $this->validate([
            'name' => 'required|string|min:2|max:255',
            'is_enabled' => 'boolean',
        ], [
            'name.required' => 'Il nome del tenant è obbligatorio.',
            'name.min' => 'Il nome deve avere almeno 2 caratteri.',
        ]);

        $tenant = Tenant::find($this->selectedTenantId);
        if ($tenant) {
            // Non permettere di modificare il tenant di sistema
            if ($tenant->is_system) {
                session()->flash('error', 'Non puoi modificare il tenant di sistema');
                $this->closeEditModal();
                return;
            }

            $tenant->name = $this->name;
            $tenant->is_enabled = $this->is_enabled;
            $tenant->save();

            $this->closeEditModal();
            $this->dispatch('pg:eventRefresh-tenants-table');
            session()->flash('success', 'Tenant aggiornato con successo!');
        }
    }

    public function deleteTenant($tenantId)
    {
        if (!auth()->user()->isSuperadmin()) {
            return;
        }

        $tenant = Tenant::find($tenantId);
        if (!$tenant) {
            return;
        }

        // Non permettere di eliminare il tenant di sistema
        if ($tenant->is_system) {
            session()->flash('error', 'Non puoi eliminare il tenant di sistema');
            return;
        }

        // Delete all users associated with this tenant (hard delete)
        $tenant->users()->delete();

        // Delete the tenant (hard delete)
        $tenant->delete();

        session()->flash('success', 'Tenant e relativi utenti eliminati con successo!');
        $this->dispatch('pg:eventRefresh-tenants-table');
    }

    public function render()
    {
        return view('livewire.admin.tenants-management');
    }
}