<?php

use Livewire\Volt\Component;

new class extends Component {
    public $userId;

    public function mount($userId = null)
    {
        $this->userId = $userId;
    }
}; ?>

<div>
    @impersonating($guard = null)
    <flux:button
        href="{{ route('impersonate.leave') }}"
        variant="primary"
        size="sm"
        class="bg-blue-600 hover:bg-blue-700 text-white"
    >
        Stop Impersonating
    </flux:button>
    @endImpersonating
</div>
