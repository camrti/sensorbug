<?php

use App\Models\User;
use Laravel\Dusk\Browser;

it('allows the user to sign up and redirects him to the dashboard', function () {
    // Act & Assert
    $this->browse(function (Browser $browser) {
        $browser->visit('/register')
            ->type('name', 'Test User')
            ->type('email', 'test.user@test.com')
            ->type('password', 'password')
            ->type('password_confirmation', 'password')
            ->press('Create account')
            ->pause(500)
            ->assertPathIs('/dashboard');
        $user = User::where('email', 'test.user@test.com')->first();

        $browser->assertSee($user->name);

        $user->delete(); // Clean up after test
    });
});

it('the sign up fails with invalid data', function () {
    // Act & Assert
    $this->browse(function (Browser $browser) {
        $browser->visit('/register')
            ->type('name', '')
            ->type('email', 'invalid-email')
            ->type('password', 'short')
            ->type('password_confirmation', 'short')
            ->press('Create account')
            ->assertPathIs('/register');
    });
});
