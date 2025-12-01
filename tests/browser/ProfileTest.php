<?php

use App\Models\User;
use Laravel\Dusk\Browser;

it('allows the user to update their profile', function () {
    // Arrange
    $user = User::factory()->create([
        'name' => 'Old Name',
        'email' => 'old.user@test.com',
    ]);

    // Act & Assert
    $this->browse(function (Browser $browser) use ($user) {
        $browser->loginAs($user)
            ->visit('/settings/profile')
            ->assertPathIs('/settings/profile')
            ->assertSee('Settings')
            ->type('name', 'New Name')
            ->type('email', 'new.user@test.com')
            ->press('Save')
            ->pause(500)
            ->assertPathIs('/settings/profile')
            ->assertSee('Saved.')
            ->visit('/dashboard')
            ->assertSee('New Name');

        $user->delete(); // Clean up after test
    });
});

it('allows the user to delete their account', function () {
    // Arrange
    $user = User::factory()->create([
        'name' => 'User to Delete',
        'email' => 'user.to.delete@test.com',
    ]);

    // Act & Assert
    $this->browse(function (Browser $browser) use ($user) {
        $browser->loginAs($user)
            ->visit('/settings/profile')
            ->assertPathIs('/settings/profile')
            ->assertSee('Settings')
            ->press('Delete account')
            ->pause(500)
            ->assertSee('Are you sure you want to delete your account?')
            ->type('password', 'password')
            ->click('@delete-user-button-modal')
            ->pause(2000)
            ->assertPathIs('/')
            ->assertSee('Log in');
    });

    $user ?? $user->delete();
})->group('test');
