<?php

use Livewire\Volt\Component;

new class extends Component {
    public $btnText;
    public string $url;
}; ?>

<div>
    <flux:button
        href="{{ $url }}"
        icon:trailing="arrow-up-right"
        target="_blank"
    >
        {{ $btnText ?? 'Go to link' }}
    </flux:button>
</div>
