<?php

use App\Models\TrackingInterest;
use App\Models\User;
use Livewire\Livewire;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

it('shows only the tracking interests that the user is associated with', function () {
    $user = User::factory()->create();
    $associatedTrackingInterests = TrackingInterest::factory(2)->create();
    $unassociatedTrackingInterest = TrackingInterest::factory()->create();

    $associatedTrackingInterests->each(fn($interest) => $interest->users()->attach($user));

    $this->actingAs($user);

    Livewire::test('tracking-interest-selector')
        ->assertSee(...$associatedTrackingInterests->pluck('interest')->toArray())
        ->assertDontSee($unassociatedTrackingInterest->interest);
})->group('RF-01');

it('selects a tracking interest', function () {
    $user = User::factory()->create();
    $trackingInterests = TrackingInterest::factory(2)->create();

    $trackingInterests->each(fn($interest) => $interest->users()->attach($user));

    $this->actingAs($user);

    Livewire::test('tracking-interest-selector')
        ->call('selectInterest', $trackingInterests[1]->id)
        ->assertSeeInOrder([
            $trackingInterests[1]->interest,
            $trackingInterests[0]->interest,
        ]);
})->group('RF-01');

it('clears the selected tracking interest', function () {
    $user = User::factory()->create();
    $trackingInterests = TrackingInterest::factory(2)->create();

    $trackingInterests->each(fn($interest) => $interest->users()->attach($user));

    $this->actingAs($user);

    Livewire::test('tracking-interest-selector')
        ->call('selectInterest', $trackingInterests[1]->id)
        ->call('clearSelection')
        ->assertSeeInOrder([
            'Seleziona interesse',
            $trackingInterests[0]->interest,
            $trackingInterests[1]->interest,
        ]);
})->group('RF-01');
