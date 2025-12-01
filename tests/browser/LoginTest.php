<?php

use App\Models\User;
use Laravel\Dusk\Browser;

it('allows the user to login and redirects him to the dashboard', function () {
    // Arrange
    $user = User::factory()->create();

    // Act & Assert
    $this->browse(function (Browser $browser) use ($user) {
        $browser->visit('/login')
            ->type('email', $user->email)
            ->type('password', 'password')
            ->press('Log in')
            ->pause(1000)
            ->assertPathIs('/dashboard')
            ->assertSee($user->name);

        $browser->logout();
    });

    $user->delete(); // Clean up after test

});

it('the login fails with invalid credentials', function () {
    // Act & Assert
    $this->browse(function (Browser $browser) {
        $browser->visit('/login')
            ->type('email', 'non-existent@email.com')
            ->type('password', 'wrongpassword')
            ->press('Log in')
            ->assertPathIs('/login');
    });
});

