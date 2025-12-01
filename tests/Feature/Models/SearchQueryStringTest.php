<?php

use App\Models\SearchQueryString;
use App\Models\TrackingInterest;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

it('can be created', function () {
    expect(SearchQueryString::count())->toBe(0);

    SearchQueryString::factory()->create();

    expect(SearchQueryString::count())->toBe(1);
})->group('RF-02');

it('can be modified', function () {
    $sqs = SearchQueryString::factory()->create();

    $sqs->search_query = 'New search query';

    expect($sqs->search_query)->toBe('New search query');
})->group('RF-02');

it('can be deleted', function () {
    $sqs = SearchQueryString::factory()->create();

    $sqs->delete();

    expect(SearchQueryString::count())->toBe(0);
})->group('RF-02');

it('can be created for a specific tracking interest', function () {
    $trackingInterest = TrackingInterest::factory()->create();

    $sqs = SearchQueryString::factory()->forTrackingInterest($trackingInterest)->create();

    expect($sqs->tracking_interest_id)->toBe($trackingInterest->id);
})->group('RF-02');

it('can be modified for a specific tracking interest', function () {
    $trackingInterest = TrackingInterest::factory()->create();
    $sqs = SearchQueryString::factory()->forTrackingInterest($trackingInterest)->create();

    $sqs->search_query = 'New search query';

    expect($sqs->search_query)->toBe('New search query');
})->group('RF-02');

it('can be deleted for a specific tracking interest', function () {
    $trackingInterest = TrackingInterest::factory()->create();
    $sqs = SearchQueryString::factory()->forTrackingInterest($trackingInterest)->create();

    $sqs->delete();

    expect(SearchQueryString::count())->toBe(0);
})->group('RF-02');

it('can be associated with a language code', function () {
    $trackingInterest = TrackingInterest::factory()->create();
    $sqs = SearchQueryString::factory()->forTrackingInterest($trackingInterest)->italian()->create();

    expect($sqs->language_code)->toBe('it');
})->group('RF-02');

it('can be associated with an optional source', function () {
    $trackingInterest = TrackingInterest::factory()->create();
    $sqs = SearchQueryString::factory()->forTrackingInterest($trackingInterest)->create();

    $sqs->source = 'google';

    expect($sqs->source)->toBe('google');
})->group('RF-02');

it('does not allow associating a search query string to a non-existent tracking interest', function () {
    $sqs = SearchQueryString::factory()->make(['tracking_interest_id' => 9999]);
        $sqs->save();
})->expectException(Exception::class)->group('RF-02');

it('does not allow duplicate search query strings for the same tracking interest', function () {
    $trackingInterest = TrackingInterest::factory()->create();
    $sqsData = [
        'query_string' => 'duplicate_query',
        'tracking_interest_id' => $trackingInterest->id,
        'language_code' => 'it'
    ];

    SearchQueryString::factory()->create($sqsData);

    expect(fn() => SearchQueryString::factory()->create($sqsData))
        ->toThrow(\Illuminate\Database\QueryException::class);

})->group('RF-02');

