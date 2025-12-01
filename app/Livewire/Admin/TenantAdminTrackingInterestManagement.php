<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Models\UserTrackingInterest;
use Livewire\Component;

class TenantAdminTrackingInterestManagement extends Component
{
    public $showManageTIModal = false;
    public $selectedUserId = null;
    public $selectedUser = null;
    public $selectedTrackingInterestId = null;

    protected $listeners = [
        'openManageTIModal' => 'openManageTIModal',
    ];

    public function openManageTIModal($userId)
    {
        if (!auth()->user()->isTenantAdmin()) {
            session()->flash('error', 'Accesso non autorizzato.');
            return;
        }

        $user = User::with('trackingInterests')->find($userId);

        if (!$user || $user->tenant_id !== auth()->user()->tenant_id) {
            session()->flash('error', 'Utente non trovato o non autorizzato.');
            return;
        }

        $this->selectedUserId = $userId;
        $this->selectedUser = $user;
        $this->selectedTrackingInterestId = null;
        $this->showManageTIModal = true;
    }

    public function closeManageTIModal()
    {
        $this->showManageTIModal = false;
        $this->selectedUserId = null;
        $this->selectedUser = null;
        $this->selectedTrackingInterestId = null;
    }

    public function addTrackingInterest()
    {
        if (!$this->selectedTrackingInterestId) {
            session()->flash('error', 'Seleziona un tracking interest.');
            return;
        }

        $user = User::find($this->selectedUserId);
        if (!$user || $user->tenant_id !== auth()->user()->tenant_id) {
            session()->flash('error', 'Operazione non consentita.');
            return;
        }

        $tenantTrackingInterestIds = auth()->user()->tenant
            ->trackingInterests()
            ->pluck('tracking_interests.id');

        if (!$tenantTrackingInterestIds->contains($this->selectedTrackingInterestId)) {
            session()->flash('error', 'Tracking interest non assegnato al tenant.');
            return;
        }

        if ($user->trackingInterests->contains($this->selectedTrackingInterestId)) {
            session()->flash('error', 'Tracking interest già assegnato all\'utente.');
            return;
        }

        UserTrackingInterest::create([
            'user_id' => $user->id,
            'tracking_interest_id' => $this->selectedTrackingInterestId,
            'assigned_by_user_id' => auth()->id(),
        ]);

        $this->selectedUser = $user->fresh('trackingInterests');
        $this->selectedTrackingInterestId = null;
        $this->dispatch('pg:eventRefresh-tenant-user-tracking-interests-table');
        session()->flash('success', 'Tracking interest aggiunto con successo!');
    }

    public function removeTrackingInterest($trackingInterestId)
    {
        $user = User::find($this->selectedUserId);
        if (!$user || $user->tenant_id !== auth()->user()->tenant_id) {
            session()->flash('error', 'Operazione non consentita.');
            return;
        }

        UserTrackingInterest::where('user_id', $user->id)
            ->where('tracking_interest_id', $trackingInterestId)
            ->delete();

        $this->selectedUser = $user->fresh('trackingInterests');
        $this->dispatch('pg:eventRefresh-tenant-user-tracking-interests-table');
        session()->flash('success', 'Tracking interest rimosso con successo!');
    }

    public function getAvailableTrackingInterestsProperty()
    {
        if (!$this->selectedUser) {
            return collect();
        }

        $assignedIds = $this->selectedUser->trackingInterests->pluck('id');

        return auth()->user()->tenant
            ->trackingInterests()
            ->where('is_active', true)
            ->whereNotIn('tracking_interests.id', $assignedIds)
            ->orderBy('interest')
            ->get();
    }

    public function render()
    {
        return view('livewire.admin.tenant-admin-tracking-interest-management');
    }
}
