<?php

declare(strict_types=1);

use App\Enums\PriceRange;
use App\Models\Place;
use App\Models\RestaurantList;
use App\Models\User;
use Livewire\Volt\Volt;

test('authenticated user can view dashboard with their lists', function (): void {
  $user = User::factory()->create();
  $list = RestaurantList::factory()->create(['user_id' => $user->id, 'name' => 'Lista da Galera']);

  $this->actingAs($user)
    ->get(route('dashboard'))
    ->assertOk()
    ->assertSee('Lista da Galera');
});

test('user can create a new list via livewire component', function (): void {
  $user = User::factory()->create();

  $this->actingAs($user);

  Volt::test('restaurant-lists.index')
    ->set('name', 'Pizzarias Favoritas')
    ->set('description', 'Melhores pizzas da cidade')
    ->call('createList')
    ->assertHasNoErrors();

  expect(RestaurantList::where('name', 'Pizzarias Favoritas')->exists())->toBeTrue();
});

test('user can join a list through invite code route', function (): void {
  $owner = User::factory()->create();
  $member = User::factory()->create();

  $list = RestaurantList::factory()->create([
    'user_id' => $owner->id,
    'name' => 'Lista Privada do Dono',
  ]);

  $this->actingAs($member)
    ->get(route('lists.join', ['code' => $list->invite_code]))
    ->assertRedirect(route('lists.show', $list));

  expect($list->members()->where('users.id', $member->id)->exists())->toBeTrue();
});

test('user can add place and toggle visited in the list component', function (): void {
  $user = User::factory()->create();
  $list = RestaurantList::factory()->create(['user_id' => $user->id]);

  $this->actingAs($user);

  Volt::test('restaurant-lists.show', ['restaurantList' => $list])
    ->set('placeName', 'Cantina do Mario')
    ->set('placeAddress', 'Rua das Flores, 123')
    ->set('placePriceRange', PriceRange::Moderate->value)
    ->set('placeDescription', 'Pedir lasagna')
    ->call('savePlace')
    ->assertHasNoErrors();

  $place = Place::where('name', 'Cantina do Mario')->first();
  expect($place)->not->toBeNull()
    ->and($place->visited)->toBeFalse();

  // Toggle visited
  Volt::test('restaurant-lists.show', ['restaurantList' => $list])
    ->call('toggleVisited', $place->id);

  expect($place->fresh()->visited)->toBeTrue();
});

test('user can select a winner in the list component', function (): void {
  $user = User::factory()->create();
  $list = RestaurantList::factory()->create(['user_id' => $user->id]);
  $place = Place::factory()->create(['restaurant_list_id' => $list->id, 'name' => 'Burger House']);

  $this->actingAs($user);

  Volt::test('restaurant-lists.show', ['restaurantList' => $list])
    ->call('selectWinner', $place->id)
    ->assertSet('pickedPlaceId', $place->id);

  expect($place->visitHistories()->where('user_id', $user->id)->exists())->toBeTrue();
});
