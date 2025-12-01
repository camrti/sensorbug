<?php

use Livewire\Volt\Component;
use App\Models\User;

new class extends Component {
    public User $user;

}; ?>

<div>
    <flux:tooltip content="{{ $user->email }}">
        <flux:badge>
            {{ $user->name }}
        </flux:badge>
    </flux:tooltip>
</div>
