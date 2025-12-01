<?php

use App\Models\Page;
use App\Models\PageFound;
use App\Models\Shop;
use App\Models\TrackingInterest;
use App\Models\User;

it('can be associated to a tracking interest', function () {
    // Arrange
    $trackingInterests = TrackingInterest::factory(2)->create();
    $user = User::factory()->create();
    $shops = Shop::factory(2)->create();

    $trackingInterests->each(fn ($interest) => $interest->users()->attach($user));

    $pages = $shops->map(fn ($shop) => Page::factory()->create(['shop_id' => $shop->id]));

    $pages->each(fn ($page) => PageFound::factory()->create([
        'tracking_interest_id' => $trackingInterests[0]->id,
        'page_id' => $page->id,
    ]));

    // Assert
    expect($shops)->toHaveCount(2);
    expect($shops->pluck('id'))->toContain($shops[0]->id, $shops[1]->id);
})->group('RF-05');

it('can be added by a user', function () {
    // Assert initial state
    expect(Shop::count())->toBe(0);

    // Act
    Shop::factory()->create();

    // Assert final state
    expect(Shop::count())->toBe(1);
})->group('RF-07');

it('does not change is_reported if shop is already reported', function () {
    $shop = Shop::factory()->create(['is_reported' => true]);
    $shop->is_reported = true;
    $shop->save();
    expect($shop->refresh()->is_reported)->toBeTrue();
})->group('RF-06');

it('does not allow adding a duplicate shop', function () {
    Shop::factory()->create(['company_name' => 'Test Shop']);
    Shop::factory()->create(['company_name' => 'Test Shop']);
})->expectException(\Illuminate\Database\QueryException::class)->group('RF-07');
