<?php

use App\Models\User;
use Livewire\Livewire;

use function Pest\Laravel\get;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

it('redirects to the home page if the user is not an admin', function () {
    // Arrange
    $admin = User::factory()->create();

    // Act
    $this->actingAs($admin);

    // Assert
    get(route('users-list'))
        ->assertRedirect('/');
})->group('RF-01');

it('displays a list of users if the user is an admin', function () {
    // Arrange
    $admin = User::factory()->create(['is_admin' => true]);
    $users = User::factory(5)->create();

    // Act
    $this->actingAs($admin);

    // Assert
    Livewire::test('users-table')
        ->assertSee([
            $admin->name,
            $users[0]->name,
            $users[1]->name,
            $users[2]->name,
            $users[3]->name,
            $users[4]->name,
        ])
        ->assertStatus(200);
})->group('RF-01', 'RF-05');

it('can toggle the enterprise status of a user if an admin is performing the action', function () {
    // Arrange
    $admin = User::factory()->create(['is_admin' => true]);
    $user = User::factory()->create(['is_enterprise' => false]);
    $not_admin = User::factory()->create(['is_admin' => false]);


    // Act
    $this->actingAs($not_admin);

    // Assert
    get(route('users-list'))
        ->assertRedirect(route('home'));


    // Act
    $this->actingAs($admin);

    // Assert
    Livewire::test('users-table')
        ->call('toggleEnterprise', $user->id)
        ->assertStatus(200);

    expect($user->fresh()->is_enterprise)->toBeTrue();
})->group('RF-01');

it('can toggle the enabled status of a user if an admin is performing the action', function () {
    // Arrange
    $admin = User::factory()->create(['is_admin' => true]);
    $user = User::factory()->create(['is_enabled' => false]);
    $not_admin = User::factory()->create(['is_admin' => false]);

    // Act
    $this->actingAs($not_admin);

    // Assert
    get(route('users-list'))
        ->assertRedirect(route('home'));

    // Act
    $this->actingAs($admin);

    // Assert
    Livewire::test('users-table')
        ->call('toggleEnabled', $user->id)
        ->assertStatus(200);

    expect($user->fresh()->is_enabled)->toBeTrue();
})->group('RF-01');

it('can filter users', function ($field) {
    // Arrange
    $admin = User::factory()->create(['is_admin' => true]);
    $enabled_user = User::factory()->create([$field => true]);
    $disabled_user = User::factory()->create();

    // Act
    $this->actingAs($admin);

    // Assert
    Livewire::test('users-table')
        ->set('filters', [
            'boolean' => [
                $field => true,
            ],
        ])
        ->assertSee($enabled_user->name)
        ->assertDontSee($disabled_user->name)
        ->assertStatus(200);
})->with([
    'is_enabled',
    'is_enterprise',
])->group('RF-01', 'RF-05');

it('can set a parent for a user it its enabled and enterprise', function () {
    // Arrange
    $admin = User::factory()->create(['is_admin' => true]);
    $child_user = User::factory()->create();
    $parent_user = User::factory()->create([
        'is_enterprise' => true,
        'is_enabled' => true,
    ]);

    // Act
    $this->actingAs($admin);

    // Assert
    Livewire::test('users-table')
        ->call('updateParentAccount', $child_user->id, $parent_user->id)
        ->assertStatus(200);

    expect($child_user->fresh()->is_subaccount_of)->toBe($parent_user->id);
})->group('RF-01');

it('can not set a parent for a user it its not enabled and enterprise', function () {
    // Arrange
    $admin = User::factory()->create(['is_admin' => true]);
    $child_user = User::factory()->create();
    $parent_user = User::factory()->create([
        'is_enterprise' => false,
        'is_enabled' => false,
    ]);

    // Act
    $this->actingAs($admin);

    // Assert
    Livewire::test('users-table')
        ->call('updateParentAccount', $child_user->id, $parent_user->id)
        ->assertStatus(200);

    expect($child_user->fresh()->is_subaccount_of)->not->toBe($parent_user->id);
})->group('RF-01');