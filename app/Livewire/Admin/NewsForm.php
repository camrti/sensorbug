<?php

namespace App\Livewire\Admin;

use App\Models\News;
use App\Models\TrackingInterest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NewsForm extends Component
{
    public $title = '';
    public $text = '';
    public $for_user_id = null;
    public $for_tracking_interest_id = null;
    public $target_type = 'user'; // 'user' or 'tracking_interest'

    public $users = [];
    public $trackingInterests = [];

    protected $rules = [
        'title' => 'required|string|max:100',
        'text' => 'required|string|min:10|max:20000',
        'target_type' => 'required|in:user,tracking_interest',
        'for_user_id' => 'nullable|exists:users,id|required_if:target_type,user',
        'for_tracking_interest_id' => 'nullable|exists:tracking_interests,id|required_if:target_type,tracking_interest',
    ];

    protected $messages = [
        'title.required' => 'Il titolo della news è obbligatorio.',
        'title.max' => 'Il titolo non può superare i 100 caratteri.',
        'text.required' => 'Il testo della news è obbligatorio.',
        'text.min' => 'Il testo deve essere di almeno 10 caratteri.',
        'text.max' => 'Il testo non può superare i 20000 caratteri.',
        'for_user_id.required_if' => 'Seleziona un utente.',
        'for_tracking_interest_id.required_if' => 'Seleziona un tracking interest.',
    ];

    public function mount()
    {
        $this->loadUsers();
        $this->loadTrackingInterests();
    }

    public function loadUsers()
    {
        $this->users = User::select('id', 'name', 'email')
            ->orderBy('name')
            ->get();
    }

    public function loadTrackingInterests()
    {
        $this->trackingInterests = TrackingInterest::select('id', 'interest')
            ->where('is_active', true)
            ->orderBy('interest')
            ->get();
    }

    public function updatedTargetType()
    {
        // Reset della selezione quando cambia il tipo
        $this->for_user_id = null;
        $this->for_tracking_interest_id = null;
    }

    public function createNews()
    {
        $this->validate();

        $data = [
            'title' => $this->title,
            'text' => $this->text,
            'added_by_user_id' => Auth::id(),
        ];

        if ($this->target_type === 'user') {
            $data['for_user_id'] = $this->for_user_id;
        } else {
            $data['for_tracking_interest_id'] = $this->for_tracking_interest_id;
        }

        News::create($data);

        // Reset del form
        $this->reset(['title', 'text', 'for_user_id', 'for_tracking_interest_id']);
        $this->target_type = 'user';

        // Emette evento per aggiornare la lista
        $this->dispatch('news-created');

        session()->flash('success', 'News creata con successo!');
    }

    public function render()
    {
        return view('livewire.admin.news-form');
    }
}