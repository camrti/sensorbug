<?php

namespace App\Livewire\Admin;

use App\Models\User;
use App\Models\TrackingInterest;
use App\Models\UserTrackingInterest;
use Livewire\Component;
use Livewire\WithPagination;

class TrackingInterestAssignmentTable extends Component
{
    use WithPagination;

    public $search = '';
    public $userFilter = 'all'; // 'all', 'admin', 'enterprise', 'user'
    public $showAssignModal = false;
    public $selectedUserId = null;
    public $selectedTrackingInterestId = null;
    public $isOwner = false;
    public $isCreator = false;
    public $assignmentError = '';

    protected $listeners = ['assignment-updated' => 'refreshList'];

    protected $queryString = [
        'search' => ['except' => ''],
        'userFilter' => ['except' => 'all'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingUserFilter()
    {
        $this->resetPage();
    }

    public function refreshList()
    {
        $this->resetPage();
    }

    public function openAssignModal()
    {
        $this->showAssignModal = true;
        $this->selectedUserId = null;
        $this->selectedTrackingInterestId = null;
        $this->isOwner = false;
        $this->isCreator = false;
        $this->assignmentError = '';
    }

    public function closeAssignModal()
    {
        $this->showAssignModal = false;
        $this->assignmentError = '';
    }

    public function assignTrackingInterest()
    {
        $this->validate([
            'selectedUserId' => 'required|exists:users,id',
            'selectedTrackingInterestId' => 'required|exists:tracking_interests,id',
        ]);

        $user = User::findOrFail($this->selectedUserId);
        $trackingInterest = TrackingInterest::findOrFail($this->selectedTrackingInterestId);

        // Check if assignment is allowed
        $canAssign = $user->canBeAssignedTrackingInterest($trackingInterest);

        if (!$canAssign['allowed']) {
            $this->assignmentError = $canAssign['reason'];
            return;
        }

        // Check if assignment already exists
        if ($user->trackingInterests()->where('tracking_interest_id', $trackingInterest->id)->exists()) {
            $this->assignmentError = 'User is already assigned to this tracking interest.';
            return;
        }

        // Perform the assignment
        $success = $user->assignTrackingInterest($trackingInterest, $this->isOwner, $this->isCreator);

        if ($success) {
            $this->closeAssignModal();
            $this->dispatch('assignment-updated');
            session()->flash('success', 'Tracking interest assigned successfully.');
        } else {
            $this->assignmentError = 'Failed to assign tracking interest. Please try again.';
        }
    }

    public function unassignTrackingInterest($userId, $trackingInterestId)
    {
        $user = User::findOrFail($userId);
        $trackingInterest = TrackingInterest::findOrFail($trackingInterestId);

        $success = $user->unassignTrackingInterest($trackingInterest);

        if ($success) {
            $this->dispatch('assignment-updated');
            session()->flash('success', 'Tracking interest unassigned successfully.');
        } else {
            session()->flash('error', 'Failed to unassign tracking interest.');
        }
    }

    public function toggleOwnership($userId, $trackingInterestId)
    {
        $assignment = UserTrackingInterest::where('user_id', $userId)
            ->where('tracking_interest_id', $trackingInterestId)
            ->first();

        if ($assignment) {
            $assignment->is_owner = !$assignment->is_owner;
            $assignment->save();
            $this->dispatch('assignment-updated');
        }
    }

    public function toggleCreator($userId, $trackingInterestId)
    {
        $assignment = UserTrackingInterest::where('user_id', $userId)
            ->where('tracking_interest_id', $trackingInterestId)
            ->first();

        if ($assignment) {
            $assignment->is_creator = !$assignment->is_creator;
            $assignment->save();
            $this->dispatch('assignment-updated');
        }
    }

    public function updatedSelectedUserId()
    {
        $this->selectedTrackingInterestId = null;
    }

    public function render()
    {
        $query = UserTrackingInterest::with(['user', 'trackingInterest']);

        // Apply search filter
        if (!empty($this->search)) {
            $query->whereHas('user', function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('email', 'like', '%' . $this->search . '%');
            })->orWhereHas('trackingInterest', function ($q) {
                $q->where('interest', 'like', '%' . $this->search . '%');
            });
        }

        // LEGACY CODE - Commented out (needs refactoring with new user_role system)
        // if ($this->userFilter !== 'all') {
        //     $query->whereHas('user', function ($q) {
        //         switch ($this->userFilter) {
        //             case 'admin': $q->where('user_role', 'tenant_admin'); break;
        //             case 'superadmin': $q->where('user_role', 'superadmin'); break;
        //             case 'user': $q->where('user_role', 'user'); break;
        //         }
        //     });
        // }

        $assignments = $query->join('users', 'user_tracking_interests.user_id', '=', 'users.id')
            ->join('tracking_interests', 'user_tracking_interests.tracking_interest_id', '=', 'tracking_interests.id')
            ->select('user_tracking_interests.*')
            ->orderBy('users.name')
            ->orderBy('tracking_interests.interest')
            ->paginate(10);

        $users = User::where('is_enabled', true)->orderBy('name')->get();

        $trackingInterests = collect();
        if ($this->selectedUserId) {
            $selectedUser = User::find($this->selectedUserId);
            if ($selectedUser) {
                // LEGACY CODE - Simplified for now
                $trackingInterests = TrackingInterest::where('is_active', true)->orderBy('interest')->get();
            }
        } else {
            $trackingInterests = TrackingInterest::where('is_active', true)->orderBy('interest')->get();
        }

        return view('livewire.admin.tracking-interest-assignment-table', compact('assignments', 'users', 'trackingInterests'));
    }
}