<?php

namespace App\Livewire\Admin;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class UsersManagement extends Component
{
    public $showCreateModal = false;
    public $showEditModal = false;

    public $first_name = '';
    public $last_name = '';
    public $email = '';
    public $password = '';
    public $user_role = 'user';
    public $tenant_id = null;
    public $is_enabled = true;

    public $editUserId = null;

    protected $listeners = [
        'deleteUser' => 'deleteUser',
        'openEditModal' => 'openEditModal',
        'showError' => 'showError'
    ];

    public function openCreateModal()
    {
        $this->showCreateModal = true;
    }

    public function closeCreateModal()
    {
        $this->showCreateModal = false;
        $this->reset(['first_name', 'last_name', 'email', 'password', 'user_role', 'tenant_id', 'is_enabled']);
        $this->resetValidation();
    }

    public function createUser()
    {
        // Solo superadmin può creare utenti
        if (!auth()->user()->isSuperadmin()) {
            session()->flash('error', 'Non hai i permessi per creare utenti.');
            return;
        }

        $this->validate([
            'first_name' => 'required|string|min:2|max:255',
            'last_name' => 'required|string|min:2|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'user_role' => 'required|in:superadmin,tenant_admin,user',
            'tenant_id' => 'required|exists:tenants,id',
            'is_enabled' => 'boolean',
        ], [
            'first_name.required' => 'Il nome è obbligatorio.',
            'first_name.min' => 'Il nome deve avere almeno 2 caratteri.',
            'last_name.required' => 'Il cognome è obbligatorio.',
            'last_name.min' => 'Il cognome deve avere almeno 2 caratteri.',
            'email.required' => 'La email è obbligatoria.',
            'email.email' => 'Inserisci una email valida.',
            'email.unique' => 'Questa email è già in uso.',
            'password.required' => 'La password è obbligatoria.',
            'password.min' => 'La password deve avere almeno 8 caratteri.',
            'user_role.required' => 'Il ruolo è obbligatorio.',
            'tenant_id.required' => 'Il tenant è obbligatorio.',
            'tenant_id.exists' => 'Il tenant selezionato non esiste.',
        ]);

        User::create([
            'name' => $this->first_name . ' ' . $this->last_name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'user_role' => $this->user_role,
            'tenant_id' => $this->tenant_id,
            'is_enabled' => $this->is_enabled,
        ]);

        $this->closeCreateModal();
        $this->dispatch('pg:eventRefresh-users-table-bvutf4-table');
        session()->flash('success', 'Utente creato con successo!');
    }

    public function openEditModal($userId)
    {
        if (!auth()->user()->isSuperadmin()) {
            session()->flash('error', 'Solo i superadmin possono modificare utenti.');
            return;
        }

        $user = User::find($userId);
        if (!$user) {
            return;
        }

        $this->editUserId = $userId;
        $nameParts = explode(' ', $user->name, 2);
        $this->first_name = $nameParts[0] ?? '';
        $this->last_name = $nameParts[1] ?? '';
        $this->email = $user->email;
        $this->password = '';
        $this->user_role = $user->user_role;
        $this->tenant_id = $user->tenant_id;
        $this->is_enabled = $user->is_enabled;
        $this->showEditModal = true;
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->reset(['editUserId', 'first_name', 'last_name', 'email', 'password', 'user_role', 'tenant_id', 'is_enabled']);
        $this->resetValidation();
    }

    public function updateUser()
    {
        if (!auth()->user()->isSuperadmin()) {
            session()->flash('error', 'Solo i superadmin possono modificare utenti.');
            return;
        }

        $this->validate([
            'first_name' => 'required|string|min:2|max:255',
            'last_name' => 'required|string|min:2|max:255',
            'email' => 'required|email|unique:users,email,' . $this->editUserId,
            'password' => 'nullable|string|min:8',
            'user_role' => 'required|in:superadmin,tenant_admin,user',
            'tenant_id' => 'required|exists:tenants,id',
            'is_enabled' => 'boolean',
        ], [
            'first_name.required' => 'Il nome è obbligatorio.',
            'first_name.min' => 'Il nome deve avere almeno 2 caratteri.',
            'last_name.required' => 'Il cognome è obbligatorio.',
            'last_name.min' => 'Il cognome deve avere almeno 2 caratteri.',
            'email.required' => 'La email è obbligatoria.',
            'email.email' => 'Inserisci una email valida.',
            'email.unique' => 'Questa email è già in uso.',
            'password.min' => 'La password deve avere almeno 8 caratteri.',
            'user_role.required' => 'Il ruolo è obbligatorio.',
            'tenant_id.required' => 'Il tenant è obbligatorio.',
            'tenant_id.exists' => 'Il tenant selezionato non esiste.',
        ]);

        $user = User::find($this->editUserId);
        if (!$user) {
            return;
        }

        $user->name = $this->first_name . ' ' . $this->last_name;
        $user->email = $this->email;
        if ($this->password) {
            $user->password = Hash::make($this->password);
        }
        $user->user_role = $this->user_role;
        $user->tenant_id = $this->tenant_id;
        $user->is_enabled = $this->is_enabled;
        $user->save();

        $this->closeEditModal();
        $this->dispatch('pg:eventRefresh-users-table-bvutf4-table');
        session()->flash('success', 'Utente modificato con successo!');
    }

    public function showError($message)
    {
        session()->flash('error', $message);
    }

    public function deleteUser($userId)
    {
        if (!auth()->user()->isAdmin()) {
            return;
        }

        $user = User::find($userId);
        if (!$user) {
            return;
        }

        if ($user->id === auth()->id()) {
            session()->flash('error', 'Non puoi eliminare te stesso.');
            return;
        }

        // Se è tenant_admin, verifica che l'utente appartenga al suo tenant
        if (auth()->user()->isTenantAdmin() && $user->tenant_id !== auth()->user()->tenant_id) {
            session()->flash('error', 'Non puoi eliminare utenti di altri tenant.');
            return;
        }

        $user->delete();

        session()->flash('success', 'Utente eliminato con successo!');
        $this->dispatch('pg:eventRefresh-users-table-bvutf4-table');
    }

    public function updatedTenantId()
    {
        // Quando cambia il tenant, resetta il ruolo se non è più disponibile
        $availableRoles = array_keys($this->availableRoles);

        if (!in_array($this->user_role, $availableRoles)) {
            // Se il ruolo corrente non è disponibile, imposta il primo disponibile
            $this->user_role = $availableRoles[0] ?? 'user';
        }
    }

    public function getAvailableRolesProperty()
    {
        // Se non hai ancora selezionato un tenant, mostra tutti i ruoli
        if (!$this->tenant_id) {
            return [
                'user' => 'User',
                'tenant_admin' => 'Tenant Admin',
                'superadmin' => 'Superadmin'
            ];
        }

        $tenant = Tenant::find($this->tenant_id);

        // Se è tenant di sistema, solo Superadmin
        if ($tenant && $tenant->is_system) {
            return ['superadmin' => 'Superadmin'];
        }

        // Altrimenti solo user e tenant_admin (non superadmin)
        return [
            'user' => 'User',
            'tenant_admin' => 'Tenant Admin'
        ];
    }

    public function render()
    {
        $tenants = Tenant::where('is_enabled', true)->orderBy('name')->get();
        return view('livewire.admin.users-management', [
            'tenants' => $tenants
        ]);
    }
}