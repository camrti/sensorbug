<?php

use Livewire\Volt\Component;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\On;

new class extends Component {
    public function with() {
        return [
            'trackingInterests' => auth()->user()->trackingInterests,
        ];
    }

    #[On('tracking-interest-saved')]
    public function refreshAfterTIChange(){
        // no need to do anything here, the component will refresh automatically
        // because it is using the `with` method to get the tracking interests
    }

    public function selectInterest($interestId)
    {
        Session::put('selected_tracking_interest_' . auth()->id(), $interestId);
        $this->dispatch('tracking-interest-selected');
    }

    public function clearSelection()
    {
        Session::forget('selected_tracking_interest_' . auth()->id());
        $this->dispatch('tracking-interest-cleared');
    }
}; ?>

<div>
    <flux:dropdown>
        <flux:button icon:trailing="chevron-down">
            @if(Session::has('selected_tracking_interest_' . auth()->id()))
                @php
                    $selectedInterest = App\Models\TrackingInterest::find(Session::get('selected_tracking_interest_' . auth()->id()));
                @endphp
                @if($selectedInterest)
                    {{ $selectedInterest->interest }}
                @else
                    @php
                        $this->clearSelection();
                    @endphp
                    {{ __('Seleziona interesse') }}
                @endif
            @else
                {{ __('Seleziona interesse') }}
            @endif
        </flux:button>
        <flux:menu>
            @if(Session::has('selected_tracking_interest_' . auth()->id()))
                <flux:menu.item wire:click="clearSelection" icon="x-mark" class="text-red-500">
                    {{ __('Rimuovi selezione') }}
                </flux:menu.item>
                <flux:menu.separator />
            @endif

            @foreach ($trackingInterests as $ti)
                <flux:menu.item wire:click="selectInterest({{ $ti->id }})" as="button" type="submit" class="w-full">
                    {{ $ti->interest }}
                </flux:menu.item>
            @endforeach
        </flux:menu>
    </flux:dropdown>
</div>
