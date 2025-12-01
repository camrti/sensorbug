<?php

use App\Models\Page;
use App\Models\PageFound;
use App\Models\Shop;
use App\Models\TrackingInterest;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

it('can be associated to a tracking interest', function () {
    $trackingInterest = TrackingInterest::factory()->create();
    $page = Page::factory()->create();

    PageFound::factory()->create([
        'page_id' => $page->id,
        'tracking_interest_id' => $trackingInterest->id,
    ]);

    $this->assertEquals($trackingInterest->id, $page->trackingInterests->first()->id);
})->group('RF-05');

it('can be associated to many tracking interests', function () {
    $trackingInterests = TrackingInterest::factory(2)->create();
    $page = Page::factory()->create();

    foreach ($trackingInterests as $trackingInterest) {
        PageFound::factory()->create([
            'page_id' => $page->id,
            'tracking_interest_id' => $trackingInterest->id,
        ]);
    }

    $this->assertCount(2, $page->trackingInterests);
})->group('RF-05');

it('can be added to a shop', function () {
    $shop = Shop::factory()->create();
    $page = Page::factory()->create();

    $page->shop()->associate($shop);

    $this->assertEquals($shop->id, $page->shop_id);
})->group('RF-07');

it('can be added', function () {
    Page::factory()->create();

    $this->assertCount(1, Page::all());
})->group('RF-05');

it('does not allow duplicates', function () {
    $page = Page::factory()->create();
    Page::factory()->make(['page_url' => $page->page_url])->save();

})->expectException(Exception::class)->group('RF-05');
