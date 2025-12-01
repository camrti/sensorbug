<?php

use App\Models\SearchQueryString;
use App\Models\TrackingInterest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;
use Livewire\Livewire;

it('does not show any search query strings if no tracking interest is selected', function () {
    // Arrange
    $trackingInterests = TrackingInterest::factory(2)->create();
    $user = User::factory()->create();
    $sqsOne = SearchQueryString::factory()->forTrackingInterest($trackingInterests[0])->italian()->create(['query_string' => 'scarpa sportiva']);
    $sqsTwo = SearchQueryString::factory()->forTrackingInterest($trackingInterests[1])->italian()->create(['query_string' => 'borsa casual']);

    $trackingInterests->each(fn($interest) => $interest->users()->attach($user));

    // Act
    $this->actingAs($user);

    // Assert
    Livewire::test('sqs-table')
        ->assertSeeHtml(expectedNoDataText())
        ->assertDontSeeHtml($sqsOne->query_string)
        ->assertDontSeeHtml($sqsTwo->query_string);
})->group('RF-02', 'RF-05');

it('shows the search query strings for the selected tracking interest if it has records', function () {
    // Arrange
    $trackingInterests = TrackingInterest::factory(2)->create();
    $user = User::factory()->create();
    $sqsOne = SearchQueryString::factory()->forTrackingInterest($trackingInterests[0])->italian()->create([
        'query_string' => 'unique-test-query-one-xyz123',
    ]);
    $sqsTwo = SearchQueryString::factory()->forTrackingInterest($trackingInterests[1])->italian()->create([
        'query_string' => 'unique-test-query-two-abc456',
    ]);

    $sqsOne->latestSearchVolume()->create([
        'volume' => 10,
        'from_date' => Carbon::now()->subMonth(),
        'to_date' => Carbon::now(),
    ]);
    $sqsTwo->latestSearchVolume()->create([
        'volume' => 20,
        'from_date' => Carbon::now(),
        'to_date' => Carbon::now(),
    ]);

    $trackingInterests->each(fn($interest) => $interest->users()->attach($user));

    // Act
    $this->actingAs($user);
    Session::put('selected_tracking_interest', $trackingInterests[0]->id);

    // Assert
    Livewire::test('sqs-table')
        ->assertSeeText([
            $sqsOne->query_string,
            $sqsOne->search_intent,
            $sqsOne->language_code,
            $sqsOne->source,
            $sqsOne->latestSearchVolume->to_date->format('d/m/Y'),
            $sqsOne->latestSearchVolume->from_date->format('d/m/Y'),
            $sqsOne->latestSearchVolume->volume,
            $sqsOne->created_at->format('Y-m-d H:i:s'),
        ])
        ->assertDontSeeHtml(expectedNoDataText())
        ->assertDontSeeHtml($sqsTwo->query_string);
})->group('RF-02', 'RF-05');

it('does not show any search query strings for the selected tracking interest if it does not have any records', function () {
    // Arrange
    $trackingInterests = TrackingInterest::factory(2)->create();
    $user = User::factory()->create();
    $sqsOne = SearchQueryString::factory()->forTrackingInterest($trackingInterests[0])->italian()->create(['query_string' => 'telefono moderno']);
    $sqsTwo = SearchQueryString::factory()->forTrackingInterest($trackingInterests[0])->italian()->create(['query_string' => 'vestito blu']);

    $sqsOne->latestSearchVolume()->create([
        'volume' => 10,
        'from_date' => Carbon::now()->subMonth(),
        'to_date' => Carbon::now(),
    ]);
    $sqsTwo->latestSearchVolume()->create([
        'volume' => 20,
        'from_date' => Carbon::now()->subMonth(),
        'to_date' => Carbon::now(),
    ]);

    $trackingInterests->each(fn($interest) => $interest->users()->attach($user));

    // Act
    $this->actingAs($user);
    Session::put('selected_tracking_interest', $trackingInterests[1]->id);

    // Assert
    Livewire::test('sqs-table')
        ->assertSeeHtml(expectedNoDataText())
        ->assertDontSeeHtml($sqsOne->query_string)
        ->assertDontSeeHtml($sqsTwo->query_string);
})->group('RF-02', 'RF-05');

it('allows the user to search for a search query string by a given field', function ($field, $value) {
    // Arrange
    $trackingInterest = TrackingInterest::factory()->create();
    $user = User::factory()->create();
    $sqsMatch = SearchQueryString::factory()->forTrackingInterest($trackingInterest)->italian()->create([
        $field => $value,
    ]);
    $sqsNonMatch = SearchQueryString::factory()->forTrackingInterest($trackingInterest)->italian()->create([
        'search_intent' => 'non_matching_intent',
        'query_string' => 'non_matching_query',
        'language_code' => 'xx',
        'source' => 'non_matching_source',
    ]);

    $sqsMatch->latestSearchVolume()->create([
        'volume' => 10,
        'from_date' => Carbon::now()->subMonth(),
        'to_date' => Carbon::now(),
    ]);
    $sqsNonMatch->latestSearchVolume()->create([
        'volume' => 20,
        'from_date' => Carbon::now()->subMonth(),
        'to_date' => Carbon::now(),
    ]);

    $trackingInterest->users()->attach($user);

    // Act
    $this->actingAs($user);
    Session::put('selected_tracking_interest', $trackingInterest->id);

    // Assert
    Livewire::test('sqs-table')
        ->set('search', $sqsMatch->$field)
        ->assertSeeHtml($sqsMatch->query_string)
        ->assertDontSeeHtml($sqsNonMatch->query_string);
})->with([
    ['search_intent', 'test search intent'],
    ['query_string', 'test query string'],
    ['language_code', 'test'],
    ['source', 'test'],
])->group('RF-02', 'RF-05');