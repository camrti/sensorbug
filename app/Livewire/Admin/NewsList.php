<?php

namespace App\Livewire\Admin;

use App\Models\News;
use Livewire\Component;
use Livewire\WithPagination;

class NewsList extends Component
{
    use WithPagination;

    public $search = '';
    public $filter = 'all'; // 'all', 'user', 'tracking_interest'

    protected $listeners = ['news-created' => 'refreshList'];

    protected $queryString = [
        'search' => ['except' => ''],
        'filter' => ['except' => 'all'],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilter()
    {
        $this->resetPage();
    }

    public function refreshList()
    {
        // Forza il refresh della lista e resetta alla prima pagina
        $this->resetPage();
    }

    public function deleteNews($newsId)
    {
        $news = News::find($newsId);

        if ($news) {
            $news->delete();
            session()->flash('success', 'News eliminata con successo!');
        }
    }

    public function render()
    {
        $query = News::with(['forUser', 'forTrackingInterest', 'addedByUser'])
            ->latest();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('title', 'like', '%' . $this->search . '%')
                  ->orWhere('text', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filter === 'user') {
            $query->whereNotNull('for_user_id');
        } elseif ($this->filter === 'tracking_interest') {
            $query->whereNotNull('for_tracking_interest_id');
        }

        $news = $query->paginate(10);

        return view('livewire.admin.news-list', [
            'news' => $news
        ]);
    }
}