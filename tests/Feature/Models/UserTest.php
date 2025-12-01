<?php

use App\Models\TrackingInterest;
use App\Models\User;
use Illuminate\Database\QueryException;

it('can be associated with a tracking interest', function () {
    // Arrange
    $trackingInterest = TrackingInterest::factory()->create();
    $user = User::factory()->create();

    // Act
    $trackingInterest->users()->attach($user);

    // Assert
    expect($trackingInterest->users()->count())->toBe(1);
})->group('RF-01');

it('can access only associated tracking interests', function () {
    // Arrange
    $trackingInterests = TrackingInterest::factory(3)->create();
    $user = User::factory()->create();

    // Act
    $trackingInterests[0]->users()->attach($user);
    $trackingInterests[1]->users()->attach($user);

    // Assert
    expect($user->trackingInterests()->count())->toBe(2);
})->group('RF-01');

it('can be associated with multiple tracking interests', function () {
    // Arrange
    $trackingInterests = TrackingInterest::factory(3)->create();
    $user = User::factory()->create();

    // Act
    $trackingInterests[0]->users()->attach($user);
    $trackingInterests[1]->users()->attach($user);
    $trackingInterests[2]->users()->attach($user);

    // Assert
    expect($user->trackingInterests()->count())->toBe(3);
})->group('RF-01');

it('does not allow setting a non-existent user as parent', function () {
    $user = User::factory()->create(['is_subaccount_of' => 9999]);
    $user->save();
})->expectException(QueryException::class)->group('RF-01');

it('can toggle is_enabled on an already disabled user without error', function () {
    $user = User::factory()->create(['is_enabled' => false]);
    $user->update(['is_enabled' => false]);
    expect($user->refresh()->is_enabled)->toBeFalse();
})->group('RF-01');

it('does not allow users with the same email', function () {
    // Arrange & Act
    User::factory()->create(["email" => "email@test.com"]);
    User::factory()->create(["email" => "email@test.com"]);
})->expectException(Exception::class)->group('RF-01');
