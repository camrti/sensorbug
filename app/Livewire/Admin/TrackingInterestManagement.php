<?php

namespace App\Livewire\Admin;

use App\Models\TrackingInterest;
use Livewire\Component;
use Livewire\WithPagination;

class TrackingInterestManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = 'all'; // 'all', 'active', 'inactive'

    // Form fields
    public $showCreateModal = false;
    public $showEditModal = false;
    public $editingTrackingInterestId = null;
    public $interest = '';
    public $isActive = true;

    protected $listeners = ['tracking-interest-created' => 'refreshList'];

    protected $queryString = [
        'search' => ['except' => ''],
        'statusFilter' => ['except' => 'all'],
    ];

    protected $rules = [
        'interest' => 'required|string|min:3|max:255',
        'isActive' => 'boolean',
    ];

    protected $messages = [
        'interest.required' => 'Il nome del tracking interest è obbligatorio.',
        'interest.min' => 'Il nome deve contenere almeno 3 caratteri.',
        'interest.max' => 'Il nome non può superare i 255 caratteri.',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingStatusFilter()
    {
        $this->resetPage();
    }

    public function refreshList()
    {
        $this->resetPage();
    }

    public function openCreateModal()
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function closeCreateModal()
    {
        $this->showCreateModal = false;
        $this->resetForm();
    }

    public function openEditModal($trackingInterestId)
    {
        $trackingInterest = TrackingInterest::find($trackingInterestId);

        if ($trackingInterest) {
            $this->editingTrackingInterestId = $trackingInterest->id;
            $this->interest = $trackingInterest->interest;
            $this->isActive = $trackingInterest->is_active;
            $this->showEditModal = true;
        }
    }

    public function closeEditModal()
    {
        $this->showEditModal = false;
        $this->resetForm();
    }

    private function resetForm()
    {
        $this->editingTrackingInterestId = null;
        $this->interest = '';
        $this->isActive = true;
        $this->resetValidation();
    }

    public function createTrackingInterest()
    {
        $this->validate();

        // Check for duplicate names
        $existing = TrackingInterest::where('interest', $this->interest)->first();
        if ($existing) {
            $this->addError('interest', 'Esiste già un tracking interest con questo nome.');
            return;
        }

        try {
            TrackingInterest::create([
                'interest' => $this->interest,
                'is_active' => $this->isActive,
            ]);

            session()->flash('success', 'Tracking Interest creato con successo!');
            $this->closeCreateModal();
            $this->dispatch('tracking-interest-created');

        } catch (\Exception $e) {
            session()->flash('error', 'Errore durante la creazione del Tracking Interest.');
        }
    }

    public function updateTrackingInterest()
    {
        $this->validate();

        // Check for duplicate names (excluding current record)
        $existing = TrackingInterest::where('interest', $this->interest)
            ->where('id', '!=', $this->editingTrackingInterestId)
            ->first();

        if ($existing) {
            $this->addError('interest', 'Esiste già un tracking interest con questo nome.');
            return;
        }

        try {
            $trackingInterest = TrackingInterest::find($this->editingTrackingInterestId);

            if ($trackingInterest) {
                $trackingInterest->update([
                    'interest' => $this->interest,
                    'is_active' => $this->isActive,
                ]);

                session()->flash('success', 'Tracking Interest aggiornato con successo!');
                $this->closeEditModal();
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Errore durante l\'aggiornamento del Tracking Interest.');
        }
    }

    public function toggleStatus($trackingInterestId)
    {
        try {
            $trackingInterest = TrackingInterest::find($trackingInterestId);

            if ($trackingInterest) {
                $trackingInterest->update([
                    'is_active' => !$trackingInterest->is_active
                ]);

                $status = $trackingInterest->is_active ? 'attivato' : 'disattivato';
                session()->flash('success', "Tracking Interest {$status} con successo!");
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Errore durante il cambio di stato.');
        }
    }

    public function deleteTrackingInterest($trackingInterestId)
    {
        try {
            $trackingInterest = TrackingInterest::find($trackingInterestId);

            if ($trackingInterest) {
                // Check if it has assigned users
                $assignedUsersCount = $trackingInterest->users()->count();

                if ($assignedUsersCount > 0) {
                    session()->flash('error', "Non è possibile eliminare questo Tracking Interest perché è assegnato a {$assignedUsersCount} utent" . ($assignedUsersCount > 1 ? 'i' : 'e') . '.');
                    return;
                }

                // Check if it has associated data
                $pagesFoundCount = $trackingInterest->pagesFound()->count();

                if ($pagesFoundCount > 0) {
                    session()->flash('error', "Non è possibile eliminare questo Tracking Interest perché ha {$pagesFoundCount} pagin" . ($pagesFoundCount > 1 ? 'e' : 'a') . ' associate.');
                    return;
                }

                $trackingInterest->delete();
                session()->flash('success', 'Tracking Interest eliminato con successo!');
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Errore durante l\'eliminazione del Tracking Interest.');
        }
    }

    public function render()
    {
        $query = TrackingInterest::query();

        // Apply search filter
        if ($this->search) {
            $query->where('interest', 'like', '%' . $this->search . '%');
        }

        // Apply status filter
        if ($this->statusFilter !== 'all') {
            $isActive = $this->statusFilter === 'active';
            $query->where('is_active', $isActive);
        }

        $trackingInterests = $query->withCount(['users', 'pagesFound'])
            ->orderBy('interest')
            ->paginate(15);

        return view('livewire.admin.tracking-interest-management', [
            'trackingInterests' => $trackingInterests,
        ]);
    }
}
