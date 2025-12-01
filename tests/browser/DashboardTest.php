<?php

use App\Models\Page;
use App\Models\PageFound;
use App\Models\SearchQueryString;
use App\Models\Seller;
use App\Models\Shop;
use App\Models\TrackingInterest;
use App\Models\User;
use App\Models\WebDomain;
use Laravel\Dusk\Browser;

it('redirects unauthenticated users to the login page', function () {
    // Act & Assert
    $this->browse(function (Browser $browser) {
        $browser->visit('/dashboard')
            ->assertPathIs('/login')
            ->assertSee('Log in');
    });
});

it('allows to the user to logout and redirects him to the home page', function () {
    // Arrange
    $user = User::factory()->create();

    // Act & Assert
    $this->browse(function (Browser $browser) use ($user) {
        $browser->loginAs($user)
            ->visit('/dashboard')
            ->assertPathIs('/dashboard')
            ->assertSee('Dashboard')
            ->assertSee($user->name)
            ->press($user->name)
            ->assertSee('Log Out')
            ->press('Log Out')
            ->assertPathIs('/')
            ->assertSee('Log in');
    });

    $user->delete(); // Clean up after test
});

it('allows any authenticated user to view different pages', function ($page, $element) {
    // Arrange
    $user = User::factory()->create();

    // Act & Assert
    $this->browse(function (Browser $browser) use ($user, $page, $element) {
        $browser->loginAs($user)
            ->visit('/dashboard')
            ->assertPathIs('/dashboard')
            ->clickLink($element)
            ->pause(500)
            ->assertPathIs("/$page");

        $browser->logout();
    });

    $user->delete(); // Clean up after test
})->with([
    ['sellers', 'Venditori'],
    ['shops', 'Piattaforme di vendita'],
    ['pages', 'Pagine'],
    ['sqs', 'Stringhe di ricerca'],
]);

it('allows an admin user to view users list', function () {
    // Arrange
    $user = User::factory()->create(['is_admin' => true]);

    // Act & Assert
    $this->browse(function (Browser $browser) use ($user) {
        $browser->loginAs($user)
            ->visit('/dashboard')
            ->assertPathIs('/dashboard')
            ->clickLink('Lista utenti')
            ->pause(500)
            ->assertPathIs('/users-list');

        $browser->logout();
    });

    $user->delete(); // Clean up after test
});

it('allows the user to select a tracking interest and change the interface accordingly on the shops page', function () {
    // Arrange
    $trackingInterest = TrackingInterest::factory()->create();
    $user = User::factory()->create();

    $shops = Shop::factory(3)->create();

    $shops->each(function ($shop) {
        $domain = WebDomain::factory()->create();
        $shop->webDomains()->save($domain);
    });

    $trackingInterest->users()->attach($user);

    $domain = $shops->first()->webDomains->first();

    // Act & Assert
    $this->browse(function (Browser $browser) use ($user, $trackingInterest, $domain) {
        $browser->loginAs($user)
            ->visit('/shops')
            ->assertPathIs('/shops')
            ->press('Seleziona interesse')
            ->pause(200)
            ->press($trackingInterest->interest)
            ->pause(200)
            ->assertSee($domain);

        $browser->visit('/logout');
    });

    // Clean up after test
    $user->delete();
    $trackingInterest->delete();
    $shops->each(function ($shop) {
        $shop->delete();
    });
});

it('allows the user to select a tracking interest and change the interface accordingly on the pages page', function () {
    // Arrange
    $trackingInterest = TrackingInterest::factory()->create();
    $user = User::factory()->create();

    $pages = Page::factory(3)->create();

    $pages->each(function ($page) {
        $domain = WebDomain::factory()->create();
        $shop = Shop::factory()->create();
        $shop->webDomains()->save($domain);
        $page->shop()->associate($shop);
        $page->save();
    });

    $trackingInterest->users()->attach($user);

    $domain = $pages->first()->shop()->first()->webDomains->first();

    // Act & Assert
    $this->browse(function (Browser $browser) use ($user, $trackingInterest, $domain) {
        $browser->loginAs($user)
            ->visit('/pages')
            ->assertPathIs('/pages')
            ->press('Seleziona interesse')
            ->pause(200)
            ->press($trackingInterest->interest)
            ->pause(200)
            ->assertSee($domain);

        $browser->logout();
    });

    $user->delete();
    $trackingInterest->delete();
    $pages->each(function ($page) {
        $page->delete();
    });

});

it('allows the user to select a tracking interest and change the interface accordingly on the sqs page', function () {
    // Arrange
    $trackingInterest = TrackingInterest::factory()->create();
    $user = User::factory()->create();

    $sqs = SearchQueryString::factory(3)->create([
        'tracking_interest_id' => $trackingInterest->id,
    ]);

    $trackingInterest->users()->attach($user);

    $query_string = $sqs->first()->query_string;

    // Act & Assert
    $this->browse(function (Browser $browser) use ($user, $trackingInterest, $query_string) {
        $browser->loginAs($user)
            ->visit('/sqs')
            ->assertPathIs('/sqs')
            ->press('Seleziona interesse')
            ->pause(200)
            ->press($trackingInterest->interest)
            ->pause(200)
            ->assertSee($query_string);

        $browser->logout();
    });

    // Clean up after test
    $user->delete();
    $trackingInterest->delete();
    $sqs->each(function ($sq) {
        $sq->delete();
    });
});

it('allows the user to select a tracking interest and change the interface accordingly on the sellers page', function () {
    // Arrange
    $trackingInterest = TrackingInterest::factory()->create();
    $user = User::factory()->create();
    $seller = Seller::factory()->create();
    $page = Page::factory()->create(['seller_id' => $seller->id]);

    $trackingInterest->users()->attach($user);

    $pf = PageFound::factory()->create([
        'tracking_interest_id' => $trackingInterest->id,
        'page_id' => $page->id,
    ]);

    // Act & Assert
    $this->browse(function (Browser $browser) use ($user, $trackingInterest, $seller) {
        $browser->loginAs($user)
            ->visit('/sellers')
            ->assertPathIs('/sellers')
            ->press('Seleziona interesse')
            ->pause(200)
            ->press($trackingInterest->interest)
            ->pause(200)
            ->assertSee($seller->name);

        $browser->logout();
    });

    // Clean up after test
    $user->delete();
    $trackingInterest->delete();
    $seller->delete();
    $page->delete();
    $pf->delete();
});
