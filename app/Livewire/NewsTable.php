<?php

namespace App\Livewire;

use App\Models\News;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;

class NewsTable extends Component
{
    use WithPagination;

    public $perPage = 2;

    public function mount()
    {
        // Non carichiamo più le news nel mount, useremo la paginazione
    }

    public function loadNews()
    {
        $this->resetPage();
    }

    #[On('tracking-interest-selected')]
    #[On('tracking-interest-cleared')]
    public function refreshOnInterestChange()
    {
        $this->resetPage();
    }

    public function render()
    {
        $user = Auth::user();
        $selectedInterestId = Session::get('selected_tracking_interest_' . auth()->id());

        if (!$selectedInterestId) {
            $news = News::where('for_user_id', $user->id)
                ->with(['forUser', 'forTrackingInterest', 'addedByUser'])
                ->orderBy('created_at', 'desc')
                ->paginate($this->perPage);
        } else {
            $news = News::where('for_tracking_interest_id', $selectedInterestId)
                ->with(['forUser', 'forTrackingInterest', 'addedByUser'])
                ->orderBy('created_at', 'desc')
                ->paginate($this->perPage);
        }

        return view('livewire.news-table', compact('news'));
    }
}