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
    @canImpersonate($guard = null)
    <flux:button
        href="{{ route('impersonate', $userId) }}"
        variant="primary"
        size="sm"
        color="blue"
    >
        Impersonate
    </flux:button>
    @endCanImpersonate
</div>
