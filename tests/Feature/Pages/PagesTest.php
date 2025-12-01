<?php

use App\Models\Page;
use App\Models\PageFound;
use App\Models\Seller;
use App\Models\Shop;
use App\Models\TrackingInterest;
use App\Models\User;
use App\Models\WebDomain;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
use Livewire\Livewire;

it('does not show any pages if no tracking interest is selected', function () {
    $trackingInterests = TrackingInterest::factory(2)->create();
    $user = User::factory()->create();
    $sellers = Seller::factory(2)->create();

    $trackingInterests->each(fn ($interest) => $interest->users()->attach($user));

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

    $this->actingAs($user);

    Livewire::test('pages-table')
        ->assertSeeHtml(expectedNoDataText())
        ->assertDontSeeHtml($page1->page_url)
        ->assertDontSeeHtml($page2->page_url);
})->group('RF-05');

it('shows pages for selected tracking interest if it has records', function () {
    $trackingInterests = TrackingInterest::factory(2)->create();
    $user = User::factory()->create();
    $sellers = Seller::factory(2)->create();

    $trackingInterests->each(fn ($interest) => $interest->users()->attach($user));

    $page = Page::factory()->create(['seller_id' => $sellers[0]->id]);

    PageFound::factory()->create([
        'tracking_interest_id' => $trackingInterests[0]->id,
        'page_id' => $page->id,
    ]);

    $this->actingAs($user);
    Session::put('selected_tracking_interest', $trackingInterests[0]->id);

    Livewire::test('pages-table')
        ->assertSee([
            $page->page_url,
            $page->shop->shop_type,
            $page->whitelist_class,
            $page->reported_at,
            $page->is_selling_page,
            $page->currently_sells,
            $page->notes,
            $page->created_at->format('Y-m-d H:i:s'),
        ])
        ->assertDontSeeHtml(expectedNoDataText());
})->group('RF-05');

it('does not show pages for selected tracking interest if it has no records', function () {
    $trackingInterests = TrackingInterest::factory(2)->create();
    $user = User::factory()->create();
    $sellers = Seller::factory(2)->create();

    $trackingInterests->each(fn ($interest) => $interest->users()->attach($user));

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

    $this->actingAs($user);
    Session::put('selected_tracking_interest', $trackingInterests[0]->id);

    Livewire::test('pages-table')
        ->assertDontSeeHtml($page1->page_url)
        ->assertDontSeeHtml($page2->page_url)
        ->assertSeeHtml(expectedNoDataText());
})->group('RF-05');

/*
it('can report a page', function () {
    $trackingInterests = TrackingInterest::factory(2)->create();
    $user = User::factory()->create();
    $sellers = Seller::factory(2)->create();

    $trackingInterests->each(fn ($interest) => $interest->users()->attach($user));

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

    $this->actingAs($user);
    Session::put('selected_tracking_interest', $trackingInterests[0]->id);

    Livewire::test('pages-table')
        ->call('reportPage', $page1->id)
        ->assertSeeHtml('Segnalato il '.Carbon::parse($page1->reported_at)->format('d/m/Y H:i'));

    $page1->refresh();
    expect($page1->is_reported)->toBeTrue();
})->group('RF-06');
*/

it('has a URL to the page', function () {
    $trackingInterests = TrackingInterest::factory(2)->create();
    $user = User::factory()->create();
    $sellers = Seller::factory(2)->create();

    $trackingInterests->each(fn ($interest) => $interest->users()->attach($user));

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

    $this->actingAs($user);
    Session::put('selected_tracking_interest', $trackingInterests[0]->id);

    Livewire::test('pages-table')
        ->assertSeeHtml($page1->page_url);
})->group('RF-05');

it('can be filtered by shop type', function () {
    $trackingInterests = TrackingInterest::factory(2)->create();
    $user = User::factory()->create();
    $shopOta = Shop::factory()->create(['shop_type' => 'OTA']);
    $shopPersonal = Shop::factory()->create(['shop_type' => 'Personal Website']);

    $trackingInterests->each(fn ($interest) => $interest->users()->attach($user));

    $pageOta = Page::factory()->create(['shop_id' => $shopOta->id]);
    $pagePersonal = Page::factory()->create(['shop_id' => $shopPersonal->id]);

    PageFound::factory()->create([
        'tracking_interest_id' => $trackingInterests[0]->id,
        'page_id' => $pageOta->id,
    ]);

    PageFound::factory()->create([
        'tracking_interest_id' => $trackingInterests[0]->id,
        'page_id' => $pagePersonal->id,
    ]);

    $this->actingAs($user);
    Session::put('selected_tracking_interest', $trackingInterests[0]->id);

    Livewire::test('pages-table')
        ->set('filters', [
            'select' => [
                'shops' => [
                    'shop_type' => 'OTA',
                ],
            ],
        ])
        ->assertSeeHtml($pageOta->page_url)
        ->assertDontSeeHtml($pagePersonal->page_url);
})->group('RF-05');

it('shows a newly added page', function () {
    // Arrange
    $trackingInterests = TrackingInterest::factory(2)->create();
    $user = User::factory()->create();
    $sellers = Seller::factory(2)->create();

    foreach ($trackingInterests as $interest) {
        $interest->users()->attach($user);
    }

    $firstPage = Page::factory()->create(['seller_id' => $sellers[0]->id]);

    PageFound::factory()->create([
        'tracking_interest_id' => $trackingInterests[0]->id,
        'page_id' => $firstPage->id,
    ]);

    // Act
    $this->actingAs($user);
    Session::put('selected_tracking_interest', $trackingInterests[0]->id);

    // Assert
    Livewire::test('pages-table')
        ->assertSeeHtml($firstPage->page_url);

    // Arrange
    $secondPage = Page::factory()->create(['seller_id' => $sellers[1]->id]);
    PageFound::factory()->create([
        'tracking_interest_id' => $trackingInterests[0]->id,
        'page_id' => $secondPage->id,
    ]);

    // Assert
    Livewire::test('pages-table')
        ->assertSeeHtml($firstPage->page_url)
        ->assertSeeHtml($secondPage->page_url);
})->group('RF-07');

it('allows the user to search for a page by platform domain', function () {
    // Arrange
    $trackingInterests = TrackingInterest::factory(2)->create();
    $user = User::factory()->create();
    $shops = Shop::factory(2)->create();

    foreach ($trackingInterests as $interest) {
        $interest->users()->attach($user);
    }

    $shops->each(function ($shop) {
        $shop->webDomains()->saveMany(WebDomain::factory(2)->make());
    });

    $firstPage = Page::factory()->create(['shop_id' => $shops[0]->id]);
    $secondPage = Page::factory()->create(['shop_id' => $shops[1]->id]);

    PageFound::factory()->create([
        'tracking_interest_id' => $trackingInterests[0]->id,
        'page_id' => $firstPage->id,
    ]);

    PageFound::factory()->create([
        'tracking_interest_id' => $trackingInterests[0]->id,
        'page_id' => $secondPage->id,
    ]);

    // Act
    $this->actingAs($user);
    Session::put('selected_tracking_interest', $trackingInterests[0]->id);

    // Assert
    Livewire::test('pages-table')
        ->set('search', $firstPage->shop->webDomains->first()->domain)
        ->assertSeeHtml($firstPage->page_url)
        ->assertDontSeeHtml($secondPage->page_url);
})->group('RF-05');
