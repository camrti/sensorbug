<?php

use Laravel\Dusk\Browser;

it('basic example', function () {
    $this->browse(function (Browser $browser) {
        $browser->visit('/')
            ->assertSee('Log in');
    });
});
