<?php

use App\Models\TrackingInterest;
use App\Models\User;
use Livewire\Livewire;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

it('adds a new tracking interest', function () {
    $user = User::factory()->create();
    $trackingInterest = TrackingInterest::factory()->create();

    $trackingInterest->users()->attach($user, ['is_owner' => true, 'is_creator' => false]);

    $this->actingAs($user);

    Livewire::test('tracking-interest-table')
        ->assertSeeHtml($trackingInterest->interest);
})->group('RF-01');

it('updates a tracking interest', function () {
    $user = User::factory()->create();
    $trackingInterest = TrackingInterest::factory()->create();

    $trackingInterest->users()->attach($user, ['is_owner' => true, 'is_creator' => false]);

    $this->actingAs($user);

    Livewire::test('tracking-interest-table')
        ->assertSeeHtml($trackingInterest->interest);

    $trackingInterest->interest = 'New interest';
    $trackingInterest->save();

    Livewire::test('tracking-interest-table')
        ->assertSeeHtml('New interest');
})->group('RF-01');

it('can be activated', function () {
    $user = User::factory()->create();
    $trackingInterest = TrackingInterest::factory()->create(['is_active' => false]);

    $trackingInterest->users()->attach($user, ['is_owner' => true, 'is_creator' => false]);

    $this->actingAs($user);

    Livewire::test('tracking-interest-table')
        ->assertSeeHtml('Non attivo');

    $trackingInterest->is_active = true;
    $trackingInterest->save();

    Livewire::test('tracking-interest-table')
        ->assertSeeHtml('Attivo');
})->group('RF-01');

it('can be deactivated', function () {
    $user = User::factory()->create();
    $trackingInterest = TrackingInterest::factory()->create(['is_active' => true]);

    $trackingInterest->users()->attach($user, ['is_owner' => true, 'is_creator' => false]);

    $this->actingAs($user);

    Livewire::test('tracking-interest-table')
        ->assertSeeHtml('Attivo');

    $trackingInterest->is_active = false;
    $trackingInterest->save();

    Livewire::test('tracking-interest-table')
        ->assertSeeHtml('Non attivo');
})->group('RF-01');

it('allows the user to search for a tracking interest by interest', function () {
    $user = User::factory()->create();
    $trackingInterest1 = TrackingInterest::factory()->create();
    $trackingInterest2 = TrackingInterest::factory()->create();

    $trackingInterest1->users()->attach($user, ['is_owner' => true, 'is_creator' => false]);

    $this->actingAs($user);

    Livewire::test('tracking-interest-table')
        ->set('search', $trackingInterest1->interest)
        ->assertSeeHtml($trackingInterest1->interest)
        ->assertDontSeeHtml($trackingInterest2->interest);
})->group('RF-01');

it('searching with special characters does not throw error', function () {
    $user = User::factory()->create();
    $trackingInterest = TrackingInterest::factory()->create();
    $trackingInterest->users()->attach($user);

    $this->actingAs($user);

    $result = Livewire::test('tracking-interest-table')
        ->set('search', "'; DROP TABLE users; --")
        ->assertStatus(200);

    expect($result)->not->toBeNull();
})->group('RF-05');

it('searching with empty string returns all results', function () {
    $user = User::factory()->create();
    $trackingInterest = TrackingInterest::factory()->create();
    $trackingInterest->users()->attach($user);

    $this->actingAs($user);

    $ti2 = TrackingInterest::factory()->create();
    $ti2->users()->attach($user);

    $result = Livewire::test('tracking-interest-table')
        ->set('search', '')
        ->assertSeeHtml($trackingInterest->interest)
        ->assertSeeHtml($ti2->interest);
    expect($result)->not->toBeNull();
})->group('RF-05');