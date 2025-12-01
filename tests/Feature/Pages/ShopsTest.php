<?php

use App\Models\Page;
use App\Models\PageFound;
use App\Models\Shop;
use App\Models\TrackingInterest;
use App\Models\User;
use App\Models\WebDomain;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
use Livewire\Livewire;

it('does not show any shops if tracking interest is not selected', function () {
    // Arrange
    $trackingInterest1 = TrackingInterest::factory()->create();
    $trackingInterest2 = TrackingInterest::factory()->create();
    $user = User::factory()->create();
    $shop1 = Shop::factory()->create();
    $shop2 = Shop::factory()->create();

    $trackingInterest1->users()->attach($user);
    $trackingInterest2->users()->attach($user);

    $page1 = Page::factory()->create(['shop_id' => $shop1->id]);
    $page2 = Page::factory()->create(['shop_id' => $shop2->id]);

    PageFound::factory()->create([
        'tracking_interest_id' => $trackingInterest1->id,
        'page_id' => $page1->id,
    ]);

    PageFound::factory()->create([
        'tracking_interest_id' => $trackingInterest2->id,
        'page_id' => $page2->id,
    ]);

    // Act
    $this->actingAs($user);

    // Assert
    Livewire::test('shops-table')
        ->assertSeeHtml(__('livewire-powergrid::datatable.labels.no_data'))
        ->assertDontSeeHtml($shop1->company_name)
        ->assertDontSeeHtml($shop2->company_name);
})->group('RF-05');

it('does not show any shops for the selected tracking interest if it does not have any records', function () {
    // Arrange
    $trackingInterest1 = TrackingInterest::factory()->create();
    $trackingInterest2 = TrackingInterest::factory()->create();
    $user = User::factory()->create();
    $shop1 = Shop::factory()->create();
    $shop2 = Shop::factory()->create();

    $trackingInterest1->users()->attach($user);
    $trackingInterest2->users()->attach($user);

    $page1 = Page::factory()->create(['shop_id' => $shop1->id]);
    $page2 = Page::factory()->create(['shop_id' => $shop2->id]);

    PageFound::factory()->create([
        'tracking_interest_id' => $trackingInterest2->id,
        'page_id' => $page1->id,
    ]);

    PageFound::factory()->create([
        'tracking_interest_id' => $trackingInterest2->id,
        'page_id' => $page2->id,
    ]);

    // Act
    $this->actingAs($user);

    Session::put('selected_tracking_interest', $trackingInterest1->id);

    // Assert
    Livewire::test('shops-table')
        ->assertSeeHtml(__('livewire-powergrid::datatable.labels.no_data'))
        ->assertDontSeeHtml($shop1->company_name)
        ->assertDontSeeHtml($shop2->company_name);
})->group('RF-05');

it('shows the shops for the tracking interest selected if it has records', function () {
    // Arrange
    $trackingInterest1 = TrackingInterest::factory()->create();
    $trackingInterest2 = TrackingInterest::factory()->create();
    $user = User::factory()->create();
    $shop1 = Shop::factory()->create();
    $shop2 = Shop::factory()->create();

    $trackingInterest1->users()->attach($user);
    $trackingInterest2->users()->attach($user);

    $page1 = Page::factory()->create(['shop_id' => $shop1->id]);

    PageFound::factory()->create([
        'tracking_interest_id' => $trackingInterest1->id,
        'page_id' => $page1->id,
    ]);

    // Act
    $this->actingAs($user);

    Session::put('selected_tracking_interest', $trackingInterest1->id);

    // Assert
    Livewire::test('shops-table')
        ->assertSeeText([
            $shop1->company_name,
            $shop1->domain,
            $shop1->shop_type,
            $shop1->reported_at,
            $shop1->email,
            $shop1->phone_number,
            $shop1->identification_number,
            $shop1->address,
            $shop1->notes,
            $shop1->created_at->format('d/m/Y H:i'),
        ])
        ->assertDontSeeHtml(expectedNoDataText());
})->group('RF-05');

/*
it('can report a shop', function () {
    // Arrange
    $trackingInterest1 = TrackingInterest::factory()->create();
    $trackingInterest2 = TrackingInterest::factory()->create();
    $user = User::factory()->create();
    $shop1 = Shop::factory()->create();
    $shop2 = Shop::factory()->create();

    $trackingInterest1->users()->attach($user);
    $trackingInterest2->users()->attach($user);

    $page1 = Page::factory()->create(['shop_id' => $shop1->id]);
    $page2 = Page::factory()->create(['shop_id' => $shop2->id]);

    PageFound::factory()->create([
        'tracking_interest_id' => $trackingInterest1->id,
        'page_id' => $page1->id,
    ]);

    PageFound::factory()->create([
        'tracking_interest_id' => $trackingInterest1->id,
        'page_id' => $page2->id,
    ]);

    // Act
    $this->actingAs($user);

    Session::put('selected_tracking_interest', $trackingInterest1->id);

    // Assert
    Livewire::test('shops-table')
        ->call('reportShop', $shop1->id)
        ->assertSeeHtml('Segnalato il '.Carbon::parse($shop1->reported_at)->format('d/m/Y H:i'));

    $shop1->refresh();

    expect($shop1->is_reported)->toBeTrue();
})->group('RF-06');
*/

it('shows a newly added shop', function () {
    // Arrange
    $trackingInterest1 = TrackingInterest::factory()->create();
    $trackingInterest2 = TrackingInterest::factory()->create();
    $user = User::factory()->create();
    $shop1 = Shop::factory()->create();
    $shop2 = Shop::factory()->create();

    $trackingInterest1->users()->attach($user);
    $trackingInterest2->users()->attach($user);

    $page1 = Page::factory()->create(['shop_id' => $shop1->id]);

    PageFound::factory()->create([
        'tracking_interest_id' => $trackingInterest1->id,
        'page_id' => $page1->id,
    ]);

    // Act
    $this->actingAs($user);

    Session::put('selected_tracking_interest', $trackingInterest1->id);

    // Assert
    Livewire::test('shops-table')
        ->assertSeeHtml($shop1->company_name);

    // Arrange
    $page2 = Page::factory()->create(['shop_id' => $shop2->id]);

    PageFound::factory()->create([
        'tracking_interest_id' => $trackingInterest1->id,
        'page_id' => $page2->id,
    ]);

    // Assert
    Livewire::test('shops-table')
        ->assertSeeHtml($shop1->company_name)
        ->assertSeeHtml($shop2->company_name);
})->group('RF-07');

it('allows the user to search for a shop by a given field', function (string $field, mixed $value) {
    // Arrange
    $trackingInterest1 = TrackingInterest::factory()->create();
    $trackingInterest2 = TrackingInterest::factory()->create();
    $user = User::factory()->create();

    $shop1 = Shop::factory()->create([$field => $value]);
    $shop2 = Shop::factory()->create(['shop_type' => 'Personal Website']);

    $trackingInterest1->users()->attach($user);
    $trackingInterest2->users()->attach($user);

    $page1 = Page::factory()->create(['shop_id' => $shop1->id]);
    $page2 = Page::factory()->create(['shop_id' => $shop2->id]);

    PageFound::factory()->create([
        'tracking_interest_id' => $trackingInterest1->id,
        'page_id' => $page1->id,
    ]);

    PageFound::factory()->create([
        'tracking_interest_id' => $trackingInterest1->id,
        'page_id' => $page2->id,
    ]);

    // Act
    $this->actingAs($user);

    Session::put('selected_tracking_interest', $trackingInterest1->id);

    // Assert
    Livewire::test('shops-table')
        ->set('search', $value)  // Search for the specific value instead of $shop1->$field
        ->assertSet('search', $value)
        ->assertSeeHtml($shop1->company_name)
        ->assertDontSeeHtml($shop2->company_name);
})->with([
    ['company_name', 'test company name'],
    ['shop_type', 'OTA'],
    ['email', 'test@example.com'],
    ['phone_number', '1234567890'],
    ['identification_number', '1234567890'],
    ['address', 'test address'],
    ['notes', 'test notes'],
])->group('RF-05');

it('allows the user to search for a shop by domain', function () {
    // Arrange
    $trackingInterests = TrackingInterest::factory()->count(2)->create();
    $user = User::factory()->create();
    $shopWithDomain1 = Shop::factory()->create();
    $shopWithDomain2 = Shop::factory()->create();

    $domain1 = WebDomain::factory()->create();
    $domain2 = WebDomain::factory()->create();

    $domain1->shops()->attach($shopWithDomain1);
    $domain2->shops()->attach($shopWithDomain2);

    $trackingInterests->each(fn ($interest) => $interest->users()->attach($user));

    $page1 = Page::factory()->create(['shop_id' => $shopWithDomain1->id]);
    $page2 = Page::factory()->create(['shop_id' => $shopWithDomain2->id]);

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

    // Store the company names to verify against
    $companyName1 = $shopWithDomain1->company_name;
    $companyName2 = $shopWithDomain2->company_name;

    // Assert
    Livewire::test('shops-table')
        ->set('search', $domain1->domain)
        ->assertSeeHtml($companyName1)
        ->assertDontSeeHtml($companyName2);
})->group('RF-05');

it('filtering with non-existent shop_type returns no results', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $result = Livewire::test('shops-table')
        ->set('filters', [
            'select' => [
                'shop_type' => 'NonExistentType',
            ],
        ])
        ->assertSeeHtml(expectedNoDataText());
    expect($result)->not->toBeNull();
})->group('RF-05');
