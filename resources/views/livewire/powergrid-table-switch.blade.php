<?php

use App\Models\TrackingInterest;
use Livewire\Volt\Component;

new class extends Component {
    public $onlyAdmin;
    public $handlerName;
    public $handlerParam;
    public $checkedValue;
};
?>

<div class="flex items-center justify-center">
    @if ($onlyAdmin)
        @if(auth()->user()->isAdmin())
        <flux:switch
            :checked="$checkedValue"
            x-on:click="$wire.call('{{$handlerName}}', {{$handlerParam}})"
            size="sm"
        />
        @endif
    @else
        <flux:switch
            :checked="$checkedValue"
            x-on:click="$wire.call('{{$handlerName}}', {{$handlerParam}})"
            size="sm"
        />
    @endif
</div>
