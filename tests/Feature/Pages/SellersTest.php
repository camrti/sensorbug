<?php

use App\Models\Page;
use App\Models\PageFound;
use App\Models\Seller;
use App\Models\TrackingInterest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
use Livewire\Livewire;

it('does not show any sellers if no tracking interest is selected', function () {
    // Arrange
    $user = User::factory()->create();
    $trackingInterests = TrackingInterest::factory(2)->create();
    $sellers = Seller::factory(2)->create();

    $trackingInterests[0]->users()->attach($user);
    $trackingInterests[1]->users()->attach($user);

    $page1 = Page::factory()->create(['seller_id' => $sellers[0]->id]);
    $page2 = Page::factory()->create(['seller_id' => $sellers[1]->id]);

    PageFound::factory()->create([
        'tracking_interest_id' => $trackingInterests[0]->id,
        'page_id' => $page1->id,
    ]);

    PageFound::factory()->create([
        'tracking_interest_id' => $trackingInterests[1]->id,
        'page_id' => $page2->id,
    ]);

    // Act
    $this->actingAs($user);

    // Assert
    Livewire::test('sellers-table')
        ->assertSeeHtml(expectedNoDataText())
        ->assertDontSeeHtml($sellers[0]->name)
        ->assertDontSeeHtml($sellers[1]->name);
})->group('RF-05');

it('does not show any sellers for the selected tracking interest if it does not have any records', function () {
    // Arrange
    $user = User::factory()->create();
    $trackingInterests = TrackingInterest::factory(2)->create();
    $sellers = Seller::factory(2)->create();

    $trackingInterests[0]->users()->attach($user);
    $trackingInterests[1]->users()->attach($user);

    $page1 = Page::factory()->create(['seller_id' => $sellers[0]->id]);
    $page2 = Page::factory()->create(['seller_id' => $sellers[1]->id]);

    PageFound::factory()->create([
        'tracking_interest_id' => $trackingInterests[1]->id,
        'page_id' => $page1->id,
    ]);

    PageFound::factory()->create([
        'tracking_interest_id' => $trackingInterests[1]->id,
        'page_id' => $page2->id,
    ]);

    // Act
    $this->actingAs($user);

    Session::put('selected_tracking_interest', $trackingInterests[0]->id);

    // Assert
    Livewire::test('sellers-table')
        ->assertSeeHtml(expectedNoDataText())
        ->assertDontSeeHtml($sellers[0]->name);
})->group('RF-05');

it('shows the sellers for the tracking interest selected if it has records', function () {
    // Arrange
    $user = User::factory()->create();
    $trackingInterests = TrackingInterest::factory(2)->create();
    $sellers = Seller::factory(2)->create();

    $trackingInterests[0]->users()->attach($user);
    $trackingInterests[1]->users()->attach($user);

    $page1 = Page::factory()->create(['seller_id' => $sellers[0]->id]);

    PageFound::factory()->create([
        'tracking_interest_id' => $trackingInterests[0]->id,
        'page_id' => $page1->id,
    ]);

    // Act
    $this->actingAs($user);

    Session::put('selected_tracking_interest', $trackingInterests[0]->id);

    // Assert
    Livewire::test('sellers-table')
        ->assertSeeHtml($sellers[0]->name)
        ->assertSeeHtml($sellers[0]->foundOnDomain->domain)
        ->assertDontSeeHtml(expectedNoDataText());
})->group('RF-05');

/*
it('can report a seller', function () {
    // Arrange
    $user = User::factory()->create();
    $trackingInterests = TrackingInterest::factory(2)->create();
    $sellers = Seller::factory(2)->create();

    $trackingInterests[0]->users()->attach($user);
    $trackingInterests[1]->users()->attach($user);

    $page1 = Page::factory()->create(['seller_id' => $sellers[0]->id]);
    $page2 = Page::factory()->create(['seller_id' => $sellers[1]->id]);

    PageFound::factory()->create([
        'tracking_interest_id' => $trackingInterests[0]->id,
        'page_id' => $page1->id,
    ]);

    PageFound::factory()->create([
        'tracking_interest_id' => $trackingInterests[0]->id,
        'page_id' => $page2->id,
    ]);

    // Act
    $this->actingAs($user);

    Session::put('selected_tracking_interest', $trackingInterests[0]->id);

    // Assert
    Livewire::test('sellers-table')
        ->call('reportSeller', $sellers[0]->id)
        ->assertSeeHtml('Segnalato il '.Carbon::parse($sellers[0]->reported_at)->format('d/m/Y H:i'));

    $sellers[0]->refresh();

    expect($sellers[0]->is_reported)->toBeTrue();
})->group('RF-06');
*/

it('allows the user to search for a seller by found on domain', function () {
    // Arrange
    $user = User::factory()->create();
    $trackingInterests = TrackingInterest::factory(2)->create();
    $sellers = Seller::factory(2)->create();

    $trackingInterests[0]->users()->attach($user);
    $trackingInterests[1]->users()->attach($user);

    $page1 = Page::factory()->create(['seller_id' => $sellers[0]->id]);
    $page2 = Page::factory()->create(['seller_id' => $sellers[1]->id]);

    PageFound::factory()->create([
        'tracking_interest_id' => $trackingInterests[0]->id,
        'page_id' => $page1->id,
    ]);

    PageFound::factory()->create([
        'tracking_interest_id' => $trackingInterests[0]->id,
        'page_id' => $page2->id,
    ]);

    // Act
    $this->actingAs($user);

    Session::put('selected_tracking_interest', $trackingInterests[0]->id);

    // Assert
    Livewire::test('sellers-table')
        ->set('search', $sellers[0]->foundOnDomain->domain)
        ->assertSee($sellers[0]->name)
        ->assertDontSee($sellers[1]->name);
})->group('RF-05');