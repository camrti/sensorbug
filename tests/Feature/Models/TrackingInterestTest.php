<?php

use App\Models\TrackingInterest;
use App\Models\User;

it('can be created', function () {
    $trackingInterest = TrackingInterest::factory()->create();

    expect(TrackingInterest::count())->toBe(1);
})->group('RF-01');

it('can be modified', function () {
    $trackingInterest = TrackingInterest::factory()->create();

    $trackingInterest->update(['interest' => 'New interest']);

    expect($trackingInterest->refresh()->interest)->toBe('New interest');
})->group('RF-01');

it('can be activated', function () {
    $trackingInterest = TrackingInterest::factory()->create(['is_active' => false]);

    $trackingInterest->update(['is_active' => true]);

    expect($trackingInterest->refresh()->is_active)->toBeTrue();
})->group('RF-01');

it('can be deactivated', function () {
    $trackingInterest = TrackingInterest::factory()->create(['is_active' => true]);

    $trackingInterest->update(['is_active' => false]);

    expect($trackingInterest->refresh()->is_active)->toBeFalse();
})->group('RF-01');

it('can have many users associated to it', function () {
    $trackingInterest = TrackingInterest::factory()->create();
    $users = User::factory(6)->create();

    $trackingInterest->users()->attach($users->pluck('id'));

    expect($trackingInterest->users()->count())->toBe(6);
})->group('RF-01');

it('can have many users associated to it with different roles', function () {
    $trackingInterest = TrackingInterest::factory()->create();
    $users = User::factory(2)->create();

    $trackingInterest->users()->attach($users->first()->id, ['is_owner' => true, 'is_creator' => false]);
    $trackingInterest->users()->attach($users->last()->id, ['is_owner' => false, 'is_creator' => true]);

    expect($trackingInterest->users()->count())->toBe(2);
})->group('RF-01');

it('can deactivate an already deactivated tracking interest without error', function () {
    $ti = TrackingInterest::factory()->create(['is_active' => false]);
    $ti->update(['is_active' => false]);
    expect($ti->refresh()->is_active)->toBeFalse();
})->group('RF-01');

it('does not duplicate user association to a tracking interest', function () {
    $ti = TrackingInterest::factory()->create();
    $user = User::factory()->create();
    $ti->users()->attach($user);

    expect(fn() => $ti->users()->attach($user))
        ->toThrow(\Illuminate\Database\QueryException::class);
})->group('RF-01');

