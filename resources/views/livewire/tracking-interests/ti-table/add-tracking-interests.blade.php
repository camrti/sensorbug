<?php

use App\Models\TrackingInterest;
use Livewire\Volt\Component;

new class extends Component {
    public $interest = '';
    public $isActive = true;

    protected $rules = [
        'interest' => 'required|string|max:100|unique:tracking_interests,interest',
        'isActive' => 'boolean',
    ];

    protected $messages = [
        'interest.required' => 'Il campo interesse è obbligatorio.',
        'interest.max' => 'L\'interesse non può superare i :max caratteri.',
        'interest.unique' => 'Questo interesse è già registrato.',
    ];

    public function resetForm()
    {
        $this->interest = '';
        $this->isActive = true;
        $this->resetValidation();
    }

    public function save()
    {
        $this->validate();

        // Creare l'interesse di tracciamento
        $trackingInterest = TrackingInterest::create([
            'interest' => $this->interest,
            'is_active' => $this->isActive,
        ]);

        // Associare l'interesse all'utente corrente come proprietario e creatore
        $trackingInterest->users()->attach(auth()->id(), [
            'is_owner' => true,
            'is_creator' => true
        ]);

        $this->resetForm();
        $this->dispatch('tracking-interest-saved');
    }
}; ?>

<div class="mb-6">

    {{-- BEGIN::add-TI-form --}}
    <div class="mb-6">
        <div class="bg-white dark:bg-zinc-900 shadow rounded-sm">
            <div class="p-4">
                <h3 class="text-base font-semibold mb-3">{{ __('Aggiungi Nuovo Interesse') }}</h3>
                <form wire:submit="save" class="space-y-4">
                    <div class="flex flex-col md:flex-row md:items-end md:space-x-4">
                        <div class="flex-grow mb-4 md:mb-0">
                            <flux:input
                                wire:model="interest"
                                :label="__('Interesse')"
                                type="text"
                                required
                                autofocus
                                :error="$errors->first('interest')"
                            />
                        </div>
                        <div class="md:w-1/4">
                            <flux:field variant="inline">
                                <flux:label>{{__('Attivo')}}</flux:label>
                                <flux:switch wire:model.live="isActive" />
                                <flux:error name="isActive" />
                            </flux:field>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <flux:button type="submit" variant="primary">
                            {{ __('Salva') }}
                        </flux:button>
                        <flux:button type="button" variant="filled" wire:click="resetForm">
                            {{ __('Annulla') }}
                        </flux:button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- END::add-TI-form --}}


</div>
